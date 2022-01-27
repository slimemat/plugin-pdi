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
require_login();

$PAGE->set_url(new moodle_url('/local/pdi/finalstep5.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title("PDI Admin");
$PAGE->set_heading('PDI Admin');
$PAGE->requires->jquery();
//$PAGE->requires->js(new moodle_url('/local/pdi/scripts/pdiscript.js'));

global $USER, $DB;

//form instance

//verifica se o logado é adm
verifyAdm($USER->username);

/////////////////////////////////////////
$trialid = $_SESSION['edittrialid'];


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

//editar chegando por aqui
if(isset($_REQUEST['edittrial'])){
  //edittrial deve conter o id da trial que está sendo editada
  sleep(1);

  //recupera os dados da url trial
  $trialid = $_REQUEST['edittrial'];
  $_SESSION['edittrialid'] = $trialid;


}


//para esconder o form
if(isset($_SESSION['authadm']) and $_SESSION['authadm'] == 'yes'){


////////////////////////////////////////
//actual page for admin
echo "<div id='myblue-bg'>";
echo "<span><a href='selectquestionsdb.php' class='pdi-nostyle my-marginr'>back</a></span>";
echo "<span><a href='index.php' class='pdi-nostyle my-marginr'>dashboard</a></span>";
echo "<div class='mypush'><span class='mylogo'>PDI</span></div>";
echo "</div><br>";

echo "<div id='mygrey-bg'>"; //grey bg starts


echo "<h1>Step 5 - Final</h1>";
echo "<footer class='my-belowh1'>Set the deadline and type
<a tabindex=\"0\" class=\"btn mybelow1\" role=\"button\" data-toggle=\"popover\" data-placement='bottom' data-trigger=\"focus\" title=\"x\" data-content=\"And here's a tip on how to do something.\"><i class=\"far fa-question-circle my-help-pop\"></i></a>
</footer>";

/////
echo "<div id='my-steps'>"; //steps

echo "<div class='my-circle-div'>
<span class=\"my-circle\" 
onclick='window.location.href = \"createtrial.php?stepnav\"'>1</span>
<footer>step 1</footer>
</div>";

echo "<div class='my-circle-div'>
<span class=\"my-circle\" 
onclick='window.location.href = \"addevaluated.php?stepnav\"'>2</span>
<footer>step 2</footer>
</div>";

echo "<div class='my-circle-div'>
<span class=\"my-circle\"
onclick='window.location.href = \"selectsectors.php?stepnav\"'>3</span>
<footer>step 3</footer>
</div>";

echo "<div class='my-circle-div'>
<span class=\"my-circle\" 
onclick='window.location.href = \"selectquestionsdb.php?stepnav\"'>4</span>
<footer>step 4</footer>
</div>";

echo "<div class='my-circle-div'>
<span class=\"my-circle\" style=\"background-color: var(--myprimary);\">5</span>
<footer>step 5</footer>
</div>";

echo "</div>";
/////

/////
echo "<br><br><br>";

////////////////////////////////////

echo "
<form class='larger-inputs text-center'>

<label for=\"final-name\" class='my-label'>Nome do processo:</label><br>
<input class='my-large-input' type=\"text\" id=\"final-name\" name=\"final-name\" required placeholder='processo nome'> <br><br>

<label for=\"start-date\" class='my-label'>Data inicial:</label><br>
<input class='my-large-input' type=\"date\" id=\"start-date\" name=\"start-date\" required> <br><br>

<label for=\"due-date\" class='my-label'>Data final:</label><br>
<input class='my-large-input' type=\"date\" id=\"due-date\" name=\"due-date\" required><br><br>    

<div class='my-div-col'>
    <label for='sel-ev-type' class='my-label'>Tipo de avaliação:</label> <br>

    <select id=\"sel-ev-type\" name=\"sel-ev-type\" class=\"my-large-input\">
        <option value=\"1\">90 degrees</option>
        <option value=\"2\" disabled>180 degrees</option>
        <option value=\"3\" disabled>360 degrees</option>
    </select>
</div>

<div id='return-div' class='my-label'></div>

</form>
<br>
";



///////////////////////////////////
//bottom buttons

echo "<div id='div-save-buttons'>";

echo "<form id=\"frm-hidden-trial\" name=\"frm-hidden-trial\" method=\"post\" action=\"\">";
echo "<input type=\"hidden\" name=\"hidden-start\" id=\"hidden-start\" value=\"\">";
echo "<input type=\"hidden\" name=\"hidden-name\" id=\"hidden-name\" value=\"\">";
echo "<input type=\"hidden\" name=\"hidden-end\" id=\"hidden-end\" value=\"\">";
echo "<input type=\"hidden\" name=\"hidden-type\" id=\"hidden-type\" value=\"\">";
echo "<input type=\"hidden\" name=\"hidden-started\" id=\"hidden-started\" value=\"\">";
//echo "<input type=\"hidden\" name=\"hidden-mytime\" id=\"hidden-mytime\" value=\"". $_SESSION['mytime']."\">";
echo "</form>";

echo "<input type='button' id='id_back_btn' class='div-save-btn my-grey-btn'
value='Back'>";
echo "<input type='button' id='id_save_btn' class='div-save-btn my-grey-btn my-marginr my-marginlauto'
value='Save'>";
echo "<input type='button' id='id_save_next_btn' class='div-save-btn my-primary-btn'
value='Save and Start'>";

echo "</div>";

//popup msg
echo "<div id ='my-smallmsg-error' class='my-smallmsg-error'>Choose at least someone to proceed, please!</div>";

echo "</div>"; //div mygrey-bg ends
//////////////////////////////////////////////
}




//js do bootstrap
echo "
<script src=\"bootstrap/js/addons/datatables.min.js\" type=\"text/javascript\"></script>
<script src=\"bootstrap/js/addons/datatables-select.min.js\" type=\"text/javascript\"></script>";


echo $OUTPUT->footer();

?>

<script>

$(document).ready(function() {

var today = new Date().toISOString().split('T')[0];
document.getElementsByName("start-date")[0].setAttribute('min', today);
document.getElementsByName("due-date")[0].setAttribute('min', today);


//pegar data
$('#id_save_btn').on('click', function(){

  var todaynow = new Date().setHours(0,0,0,0);

  var startdateF = yearMonthDayToMonthDayYear($('#start-date').val());
  var date = new Date(startdateF);  
  var day = date.getDate();
  var month = date.getMonth() + 1;
  var year = date.getFullYear();

  var startdate0Hours = date.setHours(0,0,0,0);
  
  var duedateF = yearMonthDayToMonthDayYear($('#due-date').val());
  var date2 = new Date(duedateF);
  var day2 = date2.getDate();
  var month2 = date2.getMonth() + 1;
  var year2 = date2.getFullYear();

  var unixstartdate = ""+(date.getTime()/ 1000) +""; 
  var unixenddate =  ""+(date2.getTime()/ 1000) +"";

  var evtype = $("#sel-ev-type option:selected").text();
  var trialname = ""+ $("#final-name").val() + "";

  if(trialname.trim() == ""){
    $("#final-name").focus();
    return false;
  }
  else if(unixstartdate == "NaN"){
    $("#start-date").focus();
    return false;
  }
  else if(unixenddate == "NaN"){
    $("#due-date").focus();
    return false;
  }
  else if(unixenddate <= unixstartdate){
    alert('A data final deve ser maior que a inicial');
    $("#due-date").val(null);
    return false;
  }
  else if(startdate0Hours < todaynow){
    alert('A data inicial não pode ser anterior a hoje');
    $("#start-date").focus();
    $("#start-date").val(null);
    return false;
  }
  else{
    /*
    alert("UMA AVALIAÇÃO DO TIPO "+ evtype + " SERÁ MARCADA PARA O DIA: \n"+ 
    day +"/" + month + "/" + year + "\n E TERMINARÁ NO DIA: \n"+
    day2+"/" + month2 + "/" + year2);

    alert("ESSE PLUGIN AINDA ESTA EM DESENVOLVIMENTO"); */

    var evtypeid = $("#sel-ev-type").val();

    $("#hidden-name").val(trialname);
    $("#hidden-start").val(unixstartdate);
    $("#hidden-end").val(unixenddate);
    $("#hidden-type").val(evtypeid);
    $("#hidden-started").val("0");


    //pequeno ajax
    //ajax
    var dados = $("#frm-hidden-trial").serialize();

    $.ajax({
        method: 'POST',
        url: 'print/insert_trialdetail.php',
        data: dados,

        beforeSend: function(){$("#return-div").html("loading...");}
    })
    .done(function(msg){
        $("#return-div").html(msg);

    })
    .fail(function(){
        $("#return-div").html("Failed to save!");
    });

  }


});

//save and start
$('#id_save_next_btn').on('click', function(){

  var startdateF = yearMonthDayToMonthDayYear($('#start-date').val());
  var date = new Date(startdateF);
  var day = date.getDate();
  var month = date.getMonth() + 1;
  var year = date.getFullYear();
  
  var duedateF = yearMonthDayToMonthDayYear($('#due-date').val());
  var date2 = new Date(duedateF);
  var day2 = date2.getDate();
  var month2 = date2.getMonth() + 1;
  var year2 = date2.getFullYear();

  var unixstartdate = ""+(date.getTime()/ 1000) +""; 
  var unixenddate =  ""+(date2.getTime()/ 1000) +"";

  var evtype = $("#sel-ev-type option:selected").text();
  var trialname = "" + $("#final-name").val() + "";

  if(trialname.trim() == ""){
    $("#final-name").focus();
    return false;
  }
  else if(unixstartdate == "NaN"){
    $("#start-date").focus();
    return false;
  }
  else if(unixenddate == "NaN"){
    $("#due-date").focus();
    return false;
  }
  else if(unixenddate <= unixstartdate){
    alert('A data final deve ser maior que a inicial');
    $("#due-date").val(null);
    return false;
  }
  else{
    var evtypeid = $("#sel-ev-type").val();

    $("#hidden-name").val(trialname);
    $("#hidden-start").val(unixstartdate);
    $("#hidden-end").val(unixenddate);
    $("#hidden-type").val(evtypeid);
    $("#hidden-started").val("1"); //means we should display

    //pequeno ajax
    //ajax
    var dados = $("#frm-hidden-trial").serialize();

    $.ajax({
        method: 'POST',
        url: 'print/insert_trialdetail.php',
        data: dados,

        beforeSend: function(){$("#return-div").html("loading...");}
    })
    .done(function(msg){
        $("#return-div").html(msg);
        window.location.replace("index.php");

    })
    .fail(function(){
        $("#return-div").html("Failed to save!");
    });

  }


});


/////////////////////////////
//verificar campos como nome e data se já existentes e alterar na hora
var trialid = '<?= $trialid ?>';
atribuirCamposSalvos(trialid);



}); //fimm onready



//previous page function
$("#id_back_btn").on("click", function(){
  window.location.replace("selectquestionsdb.php");
});


function atribuirCamposSalvos(trialid){
  var functionid = 13;

  var values = {        
        'function' : functionid,
        'trialid' : trialid,
  };

  $.ajax({
        method: 'POST',
        url: 'print/callphpfunctions.php',
        data: values,
    })
    .done(function(msg){
        console.log(msg);

        const objTrial = JSON.parse(msg);

        let id = objTrial.id;
        let title = objTrial.title;
        let startdate = objTrial.startdate;
        let enddate = objTrial.enddate;
        let evtype = objTrial.evtype;

        $("#final-name").val(title); //TITULO

        //data inicial fix
        if(startdate == null){
          var unixTime = new Date().getTime() + "";
          startdate = unixTime.substr(0, 10);          
        }
        //data inicial
        startdate = unixDateStringCorrector(startdate);

        $("#start-date").val(startdate); //DATA INCIAL

        //data final fix
        if(enddate == null){
          var unixTime = new Date().getTime() + "";
          enddate = unixTime.substr(0, 10);          
        }

        //data final
        enddate = unixDateStringCorrector(enddate);
        $("#due-date").val(enddate);

        //tipo de trial
        if(evtype == 1){$("#sel-ev-type").val("1").change();}
        else if(evtype == 2){$("#sel-ev-type").val("2").change();}
        else if(evtype ==3){$("#sel-ev-type").val("3").change();}

    })
    .fail(function(){
        alert('Algo deu errado ao tentar recuperar os dados!');
    });
}

//pega uma string unix e tranforma no formato de data correto para o calendario
function unixDateStringCorrector(datestring){
  var unixHoje = new Date().getTime() + "";
  unixHoje = unixHoje.substr(0, 10);

  var dateF = new Date(datestring * 1000);
  let year = dateF.getFullYear();
  let month = (dateF.getMonth() + 1) + "";
  let day = (dateF.getDate()) + "";

  if(month.length < 2){month = '0' + month;}
  if (day.length < 2){day = '0' + day;}
  
  var dateForInput = year + "-" + month + "-" + day;
  
  return dateForInput;
}

function yearMonthDayToMonthDayYear(str_yyyy_mm_dd){
  var arrDate = str_yyyy_mm_dd.split("-");
  let day = arrDate[2];
  let month = arrDate[1];
  let year = arrDate[0];

  var newDateString = month + "-" + day + "-" + year;
  return newDateString;
}


</script>