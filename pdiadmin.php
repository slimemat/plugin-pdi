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
require_once('print/fetchforevaluator.php');
require_login();

$PAGE->set_url(new moodle_url('/local/pdi/pdiadmin.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title("PDI Admin");
$PAGE->set_heading('PDI Admin');
$PAGE->requires->jquery();
//$PAGE->requires->js(new moodle_url($CFG->dirrroot . '/local/pdi/scripts/pdiscript.js'));

global $USER, $DB;

//form instance

//verifica se o logado é adm
verifyAdm($USER->username);


//programação antes da página

$retornoBlocos = "";
$retornoBlocos = fetchTrials();


///


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
echo "<span><a href='/moodle/index.php' class='pdi-nostyle my-marginr'>back</a></span>";
echo "<span><a href='index.php' class='pdi-nostyle my-marginr'>dashboard</a></span>";
echo "<span><a href='createtrial.php?newtrial=new' class='pdi-nostyle'>new trial</a></span>";
echo "<div class='mypush'><span class='mylogo'>PDI</span></div>";
echo "</div><br>";

echo "<div id='mygrey-bg'>"; //grey bg starts

echo "<h1>Dashboard</h1>";
echo "<footer class='my-belowh1'>List of trials you evaluate</footer>";

//create new btn link
echo "<div id='my-steps'>"; //steps
echo "<div class='my-circle-div'>
<a href='createtrial.php?newtrial=new' class='pdi-nostyle'>
<span class=\"my-circle\" style=\"margin-top: -18px;font-size: 18px; background-color: var(--myprimary);\">New</span>
</a>
</div>";
echo "</div>";

//
echo "<br><br>";

echo "<div id='my-centralizadora'>";
echo "<div id='div-dashboard-list'>"; //div dashboard list


/////////////////
/////////////printar aqui com o conteudo dinamico criado em cima dessa página


echo $retornoBlocos;

////////////////////////////




echo "</div>"; //</div dashboard list
echo "</div>"; //centralizadora
//

echo "<br><br>";

//btn SHOW ALL
echo "<div id='div-save-buttons'>";
echo "<input type='button' id='id_show_btn' class='div-save-btn my-primary-btn my-marginlauto'
value='Show all'>";
echo "</div>";


///parte de responder
echo "<div id='my-centralizadora'>";
echo "<div id='div-dashboard-list'>"; //div dashboard list

echo "<br><br>";
echo "<h4>Processos para responder</h4>";
echo "<footer class='my-belowh1'>Lista de processos para responder</footer>";
echo "<br><br>";

echo "<div class='my-margin-box'>
<span class=\"my-circle\" style=\"background-color: var(--myerror); color: var(--myblack);\">✖</span>
<div class='my-sidetext'>
<span class='my-circle-title'>Title with max of 25 or so</span>
<p>07/07/2021 - 10/07/2021</p>
<p>12/30 forms not answered</p>
</div>
</div>";

echo "<br>";
echo "</div>";
echo "</div>";


//
//hidden-form
echo "<form id=\"frm-trial-id\" name=\"frm-trial-id\" class='hidden' method=\"post\" action=\"\">";
echo "<input type=\"hidden\" name=\"hidden-trial-id\" id=\"hidden-trial-id\" value=\"\">";
echo "</form>";



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

$( "#id_show_btn" ).on( "click", function() {
  window.location.href = "adminshowall.php";  
});

$(".my-youev").on("click", function(){

  var trialid = $(this).attr("data-id");
  $("#hidden-trial-id").val(trialid);

  $("#frm-trial-id").attr("action", "admintrial.php");
  $("#frm-trial-id").submit();

});

});

</script>