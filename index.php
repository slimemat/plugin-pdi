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
require_login();

$PAGE->set_url(new moodle_url('/local/pdi/index.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title(get_string('idp_plugin', 'local_pdi'));
$PAGE->set_heading(get_string('idp_plugin', 'local_pdi'));

global $USER;

//vars
$btn_web_adm = "";

//if the user is an admin
if(has_capability('moodle/site:config', context_system::instance())){
    $sql = "SELECT * FROM {local_pdi_user} LIMIT 3";
    $areUsers = $DB->get_records_sql($sql);
    if(count($areUsers) > 0){
        $strYouhaveAlready = get_string('web_adm_all_set', 'local_pdi'); //you have already setup the plugin...
        \core\notification::add($strYouhaveAlready, \core\output\notification::NOTIFY_SUCCESS);
    }
    else{
        $strHiMoodleAdm = get_string('web_adm_not_set', 'local_pdi');
        \core\notification::add($strHiMoodleAdm , \core\output\notification::NOTIFY_ERROR);
        redirect($CFG->wwwroot . '/local/pdi/webadmin.php');
    }

    $strManageAdms = get_string('web_manage_adm', 'local_pdi');
    $btn_web_adm = "<a href='webadmin.php' class='pdi-nostyle'><button type='button' class='btn-pdiselect' value='adm'>$strManageAdms</button></a>";

}
else{
    $sql = "SELECT * FROM {local_pdi_user} LIMIT 3";
    $areUsers = $DB->get_records_sql($sql);

    if(count($areUsers) < 1){
        $strWaitForYourAdm = get_string('user_wait_adm', 'local_pdi');
        \core\notification::add($strWaitForYourAdm, \core\output\notification::NOTIFY_INFO);    
    }
    else{
        //webadm já adicionou um plugin adm
        $sql2 = "SELECT * FROM {local_pdi_user} WHERE username = \"$USER->username\"";
        $res2 = $DB->get_records_sql($sql2);

        //se for adm
        if(count($res2) > 0){
            foreach($res2 as $row){
                $userRole = $row->userrole;
            }
            //se estiver habilitado, 0 significa PDI ADMIN
            if($userRole == "0"){
                redirect($CFG->wwwroot . '/local/pdi/pdiadmin.php');
                die;
            }
            else{
                $strYouAdmAccDisabled = get_string('user_acc_disabled' ,'local_pdi');
                redirect($CFG->wwwroot . '/local/pdi/pdiadmin.php', $strYouAdmAccDisabled);
                die;
            }

            
        }
        else{
            //aqui é se não é adm
            $sql3 = "SELECT * FROM {local_pdi_student} WHERE studname = \"$USER->username\"";
            $res3 = $DB->get_records_sql($sql3);

            if(count($res3) > 0){
                redirect($CFG->wwwroot . '/local/pdi/pdistudent.php');
                die;
            }
            else{
                $sql_check = "SELECT `email`, `username` FROM {user} WHERE email = '$USER->email' and username = '$USER->username'";
                $res_check = $DB->get_records_sql($sql_check);

                $addStudent = new stdClass();
                $addStudent->studname = $USER->username;
                $addStudent->studemail = $USER->email;

                if(count($res_check) > 0){
                    $DB->insert_record('local_pdi_student', $addStudent);
                    $strYouAreStudent = get_string('stud_register_msg', 'local_pdi');
                    redirect($CFG->wwwroot . '/local/pdi/pdistudent.php', $strYouAreStudent);
                }
            }
        }
        
    }
}


//page variables
$html = "<div>
            $btn_web_adm
        </div>";


//actual page
echo $OUTPUT->header();

echo "<div id='myblue-bg'></div>";
echo "<div id='mygrey-bg'>";
echo "<h1 class='my-h1'>". get_string("options", "local_pdi") ."</h1>";
echo $html;
echo "</div>";

echo $OUTPUT->footer();