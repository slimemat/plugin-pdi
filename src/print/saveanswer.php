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
    $sector_id = $_POST['hidden-qsector'];
    $answer_txt = $_POST['hidden-qanswer'];
    $trial_id = $_POST['hidden-qtrialid'];
    $answeredby_id = $_POST['hidden-answeredby'];

    echo "alo";

    //fazer uma consulta de algo e salvar

    /*SELECT sm.sectorid, q.id qid, q.name, sm.trialid FROM mdl_local_pdi_sector_member sm 
LEFT JOIN mdl_local_pdi_question q 
ON sm.trialid = '1'
WHERE q.id='6'*/
    
}

