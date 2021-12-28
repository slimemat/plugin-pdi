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
require_once('print/outquestiondata.php');
require_login();

$PAGE->set_url(new moodle_url('/local/pdi/selectquestionsdb.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title("PDI Admin");
$PAGE->set_heading('PDI Admin');
$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url('/local/pdi/scripts/pdiscript.js'));

global $USER, $DB;

//form instance

//verifica se o logado é adm
verifyAdm($USER->username);

/////////////////////////////////////////
//verifica se o post foi feito
if(isset($_POST['hidden-ids'])){
  $idArray = json_decode($_POST['hidden-ids']);
  $btnPick = $_POST['hidden-btn-pick'];
  
  foreach($idArray as $r){
    $sql = "SELECT id, username, email FROM mdl_user WHERE id = '$r'";
    $res = $DB->get_records_sql($sql);

    foreach($res as $row){

      $postId = $row->id;
      $postEmail = $row->email;
      $postUsername = $row->username;

      $addEvaluator = new stdClass();
      $addEvaluator->evarole = "evaluator";
      $addEvaluator->evasector = "";
      $addEvaluator->evatimeadd = time();
      $addEvaluator->mdlid = $postId;

      $DB->insert_record('local_pdi_evaluator', $addEvaluator);

    }
  }

  if($btnPick == "0"){
    redirect($CFG->wwwroot . '/local/pdi/createtrial.php', 'Evaluators added!');
  }
  else{
    redirect($CFG->wwwroot . '/local/pdi/addevaluated.php', 'Evaluators added! Let us continue.');
  }

  
}

///////////////////////////////////////////////////
/////////////////////////////////////////////////

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


//para esconder o form
if(isset($_SESSION['authadm']) and $_SESSION['authadm'] == 'yes'){


  ///fetch data to de datatable
  //pegar o valor da trial atual
  //$timeCreated = $_SESSION['mytime'];
  $trialid = $_SESSION['edittrialid'];
  $rSQL = "SELECT * FROM {local_pdi_trial} WHERE createdby = '$USER->id' and id = $trialid";
  $resultado = $DB->get_records_sql($rSQL);
  $trialID;
  foreach($resultado as $t){$trialID = $t->id;}

  $dataSet = fetchSectors($trialID);
  //chamar na tabela com js



////////////////////////////////////////
//actual page for admin
echo "<div id='myblue-bg'>";
echo "<span><a href='selectsectors.php' class='pdi-nostyle my-marginr'>back</a></span>";
echo "<span><a href='index.php' class='pdi-nostyle my-marginr'>dashboard</a></span>";
echo "<div class='mypush'><span class='mylogo'>PDI</span></div>";
echo "</div><br>";

echo "<div id='mygrey-bg'>"; //grey bg starts


echo "<h1>Step 4 - Questions Database</h1>";
echo "<footer class='my-belowh1'>Select the questions from databases
<a tabindex=\"0\" class=\"btn mybelow1\" role=\"button\" data-toggle=\"popover\" data-placement='bottom' data-trigger=\"focus\" title=\"x\" data-content=\"And here's a tip on how to do something.\"><i class=\"far fa-question-circle my-help-pop\"></i></a>
</footer>";

/////
echo "<br><div id='my-steps'>"; //steps

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
<span class=\"my-circle\" style=\"background-color: var(--myprimary);\">4</span>
<footer>step 4</footer>
</div>";

echo "<div class='my-circle-div'>
<span class=\"my-circle\"
onclick='window.location.href = \"finalstep5.php?stepnav\"'>5</span>
<footer>step 5</footer>
</div>";

echo "</div>";
/////

/////
echo "<br><br><br>";


//hidden div manual selection
echo "<div>";
echo "<table id=\"dt-select\" class=\"table mydark-table my-highlight\" cellspacing=\"0\" width=\"100%\">
<thead>
  <tr>
    <th>Sector</th>
    <th>Database selected</th>
    <th>Selection</th>
  </tr>
</thead>
<tfoot>
  <tr>
    <th>Sector</th>
    <th>Database selected</th>
    <th>Selection</th>
  </tr>
</tfoot>
</table>";
echo "</div>";

echo "<br>";
echo "<input id='btn-create-database' type='button' class='my-primary-btn my-large-input' value='Create a question database'>";

echo "<hr>";

//bottom buttons

echo "<div id='div-save-buttons'>";

echo "<form id=\"frm-hidden-send-db\" name=\"frm-hidden-send-db\" method=\"post\" action=\"\">";
echo "<input type=\"hidden\" name=\"hidden-id\" id=\"hidden-id\" value=\"\">";
echo "<input type=\"hidden\" name=\"hidden-memberid\" id=\"hidden-memberid\" value=\"\">";
echo "</form>";

echo "<input type='button' id='id_back_btn' class='div-save-btn my-grey-btn'
value='Back'>";

echo "<input type='button' id='id_save_next_btn' class='div-save-btn my-primary-btn my-marginlauto'
value='Next'>";

echo "</div>";

//popup msg
echo "<div id ='my-smallmsg-error' class='my-smallmsg-error'>Choose at least something to proceed, please!</div>";

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

$(document).ready(function () {

var dataSet = <?= json_encode($dataSet, JSON_UNESCAPED_UNICODE) ?>;  

var table = $('#dt-select').DataTable({
data: dataSet,
"pageLength": 10,
columns: [
{
title: "Sector"
},
{
title: "Database selected"
},    
{
title: "Selection"
}
],
dom: 'Bfrtip',

});
//fim table


//btn create cohort
$( "#btn-create-database" ).on( "click", function() {
  window.location.href = "createquestiondb.php?criar";  
});

/*
//popup help
$( ".my-help-pop" ).hover(
  function() {
    $( this ).append( $( "<span class='help-div'>Lorem ipsum dolor Lorem ipsum Lorem ipsun Lorem dolor Lorem ameno</span>" ) );
  }, function() {
    $( this ).find( "span" ).last().remove();
  }
);
*/

//add database ao sector member
$(".btn-add-db").on("click", function(){
  var esseID = $(this).attr("id");
  var esseIndex = $(this).attr("data-index");
  var esseMEMID = $(this).attr("data-memid");

  //pega o id do banco selecionado
  var valSelect = $("#sel-index-"+esseIndex+"").val();

  //passa pro form escondido
  $("#hidden-id").val(valSelect);
  $("#hidden-memberid").val(esseMEMID);


  if(valSelect != null){

      //ajax
      var dados = $("#frm-hidden-send-db").serialize();

      $.ajax({
          method: 'POST',
          url: 'print/insert_dbsector.php',
          data: dados,
      })
      .done(function(msg){
          if(msg == "ok"){
            //atualizar
            document.location.reload(true);
          }
          else{
            alert("algo deu errado!");  
          }
      })
      .fail(function(){
          alert("algo deu errado!");
      });
  }
  else{
        $("#my-smallmsg-error").fadeIn(200);
        $("#my-smallmsg-error").css("display", "flex");
        $("#my-smallmsg-error").delay(2000).fadeOut(400);
  }
  
});








});//fim onready

//previous page function
$("#id_back_btn").on("click", function(){
  window.location.replace("selectsectors.php");
});

//next
$("#id_save_next_btn").on("click", function(){
  window.location.replace("finalstep5.php");
});



</script>