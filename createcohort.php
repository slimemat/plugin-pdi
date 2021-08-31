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
require_login();

$PAGE->set_url(new moodle_url('/local/pdi/createcohort.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title("PDI Admin");
$PAGE->set_heading('PDI Admin');
$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url($CFG->dirrroot . '/local/pdi/scripts/pdiscript.js'));

global $USER, $DB;



////////
//recupera valores da tabela escondida e salva
if(isset($_POST['hidden-ids']) and isset($_POST['hidden-gname'])){

  //id dos membros
  $idArray = json_decode($_POST['hidden-ids']);

  //cohort
  $cohortData = new stdClass();
  $tempoCriado = time();
  $gname = $_POST['hidden-gname'];


  $gdesc = "This cohort was created by the PDI PLUGIN ADMIN";

  $cohortData->contextid = 3; //quer dizer miscelânea
  $cohortData->name = "$gname";
  $cohortData->description = $gdesc;
  $cohortData->visible = 0;
  $cohortData->descriptionformat = 1;
  $cohortData->timecreated = $tempoCriado;
  $cohortData->timemodified = $tempoCriado;

  $DB->insert_record('cohort', $cohortData); //cria o cohort

  //add members
  //first, retrieve the cohort data
  $sqlgetcohort = "SELECT `id`, `name`, `description`, `timecreated` 
  FROM {cohort} 
  WHERE timecreated = '$tempoCriado' 
  and name = '$gname'";
  $resgetcohort = $DB->get_records_sql($sqlgetcohort);
  $gid = "";
  foreach($resgetcohort as $g){ $gid = $g->id; }

  foreach($idArray as $r){
    $sql = "SELECT id, username, email FROM {user} WHERE id = '$r'";
    $res = $DB->get_records_sql($sql);

    foreach($res as $row){
      //
      $addCMember = new stdClass();
      $addCMember->cohortid = $gid;
      $addCMember->userid = $row->id;
      $addCMember->timeadded = time();

      $DB->insert_record('cohort_members', $addCMember);
    }
  }
  redirect($CFG->wwwroot . '/local/pdi/addevaluated.php', 'Cohort creation saved!');

}


//verifica se o logado é adm
verifyAdm($USER->username);


/////////////////////////////////////////
///////////////////
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


////////////////////////////////////////
//actual page for admin
echo "<div id='myblue-bg'>";
echo "<span><a href='addevaluated.php' class='pdi-nostyle my-marginr'>back</a></span>";
echo "<span><a href='index.php' class='pdi-nostyle my-marginr'>dashboard</a></span>";
echo "<div class='mypush'><span class='mylogo'>PDI</span></div>";
echo "</div><br>";

echo "<div id='mygrey-bg'>"; //grey bg starts


echo "<h1>Step 2.1 - Create cohort</h1>";
echo "<footer class='my-belowh1'>Type the required data to create a cohort
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
onclick='window.location.href = \"addevaluated.php?stepnav\"'
style=\"background-color: var(--myprimary);\">2</span>
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
<span class=\"my-circle\"
onclick='window.location.href = \"finalstep5.php?stepnav\"'>5</span>
<footer>step 5</footer>
</div>";

echo "</div>";
/////
/////
echo "<br><br><br><br>";

//tabela
echo "<div>";


//form com ids escondidos e nome do cohort
echo "<label for='cohort-name' class='my-label'>Group name</label>";
echo "<input type='text' name='cohort-name' id='cohort-name' class='my-large-input'>";

echo "<form id=\"my-id-form\" name=\"my-id-form\" method=\"post\" action=\"createcohort.php?almost\">";
echo "<input type=\"hidden\" name=\"hidden-ids\" id=\"hidden-ids\" value=\"\">";
echo "<input type=\"hidden\" name=\"hidden-gid\" id=\"hidden-gid\" value=\"\">";
echo "<input type=\"hidden\" name=\"hidden-gname\" id=\"hidden-gname\" value=\"\">";
echo "<input type=\"hidden\" name=\"hidden-btn-pick\" id=\"hidden-btn-pick\" value=\"\">";
echo "</form>";

echo "<table id=\"dt-members\" class=\"table mydark-table my-pointer my-highlight\" cellspacing=\"0\" width=\"100%\">
<thead>
  <tr>
    <th>ID</th>
    <th>Username</th>
    <th>Email</th>
    <th>Company</th>
    <th>Full name</th>
  </tr>
</thead>
<tfoot>
  <tr>
    <th>ID</th>
    <th>Username</th>
    <th>Email</th>
    <th>Company</th>
    <th>Full name</th>
  </tr>
</tfoot>
</table>";
echo "</div>";


echo "<br>";
echo "<span id='span-evaluators' class='my-label'>Members selected: </span>";



//bottom buttons

echo "<div id='div-save-buttons'>";


echo "<input type='button' id='id_back_btn' class='div-save-btn my-grey-btn'
value='Back'>";
echo "<input type='button' id='id_save_next_btn' class='div-save-btn my-primary-btn my-marginlauto'
value='Save and Next'>";

echo "</div>";


//popup msg
echo "<div id ='my-smallmsg-error' class='my-smallmsg-error'>Choose at least someone to proceed, please!</div>";
echo "<div id ='my-smallmsg-error2' class='my-smallmsg-error'>Type a valid name, please!</div>";
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

//abrir coortes
$(document).ready(function() {

var dataSet = <?= json_encode($outmoodle_lista, JSON_UNESCAPED_UNICODE) ?>; //valor chamado do php

//tabela dinamica
var table = $('#dt-members').DataTable({
data: dataSet,
columns: [
{
title: "ID"
},  
{
title: "Username"
},
{
title: "Email"
},
{
title: "Company"
},
{
title: "Fullname"
}
],

dom: 'Bfrtip',
select: 'multi',
buttons: [{
text: 'Select all',
action: function () {
table.rows().select();
}
},
{
text: 'Select none',
action: function () {
table.rows().deselect();
}
}
]

});


//eventos da tabela
table
.on( 'select', function ( e, dt, type, indexes ) {
    var rowData = table.rows( indexes ).data().toArray();
    console.log(rowData);
    
    var eArr = rowData.values();
    var mArray = (eArr.next().value);
    var obj = Object.assign({}, mArray);
    
    var id = obj[0];
    var fullname = obj[4];

    var id_index = "id_"+indexes;

    $( "#span-evaluators" )
    .after( "<span class='selected-block' id='"+id_index +"' data-id='"+id +"'>"+ fullname +"</span>" );
    
} )
.on( 'deselect', function ( e, dt, type, indexes ) {
    var rowData = table.rows( indexes ).data().toArray();
    console.log("remover rowData");

    var id_index = "id_"+indexes;

    $("#"+id_index).remove();
    
} );

/*
//help hover
$( ".my-help-pop" ).hover(
  function() {
    $( this ).append( $( "<span class='help-div'>Lorem ipsum dolor Lorem ipsum Lorem ipsun Lorem dolor Lorem ameno</span>" ) );
  }, function() {
    $( this ).find( "span" ).last().remove();
  }
);
*/


$('#cohort-select').change(function(){ 
    var value = $(this).val();
    
    $('#form-s-cohort').attr('action', 'createcohort.php#cohort-select');
    $('#form-s-cohort').submit();

    //pegar o post lá em cima no php e mandar pra tabela em json string
    
});

//esconder as informações de seleção data table
$("#dt-members_info").hide();


}); //fim on ready


//onready para salvar e continuar
$(document).ready(function() {

  //CREATE GROUP BTN
  $( "#id_save_next_btn" ).on( "click", function() {
    const arrayIds= [];

    $('.selected-block').each(function(){
      var thisBlock = $(this);
      var strId = thisBlock.attr('data-id');

      //console.log(thisBlock.attr('data-id'));
      arrayIds.push(strId);
    });

    //console.log(arrayIds);
    var strJson = JSON.stringify(arrayIds);
    //console.log(strJson);

    strCohortname = $("#cohort-name").val();
    //alert('nome: '+ strCohortname);

    if(arrayIds.length > 0 && strCohortname.length > 1){

      var groupid = $("#cohort-select").val();

      $("#hidden-ids").val(strJson);
      $("#hidden-gid").val(groupid);
      $("#hidden-gname").val(strCohortname);

      document.forms["my-id-form"].submit();
    }
    else if(strCohortname.length < 1){
      $("#my-smallmsg-error2").fadeIn(200);
      $("#my-smallmsg-error2").css("display", "flex");
      $("#my-smallmsg-error2").delay(2000).fadeOut(400);

      $("#cohort-name").focus();
    }
    else{
      //my-smallmsg-error
      $("#my-smallmsg-error").fadeIn(200);
      $("#my-smallmsg-error").css("display", "flex");
      $("#my-smallmsg-error").delay(2000).fadeOut(400);
    }

  });

  //previous page function
$("#id_back_btn").on("click", function(){
  window.location.replace("addevaluated.php");
});


//seleção do dropdown
var selectBox = "" + <?php echo "$cvalue" ?> + "";
console.log("valor "+ selectBox);

$("#cohort-select").val(selectBox);

}); //fim on ready

</script>