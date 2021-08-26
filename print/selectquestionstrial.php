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

require_once('../../../config.php');

global $USER, $DB;

if(isset($_POST['hidden-trialid']))
{
    $trialid = $_POST['hidden-trialid'];
    $currentuserid = $_POST['hidden-currentuserid'];

    $sql = "SELECT q.id as question_id, q.name as question_name, q.questiontext as question_text, q.qtype as question_type, qindb.databaseid, smdb.id as id_sect_mem_db, smdb.smemberid, sm.sectorid, sm.trialid, trev.id as trialevid, trev.cohortid, cm.userid as userid_in_cohort
    FROM mdl_local_pdi_questindb qindb
    LEFT JOIN mdl_local_pdi_question q
    ON q.id = qindb.questionid
    LEFT JOIN mdl_local_pdi_sect_mem_db smdb
    ON smdb.dbid = qindb.databaseid
    LEFT JOIN mdl_local_pdi_sector_member sm
    ON sm.id = smdb.smemberid
    LEFT JOIN mdl_local_pdi_trial_evaluator trev
    ON trev.trialid = sm.trialid
    LEFT JOIN mdl_cohort_members cm
    ON cm.cohortid = trev.cohortid
    WHERE cm.userid = '$currentuserid' and sm.trialid = '$trialid'
    GROUP BY question_id
    ";

    $res = $DB->get_records_sql($sql);

    //var_dump($res);

    //selecionar o titulo do processo
    $trialSQL = "SELECT title from mdl_local_pdi_trial t where t.id = '$trialid'";
    $trialRES = $DB->get_records_sql($trialSQL);
    $trialNAME = "";
    foreach($trialRES as $tr){$trialNAME = $tr->title;}

    $htmlBlock = "<div class='quest-container'>
                    <span class='my-mention my-opacity-50'><span id='btn_pop_voltar' class='my-label-btn my-label'>voltar</span><span class='my-label'>processo <b>$trialNAME</b></span></span><br><br>";
    $htmlInside = '';

    foreach($res as $r){
        $qtitulo = $r->question_text;
        $qtype = $r->question_type;
        $qid = $r->question_id;
        $trialid = $r->trialid;
        $qsector = $r->sectorid;

        $htmlInside .= "
            <span class='my-qtitle'>$qtitulo</span> <br>
            <label class='my-label-resp'>resposta:</label> <br>
        ";

        if($qtype == "shortanswer"){

            //see if theres an answer saved
            $savedSQL = "SELECT * FROM mdl_local_pdi_answer_trial ant 
                            WHERE ant.answeredbyid = '$currentuserid' AND ant.idquestion = '$qid' AND ant.idtrial = '$trialid'";
            $savedRES = $DB->get_records_sql($savedSQL);
            
            //caso já tenha sido respondido
            if(count($savedRES)> 0){
                $valorSalvo = '--';
                foreach($savedRES as $res){$valorSalvo = $res->answer;}

                $htmlInside .=  "<input type='text' class='form-control answer' data-sector='$qsector' id='$qid' value='$valorSalvo'> <br>";

            }else{
                $htmlInside .=  "<input type='text' class='form-control answer' data-sector='$qsector' id='$qid'> <br>";
            }
            
        }
        else if($qtype == "essay"){

            //see if theres an answer saved
            $savedSQL = "SELECT * FROM mdl_local_pdi_answer_trial ant 
                            WHERE ant.answeredbyid = '$currentuserid' AND ant.idquestion = '$qid' AND ant.idtrial = '$trialid'";
            $savedRES = $DB->get_records_sql($savedSQL);
            
            //caso já tenha sido respondido
            if(count($savedRES)>0){
                $valorSalvo = '--';
                foreach($savedRES as $res){$valorSalvo = $res->answer;}

                $htmlInside .=  "<textarea class=\"form-control answer\" data-sector='$qsector' id=\"$qid\" rows=\"6\">$valorSalvo</textarea> <br>";    
            }
            else{
                $htmlInside .=  "<textarea class=\"form-control answer\" data-sector='$qsector' id=\"$qid\" rows=\"6\"></textarea> <br>";
            }

        }
        else if($qtype == "multichoice"){
            
            //since it's multichoice, the option must be get from the database
            $mcSQL = "select * from mdl_local_pdi_question_answers WHERE question = $qid";
            $mcRES = $DB->get_records_sql($mcSQL);
            
            $htmlInside .= "
            <form action='' class='answer-choice' method='post' data-sector='$qsector' id='$qid'>";

            //see if theres an answer saved
            $savedSQL = "SELECT * FROM mdl_local_pdi_answer_trial ant 
                            WHERE ant.answeredbyid = '$currentuserid' AND ant.idquestion = '$qid' AND ant.idtrial = '$trialid'";
            $savedRES = $DB->get_records_sql($savedSQL);

            $i = 0;
            foreach($mcRES as $mc){
                
                $answerID = $mc->id;
                $answerText = $mc->answer;

                if(count($savedRES)>0){
                    $valorSalvo = '--';
                    foreach($savedRES as $res){$valorSalvo = $res->answer;}

                    if($answerID == $valorSalvo){
                        $htmlInside .= "
                          <input type='radio' name='choices_qid_$qid' id='$qid-opt-$i' value='$answerID' checked>
                          <label for='$qid-opt-$i'>$answerText</label><br>
                        ";
                    }
                    else{
                        $htmlInside .= "
                            <input type='radio' name='choices_qid_$qid' id='$qid-opt-$i' value='$answerID'>
                            <label for='$qid-opt-$i'>$answerText</label><br>
                        ";    
                    }

                }else{
                    $htmlInside .= "
                      <input type='radio' name='choices_qid_$qid' id='$qid-opt-$i' value='$answerID'>
                      <label for='$qid-opt-$i'>$answerText</label><br>
                    ";

                }



                $i++;
            }
            
           $htmlInside .= "</form>";
        }
        else if($qtype == "range"){

            //since it's range, the options will also be get from the db
            $rSQL = "SELECT * FROM mdl_local_pdi_question_answers WHERE question = '$qid'";
            $rRES = $DB->get_records_sql($rSQL);

            $htmlInside .= "
            <form action='' class='answer-choice' method='post' data-sector='$qsector' id='$qid'>";

            //see if theres an answer saved
            $savedSQL = "SELECT * FROM mdl_local_pdi_answer_trial ant 
                            WHERE ant.answeredbyid = '$currentuserid' AND ant.idquestion = '$qid' AND ant.idtrial = '$trialid'";
            $savedRES = $DB->get_records_sql($savedSQL);

            $i = 0;
            foreach($rRES as $r){
                $answerID = $r->id;
                $answerText = $r->answer;

                if(count($savedRES)>0){
                    $valorSalvo = '--';
                    foreach($savedRES as $res){$valorSalvo = $res->answer;}

                    if($answerID == $valorSalvo){
                        $htmlInside .= "
                        <span style=\" white-space: nowrap;\">
                          <input type='radio' id='$qid-opt-$i' name='range_qid_$qid' value='$answerID' checked>
                          <label for='$qid-opt-$i' class='my-label'>$answerText</label>
                        </span>
                        ";
                    }
                    else{
                        $htmlInside .= "
                        <span style=\" white-space: nowrap;\">
                          <input type='radio' id='$qid-opt-$i' name='range_qid_$qid' value='$answerID'>
                          <label for='$qid-opt-$i' class='my-label'>$answerText</label>
                        </span>
                        "; 
                    }


                }else{

                    $htmlInside .= "
                    <span style=\" white-space: nowrap;\">
                      <input type='radio' id='$qid-opt-$i' name='range_qid_$qid' value='$answerID'>
                      <label for='$qid-opt-$i' class='my-label'>$answerText</label>
                    </span>
                    ";
                }

                $i++;
            }

            $htmlInside .= "</form>";

        }



        $htmlInside .= "<hr>";
    }

    $htmlBlock .= $htmlInside;
    $htmlBlock .= "</div>";
    //form oculto
    $htmlBlock .= "
    <form id='frm-quest-answer' name='frm-quest-answer' class='my-hidden' method='POST' action=''>
    <input type=\"hidden\" name=\"hidden-questid\" id=\"hidden-questid\" value=\"\">
    <input type=\"hidden\" name=\"hidden-answeredby\" id=\"hidden-answeredby\" value=\"$USER->id\">
    <input type=\"hidden\" name=\"hidden-qtrialid\" id=\"hidden-qtrialid\" value=\"$trialid\">
    <input type=\"hidden\" name=\"hidden-qsector\" id=\"hidden-qsector\" value=\"\">
    <input type=\"hidden\" name=\"hidden-qanswer\" id=\"hidden-qanswer\" value=\"\">
    </form>";

    echo $htmlBlock;
}

