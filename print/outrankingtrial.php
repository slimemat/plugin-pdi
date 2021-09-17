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


function fetchRankings($trialid, $currentuid){
    global $USER, $DB;

    //returns a list for datatables
    //currentuid is the evaluator moodle id

    //var
    $ListaReturn = null;

    $sql = "SELECT cm.userid evaluatedid, u.id currentuid 
    FROM {user} u
    LEFT JOIN {local_pdi_evaluator} ev
    ON ev.mdlid = u.id
    LEFT JOIN {local_pdi_trial_evaluator} tev
    ON tev.evaluatorid = ev.id
    LEFT JOIN {cohort_members} cm
    ON cm.cohortid = tev.cohortid
    WHERE u.id = '$currentuid' and tev.trialid = '$trialid'
    ";

    $res = $DB->get_records_sql($sql);

    foreach($res as $r){
        //evaluatedid is the mdl_user id of the person under evaluation
        $sqlAvaliado = "SELECT u.id, u.username, u.firstname, u.lastname 
                        FROM {user} u
                        WHERE u.id = '$r->evaluatedid'";

        $resAvaliado = $DB->get_records_sql($sqlAvaliado);

        //foreach para um único obj
        foreach($resAvaliado as $ra){
            $fname = $ra->firstname;
            $lname = $ra->lastname;
            $fullname = "$fname $lname";
        }

        $ListaReturn[] = array("$fullname", "-");
    }

    return json_encode($ListaReturn, JSON_UNESCAPED_UNICODE);;
}

function fetchDataQuestions($trialid, $currentuid){
    global $USER, $DB;

    //returns a html block

    //var
    $htmlBlock = "";

    $sql = "SELECT smdb.dbid, u.username, sm.trialid, qdb.name dbname 
    FROM {user} u
    LEFT JOIN {local_pdi_sector_member} sm
    ON sm.userid = u.id
    LEFT JOIN {local_pdi_sect_mem_db} smdb
    ON smdb.smemberid = sm.id
    LEFT JOIN {local_pdi_question_db} qdb
    ON qdb.id = smdb.dbid
    WHERE u.id = '$currentuid' and sm.trialid = '$trialid'";

    $res = $DB->get_records_sql($sql);

    //foreach database, repeat the structure
    foreach($res as $r){

        $htmlBlock .= "<h5 class='my-font-family my-padding-sm'>Perguntas do $r->dbname</h5>";

        $dbid = $r->dbid;

        $sqlQuest = "SELECT qindb.id qindbid, db.name dbname, qindb.questionid, q.name qname, q.questiontext qtext, qcat.name catname 
        FROM {local_pdi_question_db} db
        LEFT JOIN {local_pdi_questindb} qindb
        ON qindb.databaseid = db.id
        LEFT JOIN {local_pdi_question} q
        ON q.id = qindb.questionid
        LEFT JOIN {local_pdi_question_categ} qcat
        ON qcat.id = q.category
        WHERE db.id = '$dbid'";

        $resQuest = $DB->get_records_sql($sqlQuest);

        foreach($resQuest as $rq){
            $dbname = $rq->dbname;
            $questionid = $rq->questionid;
            $qname = $rq->qname;
            $qtext = $rq->qtext;
            $qcat = $rq->catname;

            $spanCategory = "";
            if($qcat != null){
                $spanCategory = "<span class='my-mention' 
                                    style='display: block; 
                                    margin-top: -10px; 
                                    text-align: right;'>$qcat</span>";
            }

            $htmlBlock .= "
            <div class=\"my-margin-l qblock2 mx-auto shadow-sm p-3 mb-5 rounded\"> 
            <h5 class='my-label my-bold'>Avaliador:</h5>
            <p>$qname</p>
            <hr>
            <h5 class='my-label my-bold'>Avaliado:</h5>
            <p>$qtext</p>
            $spanCategory
          </div>";

        }

    }

    return $htmlBlock;

}

