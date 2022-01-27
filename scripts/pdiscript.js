//alert('hello world, js active');

//everything that requires jquery should go inside
require(['jquery'], function($){

    //btn table pdi adm
    $(document).ready(function() {
        var click_state = false;
        $("#btn-collap-select").click(function(){
            if(!click_state){
                $("#hide_select_div").hide(1000);
                click_state = !click_state;
            }
            else{
                $("#hide_select_div").show(1000);;
                click_state = !click_state;
            }
            
        })        
    });

    //btn table pdi moodle users
    $(document).ready(function() {
        var click_state = false;
        $("#btn-collap-see").click(function(){
            if(!click_state){
                $("#pditable-user").hide(1000);
                click_state = !click_state;
            }
            else{
                $("#pditable-user").show(1000);
                click_state = !click_state;
            }
            
        })        
    });

    //btn table students
    $(document).ready(function() {
        var click_state = false;
        $("#btn-collap-stud").click(function(){
            if(!click_state){
                $("#pditable-stud").hide(1000);
                click_state = !click_state;
            }
            else{
                $("#pditable-stud").show(1000);
                click_state = !click_state;
            }   
        }) 
    });

    //btn datatable moodle users
    $(document).ready(function() {
        var click_state = false;
        $("#btn-collap-datatb").click(function(){
            if(!click_state){
                $("#divdttable").hide(1000);
                click_state = !click_state;
            }
            else{
                $("#divdttable").show(1000);
                click_state = !click_state;
            }   
        }) 
    });


    //scroll down shows navigation
    $(document).ready(function() {
        window.onscroll = function() {scrollFunction()};
        
        function scrollFunction() {
            if (document.body.scrollTop > 220 || document.documentElement.scrollTop > 220) {
              document.getElementById("myblue-bg").style.top = "50";
              document.getElementById("myblue-bg").style.right = "0";
              document.getElementById("myblue-bg").style.width = "100%";
              document.getElementById("myblue-bg").style.position = "fixed";
            } else {
              document.getElementById("myblue-bg").style.top = "-50px";
              document.getElementById("myblue-bg").style.width = "100%";
              document.getElementById("myblue-bg").style.position = "sticky";
            }
          }

    });
    
    //rotina do popup
    $(document).ready(function(){

    });

});

