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

//report das notas
$html_notas = fetchTablesGrades($trialid, $currentuid);


//page STARTS HERE
echo $OUTPUT->header();


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
echo "<span><a href='pdistudent.php' class='pdi-nostyle my-marginr'>dashboard</a></span>";
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

<hr>
<h5 class='my-font-family my-padding-sm'>Notas</h5>
<footer class='my-padding-sm my-footer2'>Apenas as questões que já tem uma escala recebem uma nota.</footer>

<div id='my-tab1-inner2'>

$html_notas
    
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

  <div id='my-tab2-inner2' style='padding: 0 10% 0 10%;'>


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

//hidden messages
echo "<div id ='my-smallmsg-error' class='my-smallmsg-error'>Poucos caracteres!</div>";
echo "<div id ='my-smallmsg-success' class='my-smallmsg-success'>Objetivo adicionado!</div>";
echo "<div id ='my-smallmsg-success2' class='my-smallmsg-success'>Objetivo atualizado!</div>";

//hidden form
echo "<form id=\"frm-anstatus-id\" name=\"frm-anstatus-id\" class='hidden' method=\"post\" action=\"\">";
echo "<input type=\"hidden\" name=\"hidden-anstatus-id\" id=\"hidden-anstatus-id\" value=\"\">";
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

$(".my-youev").on("click", function(){
  var alunoid = $(this).attr("data-uid");
  var sectorid = $(this).attr("data-sector");
  var trialid = $(this).attr("data-trial");
  var functionid = 3; //verificar callphpfunctions.php para ver qual é a três

  //esquema de mudar o estilo do selecionado
  //estilo
  $(".my-youev").each(function(){
      $(this).removeClass("my-bg-greylight-round");
    });
    $(this).addClass("my-bg-greylight-round");

  var values = {
        'alunoid'  : alunoid,
        'sectorid' : sectorid,
        'trialid'  : trialid,
        'function' : functionid
  };

  $.ajax({
        method: 'POST',
        url: 'print/callphpfunctions.php',
        data: values,

        beforeSend: function(){  }
    })
    .done(function(msg){
        var resposta = msg;

        $("#my-tab2-inner2").html(resposta);
        
        //mostrar os bloquinhos
        fetchBlocosGoal(alunoid, sectorid, trialid)
        
    })
    .fail(function(){
        alert('Algo deu errado!');
    });


});

//btn criar objetivo
$("#my-tab2-inner2").on("click", "#btn-add-goal", function(){
  //var
  var title = $("#input-nome-goal").val();
  var desc = $("#input-desc-goal").val();
  var alunoid = $("#hidden-aluno-id").val();
  var sectorid = $("#hidden-sector-id").val();
  var trialid = $("#hidden-trial-id").val();
  var functionid = 4; //verificar callphpfunctions.php qual é a quatro

  if(title.length <= 5){
    $("#input-nome-goal").focus();
    $("#my-smallmsg-error").fadeIn(200);
    $("#my-smallmsg-error").css("display", "flex");
    $("#my-smallmsg-error").delay(2000).fadeOut(400);
    
    return false;
  }
  else if(desc.length <= 10){
    $("#input-desc-goal").focus();
    $("#my-smallmsg-error").fadeIn(200);
    $("#my-smallmsg-error").css("display", "flex");
    $("#my-smallmsg-error").delay(2000).fadeOut(400);

    return false;
  }

  //ajax values
  var values = {
        'alunoid'  : alunoid,
        'sectorid' : sectorid,
        'trialid'  : trialid,
        'title' : title,
        'desc'  : desc,
        'function' : functionid
  };

  //ajax
  $.ajax({
        method: 'POST',
        url: 'print/callphpfunctions.php',
        data: values,

        beforeSend: function(){  }
    })
    .done(function(msg){
        var resposta = msg;

        //ok
        //console.log(msg);

        if(resposta == "ok"){
          //limpar campos
          $("#input-nome-goal").val("");
          $("#input-desc-goal").val("");

          //mensagem de sucesso
          $("#my-smallmsg-success").fadeIn(200);
          $("#my-smallmsg-success").css("display", "flex");
          $("#my-smallmsg-success").delay(2000).fadeOut(400);  

          //mostrar os bloquinhos
          //fazer uma nova consulta
          fetchBlocosGoal(alunoid, sectorid, trialid)                                     
          
        }
        else{
          alert(resposta);
        } 
        
    })
    .fail(function(){
        alert('Algo deu errado!');
    });

});

