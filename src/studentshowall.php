<html>
<head>

<!-- DataTables CSS -->
<link href="bootstrap/css/addons/datatables.min.css" rel="stylesheet">

<!-- DataTables Select CSS -->
<link href="bootstrap/css/addons/datatables-select.min.css" rel="stylesheet">
<link rel="stylesheet" href="styles/pdistyle.css">
<link rel="stylesheet" href="styles/pdinonav.css">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.11.2/css/all.css">

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
require_once('lib.php');
require_once('print/outputmoodleusers.php');
require_login();

$PAGE->set_url(new moodle_url('/local/pdi/studentshowall.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title("PDI Student - Dashboard");
$PAGE->set_heading('PDI Student');
$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url($CFG->dirrroot . '/local/pdi/scripts/pdiscript.js'));

global $USER, $DB;

//page STARTS HERE
echo $OUTPUT->header();

//actual page for admin
echo "<div id='myblue-bg'>";
echo "<span><a href='index.php' class='pdi-nostyle my-marginr'>back</a></span>";
echo "<span><a href='index.php' class='pdi-nostyle my-marginr'>dashboard</a></span>";
echo "<div class='mypush'><span class='mylogo'>PDI</span></div>";
echo "</div><br>";

echo "<div id='mygrey-bg'>"; //grey bg starts

echo "<h1>Dashboard - All</h1>";
echo "<footer class='my-belowh1'>List of all trials</footer>";

//
echo "<br>";

echo "<input type='text' id='my-searchbar' class='my-large-input' placeholder='Name of the trial...'>";
echo "<input type='button' id='my-searchbtn' class='btn my-primary-btn' value='search'>";

echo "<br><br>";

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

echo "<div class='my-margin-box'>
<span class=\"my-circle\" style=\"background-color: var(--myerror); color: var(--myblack);\">✖</span>
<div class='my-sidetext'>
<span class='my-circle-title'>Title with max of 25 or so</span>
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

echo "<div class='my-margin-box'>
<span class=\"my-circle\" style=\"background-color: var(--myerror); color: var(--myblack);\">✖</span>
<div class='my-sidetext'>
<span class='my-circle-title'>Title with max of 25 or so</span>
<p>07/07/2021 - 10/07/2021</p>
<p>12/30 forms not answered</p>
</div>
</div>";


echo "</div>"; //</div dashboard list

echo "<div class='my-pagination-div'>
<span><a href='#'>1</a></span>
<span><a href='#'>2</a></span>
<span><a href='#'>3</a></span>
<span><a href='#'> >> </a></span>
<span><a href='#'> More </a></span>
</div>";

echo "</div>"; //centralizadora
//


echo "</div>"; //div mygrey-bg ends


//js do bootstrap
echo "
<script src=\"bootstrap/js/addons/datatables.min.js\" type=\"text/javascript\"></script>
<script src=\"bootstrap/js/addons/datatables-select.min.js\" type=\"text/javascript\"></script>";


echo $OUTPUT->footer();

?>

<script>

$(document).ready(function() {

$( ".my-margin-box" ).on( "click", function() {
  window.location.href = "studenttrial.php";  
});

});

</script>