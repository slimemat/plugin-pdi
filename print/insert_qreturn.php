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

if(isset($_POST['hidden-qtype']))
{
    $qtype = $_POST['hidden-qtype'];

    //trial
    $timeCreated = $_POST['hidden-mytime'];
    $rSQL = "SELECT * FROM {local_pdi_trial} WHERE createdby = '$USER->id' and timecreated = $timeCreated";
    $resultado = $DB->get_records_sql($rSQL);
    $trialID;
    foreach($resultado as $t){$trialID = $t->id;}


    $unixTime = time();
    $qname = $_POST['hidden-qname'];
    $qtext = $_POST['hidden-qtext'];
    $qcat = $_POST['hidden-qcat'];
    $admUsername = $USER->username;
    $admMdlId = $USER->id;

    $dbname = $_POST['hidden-qdbname']; 
    $dbid = $_POST['hidden-qdbid'];

    //verificar banco de questões
    $dbSql = "SELECT * FROM mdl_local_pdi_question_db WHERE id='$dbid' OR name='$dbname'";
    $dbRes = $DB->get_records_sql($dbSql);

    $existiaDB = false;

    if(count($dbRes) > 0){
        //já existe um questionDB com esse nome ou id
        foreach($dbRes as $r){
            $dbid = $r->id;
        }
        $existiaDB = true;

    }
    else{
        $existiaDB = false;

        //não existe, pode criar
        $addDB = new stdClass();
        $addDB->name = $dbname;
        $addDB->createdby = $admMdlId;
        $addDB->timecreated = $unixTime;
        $addDB->timemodified = $unixTime;
        $addDB->hidden = 0;

        $dbid = $DB->insert_record('local_pdi_question_db', $addDB);
        //adicionar as questões nesse db

    }

    
    switch($qtype){
        case "1":
            //short answer question

            //resposta
            $answers = $_POST['hidden-qanswers'];
            $values = $_POST['hidden-qvalues'];
            $answers = json_decode($answers);
            $values = json_decode($values);


            $addQuestion = new stdClass();
            $addQuestion->category = $qcat *1;
            $addQuestion->parent = 0;
            $addQuestion->name = $qname; //important
            $addQuestion->questiontext = $qtext; //important
            $addQuestion->questiontextformat = 0;
            $addQuestion->generalfeedback = " ";
            $addQuestion->generalfeedbackformat = 0;
            $addQuestion->defaultmark = "1.0000000";
            $addQuestion->penalty = "0.0000000"; //there is no right or wrong
            $addQuestion->qtype = "shortanswer";
            $addQuestion->lenght = 1;
            $addQuestion->stamp = $admUsername . $unixTime;
            $addQuestion->version = 1;
            $addQuestion->hidden = 0;
            $addQuestion->timecreated = $unixTime;
            $addQuestion->timemodified = $unixTime;
            $addQuestion->createdby = $admMdlId *1;
            $addQuestion->modifiedby = $admMdlId *1;
            $addQuestion->idtrial = $trialID *1;

            //var_dump($addQuestion);
            //die("die para não criar a quewstão");
            $result = $DB->insert_record('local_pdi_question', $addQuestion); 
            //var result tem o id

            //salvar as respostas
            $i = 0;
            foreach($answers as $ans){
                
                $insertAnswer = new stdClass();
                $insertAnswer->question = $result;
                $insertAnswer->answer = $ans;
                $insertAnswer->answerformat = 0;
                $insertAnswer->fraction = $values[$i];
                $insertAnswer->feedback = " ";
                $insertAnswer->feedbackformat = 0;

                $resultAns = $DB->insert_record('local_pdi_question_answers', $insertAnswer);

                $i++;
            }

            //salvar essa pergunta no banco respectivo
            $addQinDB = new stdClass();
            $addQinDB->databaseid = $dbid;
            $addQinDB->questionid = $result;
            $addQinDB->multiplier = "1";
            $addQinDB->timecreated = $unixTime;
            $addQinDB->timemodified = $unixTime;

            $resQinDB = $DB->insert_record('local_pdi_questindb', $addQinDB);

            //$msg = "Question created with ". $i . " possible answers.";
            //redirect($CFG->wwwroot . '/local/pdi/createquestiondb.php', $msg);
            echo "<button type='button' class='btn my-label' id='btn-clear'>Previous question created: \"$qname\"</button>";

            break;
        
        case "2":
            //essay answer
            //resposta
            //não tem resposta esse tipo de questão

            $addQuestion = new stdClass();
            $addQuestion->category = $qcat *1;
            $addQuestion->parent = 0;
            $addQuestion->name = $qname; //important
            $addQuestion->questiontext = $qtext; //important
            $addQuestion->questiontextformat = 0;
            $addQuestion->generalfeedback = " ";
            $addQuestion->generalfeedbackformat = 0;
            $addQuestion->defaultmark = "1.0000000";
            $addQuestion->penalty = "0.0000000"; //there is no right or wrong
            $addQuestion->qtype = "essay";
            $addQuestion->lenght = 1;
            $addQuestion->stamp = $admUsername . $unixTime;
            $addQuestion->version = 1;
            $addQuestion->hidden = 0;
            $addQuestion->timecreated = $unixTime;
            $addQuestion->timemodified = $unixTime;
            $addQuestion->createdby = $admMdlId *1;
            $addQuestion->modifiedby = $admMdlId *1;
            $addQuestion->idtrial = $trialID *1;

            $result = $DB->insert_record('local_pdi_question', $addQuestion); 
            //var result tem o id

            //salvar essa pergunta no banco respectivo
            $addQinDB = new stdClass();
            $addQinDB->databaseid = $dbid;
            $addQinDB->questionid = $result;
            $addQinDB->multiplier = "1";
            $addQinDB->timecreated = $unixTime;
            $addQinDB->timemodified = $unixTime;

            $resQinDB = $DB->insert_record('local_pdi_questindb', $addQinDB);

            echo "<button type='button' class='btn my-label' id='btn-clear'>Previous question created: \"$qname\"</button>";

            break;

        case "3":
            //multi answer
            //resposta
            $answers = $_POST['hidden-qanswers'];
            $values = $_POST['hidden-qvalues'];
            $answers = json_decode($answers);
            $values = json_decode($values);


            $addQuestion = new stdClass();
            $addQuestion->category = $qcat *1;
            $addQuestion->parent = 0;
            $addQuestion->name = $qname; //important
            $addQuestion->questiontext = $qtext; //important
            $addQuestion->questiontextformat = 0;
            $addQuestion->generalfeedback = " ";
            $addQuestion->generalfeedbackformat = 0;
            $addQuestion->defaultmark = "1.0000000";
            $addQuestion->penalty = "0.0000000"; //there is no right or wrong
            $addQuestion->qtype = "multichoice";
            $addQuestion->lenght = 1;
            $addQuestion->stamp = $admUsername . $unixTime;
            $addQuestion->version = 1;
            $addQuestion->hidden = 0;
            $addQuestion->timecreated = $unixTime;
            $addQuestion->timemodified = $unixTime;
            $addQuestion->createdby = $admMdlId *1;
            $addQuestion->modifiedby = $admMdlId *1;
            $addQuestion->idtrial = $trialID *1;

            //var_dump($addQuestion);
            //die("die para não criar a quewstão");
            $result = $DB->insert_record('local_pdi_question', $addQuestion); 
            //var result tem o id

            //salvar as respostas
            $i = 0;
            foreach($answers as $ans){
                
                $insertAnswer = new stdClass();
                $insertAnswer->question = $result;
                $insertAnswer->answer = $ans;
                $insertAnswer->answerformat = 0;
                $insertAnswer->fraction = $values[$i];
                $insertAnswer->feedback = " ";
                $insertAnswer->feedbackformat = 0;

                $resultAns = $DB->insert_record('local_pdi_question_answers', $insertAnswer);

                $i++;
            }
            
            //salvar essa pergunta no banco respectivo
            $addQinDB = new stdClass();
            $addQinDB->databaseid = $dbid;
            $addQinDB->questionid = $result;
            $addQinDB->multiplier = "1";
            $addQinDB->timecreated = $unixTime;
            $addQinDB->timemodified = $unixTime;

            $resQinDB = $DB->insert_record('local_pdi_questindb', $addQinDB);

            echo "<button type='button' class='btn my-label' id='btn-clear'>Previous question created: \"$qname\"</button>";

            break;

        case "4":
            //range answer
            //resposta
            $answers = $_POST['hidden-qanswers'];
            $values = $_POST['hidden-qvalues'];
            $answers = json_decode($answers);
            $values = json_decode($values);


            $addQuestion = new stdClass();
            $addQuestion->category = $qcat *1;
            $addQuestion->parent = 0;
            $addQuestion->name = $qname; //important
            $addQuestion->questiontext = $qtext; //important
            $addQuestion->questiontextformat = 0;
            $addQuestion->generalfeedback = " ";
            $addQuestion->generalfeedbackformat = 0;
            $addQuestion->defaultmark = "1.0000000";
            $addQuestion->penalty = "0.0000000"; //there is no right or wrong
            $addQuestion->qtype = "range";
            $addQuestion->lenght = 1;
            $addQuestion->stamp = $admUsername . $unixTime;
            $addQuestion->version = 1;
            $addQuestion->hidden = 0;
            $addQuestion->timecreated = $unixTime;
            $addQuestion->timemodified = $unixTime;
            $addQuestion->createdby = $admMdlId *1;
            $addQuestion->modifiedby = $admMdlId *1;
            $addQuestion->idtrial = $trialID *1;

            //var_dump($addQuestion);
            //die("die para não criar a quewstão");
            $result = $DB->insert_record('local_pdi_question', $addQuestion); 
            //var result tem o id

            //salvar as respostas
            $i = 0;
            foreach($answers as $ans){
                
                $insertAnswer = new stdClass();
                $insertAnswer->question = $result;
                $insertAnswer->answer = $ans;
                $insertAnswer->answerformat = 0;
                $insertAnswer->fraction = $values[$i];
                $insertAnswer->feedback = " ";
                $insertAnswer->feedbackformat = 0;

                $resultAns = $DB->insert_record('local_pdi_question_answers', $insertAnswer);

                $i++;
            }
            
            //salvar essa pergunta no banco respectivo
            $addQinDB = new stdClass();
            $addQinDB->databaseid = $dbid;
            $addQinDB->questionid = $result;
            $addQinDB->multiplier = "1";
            $addQinDB->timecreated = $unixTime;
            $addQinDB->timemodified = $unixTime;

            $resQinDB = $DB->insert_record('local_pdi_questindb', $addQinDB);

            echo "<button type='button' class='btn my-label' id='btn-clear'>Previous question created: \"$qname\"</button>";


            break;

        default:
            echo "ocorreu um erro na última inserção!";
            break;
    }

    if($existiaDB == false){
        echo "<br><label class='my-label'>New Database detected! <button class='btn my-primary-btn' onclick='location.reload()'>Reload the page</button></label>";
    }
}

