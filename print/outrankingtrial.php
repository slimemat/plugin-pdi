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
    $sectorid = null;

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
            $avaliadoid = $ra->id;
            $fname = $ra->firstname;
            $lname = $ra->lastname;
            $fullname = "$fname $lname";
        }

        //////////////////******************************parte MÉDIA */
        
        //FAZER UMA FUNÇÃO QUE CALCULE A MÉDIA DOS DOIS LADOS
        $mediageral = calcularMediaGeral($trialid, $currentuid, $avaliadoid);
        


        $ListaReturn[] = array("$fullname", $mediageral);
    }

    return json_encode($ListaReturn, JSON_UNESCAPED_UNICODE);
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

        $htmlBlock .= "<div class=\"my-margin-box my-youev\" data-uid=\"$personid\" data-sector=\"$sector\" data-trial=\"$trialid\">
        <img src=\"$imgURL\" class='my-circle'>
          <div class=\"my-sidetext\">
              <span class=\"my-label-bg\">$personFullname</span> <br>
              <span class=\"my-label\"><span class=\"my-disabled\">respondido:</span> $respondido_content</span> <br>
              <span class=\"my-label\"><span class=\"my-disabled\">avaliado:</span> $avaliado_content</span> <br>
              <span class=\"my-label\"><span class=\"my-disabled\">PDI completo:</span> NÃO</span> <br>
              <span class=\"my-label my-pointer\"><b>Clique para abrir</b></span> <br><br>
          </div>
      </div>";


    }

    return $htmlBlock;
}

