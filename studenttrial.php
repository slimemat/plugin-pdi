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
require_once('print/outrankingtrial.php');
require_once('print/trialsfunctions.php');
require_login();

$PAGE->set_url(new moodle_url('/local/pdi/studenttrial.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title("PDI Student - Trial");
$PAGE->set_heading('PDI Student');
$PAGE->requires->jquery();
//$PAGE->requires->js(new moodle_url($CFG->dirrroot . '/local/pdi/scripts/pdiscript.js'));

global $USER, $DB;

//chamar funções

//report das notas
//pegar o id do avaliador desse processo
$avaliatorid = ;
$html_notas = fetchTablesGrades($trialid, $avaliatorid);

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
echo "<span><a href='studentshowall.php' class='pdi-nostyle my-marginr'>back</a></span>";
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

echo "
<div id='my-tab1' class='my-inside-container my-hidden'>

      <h5 class='my-font-family my-padding-sm'>Notas</h5>
      <footer class='my-padding-sm my-footer2'>Apenas as questões que já tem uma escala recebem uma nota.</footer>

      <div id='my-tab1-inner2'>

        $html_notas
          
      </div>

</div>";

echo "<div id='my-tab2' class='my-inside-container my-hidden'>my IDP</div>";

}
else{
  echo "
  <div id='my-tab1' class='my-inside-container'>

    <div class=\"card\">
        <div class=\"card-body my-bg-light\">
          <h5 class=\"card-title\">Vazio...</h5>
          <p class=\"card-text my-font-family\">É necessário <b class='my-label-btn btn-responder' data-idtrial='$trialid'>responder</b> esse processo antes de ver os relatórios.</p>
        </div>
    </div>

  </div>
  
  <div id='my-tab2' class='my-inside-container my-hidden'>

  <div class=\"card\">
      <div class=\"card-body my-bg-light\">
        <h5 class=\"card-title\">Vazio...</h5>
        <p class=\"card-text my-font-family\">É necessário <b class='my-label-btn btn-responder' data-idtrial='$trialid'>responder</b> esse processo antes de ver os relatórios.</p>
      </div>
  </div>

  </div>

  ";

  //formulário de respostas
  echo "<div id='big-back-btn' class='my-big-btn my-hidden-2'>Voltar para os processos</div>";
  echo "<div class='my-scroll my-hidden mx-auto' id='scroll-div-2' style='max-width: 70%; box-shadow: 1px 1px 5px grey;'>
  </div>";
  echo "<div id='div-q-save-btns' class='mx-auto my-hidden'>
  <div class='grey-bottom-block'>
    <input type='button' id='btn_salvar' class='my-grey-btn my-marginr btn my-large-input'
        value='Salvar'>
    <input type='button' id='btn_finalizar' class='my-primary-btn my-marginr btn'
        value='Finalizar'>
  </div>
  </div>";
}


//hidden-form
echo "<form id=\"frm-trial-id-evaluate\" name=\"frm-trial-id-evaluate\" class='hidden' method=\"post\" action=\"\">";
echo "<input type=\"hidden\" name=\"hidden-trialid\" id=\"hidden-trialid\" value=\"\">";
echo "<input type=\"hidden\" name=\"hidden-currentuserid\" id=\"hidden-currentuserid\" value=\"$USER->id\">";
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

//************************************************************* */
///////////////////POPUP DE RESPOSTAS


$( ".btn-responder" ).on( "click", function() {
  
  var idtrial = $(this).attr("data-idtrial");
    
    //passar o valor pro form
    $("#hidden-trialid").val(idtrial);
    
    //ajax
    var dados = $("#frm-trial-id-evaluate").serialize();

    $.ajax({
        method: 'POST',
        url: 'print/selectquestionstrial.php',
        data: dados,

        beforeSend: function(){
            $("#scroll-div-2").html("<h3>loading...</h3>");
            $(".my-inside-container").hide(400);          
        }
    })
    .done(function(msg){
        $("#btn-myidp").attr("disabled", true);
        $("#btn-reports").attr("disabled", true);

        $("#big-back-btn").removeClass("my-hidden-2");
        $("#scroll-div-2").show(400);
        $("#scroll-div-2").html(msg);
        $("#div-q-save-btns").show(400);

        $("#btn_salvar").prop("disabled",false);
        $("#btn_finalizar").prop("disabled",false);

    })
    .fail(function(){
        $("#scroll-div-2").html("Falha ao carregar!");
        $("#bloco-div-1").show();
    });


});

$("#btn_salvar").on("click", function(){
    //alert('salvar');

    //para os escritos
    $(".answer").each(function(){
        var txtAnswer = ""+ $(this).val() + "";
        var inputID = $(this).attr("id");
        var setorID = $(this).attr("data-sector");
        //alert("Conteúdo escrito: " + txtAnswer + "\nQuestão id: "+ inputID);

        if(txtAnswer.length < 1){
            //$(this).focus();
            //return false;

            //não salvar esse

        }else{

            saveDaAnswer(txtAnswer, inputID, setorID);
        }


    });
    //para os de escolha
    $(".answer-choice").each(function(){
        var formID = $(this).attr("id"); //também é o id da questão
        var setorID = $(this).attr("data-sector");
        var radioVal = ""+ $("#"+formID+" input[type='radio']:checked").val() + "";
        //alert("Conteúdo escolhido: " + radioVal + "\nQuestão id: "+ formID);

        if(radioVal == "undefined"){
            //$(this).focus();
            //alert('preencher questões de escolha');
            //return false;

            //não fazer nada

        }else{
            saveDaAnswer(radioVal, formID, setorID);

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
        var setorID = $(this).attr("data-sector");
        //alert("Conteúdo escrito: " + txtAnswer + "\nQuestão id: "+ inputID);

        if(txtAnswer.length < 1){
            $(this).focus();
            restantes++;
            return false;
        }else{
            saveDaAnswer(txtAnswer, inputID, setorID);
        }


    });
    //para os de escolha
    $(".answer-choice").each(function(){
        var formID = $(this).attr("id"); //também é o id da questão
        var setorID = $(this).attr("data-sector");
        var radioVal = ""+ $("#"+formID+" input[type='radio']:checked").val() + "";
        //alert("Conteúdo escolhido: " + radioVal + "\nQuestão id: "+ formID);

        if(radioVal == "undefined"){
            $(this).focus();
            //alert('preencher questões de escolha');
            restantes++;
            return false;

        }else{
            saveDaAnswer(radioVal, formID, setorID);
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
function saveDaAnswer(txtanswer, questid, setorid) {
    $("#hidden-questid").val(questid);
    $("#hidden-qsector").val(setorid);
    $("#hidden-qanswer").val(txtanswer);

    //ajax
    var dados = $("#frm-quest-answer").serialize();

    $.ajax({
        method: 'POST',
        url: 'print/saveanswer.php',
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
        url: 'print/finishform.php',
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


$("#big-back-btn").on("click", function(){
  $("#btn-myidp").attr("disabled", false);
  $("#btn-reports").attr("disabled", false);
  $("#big-back-btn").addClass("my-hidden-2");
  $("#scroll-div-2").hide(200);
  $("#div-q-save-btns").hide();
  $("#my-tab1").show();
});

//elemento que ainda não existe na criação
$(document).on('click', '#btn_pop_voltar', function(){
  $("#btn-myidp").attr("disabled", false);
  $("#btn-reports").attr("disabled", false);
  $("#big-back-btn").addClass("my-hidden-2");
  $("#scroll-div-2").hide(200);
  $("#div-q-save-btns").hide();
  $("#my-tab1").show();
});





});

</script>