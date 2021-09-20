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

$PAGE->set_url(new moodle_url('/local/pdi/evalushowall.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title("Avaliador - Dashboard");
$PAGE->set_heading('PDI Avaliador');
$PAGE->requires->jquery();
//$PAGE->requires->js(new moodle_url($CFG->dirrroot . '/local/pdi/scripts/pdiscript.js'));

global $USER, $DB;

//programação antes da página

//verifica se o logado é adm
//verifyAdm($USER->username);
//this return a strig with "yes" or "no"


$retornoBlocos = "";
$retornoBlocos = fetchTrials(0, 6);


/////////////////////


//page STARTS HERE
echo $OUTPUT->header();

/*
$auth = ($_SESSION['authadm']);
if($auth == "yes"){
    //do something if needed
}else{
  echo "<div id='myblue-bg'>";
  echo "<span><a href='../../my/index.php' class='pdi-nostyle'>back</a></span>";
  echo "<div class='mypush'><span class='mylogo'>PDI</span></div>";
  echo "</div><br>";

    echo "<footer>That is a page for plugin admins only.</footer><br>";
    \core\notification::add("You are not registered as a plugin admin!", \core\output\notification::NOTIFY_ERROR);
    echo "<span><a href='index.php' class='pdi-nostyle'>back</a></span>";
}
*/


//actual page for admin
echo "<div id='myblue-bg'>";
echo "<span><a href='pdistudent.php' class='pdi-nostyle my-marginr'>back</a></span>";
echo "<span><a href='pdistudent.php' class='pdi-nostyle my-marginr'>dashboard</a></span>";
echo "<div class='mypush'><span class='mylogo'>PDI</span></div>";
echo "</div><br>";

echo "<div id='mygrey-bg'>"; //grey bg starts

echo "<h1>Dashboard - All</h1>";
echo "<footer class='my-belowh1'>List of all trials</footer>";

//
echo "<br>";

echo "<div class=\"input-group\" style='max-width: 30%;'>
<input type=\"text\" id='my-searchbar' class=\"form-control\" placeholder=\"Pesquisar por nome\" aria-label=\"Recipient's username\" aria-describedby=\"button-addon2\">
<button class=\"btn my-search-btn\" id='my-searchbtn' type=\"button\" id=\"button-addon2\"><i class=\"fas fa-search\"></i></button>
</div>";

echo "<br><br>";

echo "<div id='my-centralizadora'>";
echo "<div id='div-dashboard-list'>"; //div dashboard list


//printar todos os processos de maneira dinâmica aqui
echo "<div id='div-padrao'>";
echo $retornoBlocos;
echo "</div>";

//printar de acordo com a pesquisa
echo "<div id='div-pesquisados'></div>";

echo "</div>"; //</div dashboard list

echo "<br>";

echo "<div class='my-pagination-div'>
<span class='my-clickable my-hidden' id='btn-ver-menos'> << </span>
<span class='my-clickable' id='btn-ver-mais'> próxima >> </span>
</div>";

echo "</div>"; //centralizadora
//


//hidden-form
echo "<form id=\"frm-trial-id-evaluate\" name=\"frm-trial-id-evaluate\" class='hidden' method=\"post\" action=\"\">";
echo "<input type=\"hidden\" name=\"hidden-trial-id\" id=\"hidden-trial-id\" value=\"\">";
echo "</form>";

echo "</div>"; //div mygrey-bg ends


//js do bootstrap
echo "
<script src=\"bootstrap/js/addons/datatables.min.js\" type=\"text/javascript\"></script>
<script src=\"bootstrap/js/addons/datatables-select.min.js\" type=\"text/javascript\"></script>";


echo $OUTPUT->footer();

?>

<script>

$(document).ready(function() {

$(".my-youev").on("click", function(){

var trialid = $(this).attr("data-id");
$("#hidden-trial-id").val(trialid);

$("#frm-trial-id-evaluate").attr("action", "admintrialalt.php");
$("#frm-trial-id-evaluate").submit();

});

//elemento gerado depois
$("#div-pesquisados").on("click", ".my-youev", function(){

var trialid = $(this).attr("data-id");
$("#hidden-trial-id").val(trialid);

$("#frm-trial-id-evaluate").attr("action", "admintrialalt.php");
$("#frm-trial-id-evaluate").submit();

});



//pesquisa
$("#my-searchbar").on("keyup", function(){
  buscarProcessos();
});

$("#my-searchbtn").on("click", function(){
  buscarProcessos();
});


function buscarProcessos() {
  var txtPesquisa = "" + $("#my-searchbar").val() + "";
  txtPesquisa = txtPesquisa.trim();

  if(txtPesquisa != ""){
      var values = {
              'pesquisa': txtPesquisa
      };
      $.ajax({
          method: "POST",
          url: "print/searchtrials.php",
          data: values,
      })
      .done(function(msg){
        $("#div-padrao").hide();
        $("#div-pesquisados").html(msg);

      })
      .fail(function(){
        $("#div-pesquisados").html("Não foi possível acessar os dados.");
      });
  }
  else{
    $("#div-padrao").show();
    $("#div-pesquisados").html("");
  }
}



//ver mais
var maisRows = 6;
var maisOffset = 0;
$("#btn-ver-mais").on("click", function(){

  maisRows = 6;
  maisOffset += 6;

  console.log("proxi: \nmaisROW {" + maisRows + "}\nmaisOffset {" + maisOffset + "}");
  
  var values = {
              'function': 0, 
              'rows': maisRows,
              'offset': maisOffset
      };
      $.ajax({
          method: "POST",
          url: "print/callphpfunctions.php",
          data: values,
      })
      .done(function(msg){
        if(msg == ""){
          $("#btn-ver-mais").hide();
        }

        $("#div-padrao").hide();
        $("#div-pesquisados").html(msg);
        $("#btn-ver-menos").show();

      })
      .fail(function(){
        $("#div-pesquisados").html("Não foi possível acessar os dados.");
      });

});

$("#btn-ver-menos").on("click", function(){

maisRows = 6;
maisOffset -= 6;

console.log("proxi: \nmaisROW {" + maisRows + "}\nmaisOffset {" + maisOffset + "}");

if(maisOffset < 1){
  $("#btn-ver-menos").hide();
}

$("#btn-ver-mais").show();


var values = {
            'function': 0, 
            'rows': maisRows,
            'offset': maisOffset
    };
    $.ajax({
        method: "POST",
        url: "print/callphpfunctions.php",
        data: values,
    })
    .done(function(msg){
      $("#div-padrao").hide();
      $("#div-pesquisados").html(msg);

    })
    .fail(function(){
      $("#div-pesquisados").html("Não foi possível acessar os dados.");
    });

});


});

</script>