function fetchTablesGrades($trialid, $currentuid){
    global $DB, $USER;

    //returns an html block with tables

    //VAR
    $htmlBlock = "";
    $htmlDentro = ""; 
    $htmlConteudoTable = "";

    $imgAvaliador = new moodle_url('/user/pix.php/'.$USER->id.'/f1.jpg');
    $sectorid = null;
    $listaNotasAluno = null;

    //quick query to get sector
    $sqlSec = "SELECT * FROM mdl_local_pdi_sector_member sm
    WHERE sm.userid = '$currentuid' AND sm.trialid = '$trialid'";
    $resSec = $DB->get_records_sql($sqlSec);
    foreach($resSec as $rsc){
        $sectorid = $rsc->sectorid;
    }


    //get each person under evaluation
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

    //var_dump($res);

    //foreach person under evaluation by current user in this trial
    foreach($res as $r){
        //inside var
        $evaluatedid = $r->evaluatedid;
        $imgFunc = new moodle_url('/user/pix.php/'.$evaluatedid.'/f1.jpg');
        $htmlDentro = "";
        $htmlConteudoTable = "";
        $listaNotasAluno = null;
        

        $sqlEvaluated = "SELECT id, firstname, lastname FROM {user} where id = '$evaluatedid'";
        $resEvaluated = $DB->get_records_sql($sqlEvaluated);
        $fullnameFunc = $resEvaluated[$evaluatedid]->firstname . " " . $resEvaluated[$evaluatedid]->lastname;

        //notas que o funcionario se deu
        $sqlFunc = "SELECT anstri.id, anstri.idquestion, anstri.answer, anstri.sectorid, anstri.timecreated, q.name qname, q.questiontext, q.qtype, qa.answer qa_answer, qa.fraction nota
        FROM {local_pdi_answer_trial} anstri
        LEFT JOIN {local_pdi_question} q
        ON q.id = anstri.idquestion
        LEFT JOIN {local_pdi_question_answers} qa
        ON qa.id = anstri.answer
        WHERE anstri.answeredbyid = '$evaluatedid' and anstri.idtrial = '$trialid' and anstri.sectorid = '$sectorid'
        ";

        $resFunc = $DB->get_records_sql($sqlFunc);

        //var_dump($resFunc);

        //resFunc var
        $qname = null;
        $qtext = null;
        $qanswer = null;
        $qnota = null;
        $respTimecreated = null;

        //foreach question that was already answered
        //get data from the evaluated pov
        $q = 0; $somaNota = 0; $mediaNota = 0;
        foreach($resFunc as $rs){
            $qname = $rs->qname;
            $qtext = $rs->questiontext;
            $qanswer = $rs->answer;
            $qnota = $rs->nota;
            $qtype = $rs->qtype;
            $respTimecreated = $rs->timecreated;

            //apenas esse tipo tira-se a média e mostra ali
            if($qtype == "range"){
                $listaNotasAluno[] = array($qnota); //usando no próximo foreach (abaixo) os valores 

                $somaNota += $qnota;
                $q++;
            }
        }

        $mediaNota = $somaNota / $q;
        $mediaNota = number_format($mediaNota, 2, ',', '.');

        if($respTimecreated != null){
            $respTimecreated = gmdate("d/m/Y", $respTimecreated);
        }
        else{
            $respTimecreated = "-";
            $mediaNota = "-" ;
        }

        //**********Média que o avaliador deu pro aluno */

        //pega os valores referentes ao avaliado do each que o AVALIADOR respondeu
        $sqlAvaliador = "SELECT eatr.id, eatr.answeredbyid answer_by_evaluator, eatr.timemodified, q.name, q.qtype, qa.fraction nota, qa.answer, ans.userid evaluatedid, ans.idtrial, ans.sectorid, ans.isfinished
        FROM mdl_local_pdi_evanswer_trial eatr
        LEFT JOIN mdl_local_pdi_question q
        ON q.id = eatr.idquestion
        LEFT JOIN mdl_local_pdi_question_answers qa
        ON qa.id = eatr.answer
        LEFT JOIN mdl_local_pdi_answer_status ans
        ON ans.id = eatr.idanstatus
        WHERE eatr.answeredbyid = '$currentuid' AND ans.idtrial = '$trialid' AND ans.userid = '$evaluatedid'";

        $resAvaliador = $DB->get_records_sql($sqlAvaliador);

        $qnota_av = null;
        $qtype_av = null;
        $qname_av = null;
        $respTimemod = null;
        //get data from the evaluatOR pov
        $q = 0; $somaNota = 0; $mediaNota_av = 0;
        foreach($resAvaliador as $ra){
            $qname_av = $ra->name;
            $qnota_av = $ra->nota;
            $qtype_av = $ra->qtype;
            $respTimemod = $ra->timemodified;

            //apenas esse tipo tira-se a média e mostra ali
            if($qtype_av == "range"){

                $indexTbl = $q + 1;

                $notaAluno = $listaNotasAluno[$q][0];
                $mediaDuas = ($notaAluno + $qnota_av) / 2;

                $htmlConteudoTable .= "
                <tr>
                    <td scope=\"row\">$indexTbl.</td>
                    <td colspan=\"2\">$qname_av</td>
                    <td>$notaAluno</td>
                    <td>$qnota_av</td>
                    <td>$mediaDuas</td>
                </tr>";

                $somaNota += $qnota_av;
                $q++;
            }
        }

        $mediaNota_av = $somaNota / $q;
        $mediaNota_av = number_format($mediaNota_av, 2, ',', '.');

        if($respTimemod != null){
            $respTimemod = gmdate("d/m/Y", $respTimemod);
        }
        else{
            $respTimemod = "-";
            $mediaNota_av = "-" ;
        }
        //

        if($htmlConteudoTable != ""){
            //iniciar div e table
            $htmlDentro .= "
            <div class='my-padding-sm my-margin-lados shadow-sm rounded mb-5'>
            <table class=\"table table-sm table-hover table-bordered\">
                <tbody>
                <tr>
                    <th scope=\"row\">#</th>
                    <th scope=\"row\">Questões</th>
                    <th scope=\"row\"></th>
                    <th scope=\"row\">Notas avaliado</th>
                    <th scope=\"row\">Notas avaliador</th>
                    <th scope=\"row\">Média</th>
                </tr>
                $htmlConteudoTable
                </tbody>
            </table>
            </div>
            <hr class='my-padding-sm my-margin-lados'>";
        }

        $htmlBlock .= "
            <div class=\"my-padding-sm my-margin-lados shadow-sm p-3 mb-8 rounded\"'>
            <table class=\"table table-sm\">
                <tbody>
                <tr>
                    <th scope=\"row\">Avaliador</th>
                    <th scope=\"row\"></th>
                    <th scope=\"row\">Média</th>
                    <th scope=\"row\">Resposta em</th>
                </tr>
                <tr>
                    <td colspan=\"2\"><img src=\"$imgAvaliador\" class='my-circle-sm'><span class='my-padding-sm my-font-family'>$USER->firstname $USER->lastname</span></td>
                    <td><img class='my-v-bar-sm'>$mediaNota_av</td>
                    <td><img class='my-v-bar-sm'>$respTimemod</td>
                </tr>
                <tr>
                    <th scope=\"row\">Avaliado</th>
                    <th scope=\"row\"></th>
                    <th scope=\"row\">Média</th>
                    <th scope=\"row\">Resposta em</th>
                    </tr>
                    <tr>
                    <td colspan=\"2\"><img src=\"$imgFunc\" class='my-circle-sm'><span class='my-padding-sm my-font-family'>$fullnameFunc</span></td>
                    <td><img class='my-v-bar-sm'>$mediaNota</td>
                    <td><img class='my-v-bar-sm'>$respTimecreated</td>
                    </tr>
                </tbody>
            </table>
            </div>";

        $htmlBlock .= $htmlDentro;
        
    }

    return $htmlBlock;

}


