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
$outputuser_sel_sql = "SELECT `id`, `username`, `firstname`, `lastname`, `email`,  `institution` FROM {user} WHERE `email` != 'root@localhost'";
$outputuser_sel_avUsers = $DB->get_records_sql($outputuser_sel_sql);

$outmoodle_list_simple;

//verifica cochorts
//verifica os coortes
$html_cortes_ous = "<select name=\"select-cohort\">";

$sql_ous = "SELECT * FROM {cohort}";
$res_ous = $DB->get_records_sql($sql_ous);

$html_cortes_ous .= "
        <option value=\"0\" selected>-</option>
    ";

foreach($res_ous as $r){
    $html_cortes_ous .= "
        <option value=\"$r->id\">$r->name</option>
    ";
}

$html_cortes_ous .= "</select>";
/////

//pagina
foreach($outputuser_sel_avUsers as $user){
    $userfullname = "$user->firstname" . " " . "$user->lastname";

    $outmoodle_list_simple[] = array("$user->username", $userfullname, "$user->email", "$html_cortes_ous");
}

//echo json_encode($outmoodle_lista, JSON_UNESCAPED_UNICODE);

