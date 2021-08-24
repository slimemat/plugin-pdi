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

$out_cohortID = $_POST['out_cohortID']; //post aqui

$outCohortUsers_sql = "SELECT mdl_cohort_members.userid, mdl_user.firstname, mdl_user.lastname 
FROM mdl_cohort
INNER JOIN mdl_cohort_members
ON mdl_cohort_members.cohortid = mdl_cohort.id 
INNER JOIN mdl_user
ON mdl_user.id = mdl_cohort_members.userid
WHERE mdl_cohort.id = '$out_cohortID'";

$outCohortUsers_res = $DB->get_records_sql($outCohortUsers_sql);

foreach($outCohortUsers_res as $rr){
    $userid = $rr->userid;
    $userfname = $rr->firstname;
    $userlname = $rr->lastname;

    $status[] = array("userid"=>"$userid", "firstname"=>"$userfname", "lastname"=>"$userlname");
}

echo json_encode($status, JSON_UNESCAPED_UNICODE);
die;


