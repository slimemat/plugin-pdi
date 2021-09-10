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
require_once('print/trialsfunctions.php');
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
$retornoBlocos = fetchTrials(0, 6);

$blocoHtml = mostrarBlocosTrial(0, 6);


///


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

//actual page for admin
echo "<div id='myblue-bg'>";
echo "<span><a href='../../my/index.php' class='pdi-nostyle my-marginr'>back</a></span>";
echo "<span><a href='index.php' class='pdi-nostyle my-marginr'>dashboard</a></span>";
echo "<span><a href='createtrial.php?newtrial=new' class='pdi-nostyle my-marginr'>new trial</a></span>";
echo "<span><a href='questions.php' class='pdi-nostyle'>questions</a></span>";
echo "<div class='mypush'><span class='mylogo'>PDI</span></div>";
echo "</div><br>";

echo "<div id='mygrey-bg'>"; //grey bg starts

echo "<h1>Dashboard</h1>";
echo "<footer class='my-belowh1'>Lista de processos abertos para avaliar</footer>";

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
/////////////printado aqui com o conteudo dinamico criado em cima dessa página

echo $retornoBlocos;

////////////////////////////




echo "</div>"; //</div dashboard list
echo "</div>"; //centralizadora
//

echo "<br><br>";

//btn SHOW ALL
echo "<div id='div-save-buttons'>";
echo "<input type='button' id='id_show_btn' class='my-large-input my-primary-btn my-marginlauto'
value='Show all'>";
echo "</div>";


///parte de responder
echo "<div id='my-centralizadora'>";
echo "<div id='div-dashboard-list'>"; //div dashboard list

echo "<br><br>";
echo "<h4>Processos para responder</h4>";
echo "<footer class='my-belowh1'>Lista de processos para responder</footer>";
echo "<br><br>";

//printar os processos para responder
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


echo "<br>";
echo "</div>";
echo "</div>";


//btn SHOW ALL
echo "<div id='div-save-buttons'>";
echo "<input type='button' id='id_show_btn2' class='my-large-input my-primary-btn my-marginlauto'
value='Show all'>";
echo "</div>";


//
//hidden-form
echo "<form id=\"frm-trial-id-evaluate\" name=\"frm-trial-id-evaluate\" class='hidden' method=\"post\" action=\"\">";
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

  $("#frm-trial-id-evaluate").attr("action", "admintrial.php");
  $("#frm-trial-id-evaluate").submit();

});





///////////////////////
//replica da parte de respostas

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

////////////

$( "#id_show_btn2" ).on( "click", function() {
  window.location.href = "studentshowall.php";  
});



});

</script>