<html>
<head>
<link rel="stylesheet" href="styles/pdistyle.css">
</head>
</html>

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

require_once('../../config.php');
require_once($CFG->dirroot . '/local/pdi/classes/forms/insert_student.php');
require_login();

$PAGE->set_url(new moodle_url('/local/pdi/signupstudent.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title("PDI Sign-up Student");
$PAGE->set_heading('PDI Sign-up Student');
$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url($CFG->dirrroot . '/local/pdi/scripts/pdiscript.js'));

global $DB;

//form instance
$mform = new insert_student();

//form setup
if($mform->is_cancelled()){
    //do something...
}
else if($fromform = $mform->get_data()){
    $addStudent = new stdClass();

    $addStudent->studname = $fromform->studname;
    $addStudent->studemail = $fromform->studemail;

    $websql = "SELECT studname, studemail FROM mdl_local_pdi_student WHERE studname = '$addStudent->studname' or studemail = '$addStudent->studemail'";
    $alreadyExist = $DB->get_records_sql($websql);

    $sql = "SELECT `email`, `username` FROM `mdl_user` WHERE email = '$addStudent->studemail' and username = '$USER->username'";
    $res = $DB->get_records_sql($sql);

    if(count($alreadyExist) > 0){
        redirect($CFG->wwwroot . '/local/pdi/signupstudent.php', 'Student already exists in the database! Please, contact your supervisor.');
    }
    else if(count($res) > 0){
        $DB->insert_record('local_pdi_student', $addStudent);
        redirect($CFG->wwwroot . '/local/pdi/pdistudent.php', 'You can now verify your username');
    }
    else{
        redirect($CFG->wwwroot . '/local/pdi/signupstudent.php', 'Invalid user! The fields might be incorrect.');
    }
}


//page setup


//page
echo $OUTPUT->header();

echo "<span><a href='pdistudent.php' class='pdi-nostyle'>back</a></span>";
echo "<hr>";

//tabela de inserir adms
echo "<h4>Student</h4>";
echo "<footer>We just need you current moodle username and email</footer>";
echo "<br><br>";

$mform->display();

echo "<br><hr>";

echo $OUTPUT->footer();