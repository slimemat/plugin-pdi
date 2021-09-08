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

require_once('print/trialsfunctions.php');
require_once('print/fetchforevaluator.php');

$PAGE->set_url(new moodle_url('/local/pdi/pdistudent.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title("PDI Student");
$PAGE->set_heading('PDI Student');
$PAGE->requires->jquery();

global $USER, $DB;


//page setup

$blocoHtml = mostrarBlocosTrial(0, 6);


//parte avaliar
$retornoBlocos = "";
$retornoBlocos = fetchTrials(0, 6);

///


//page
echo $OUTPUT->header();

echo "<div id='myblue-bg'>";
echo "<span><a href='../../my/index.php' class='pdi-nostyle'>back</a></span>";
echo "</div><br>";

echo "<div id='mygrey-bg'>"; //grey bg starts

echo "<h4>Seus processos de pdi</h4>";
echo "<footer>Clique em um para responder</footer>";
echo "<br>";

///////
echo "<div id='my-centralizadora'>";
echo "<div id='div-dashboard-list'>"; //div dashboard list



/////////
echo "<div id='bloco-div-1'>";

  echo $blocoHtml;

echo "</div>";

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

//////

//btn SHOW ALL
echo "<div id='div-save-buttons'>";
echo "<input type='button' id='id_show_btn' class='my-large-input my-primary-btn my-marginlauto'
value='Show all'>";
echo "</div>";
echo "<hr>";




echo "<br>";

echo "<h4>Para você avaliar</h4>";
echo "<footer>Os processos que você avalia aparecerão aqui</footer><br>";

echo $retornoBlocos;

echo "<div class='div-save-buttons'>";
echo "<input type='button' id='id_show_btn2' class='my-large-input my-primary-btn my-marginlauto'
value='Show all'>";
echo "</div>";

echo "<br><br>";


echo "</div>"; //</div dashboard list
echo "</div>"; //centralizadora
///////////////////


//hidden-form
echo "<form id=\"frm-trial-id-evaluate\" name=\"frm-trial-id-evaluate\" class='hidden' method=\"post\" action=\"\">";
echo "<input type=\"hidden\" name=\"hidden-trial-id\" id=\"hidden-trial-id\" value=\"\">";
echo "</form>";


echo "</div>"; //end of bg-grey


echo $OUTPUT->footer();

?>

<script>

$(document).ready(function() {

//ver todos que avalia
$( "#id_show_btn2" ).on( "click", function() {
  window.location.href = "evalushowall.php";  
});


$( ".my-round-card" ).on( "click", function() {
  //window.location.href = "studenttrial.php";
  
  var idtrial = $(this).attr("data-idtrial");
    
    //passar o valor pro form
    $("#hidden-trialid").val(idtrial);
    
    //ajax
    var dados = $("#frm-trial-id").serialize();

    $.ajax({
        method: 'POST',
        url: 'print/selectquestionstrial.php',
        data: dados,

        beforeSend: function(){
            $("#scroll-div-2").html("<h3>loading...</h3>");
            $("#bloco-div-1").hide(400);
        }
    })
    .done(function(msg){
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



$( "#id_show_btn" ).on( "click", function() {
  window.location.href = "studentshowall.php";  
});


$("#big-back-btn").on("click", function(){
  $("#big-back-btn").addClass("my-hidden-2");
  $("#scroll-div-2").hide(200);
  $("#div-q-save-btns").hide();
  $("#bloco-div-1").show();
});

//elemento que ainda não existe na criação
$(document).on('click', '#btn_pop_voltar', function(){
  $("#big-back-btn").addClass("my-hidden-2");
  $("#scroll-div-2").hide(200);
  $("#div-q-save-btns").hide();
  $("#bloco-div-1").show();
});


//avaliação

$(".my-youev").on("click", function(){

var trialid = $(this).attr("data-id");
$("#hidden-trial-id").val(trialid);

$("#frm-trial-id-evaluate").attr("action", "admintrialalt.php");
$("#frm-trial-id-evaluate").submit();

});


});

</script>