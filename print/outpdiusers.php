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

global $DB;

//pré-página
$outputmoodle_sql = "SELECT mdl_user.id, mdl_user.username, mdl_user.firstname, mdl_user.lastname, mdl_user.email, mdl_user.institution 
FROM mdl_user 
LEFT JOIN mdl_local_pdi_evaluator
ON mdl_user.id = mdl_local_pdi_evaluator.mdlid
WHERE mdl_user.email != 'root@localhost' 
and mdl_local_pdi_evaluator.mdlid IS NULL";
$outputmoodle_avUsers = $DB->get_records_sql($outputmoodle_sql);

$outev_lista;
//pagina
foreach($outputmoodle_avUsers as $user){
    $userfullname = "$user->firstname" . " " . "$user->lastname";

    $outev_lista[] = array("$user->id", "$user->username", "$user->email", "$user->institution", "$userfullname");
}

//echo json_encode($outmoodle_lista, JSON_UNESCAPED_UNICODE);

