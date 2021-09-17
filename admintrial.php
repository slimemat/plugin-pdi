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
require_once('print/outrankingtrial.php');
require_once('print/fetchforevaluator.php');
require_login();

$PAGE->set_url(new moodle_url('/local/pdi/admintrial.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title("PDI Admin - Trial");
$PAGE->set_heading('PDI Admin');
$PAGE->requires->jquery();
//$PAGE->requires->js(new moodle_url($CFG->dirrroot . '/local/pdi/scripts/pdiscript.js'));

global $USER, $DB;

//código

//verifica se o logado é adm
verifyAdm($USER->username);

//preparação para a lista da tabela
$trialid = $_POST['hidden-trial-id'];
$currentuid = $USER->id;

//rankings
$lista_rank = fetchRankings($trialid, $currentuid);

//questionários
$html_quest = fetchDataQuestions($trialid, $currentuid);

//status
$html_status = fetchStatusAvaliados($trialid, $currentuid);


//page STARTS HERE
echo $OUTPUT->header();

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


//para esconder o form
if(isset($_SESSION['authadm']) and $_SESSION['authadm'] == 'yes'){

//some before page coding
  if(isset($_POST['hidden-trial-id'])){

    //parte 1, dados do processo
    $trialid = $_POST['hidden-trial-id'];

    $resTrial = getTrialById($trialid);

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

    //parte 2, quem respondeu meu setor

    $uid = $USER->id; 
    $blocoResponderam = getWhoAnsweredByTrial($uid, $trialid);
    
  }




//actual page for admin
echo "<div id='myblue-bg'>";
echo "<span><a href='pdiadmin.php' class='pdi-nostyle my-marginr'>dashboard</a></span>";
echo "<span><a href='createtrial.php?newtrial=new' class='pdi-nostyle'>new trial</a></span>";
echo "<div class='mypush'><span class='mylogo'>PDI</span></div>";
echo "</div><br>";

echo "<div>
<input type='button' value='reports' class='my-secondary-btn my-btn-pad' id='btn-reports'>
<input type='button' value='status' class='my-secondary-btn my-btn-pad' id='btn-status'>
<input type='button' value='ranking' class='my-secondary-btn my-btn-pad' id='btn-ranking'>
<input type='button' value='questions database' class='my-secondary-btn my-btn-pad' id='btn-questions'>
</div>";

echo "<div id='mygrey-bg'>"; //grey bg starts

echo "<h1>$trialTitle</h1>";
echo "<footer class='my-belowh1'>$dateInicioF - $dateFimF</footer>";


//bloco com conteúdo gerado no começo da página
echo "
<div id='my-tab1' class='my-inside-container my-hidden'>

<div id='my-tab1-inner'>
  $blocoResponderam
</div>

<div id='nome-do-avaliado' class='my-padding-sm my-qtitle my-hidden my-center'></div>
<div id='big-back-btn' class='my-big-btn my-hidden-2'>Voltar para os processos</div>


<div id='my-tab1-inner-formdiv' class='my-hidden my-scroll mx-auto' style='max-width: 70%; box-shadow: 1px 1px 5px grey;'>
  Carregando...
</div>


<div id='div-q-save-btns' class='mx-auto my-hidden'>

<div class='grey-bottom-block'>
  <input type='button' id='btn_salvar' class='my-grey-btn my-marginr btn my-large-input'
      value='Salvar'>
  <input type='button' id='btn_finalizar' class='my-primary-btn my-marginr btn'
      value='Finalizar'>
</div>

</div>


</div>";

///////////////////////////////trabalhando nessa tela
///////////////////////////////////////////////////////
///////////////////////////////////
$userid_pic = '4';
$imgURL = new moodle_url('/user/pix.php/'.$userid_pic.'/f1.jpg');

///TESTE COM IMAGENS
///////////////////////

echo "<div id='my-tab2' class='my-inside-container my-hidden'>

<div id='my-tab2-inner'>

$html_status



</div>

</div>";
echo "<div id='my-tab3' class='my-inside-container my-hidden'>

  <h5 class='my-font-family my-padding-sm'>Ranking dos avaliados</h5>
  <div id='my-tab3-inner' class='my-padding-sm my-margin-lados my-bg-light shadow-sm p-3 mb-5 rounded'>
  
    <table id=\"dt-ranking\" class=\"table my-highlight\" cellspacing=\"0\" width=\"100%\">
    <thead>
      <tr>
        <th class='col-8'>Nome avaliado</th>
        <th class='col-4'>Média</th>
      </tr>
    </thead>
    
    </table>


  </div>


</div>";
echo "<div id='my-tab4' class='my-inside-container my-hidden'>

<div id='my-tab4-inner'>

  $html_quest

</div>

</div>";



//hidden form
echo "<form id=\"frm-anstatus-id\" name=\"frm-anstatus-id\" class='hidden' method=\"post\" action=\"\">";
echo "<input type=\"hidden\" name=\"hidden-anstatus-id\" id=\"hidden-anstatus-id\" value=\"\">";
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

//tabela

var dataSet = <?= $lista_rank ?>; //valor chamado do php

//tabela dinamica
var table = $('#dt-ranking').DataTable({
data: dataSet,
"pageLength": 25,
columns: [
{
title: "Nome avaliado"
},  
{
title: "Média"
}
],
dom: 'Bfrtip',

});


///fim tabela


//valor padrão
var blockID;

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


$(".my-answer-this").on("click", function(){

  blockID = $(this).attr('data-anstatusid');
  var nomeAvaliado = $(this).children(".my-label-bg").text();

  $("#hidden-anstatus-id").val(blockID);

  //$("#my-tab1-inner-formdiv").show();

  //ajax
  var dados = $("#frm-anstatus-id").serialize();

  $.ajax({
      method: 'POST',
      url: 'print/showevaluationform.php',
      data: dados,

      beforeSend: function(){
        $("#my-tab1-inner").hide(400);
        $("#my-tab1-inner-formdiv").show();
      }

      //CRIAR UM ARQUIVO PHP QUE MANDA O FORM PRA RESPONDER
      //checar o php e ver se é possível criar a função lá
  })
  .done(function(msg){
      $("#big-back-btn").removeClass("my-hidden-2");

      $("#nome-do-avaliado").show();
      $("#nome-do-avaliado").html("Avaliando agora: <b>"+nomeAvaliado+"</b>.");

      $("#div-q-save-btns").show(400);

      $("#my-tab1-inner-formdiv").html(msg);
  })
  .fail(function(){
      $("#my-tab1-inner-formdiv").html("Failed to reach database!");
      $("#my-tab1-inner").show(400);

  });




});


//elementos perguntas
$("#big-back-btn").on("click", function(){
  $("#big-back-btn").addClass("my-hidden-2");
  $("#my-tab1-inner-formdiv").hide(400);
  $("#div-q-save-btns").hide();
  $("#my-tab1-inner").show();
  $("#nome-do-avaliado").html("");
  $("#nome-do-avaliado").hide();
});

$("#btn_salvar").on("click", function(){
    //alert('salvar');

    //para os escritos
    $(".answer").each(function(){
        var txtAnswer = ""+ $(this).val() + "";
        var inputID = $(this).attr("id");
        var anstatusID = blockID;
        //alert("Conteúdo escrito: " + txtAnswer + "\nQuestão id: "+ inputID);

        if(txtAnswer.length < 1){
            //$(this).focus();
            //return false;

            //não salvar esse

        }else{

            saveDaAnswer(txtAnswer, inputID, anstatusID);
        }


    });
    //para os de escolha
    $(".answer-choice").each(function(){
        var formID = $(this).attr("id"); //também é o id da questão
        var anstatusID = blockID;
        var radioVal = ""+ $("#"+formID+" input[type='radio']:checked").val() + "";
        //alert("Conteúdo escolhido: " + radioVal + "\nQuestão id: "+ formID);

        if(radioVal == "undefined"){
            //$(this).focus();
            //alert('preencher questões de escolha');
            //return false;

            //não fazer nada

        }else{
            saveDaAnswer(radioVal, formID, anstatusID);

        }


    });

});


$("#btn_finalizar").on("click", function(){

if (window.confirm("Finalizar\nVocê não poderá alterar mais!")) {    

    var restantes = 0;

    //para os escritos
    $(".answer").each(function(){
        var txtAnswer = ""+ $(this).val() + "";
        var inputID = $(this).attr("id");
        var anstatusID = blockID;
        //alert("Conteúdo escrito: " + txtAnswer + "\nQuestão id: "+ inputID);

        if(txtAnswer.length < 1){
            $(this).focus();
            restantes++;
            return false;
        }else{
            saveDaAnswer(txtAnswer, inputID, anstatusID);
        }


    });
    //para os de escolha
    $(".answer-choice").each(function(){
        var formID = $(this).attr("id"); //também é o id da questão
        var anstatusID = blockID;
        var radioVal = ""+ $("#"+formID+" input[type='radio']:checked").val() + "";
        //alert("Conteúdo escolhido: " + radioVal + "\nQuestão id: "+ formID);

        if(radioVal == "undefined"){
            $(this).focus();
            //alert('preencher questões de escolha');
            restantes++;
            return false;

        }else{
            saveDaAnswer(radioVal, formID, anstatusID);
        }

    });

    if(restantes == 0){
        finishDaForm();
    }else{
        alert("Responda todas as questões para finalizar!");
    }
}
});



//funções que tem no script
//each call saves each answer
function saveDaAnswer(txtanswer, questid, anstatusid) {
    $("#hidden-questid").val(questid);
    $("#hidden-anstatusid").val(anstatusid);
    $("#hidden-qanswer").val(txtanswer);

    console.log("qid "+ questid + " // anstatus "+ anstatusid + "  // answer  "+ txtanswer);

    //ajax
    var dados = $("#frm-quest-answer").serialize();

    $.ajax({
        method: 'POST',
        url: 'print/saveevanswer.php',
        data: dados,

        beforeSend: function(){
            //do nothing 
        }

    })
    .done(function(msg){
        var resposta = msg;
        var originalColor = $("#btn_salvar").css("background");

        //mudar a cor
        $("#btn_salvar").attr('style', 'background-color: var(--mysuccess) !important');
        $("#btn_salvar").attr('value', 'Salvando');

        //esperar para voltar
        setTimeout(function(){
            $("#btn_salvar").attr('style', 'background-color: originalColor !important');
            $("#btn_salvar").attr('value', 'Salvar');
          }, 1000);

    })
    .fail(function(){
        alert('Algo deu errado!');
    });
}


function finishDaForm(){
    //ajax
    var dados = $("#frm-quest-answer").serialize();

    $.ajax({
        method: 'POST',
        url: 'print/finishevform.php',
        data: dados,

        beforeSend: function(){  }
    })
    .done(function(msg){
        var resposta = msg;
        window.location.reload();
    })
    .fail(function(){
        alert('Algo deu errado!');
    });
}



});

</script>