//botão de editar card de objetivos
$("#my-tab2").on("click", ".btn-edit-goal", function(){
  
  var idgoal = $(this).attr("data-idgoal");
  
  //ocultar e mostrar
  $("#h-goal-"+idgoal+"").hide(100);
  $("#lbl-input-"+idgoal+"").show(100);
  $("#input-edit-"+idgoal+"").show(100);

  $("#p-goal-"+idgoal+"").hide(100);
  $("#lbl-text-"+idgoal+"").show(100);
  $("#text-edit-"+idgoal+"").show(100);

  $("#btn-edit-goal-"+idgoal+"").hide(200);
  $("#btn-cancel-goal-"+idgoal+"").show(200);
  $("#btn-save-goal-"+idgoal+"").show(200);

});

//botão de cancelar edição do card
$("#my-tab2").on("click", ".btn-cancel-goal", function(){

  var idgoal = $(this).attr("data-idgoal");

  //var
  var alunoid = $("#hidden-aluno-id").val();
  var sectorid = $("#hidden-sector-id").val();
  var trialid = $("#hidden-trial-id").val();

  //ocultar edição, mostrar padrão
  $("#lbl-input-"+idgoal+"").hide(100);
  $("#input-edit-"+idgoal+"").hide(100);
  $("#h-goal-"+idgoal+"").show(100);

  $("#lbl-text-"+idgoal+"").hide(100);
  $("#text-edit-"+idgoal+"").hide(100);
  $("#p-goal-"+idgoal+"").show(100);

  $("#btn-cancel-goal-"+idgoal+"").hide(200);
  $("#btn-save-goal-"+idgoal+"").hide(200);
  $("#btn-edit-goal-"+idgoal+"").show(200);

  //mostrar os bloquinhos de novo
  fetchBlocosGoal(alunoid, sectorid, trialid)   

});

//botão de salvar a edição do card
$("#my-tab2").on("click", ".btn-save-goal", function(){

  var idgoal = $(this).attr("data-idgoal");
  var functionid = 6; //verificar 6 no callphpfunctions.php

  //recuperar novos valores
  var txtTitle = $("#input-edit-"+idgoal+"").val().trim();
  var txtDesc = $("#text-edit-"+idgoal+"").val().trim();

  //var aluno
  //var
  var alunoid = $("#hidden-aluno-id").val();
  var sectorid = $("#hidden-sector-id").val();
  var trialid = $("#hidden-trial-id").val();

  //ajax values
  var values = {
        'idgoal'    : idgoal,
        'txttitle'  : txtTitle,
        'txtdesc'   : txtDesc,
        'function' : functionid
  };

  //ajax
  $.ajax({
        method: 'POST',
        url: 'print/callphpfunctions.php',
        data: values,

        beforeSend: function(){ $("#btn-save-goal-"+idgoal+"").html("salvando...") }
    })
    .done(function(msg){
        resposta = msg;    
        
        $("#btn-save-goal-"+idgoal+"").html("<i class=\"far fa-save\"></i>")
        
        if(resposta == 1){
          //mensagem de sucesso UPDATE
          $("#my-smallmsg-success2").fadeIn(200);
          $("#my-smallmsg-success2").css("display", "flex");
          $("#my-smallmsg-success2").delay(2000).fadeOut(400);  
      
          //mostrar os bloquinhos atualizados
          fetchBlocosGoal(alunoid, sectorid, trialid)                                     
        } 
        else{
          alert("Não foi possível salvar! Tente novamente.");
        }
        
    })
    .fail(function(){
        alert('Algo deu errado ao salvar!');
    });

});


