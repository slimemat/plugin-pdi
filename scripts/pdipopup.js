require(['jquery'], function($){

$(document).ready(function(){

//fazer o minimizar
//alert('outra coisa');

$("#btn-popover").on("mouseover", function(){
    $("#msg-popover").css("visibility", "visible");
    $("#popover-arrow").css("visibility", "visible");
});
$("#btn-popover").on("mouseout", function(){
    $("#msg-popover").css("visibility", "hidden");
    $("#popover-arrow").css("visibility", "hidden");
});

$("#btn-popover").on("click", function(){
    $("#div-popup").show(500);
    $("#div-closed-popup").hide();
});

$("#btn-minimize-pop").on("click", function(){
    $("#div-popup").hide(500);
    $("#div-closed-popup").show();
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


//each call saves each answer
function saveDaAnswer(txtanswer, questid, setorid) {
    $("#hidden-questid").val(questid);
    $("#hidden-qsector").val(setorid);
    $("#hidden-qanswer").val(txtanswer);

    //ajax
    var dados = $("#frm-quest-answer").serialize();

    $.ajax({
        method: 'POST',
        url: '../local/pdi/print/saveanswer.php',
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
        url: '../local/pdi/print/finishform.php',
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


//elemento que ainda não existe na criação
$(document).on('click', '#btn_pop_voltar', function(){
    $("#scroll-div").show();
    $("#scroll-div-2").hide();
});


//clicar no processo
$(".my-round-card").on("click", function(){

    var idtrial = $(this).attr("data-idtrial");
    
    //passar o valor pro form
    $("#hidden-trialid").val(idtrial);
    
    //ajax
    var dados = $("#frm-trial-id").serialize();

    $.ajax({
        method: 'POST',
        url: '../local/pdi/print/selectquestionstrial.php',
        data: dados,

        beforeSend: function(){
            $("#scroll-div-2").html("<h3>loading...</h3>");
            $("#scroll-div").hide();
        }
    })
    .done(function(msg){
        $("#scroll-div-2").show();
        $("#scroll-div-2").html(msg);

        $("#btn_salvar").prop("disabled",false);
        $("#btn_finalizar").prop("disabled",false);

    })
    .fail(function(){
        $("#scroll-div-2").html("Falha ao carregar!");
        $("#scroll-div").show();
    });

});



}); //fim onready



}); //fim jquery