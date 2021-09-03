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

if(isset($_POST['hidden-answeredby']))
{

    $current_userid = $_POST['hidden-answeredby'];
    $anstatusID = $_POST['hidden-asntatusid'];
    $trialID = $_POST['hidden-trialid'];
    $isfinished = 1;
    $tempoUnix = time();

    //pegar alguns dados de outras tabelas
    //sector_member -> id (quem avaliou)
    $sector_member_id = '';
    //answer_status -> userid (quem foi avaliado)
    $userid_avaliado = '';

    //sql do sector_member
    $sql = "SELECT * FROM {local_pdi_sector_member} x
    WHERE x.userid = '$current_userid' AND x.trialid = '$trialID'";

    $res = $DB->get_records_sql($sql);

    foreach($res as $r){
        $sector_member_id = $r->id;
    }

    //sql do avaliado
    $sql2 = "SELECT * FROM {local_pdi_answer_status} y
    WHERE y.id = '$anstatusID'";

    $res2 = $DB->get_records_sql($sql2);

    foreach($res2 as $r){
        $userid_avaliado = $r->userid;
    }


    //modificar o status das respostas do avaliado para 2
    //0 -> não respondido
    //1 -> respondido
    //2 -> respondido e avaliado
    $updateStatusAvaliado = new stdClass();
    $updateStatusAvaliado->id = $anstatusID;
    $updateStatusAvaliado->isfinished = 2;
    $updateStatusAvaliado->timemodified = time();

    $DB->update_record('local_pdi_answer_status', $updateStatusAvaliado);
    

    //inserir no status do avaliador
    $status = 0;
    
    $addFinishStatus = new stdClass();
    $addFinishStatus->secmemberid = $sector_member_id;
    $addFinishStatus->evaluatedid = $userid_avaliado;
    $addFinishStatus->isfinished = $isfinished;
    $addFinishStatus->timecreated = $tempoUnix;
    $addFinishStatus->timemodified = $tempoUnix;

    $status = $DB->insert_record('local_pdi_evanswer_status', $addFinishStatus);
    

    if($status > 0){
        echo "ok";
        die;
    }

}

