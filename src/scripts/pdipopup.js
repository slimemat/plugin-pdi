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
        var txtAnswer = $(this).val();
        var inputID = $(this).attr("id");
        var setorID = $(this).attr("data-sector");
        //alert("Conteúdo escrito: " + txtAnswer + "\nQuestão id: "+ inputID);

        saveDaAnswer(txtAnswer, inputID, setorID);

    });
    //para os de escolha
    $(".answer-choice").each(function(){
        var formID = $(this).attr("id"); //também é o id da questão
        var setorID = $(this).attr("data-sector");
        var radioVal = $("#"+formID+" input[type='radio']:checked").val();
        //alert("Conteúdo escolhido: " + radioVal + "\nQuestão id: "+ formID);

        saveDaAnswer(radioVal, formID, setorID);

    });


    ///PEGAR O VALOR DOS SETORES


});

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
        //alert('foi: '+ msg);
    })
    .fail(function(){
        //alert('não foi');
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