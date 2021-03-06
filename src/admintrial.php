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

$PAGE->set_url(new moodle_url('/local/pdi/admintrial.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title("PDI Admin - Trial");
$PAGE->set_heading('PDI Admin');
$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url($CFG->dirrroot . '/local/pdi/scripts/pdiscript.js'));

global $USER, $DB;

//form instance

//verifica se o logado é adm
verifyAdm($USER->username);


//students available table
/*$sql_students = "SELECT mdl_local_pdi_student.id, 
                        mdl_local_pdi_student.studname,
                        mdl_local_pdi_student.studemail,
                        mdl_user.institution,
                        mdl_user.firstname,
                        mdl_user.lastname
                        FROM mdl_local_pdi_student INNER JOIN mdl_user 
                        ON mdl_local_pdi_student.studname = mdl_user.username";
*/


//page STARTS HERE
echo $OUTPUT->header();

$auth = ($_SESSION['authadm']);
if($auth == "yes"){
    //do something if needed
}else{
  echo "<div id='myblue-bg'>";
  echo "<span><a href='/moodle/index.php' class='pdi-nostyle'>back</a></span>";
  echo "<div class='mypush'><span class='mylogo'>PDI</span></div>";
  echo "</div><br>";

    echo "<footer>That is a page for plugin admins only.</footer><br>";
    \core\notification::add("You are not registered as a plugin admin!", \core\output\notification::NOTIFY_ERROR);
    echo "<span><a href='index.php' class='pdi-nostyle'>back</a></span>";
}


//para esconder o form
if(isset($_SESSION['authadm']) and $_SESSION['authadm'] == 'yes'){

//actual page for admin
echo "<div id='myblue-bg'>";
echo "<span><a href='pdiadmin.php' class='pdi-nostyle my-marginr'>back</a></span>";
echo "<span><a href='pdiadmin.php' class='pdi-nostyle my-marginr'>dashboard</a></span>";
echo "<span><a href='createtrial.php?newtrial=new' class='pdi-nostyle'>new trial</a></span>";
echo "<div class='mypush'><span class='mylogo'>PDI</span></div>";
echo "</div><br>";

echo "<div>
<input type='button' value='reports' class='my-secondary-btn my-btn-pad' id='btn-reports'>
<input type='button' value='status' class='my-secondary-btn my-btn-pad' id='btn-status'>
<input type='button' value='ranking' class='my-secondary-btn my-btn-pad' id='btn-ranking'>
<input type='button' value='questions database' class='my-secondary-btn my-btn-pad' id='btn-questions'>
<input type='button' value='settings' class='my-secondary-btn my-btn-pad' id='btn-settings'>
</div>";

echo "<div id='mygrey-bg'>"; //grey bg starts

echo "<h1>This Trial Name</h1>";
echo "<footer class='my-belowh1'>01/07/2021 - 08/07/2021</footer>";


echo "<div id='my-tab1' class='my-inside-container my-hidden'>

Nome de quem respondeu <br>
Data que finalizou <br>
<b style='cursor: pointer;'>clicar para responder</b><br><br><br>

Nome de quem respondeu <br>
Data que finalizou <br>
<b style='cursor: pointer;'>clicar para responder</b><br><br><br>

Nome de quem respondeu <br>
Data que finalizou <br>
<b style='cursor: pointer;'>clicar para responder</b><br><br><br>

Nome de quem respondeu <br>
Data que finalizou <br>
<b style='cursor: pointer;'>clicar para responder</b><br><br><br>

</div>";
echo "<div id='my-tab2' class='my-inside-container my-hidden'>status</div>";
echo "<div id='my-tab3' class='my-inside-container my-hidden'>ranking</div>";
echo "<div id='my-tab4' class='my-inside-container my-hidden'>questions db</div>";
echo "<div id='my-tab5' class='my-inside-container my-hidden'>settings</div>";

echo "</div>"; //div mygrey-bg ends

}

//js do bootstrap
echo "
<script src=\"bootstrap/js/addons/datatables.min.js\" type=\"text/javascript\"></script>
<script src=\"bootstrap/js/addons/datatables-select.min.js\" type=\"text/javascript\"></script>";


echo $OUTPUT->footer();

?>

<script>

$(document).ready(function() {

$("#btn-reports").attr('style', 'background-color: var(--myprimary) !important');
$("#my-tab1").show();

$( ".my-secondary-btn" ).on( "click", function() {
  var element = $(this);
  var idElement = element.attr('id');

  $(".my-secondary-btn").attr('style', 'background-color: var(--mysecondary) !important');
  element.attr('style', 'background-color: var(--myprimary) !important');
  
  switch(idElement) 
  {
    case "btn-reports":
      //show this div
      $(".my-inside-container").hide();
      $("#my-tab1").show();
      break;

    case "btn-status":
      //show
      $(".my-inside-container").hide();
      $("#my-tab2").show();
      break;

    case "btn-ranking":
      //show
      $(".my-inside-container").hide();
      $("#my-tab3").show();
      break;

    case "btn-questions":
      //show
      $(".my-inside-container").hide();
      $("#my-tab4").show();
      break;

    case "btn-settings":
      //show
      $(".my-inside-container").hide();
      $("#my-tab5").show();
      break;

    default:
      alert("Error");
      //but still show some default div
      $(".my-inside-container").hide();
      $("#my-tab1").show();
      break;
  }
});

});

</script>