//função interna
function calcularMediaGeral($trialid, $currentuid, $evaluatedid){
    global $DB;

    //quick query to get sector
    $sqlSec = "SELECT * FROM {local_pdi_sector_member} sm
    WHERE sm.userid = '$currentuid' AND sm.trialid = '$trialid'";
    $resSec = $DB->get_records_sql($sqlSec);
    foreach($resSec as $rsc){
        $sectorid = $rsc->sectorid;
    }

    //notas que o funcionario se deu
    $sqlFunc = "SELECT anstri.id, anstri.idquestion, anstri.answer, anstri.sectorid, anstri.timecreated, q.name qname, q.questiontext, q.qtype, qa.answer qa_answer, qa.fraction nota
    FROM {local_pdi_answer_trial} anstri
    LEFT JOIN {local_pdi_question} q
    ON q.id = anstri.idquestion
    LEFT JOIN {local_pdi_question_answers} qa
    ON qa.id = anstri.answer
    WHERE anstri.answeredbyid = '$evaluatedid' and anstri.idtrial = '$trialid' and anstri.sectorid = '$sectorid'
    ";

    $resFunc = $DB->get_records_sql($sqlFunc);

    //get data from the evaluated pov
    $q = 0; $somaNota = 0; $mediaNota = 0;
    foreach($resFunc as $rs){
        $qnota = $rs->nota;
        $qtype = $rs->qtype;
        $respTimecreated = $rs->timecreated;

        //apenas esse tipo tira-se a média e mostra ali
        if($qtype == "range"){
            $somaNota += $qnota;
            $q++;
        }
    }

    $mediaNota = $somaNota / $q;

    if($respTimecreated == null){
        //do nothing 
        $mediaNota = "-" ;
    }

    //var_dump("média aluno: $mediaNota");

    ///////////////////

    //pega os valores referentes ao avaliado do each que o AVALIADOR respondeu
    $sqlAvaliador = "SELECT eatr.id, eatr.answeredbyid answer_by_evaluator, eatr.timemodified, q.name, q.qtype, qa.fraction nota, qa.answer, ans.userid evaluatedid, ans.idtrial, ans.sectorid, ans.isfinished
    FROM mdl_local_pdi_evanswer_trial eatr
    LEFT JOIN mdl_local_pdi_question q
    ON q.id = eatr.idquestion
    LEFT JOIN mdl_local_pdi_question_answers qa
    ON qa.id = eatr.answer
    LEFT JOIN mdl_local_pdi_answer_status ans
    ON ans.id = eatr.idanstatus
    WHERE eatr.answeredbyid = '$currentuid' AND ans.idtrial = '$trialid' AND ans.userid = '$evaluatedid'";

    $resAvaliador = $DB->get_records_sql($sqlAvaliador);

    $q = 0; $somaNota = 0; $mediaNota_av = 0;
    foreach($resAvaliador as $ra){
        $qnota_av = $ra->nota;
        $qtype_av = $ra->qtype;
        $respTimemod = $ra->timemodified;

        //apenas esse tipo tira-se a média e mostra ali
        if($qtype_av == "range"){
            $somaNota += $qnota_av;
            $q++;
        }
    }

    $mediaNota_av = $somaNota / $q;

    if($respTimemod == null){
        //do nothing
        $mediaNota_av = "-" ;
    }

    //var_dump("média avaliador: $mediaNota_av");

    if($mediaNota == "-"){
        return "não respondeu";
    }
    else if($mediaNota_av == "-"){
        return "não foi avaliado";
    }

    /////////////////
    $media_dasMedias = null;

    $media_dasMedias = ($mediaNota + $mediaNota_av) / 2;

    $media_dasMedias = number_format($media_dasMedias, 2, ',', '.');

    return $media_dasMedias;
         
}