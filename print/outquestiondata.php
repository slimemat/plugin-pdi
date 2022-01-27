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


 function fetchSectors($trialID){
     global $DB;

     $sql = "SELECT sec.id, sec.sectorname, sec.timecreated, mem.id as memid, mem.sectorid as sid, mem.trialid, mem.userid 
     FROM {local_pdi_sector} sec
     LEFT JOIN {local_pdi_sector_member} mem
     ON mem.sectorid = sec.id
     WHERE mem.trialid = '$trialID'";

     $result = $DB->get_records_sql($sql);
    

     //foreach dos setores dessa trial
     $i = 1;
     foreach($result as $sec){
        $selectHTML = "<select id='sel-index-$i' name=\"select-question\" class=\"select-question my-large-input\">
        <option value=\"0\" disabled selected>-</option>";

        //fazer foreach das databases no sector_member
        $sqliSecDB = "SELECT memdb.id, memdb.smemberid, memdb.dbid, db.name as dbname FROM {local_pdi_sect_mem_db} memdb
                        INNER JOIN {local_pdi_question_db} db
                        ON memdb.dbid = db.id
                        WHERE memdb.smemberid = '$sec->memid'";
        $resSecDB = $DB->get_records_sql($sqliSecDB);
        
        $databaseBlock = "";
        $db_id_list = "(";
        foreach($resSecDB as $sdb){
            $databaseBlock .= "$sdb->dbname <br>";
            $db_id_list .= "$sdb->dbid, ";
        }
        $db_id_list .= "0)";

        //fazer foreach das databases
        $sqldb = "SELECT * FROM {local_pdi_question_db} WHERE id NOT IN $db_id_list";
        $resultdb = $DB->get_records_sql($sqldb);
        
        foreach($resultdb as $db){
            $selectHTML .= "<option value=\"$db->id\">$db->name</option>";
        }


        $selectHTML .= "</select>";

        $selectHTML .= "<button id='btn-index-$i' data-index='$i' data-memid='$sec->memid' class='btn my-primary-btn my-margin-l btn-add-db'>add</button>";

        $dataList[] = array("$sec->sectorname", $databaseBlock, $selectHTML);

        $i++;
     }

     if(!isset($dataList)){echo "<div class=\"alert alert-warning\" role=\"alert\">Complete o passo anterior antes de prosseguir!</div>";}
     else{
        return $dataList;
     }
 }

