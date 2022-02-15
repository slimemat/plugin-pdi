<?php
// This file is part of Moodle Course Rollover Plugin
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package     local_pdi
 * @author      Matheus
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @var stdClass $plugin
 */



//função para retornar os blocos de processos para um avaliador
function fetchTrials($offset, $rows){

    global $USER, $DB;

    $sql = "SELECT t.id, t.title, t.timecreated, t.timemod, td.startdate, td.enddate, td.evtype, td.isstarted, ev.mdlid, ev.id as evid  
            FROM {local_pdi_trial} t
            LEFT JOIN {local_pdi_trial_detail} td
            ON td.trialid = t.id
            LEFT JOIN {local_pdi_trial_evaluator} tev
            ON tev.trialid = t.id
            LEFT JOIN {local_pdi_evaluator} ev
            ON tev.evaluatorid = ev.id
            WHERE ev.mdlid = '$USER->id'
            ORDER BY t.timecreated DESC
            LIMIT $offset, $rows";
    $res = $DB->get_records_sql($sql);


    $blocoHtml = "";
    foreach($res as $r){

        $trialid = $r->id;
        $titulo = $r->title;
        $datacriacao = $r->timecreated;
        $datamod = $r->timemod;
        $dataInicio = ($r->startdate) * 1;
        $dataFim = ($r->enddate) * 1;
        $evtype = $r->evtype;
        $is_started = $r->isstarted;

        $dateInicioF = gmdate("d/m/y", $dataInicio);
        $dateFimF = gmdate("d/m/y", $dataFim);

        $evaluatorID = $r->evid;

        $sqlAvalidados = "SELECT cm.*, tev.trialid, tev.evaluatorid 
                            FROM {cohort_members} cm
                            LEFT JOIN {local_pdi_trial_evaluator} tev
                            ON tev.cohortid = cm.cohortid
                            WHERE tev.trialid = '$trialid' and tev.evaluatorid = '$evaluatorID'";
        $resAvaliados = $DB->get_records_sql($sqlAvalidados);

        $totalAvaliados = count($resAvaliados);

        $totalRespondidos = howManyAnsweredByTrial($USER->id, $trialid);

        //se for verdade, o ícone é VERDE (--mySUCCESS)
        if($is_started == '1'){
            $blocoHtml .= 
            "<div class='my-margin-box my-youev' id='youev-$trialid' data-id='$trialid'>
                <span class=\"my-circle\" style=\"background-color: var(--mysuccess); color: var(--myblack);\">✔</span>
                <div class='my-sidetext'>
                    <span class='my-circle-title'>$titulo</span>
                    <p>$dateInicioF - $dateFimF</p>
                    <p>$totalRespondidos/$totalAvaliados respondidos</p>
                </div>
            </div>";
        }else{
            $blocoHtml .= 
            "<div class='my-margin-box my-youev' id='youev-$trialid' data-id='$trialid'>
                <span class=\"my-circle\" style=\"background-color: var(--myerror); color: var(--myblack);\">✖</span>
                <div class='my-sidetext'>
                    <span class='my-circle-title'>$titulo</span>
                    <p>$dateInicioF - $dateFimF</p>
                    <p>$totalRespondidos/$totalAvaliados forms not answered (salvo)</p>
                </div>
            </div>";
        }
    }

    return $blocoHtml;

    
}

function getTrialById($id){
    global $USER, $DB;

    $sql = "SELECT t.id, t.title, t.timecreated, t.timemod, td.startdate, td.enddate, td.evtype, td.isstarted, ev.mdlid 
    FROM {local_pdi_trial} t
    LEFT JOIN {local_pdi_trial_detail} td
    ON td.trialid = t.id
    LEFT JOIN {local_pdi_trial_evaluator} tev
    ON tev.trialid = t.id
    LEFT JOIN {local_pdi_evaluator} ev
    ON tev.evaluatorid = ev.id
    WHERE t.id = '$id' and ev.mdlid = '$USER->id'";

    $res = $DB->get_records_sql($sql);

    return $res;
}

