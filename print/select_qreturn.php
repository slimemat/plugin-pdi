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

if(isset($_POST['dbhidden-id']))
{
    $databaseId = $_POST['dbhidden-id'];
    $databaseName = $_POST['dbhidden-name'];

    $sql = "SELECT question.id, question.name, question.questiontext, question.qtype, questindb.databaseid, db.name as dbname FROM {local_pdi_question} question 
    INNER JOIN {local_pdi_questindb} questindb
    ON questindb.questionid = question.id
    INNER JOIN {local_pdi_question_db} db
    ON db.id = questindb.databaseid
    WHERE questindb.databaseid = '$databaseId' or db.name = '$databaseName'";

    $res = $DB->get_records_sql($sql);

    if(count($res) < 1){
        echo "<label class='my-label btn-pdicollapse'>Não há questões nesse banco!</label>";
    }
    else{
        foreach($res as $q){
            $qname = $q->name;
            $qtext = $q->questiontext;
            $qtype = $q->qtype;

            echo "<div class='my-margin-l qblock'";
            echo "<span class='my-padding-sm'><b>$qname</b></span> <br>";
            echo "<span class='my-padding-sm my-label'>$qtext</span> <br><br>";
            echo "</div>";
        }
    }
}

