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
    $anstatus_id = $_POST['hidden-asntatusid'];
    $answer_txt = $_POST['hidden-qanswer'];
    $answeredby_id = $_POST['hidden-answeredby'];

    $sqlVerificar = "SELECT * FROM {local_pdi_evanswer_trial} x
    WHERE x.answeredbyid = '$answeredby_id' and x.idquestion = '$question_id' and x.idanstatus = '$trial_id'";
    $resVerificar = $DB->get_records_sql($sqlVerificar);

    //se já existir, update deverá ser feito
    if(count($resVerificar) > 0){
        
        //update
        $status = 0;
        foreach($resVerificar as $rv){
            $addAnswerTrial = new stdClass();
            $addAnswerTrial->id = $rv->id;            
            $addAnswerTrial->answer = $answer_txt;
            $addAnswerTrial->timemodified = time();

            $status = $DB->update_record('local_pdi_evanswer_trial', $addAnswerTrial);
        }

        if($status){
            echo "ok atualizado";
            die;
        }

    }
    else{

        $status=0;
        
        $addAnswerTrial = new stdClass();
        $addAnswerTrial->answeredbyid = $answeredby_id;
        $addAnswerTrial->idquestion = $question_id;
        $addAnswerTrial->idanstatus = $anstatus_id;
        $addAnswerTrial->answer = $answer_txt;
        $addAnswerTrial->timecreated = time();
        $addAnswerTrial->timemodified = time();

        $status = $DB->insert_record('local_pdi_evanswer_trial', $addAnswerTrial);
        

        if($status>0){
            echo "ok";
            die;
        }
    
    }
}