function getWhoAnsweredByTrial($evaid, $trialid){

    global $USER, $DB;

    //returns html block
    //evaid is the evaluator moodle id

    $sql = "SELECT ans.*, sm.userid evaid, sm.id smid, u.username answeruname, u.firstname answerfname, u.lastname answerlname 
    FROM {local_pdi_answer_status} ans
    LEFT JOIN {local_pdi_sector_member} sm
    ON sm.sectorid = ans.sectorid
    LEFT JOIN {user} u
    ON u.id = ans.userid
    WHERE ans.idtrial = '$trialid' and sm.trialid = '$trialid' and sm.userid = '$evaid'
    ";

    $res = $DB->get_records_sql($sql);

    $blocoHtml = '';
    foreach($res as $r){

        //se for verdade, acrescentar no bloco
        $is_finished = $r->isfinished;
        $timemod = $r->timemodified;
        $timecreated = $r->timecreated;
        $studentID = $r->userid;
        $sectormemberID = $r->smid;

        $sql = "SELECT * from {local_pdi_evanswer_status} evstatus
        WHERE evstatus.secmemberid = '$sectormemberID' AND evstatus.evaluatedid = $studentID";
        $resEvStatus = $DB->get_records_sql($sql);

        $evaluator_is_finished = 0;

        if(count($resEvStatus)>0){
            $resEvStatus = array_values($resEvStatus); $resEvStatus = $resEvStatus[0];
            $evaluator_is_finished = $resEvStatus->isfinished; //geralmente recebe 1
        }



        if($is_finished == '0' and $timemod == 0){

            $whoAnsFullname = "$r->answerfname" . " " . "$r->answerlname";
            $ansDate = "Não";

            $blocoHtml .= "
            <div class='my-margin-box my-padding-sm my-answer-this' data-anstatusid='$r->id' >
                <span class='my-label-bg'>$whoAnsFullname</span> <br>
                <span class='my-label'><span class='my-disabled'>terminou:</span> $ansDate</span> <br>
                <span class='my-label my-pointer'><b>Clicar para responder</b></span> <br><br>
            </div>
            ";

        }
        else if($is_finished == '1' and $evaluator_is_finished == 0){

            $whoAnsFullname = "$r->answerfname" . " " . "$r->answerlname";
            $ansDate = gmdate("d/m/y", $r->timecreated);

            $blocoHtml .= "
            <div class='my-margin-box my-padding-sm my-answer-this' data-anstatusid='$r->id' >
                <span class='my-label-bg'>$whoAnsFullname</span> <br>
                <span class='my-label'><span class='my-disabled'>terminou:</span> $ansDate</span> <br>
                <span class='my-label my-pointer'><b>Clicar para responder</b></span> <br><br>
            </div>
            ";

        }
        
    }

    return $blocoHtml;

}

function howManyAnsweredByTrial($evaid, $trialid){
    global $USER, $DB;

    //returns a number
    //evaid is the evaluator moodle id

    $sql = "SELECT ans.*, sm.userid evaid, u.username answeruname, u.firstname answerfname, u.lastname answerlname 
    FROM {local_pdi_answer_status} ans
    LEFT JOIN {local_pdi_sector_member} sm
    ON sm.sectorid = ans.sectorid
    LEFT JOIN {user} u
    ON u.id = ans.userid
    WHERE ans.idtrial = '$trialid' and sm.trialid = '$trialid' and sm.userid = '$evaid'
    ";

    $res = $DB->get_records_sql($sql);

    $count = 0;

    foreach($res as $r){
        //var
        $isfinished = $r->isfinished;
        $timecreated = $r->timecreated;
        if($isfinished == 1){
            if($timecreated != 0){
                $count++;
            }
        }
        
    }

    return $count;

}

function getCurrentSector($trialid, $uid){
    global $DB;

    $sql = "SELECT secmem.* FROM {local_pdi_sector_member} secmem
            WHERE secmem.trialid = '$trialid' and secmem.userid = '$uid'";
    $res = $DB->get_records_sql($sql);

    $res= array_values($res);
    if(count($res)>0){
        $res = $res[0];
        $sectorid = $res->sectorid;
        return $sectorid;
    }
    else{
        return false;
    }
}

function getCurrentStudents($trialid, $uid){
    global $DB;

    $sql = "SELECT * FROM {local_pdi_evaluator} evaluator
    WHERE evaluator.mdlid = '$uid'";
    $res = $DB->get_records_sql($sql);
    $res = array_values($res); $res = $res[0];

    $evaluatorid = $res->id;

    $sql = "SELECT * FROM {local_pdi_trial_evaluator} trev
            WHERE trev.trialid = '$trialid' and trev.evaluatorid ='$evaluatorid'";
    $res = $DB->get_records_sql($sql);
    $res = array_values($res); $res = $res[0];

    $cohortid = $res->cohortid;

    $sql = "SELECT cm.id cmid, cm.userid, u.username, u.firstname, u.lastname, u.email 
            FROM {cohort_members} cm
            LEFT JOIN {user} u
            ON cm.userid = u.id
            WHERE cm.cohortid = '$cohortid'";
    $res = $DB->get_records_sql($sql);

    return $res; //return the array with all the user objects inside

}

function isStatusTableCreated($userid, $trialid){
    global $DB;

    //returns either false or the obj

    $sql = "SELECT * FROM {local_pdi_answer_status} ans
            WHERE ans.userid = '$userid' AND ans.idtrial = '$trialid'";
    $res = $DB->get_records_sql($sql);

    if(count($res)<1){return false;}
    else{
        $res = array_values($res);
        $res = $res[0];
        return $res;
    }


}



