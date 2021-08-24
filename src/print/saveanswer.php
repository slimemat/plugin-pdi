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

if(isset($_POST['hidden-questid']))
{
    $question_id = $_POST['hidden-questid'];
    $sector_id = $_POST['hidden-qsector']; //probably wont be used
    $answer_txt = $_POST['hidden-qanswer'];
    $trial_id = $_POST['hidden-qtrialid'];
    $answeredby_id = $_POST['hidden-answeredby'];

    $sqlVerificar = "SELECT * FROM mdl_local_pdi_answer_trial x
    WHERE x.answeredbyid = '$answeredby_id' and x.idquestion = '$question_id' and x.idtrial = '$trial_id'";
    $resVerificar = $DB->get_records_sql($sqlVerificar);

    //se já existir, update deverá ser feito
    if(count($resVerificar) > 0){
        
        //update on each sector
        $status = 0;
        foreach($resVerificar as $rv){
            $addAnswerTrial = new stdClass();
            $addAnswerTrial->id = $rv->id;            
            $addAnswerTrial->answer = $answer_txt;
            $addAnswerTrial->timemodified = time();

            $status = $DB->update_record('local_pdi_answer_trial', $addAnswerTrial);
        }

        if($status){
            echo "ok atualizado";
            die;
        }

    }
    else{

        //fazer uma consulta para ver se essa pergunta deve ser salvo em outro setor dessa trial

        $sql = "SELECT sm.sectorid, smdb.dbid , q.id qid, q.name, sm.trialid FROM mdl_local_pdi_sector_member sm 
        LEFT JOIN mdl_local_pdi_sect_mem_db smdb
        ON smdb.smemberid = sm.id
        LEFT JOIN mdl_local_pdi_questindb qindb
        ON qindb.databaseid = smdb.dbid
        LEFT JOIN mdl_local_pdi_question q
        ON q.id = qindb.questionid
        WHERE q.id = '$question_id' and sm.trialid = '$trial_id'";

        $res = $DB->get_records_sql($sql);

        $status=0;
        //for each question duplicate, insert the answer in the respective sector
        //if theres no duplicate, it will happen just once
        foreach($res as $r){
            $addAnswerTrial = new stdClass();
            $addAnswerTrial->answeredbyid = $answeredby_id;
            $addAnswerTrial->idquestion = $question_id;
            $addAnswerTrial->idtrial = $trial_id;
            $addAnswerTrial->sectorid = $r->sectorid;
            $addAnswerTrial->answer = $answer_txt;
            $addAnswerTrial->timecreated = time();
            $addAnswerTrial->timemodified = time();

            $status = $DB->insert_record('local_pdi_answer_trial', $addAnswerTrial);
        }

        if($status>0){
            echo "ok";
            die;
        }
    
    }
}