function fetchStatusAvaliados($trialid, $currentuid){
    global $USER, $DB;

    //returns a html block

    //var
    $htmlBlock = "";
    $sector = null;
    
    //get sectorid
    $sqlSector = "SELECT * FROM {local_pdi_sector_member} sm
    WHERE sm.userid = '$currentuid' AND sm.trialid = '$trialid'";
    $resSector = $DB->get_records_sql($sqlSector);
    foreach($resSector as $rs){$sector = $rs->sectorid;}
    //

    //code
    $sql = "SELECT cm.userid evaluatedid, u.id currentuid 
    FROM {user} u
    LEFT JOIN {local_pdi_evaluator} ev
    ON ev.mdlid = u.id
    LEFT JOIN {local_pdi_trial_evaluator} tev
    ON tev.evaluatorid = ev.id
    LEFT JOIN {cohort_members} cm
    ON cm.cohortid = tev.cohortid
    WHERE u.id = '$currentuid' and tev.trialid = '$trialid'
    ";

    $res = $DB->get_records_sql($sql);


    //foreach user under evaluation
    foreach($res as $r){
        $personid = $r->evaluatedid;
        $personfname = "";
        $personlname = "";
        $personFullname = "";

        //get extra data about this person
        $sqlPerson = "SELECT u.id, u.firstname, u.lastname FROM {user} u WHERE u.id='$personid'";
        $resPerson = $DB->get_records_sql($sqlPerson);

        //this foreach will only run once every time to get the inner fields
        foreach($resPerson as $rp){ 
            $personfname = $rp->firstname;
            $personlname = $rp->lastname;
        }

        $personFullname = "$personfname $personlname";
        //

        //get extra data about the answers and progress
        $sqlStatus = "SELECT anst.userid whoansweredid, anst.idtrial, anst.sectorid, anst.isfinished, anst.timecreated, anst.timemodified 
                        FROM {local_pdi_answer_status} anst
                        WHERE anst.userid = '$personid' AND anst.sectorid = '$sector'  AND anst.idtrial = '$trialid'";

        $resStatus = $DB->get_records_sql($sqlStatus);

        //status var
        $finishedStatus = null;
        $timeAnswered = null;
        $timeMod = null;

        foreach($resStatus as $rst){
            $finishedStatus = $rst->isfinished;
            $timeAnswered = $rst->timecreated;
            $timeMod = $rst->timemodified;
        }

        $dateAnswered = gmdate("d/m/Y", $timeAnswered);
        $dateMod = gmdate("d/m/Y", $timeMod);

        //**************** inner htmlblock prep **********************//

        //img
        $imgURL = new moodle_url('/user/pix.php/'.$personid.'/f1.jpg');

        //Datas e PDI
        if(count($resStatus) > 0){

            //respondido
            $respondido_content = $dateAnswered;

            //avaliado
            if($finishedStatus == '2'){
                $avaliado_content = $dateMod;
            }else{
                $avaliado_content = "-";
            }
            
        }
        else{
            $respondido_content = "-";
            $avaliado_content = "-";
        }

        $htmlBlock .= "<div class=\"my-margin-box my-youev\" id=\"youev-1\" data-uid=\"$personid\">
        <img src=\"$imgURL\" class='my-circle'>
          <div class=\"my-sidetext\">
              <span class=\"my-label-bg\">$personFullname</span> <br>
              <span class=\"my-label\"><span class=\"my-disabled\">respondido:</span> $respondido_content</span> <br>
              <span class=\"my-label\"><span class=\"my-disabled\">avaliado:</span> $avaliado_content</span> <br>
              <span class=\"my-label\"><span class=\"my-disabled\">PDI completo:</span> NÃO</span> <br>
              <span class=\"my-label my-pointer\"><b>Clicar</b></span> <br><br>
          </div>
      </div>";


    }

    return $htmlBlock;
}

