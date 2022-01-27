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

if(isset($_POST['hidden-qtrialid']))
{
    $trial_id = $_POST['hidden-qtrialid'];
    $current_userid = $_POST['hidden-answeredby'];
    $isfinished = 1;
    $tempoUnix = time();

    //pega os setores que foram usados nesse processo
    $sql= "SELECT sm.sectorid, smdb.dbid , q.id qid, q.name, sm.trialid FROM {local_pdi_sector_member} sm 
    LEFT JOIN {local_pdi_sect_mem_db} smdb
    ON smdb.smemberid = sm.id
    LEFT JOIN {local_pdi_questindb} qindb
    ON qindb.databaseid = smdb.dbid
    LEFT JOIN {local_pdi_question} q
    ON q.id = qindb.questionid
    WHERE sm.trialid = '$trial_id'
    GROUP BY sm.sectorid";

    $res = $DB->get_records_sql($sql);

    //para cada setor
    $status = 0;
    foreach($res as $r){
        $addFinishStatus = new stdClass();
        $addFinishStatus->userid = $current_userid;
        $addFinishStatus->idtrial = $trial_id;
        $addFinishStatus->sectorid = $r->sectorid;
        $addFinishStatus->isfinished = $isfinished;
        $addFinishStatus->timecreated = $tempoUnix;
        $addFinishStatus->timemodified = $tempoUnix;

        $status = $DB->insert_record('local_pdi_answer_status', $addFinishStatus);
    }

    if($status > 0){
        echo "ok";
        die;
    }

}