//função que traz os blocos de objetivos
function fetchBlocosGoal(alunoid, sectorid, trialid){

  var functionid = 5; //verificar callphpfunctions.php qual é a cinco
  var resposta = "";

  //ajax values
  var values = {
        'alunoid'  : alunoid,
        'sectorid' : sectorid,
        'trialid'  : trialid,
        'function' : functionid
  };

  //ajax
  $.ajax({
        method: 'POST',
        url: 'print/callphpfunctions.php',
        data: values,

        beforeSend: function(){  }
    })
    .done(function(msg){

        resposta = msg;

        //resposta = linkify(resposta);
      
        $("#div-cards").html("");
        $("#div-cards").append(resposta);

        //parte de links avaliador
        var lblDesc = $(".lbl-obj-onoff");

        lblDesc.each(function(){
          var texto = $(this).text();
          texto = linkify(texto);
          $(this).html(texto);
        });

        //essa parte que é o feedback
        var lblDesc = $(".mylabel-onoff");
      
        lblDesc.each(function(){

          var texto = $(this).text();
          texto = linkify(texto);
          $(this).html(texto);
        });
        
    })
    .fail(function(){
        alert('Algo deu errado ao carregar!');
    });

}

//código que ativa os acorddions que foram gerados no php statusfunctions
$("#my-tab2").on("click", ".acordeon-header", function() {
  $(this).toggleClass("active").next().slideToggle();
});


//função de criar url clicavel
function linkify(text) {
    var urlRegex =/(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
    return text.replace(urlRegex, function(url) {
        return '<a href="' + url + '" target="_blank">' + url + '</a>';
    });
}


$("#my-tab2").on("click", "#btn-add-course", function(){
  
  var functionid = 11; //verificar callphpfunctions.php qual é a 11
  var coursecatid = $("#form-create-course").attr("data-id-coursecat");
  var coursename = $("#input-nome-course").val().trim();
  var trialid = "" + <?= $_POST['hidden-trial-id'] ?> + "";

  if(coursename.length < 5){
    $("#input-nome-course").focus();
    $("#my-smallmsg-error").fadeIn(200);
    $("#my-smallmsg-error").css("display", "flex");
    $("#my-smallmsg-error").delay(2000).fadeOut(400);

    return false;
  }

  //ajax values
  var values = {       
        'coursecatid'  : coursecatid,
        'coursename' : coursename,
        'trialid'   : trialid,
        'function' : functionid
  };

  //ajax
  $.ajax({
        method: 'POST',
        url: 'print/callphpfunctions.php',
        data: values,

        beforeSend: function(){ 
          $("#my-tab2 #btn-add-course").html("Criando...");
          $("#my-tab2 #btn-add-course").prop("disabled",true);
        }
    })
    .done(function(msg){
      $("#my-tab2 #btn-add-course").html("Criar");
      $("#my-tab2 #btn-add-course").prop("disabled",false);
        if(msg == "ok"){
          //atualizar        
          //atualiza clicando no avaliado ativo, que tem essa classe
          $("#my-tab2-inner .my-bg-greylight-round").click();
          console.log("ok, atualizar reunião");
        }
        else{
          console.log("DONE, but not ok");
          console.log(msg);
        }
        
    })
    .fail(function(msg){
      $("#my-tab2 #btn-add-course").html("Criar");
      $("#my-tab2 #btn-add-course").prop("disabled",false);
        alert('Algo deu errado ao carregar!');
        console.log(msg);
    });

});


//botões dentro da reunião criada
$("#my-tab2").on("click", "#btn-ver-reuniao", function(){
  var urlCourse = $(this).attr('data-url');
  window.location.href = urlCourse;
});


$("#my-tab2").on("click", "#btn-ocultar-curso", function(){
  var functionid = 12;
  var courseid = $(this).attr('data-cid');

  var values = {        
        'function' : functionid,
        'courseid' : courseid,
  };

  $.ajax({
        method: 'POST',
        url: 'print/callphpfunctions.php',
        data: values,
    })
    .done(function(msg){
        if(msg == "ok"){
          //atualiza clicando no avaliado ativo, que tem essa classe
          $("#my-tab2-inner .my-bg-greylight-round").click();
        }
        else{
          alert("Não foi possível alterar a visibilidade!");
        }
    })
    .fail(function(){
        alert('Algo deu errado ao tentar ocultar!');
    });
});






});

</script>