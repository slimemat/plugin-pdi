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
require_once('print/trialsfunctions.php');
require_login();

$PAGE->set_url(new moodle_url('/local/pdi/studenttrial.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title("PDI Student - Trial");
$PAGE->set_heading('PDI Student');
$PAGE->requires->jquery();
//$PAGE->requires->js(new moodle_url($CFG->dirrroot . '/local/pdi/scripts/pdiscript.js'));

global $USER, $DB;

//some before page coding
if(isset($_POST['hidden-trial-id'])){

  //parte 1, dados do processo
  $trialid = $_POST['hidden-trial-id'];

  $resTrial = getOneStudentTrial($trialid);

  $trialTitle = '';
    $trialStart = '';
    $trialEnd = '';
    $trialEvType = '';

    foreach($resTrial as $r){
      $trialTitle = $r->title;
      $trialStart = $r->startdate;
      $trialEnd = $r->enddate;
      $trialEvType = $r->evtype;
    }

    $dateInicioF = gmdate("d/m/y", $trialStart);
    $dateFimF = gmdate("d/m/y", $trialEnd);

  //parte 2, se ela não tiver sido respondida, obrigar a responder

  $responderSQL = "SELECT * FROM {local_pdi_answer_status} anstatus
  WHERE anstatus.idtrial = '$trialid' and anstatus.userid = '$USER->id'";

  $responderRES = $DB->get_records_sql($responderSQL);

  $respondido = 0;

  if(count($responderRES) > 0){
    foreach($responderRES as $rr){
      $respondido = $rr->isfinished;
    }
  }

}

////

//page STARTS HERE
echo $OUTPUT->header();

//actual page
echo "<div id='myblue-bg'>";
echo "<span><a href='index.php' class='pdi-nostyle my-marginr'>back</a></span>";
echo "<span><a href='index.php' class='pdi-nostyle my-marginr'>dashboard</a></span>";
echo "<div class='mypush'><span class='mylogo'>PDI</span></div>";
echo "</div><br>";

echo "<div>
<input type='button' value='reports' class='my-secondary-btn my-btn-pad' id='btn-reports'>
<input type='button' value='my IDP' class='my-secondary-btn my-btn-pad' id='btn-myidp'>
</div>";

echo "<div id='mygrey-bg'>"; //grey bg starts

echo "<h1>$trialTitle</h1>";
echo "<footer class='my-belowh1'>$dateInicioF - $dateFimF</footer>";

if($respondido > 0){

echo "<div id='my-tab1' class='my-inside-container my-hidden'></div>";
echo "<div id='my-tab2' class='my-inside-container my-hidden'>my IDP</div>";

}
else{
  echo "<div>MANDAR PREENCHER</div>";
}

echo "</div>"; //div mygrey-bg ends

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

    case "btn-myidp":
      //show
      $(".my-inside-container").hide();
      $("#my-tab2").show();
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