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
$outevacohort_sql = "SELECT mdl_user.id, mdl_user.username, mdl_user.firstname, mdl_user.lastname, mdl_user.email FROM mdl_user
                        INNER JOIN mdl_local_pdi_evaluator
                        ON mdl_user.id = mdl_local_pdi_evaluator.mdlid
                        WHERE `email` != 'root@localhost'";
$outres = $DB->get_records_sql($outevacohort_sql);

$outevacohort_list;

//verifica cochorts
//verifica os coortes
$html_cortes_oec = "<select name=\"select-cohort\">";

$sql_oec = "SELECT * FROM {cohort}";
$res_oec = $DB->get_records_sql($sql_oec);

$html_cortes_oec .= "
        <option value=\"0\" selected>-</option>
    ";

foreach($res_oec as $r){
    $html_cortes_oec .= "
        <option value=\"$r->id\">$r->name</option>
    ";
}

$html_cortes_oec .= "</select>";
/////

//pagina
foreach($outres as $user){
    $userfullname = "$user->firstname" . " " . "$user->lastname";

    $outevacohort_list[] = array("$user->username", $userfullname, "$user->email", "$html_cortes_oec");
}

//echo json_encode($outmoodle_lista, JSON_UNESCAPED_UNICODE);

