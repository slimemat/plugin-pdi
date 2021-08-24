<html>
<head>
<link rel="stylesheet" href="styles/pdistyle.css">
<link rel="stylesheet" href="styles/pdinonav.css">
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
require_once($CFG->dirroot . '/local/pdi/classes/forms/auth_student.php');
require_login();

$PAGE->set_url(new moodle_url('/local/pdi/pdistudent.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title("PDI Student");
$PAGE->set_heading('PDI Student');
$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url($CFG->dirrroot . '/local/pdi/scripts/pdiscript.js'));

global $USER, $DB;


//page setup


//page
echo $OUTPUT->header();

echo "<div id='myblue-bg'>";
echo "<span><a href='/moodle/index.php' class='pdi-nostyle'>back</a></span>";
echo "</div><br>";

echo "<div id='mygrey-bg'>"; //grey bg starts

echo "<h4>Your trials list</h4>";
echo "<footer>Pick one to see more details</footer>";
echo "<br>";

///////
echo "<div id='my-centralizadora'>";
echo "<div id='div-dashboard-list'>"; //div dashboard list

echo "<div class='my-margin-box'>
<span class=\"my-circle\" style=\"background-color: var(--mysuccess); color: var(--myblack);\">✔</span>
<div class='my-sidetext'>
<span class='my-circle-title'>IT Trial</span>
<p>07/07/2021 - 10/07/2021</p>
<p>12/30 forms not answered</p>
</div>
</div>";

echo "<div class='my-margin-box'>
<span class=\"my-circle\" style=\"background-color: var(--mysuccess); color: var(--myblack);\">✔</span>
<div class='my-sidetext'>
<span class='my-circle-title'>Title with max of 25 chara</span>
<p>07/07/2021 - 10/07/2021</p>
<p>12/30 forms not answered</p>
</div>
</div>";

echo "<div class='my-margin-box'>
<span class=\"my-circle\" style=\"background-color: var(--myerror); color: var(--myblack);\">✖</span>
<div class='my-sidetext'>
<span class='my-circle-title'>Title with max of 25 or so</span>
<p>07/07/2021 - 10/07/2021</p>
<p>12/30 forms not answered</p>
</div>
</div>";

//btn SHOW ALL
echo "<div id='div-save-buttons'>";
echo "<input type='button' id='id_show_btn' class='my-large-input my-primary-btn my-marginlauto'
value='Show all'>";
echo "</div>";
echo "<hr>";

echo "</div>"; //</div dashboard list
echo "</div>"; //centralizadora
///////////////////


echo "<br>";

echo "<h4>Your groups</h4>";
echo "<footer>The Admin has to add you in a group</footer>";

echo "<br><br>";

echo "</div>"; //end of bg-grey
echo $OUTPUT->footer();

?>

<script>

$(document).ready(function() {

$( ".my-margin-box" ).on( "click", function() {
  window.location.href = "studenttrial.php";
});

$( "#id_show_btn" ).on( "click", function() {
  window.location.href = "studentshowall.php";  
});

});

</script>