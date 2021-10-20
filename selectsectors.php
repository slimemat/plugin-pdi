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
require_once('print/outevaluatorsector.php');
require_login();

$PAGE->set_url(new moodle_url('/local/pdi/selectsectors.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title("PDI Admin");
$PAGE->set_heading('PDI Admin');
$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url($CFG->dirrroot . '/local/pdi/scripts/pdiscript.js'));

global $USER, $DB;

//form instance

//verifica se o logado é adm
verifyAdm($USER->username);

/////////////////////////////////////////

//verifica se o post foi feito
if(isset($_POST['hidden-ids'])){
  $idArray = json_decode($_POST['hidden-ids']);
  $personIdArray = json_decode($_POST['hidden-person-ids']);
  $btnPick = $_POST['hidden-btn-pick'];

  $i = 0;
  $nUpdated = 0;
  $nAttributed = 0;

  //pegar o valor da trial atual
  $timeCreated = $_SESSION['mytime'];
  $rSQL = "SELECT * FROM {local_pdi_trial} WHERE createdby = '$USER->id' and timecreated = $timeCreated";
  $resultado = $DB->get_records_sql($rSQL);
  $trialID;
  foreach($resultado as $t){$trialID = $t->id;}

  //iniciar a inserção
  foreach($idArray as $r){

    if(!is_null($r))
    {
      /////
        $pid = $personIdArray[$i];

        $idPessoa = $pid;
        $sql = "SELECT id, userid, sectorid FROM {local_pdi_sector_member} WHERE userid = '$idPessoa' and trialid='$trialID'";
        $res = $DB->get_records_sql($sql);

        if(count($res)< 1){
          //FAZER A INSERÇÃO porque não existe
          $now = time();
          $sectorid = $r;
          $userid = $pid;

          $addMember = new stdClass();
          $addMember->timecreated = $now;
          $addMember->sectorid = $sectorid;
          $addMember->userid = $userid;
          $addMember->trialid = $trialID;
        
          $res = $DB->insert_record('local_pdi_sector_member', $addMember);

          $nAttributed++;
          //redirect($CFG->wwwroot . '/local/pdi/selectsectors.php', 'Sector attributed to Evaluator(s)!');
        }
        else{
          //fazer o update porque JÁ existe
          $sector_member_id;
          $sector_id; //id que virá do já existente do banco
          foreach($res as $y){ $sector_member_id = $y->id; $sector_id = $y->sectorid; }

          //$r é o id selecionado agora
          //o update só ocorre se o setor mudar mesmo na seleção
          if($sector_id != $r){

            $now = time();
            $sectorid = $r;
            $userid = $pid;

            $updateMember = new stdClass();
            $updateMember->id = $sector_member_id;
            $updateMember->sectorid = $sectorid;
            $updateMember->userid = $userid;

            $DB->update_record('local_pdi_sector_member', $updateMember);

            $nUpdated++;
            //redirect($CFG->wwwroot . '/local/pdi/selectsectors.php', 'Sector updated!');
          }
        }
      /////
    }
    $i++;
  }

  $totalChanges = $nUpdated + $nAttributed;
  if($totalChanges > 0){
    if($btnPick == 1){
      redirect($CFG->wwwroot . '/local/pdi/selectquestionsdb.php', $totalChanges . ' change(s) saved!');
    }
    else{
      redirect($CFG->wwwroot . '/local/pdi/selectsectors.php', $totalChanges . ' change(s) saved!');
    }
  }
  else if($btnPick == 1){
    redirect($CFG->wwwroot . '/local/pdi/selectquestionsdb.php');
  }
  
}

///////////////////////////////////////////////////
if(isset($_POST['hidden_sector'])){
  $hiddenSector = $_POST['hidden_sector'];

  //pegar o valor da trial atual
  $timeCreated = $_SESSION['mytime'];
  $rSQL = "SELECT * FROM {local_pdi_trial} WHERE createdby = '$USER->id' and timecreated = $timeCreated";
  $resultado = $DB->get_records_sql($rSQL);
  $trialID;
  foreach($resultado as $t){$trialID = $t->id;}

  //add
  $addSector = new stdClass();
  $addSector->sectorname = $hiddenSector;
  $addSector->timecreated = time();
  $addSector->trialid = $trialID;

  $DB->insert_record('local_pdi_sector', $addSector);

  redirect($CFG->wwwroot . '/local/pdi/selectsectors.php', 'Sector added!');
  die;
}
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


echo "<h1>Step 3 - Sectors</h1>";
echo "<footer class='my-belowh1'>Select the sectors
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
onclick='window.location.href = \"addevaluated.php?stepnav\"'>2</span>
<footer>step 2</footer>
</div>";

echo "<div class='my-circle-div'>
<span class=\"my-circle\" style=\"background-color: var(--myprimary);\">3</span>
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
echo "<br><br><br>";


//hidden div manual selection
echo "<div>";
echo "<table id=\"dt-select\" class=\"table mydark-table my-pointer my-highlight\" cellspacing=\"0\" width=\"100%\">
<thead>
  <tr>
    <th>Id</th>
    <th>Username</th>
    <th>Full name</th>
    <th>Email</th>
    <th>Sector</th>
  </tr>
</thead>
<tfoot>
  <tr>
    <th>Id</th>
    <th>Username</th>
    <th>Full name</th>
    <th>Email</th>
    <th>Sector</th>
  </tr>
</tfoot>
</table>";
echo "</div>";

echo "<br>";
echo "<div class='my-horizontal-div'>";
echo "<input type='text' id='my-searchbar' class='my-large-input my-wider' placeholder='Name of the sector...'>";
echo "<input id='btn-create-sector' type='button' class='my-primary-btn' value='Create a sector'>";
echo "</div>";
echo "<hr>";

//form fora do botao create sector
echo "<form id='create_sector' name='create_sector' method='POST' action='selectsectors.php'>
<input type='hidden' id='hidden_sector' name='hidden_sector' value=''>
</form>
";

//bottom buttons

echo "<div id='div-save-buttons'>";

echo "<form id=\"my-save-form-sector\" name=\"my-save-form-sector\" method=\"post\" action=\"selectsectors.php\">";
echo "<input type=\"hidden\" name=\"hidden-ids\" id=\"hidden-ids\" value=\"\">";
echo "<input type=\"hidden\" name=\"hidden-btn-pick\" id=\"hidden-btn-pick\" value=\"\">";
echo "<input type=\"hidden\" name=\"hidden-person-ids\" id=\"hidden-person-ids\" value=\"\">";
echo "</form>";

echo "<input type='button' id='id_back_btn' class='div-save-btn my-grey-btn'
value='Back'>";
echo "<input type='button' id='id_save_btn' class='div-save-btn my-grey-btn my-marginr my-marginlauto'
value='Save'>";
echo "<input type='button' id='id_save_next_btn' class='div-save-btn my-primary-btn'
value='Save and next'>";

echo "</div>";

//popup msg
echo "<div id ='my-smallmsg-error' class='my-smallmsg-error'>Choose at least someone to proceed, please!</div>";

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

var dataSet = <?= json_encode($trial_sector_list, JSON_UNESCAPED_UNICODE) ?>;

var table = $('#dt-select').DataTable({
data: dataSet,
'createdRow': function( row, data, dataIndex ) {
      		$(row).attr('class', 'personId');
},
"pageLength": 10,
columns: [
{
title: "Id"
},
{
title: "Username"
},
{
title: "Full name"
},    
{
title: "Email"
},
{
title: "Sector"
}
],
dom: 'Bfrtip',
select: 'multi',
});
//fim table


//btn create sector
$( "#btn-create-sector" ).on( "click", function() {
  var sectorName;
  sectorName = $("#my-searchbar").val();

  if(sectorName.length > 1){
    $("#hidden_sector").val(sectorName);
    $("#btn-create-sector").hide();
    document.forms["create_sector"].submit();
    
  }
  else{
    alert('You need to type the sector name!');
  }
  
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

});

//botão de apenas salvar
$( "#id_save_btn" ).on( "click", function saveSector() {

  const arrayIds= [];
  const arrayPersonId= [];

  //for each selectbox
    $(".select-sector").each(function(){
        var strId = $(this).val();
        arrayIds.push(strId);
    });

    //each row
    $('#dt-select tbody tr').each(function() {
        var personId = $(this).find("td").eq(0).html();
        arrayPersonId.push(personId);    
    });

    var strJson = JSON.stringify(arrayIds);
    console.log(strJson);

    var strJsonPid = JSON.stringify(arrayPersonId);
    console.log(strJsonPid);

    if(arrayIds.length > 0){
      $("#hidden-ids").val(strJson);
      $("#hidden-person-ids").val(strJsonPid);
      $("#hidden-btn-pick").val("0");

      document.forms["my-save-form-sector"].submit();
    }
    else{
      //my-smallmsg-error
      alert("Something went wrong");
    }


});

$( "#id_save_next_btn" ).on( "click", function(){
  const arrayIds= [];
  const arrayPersonId= [];

  //for each selectbox
    $(".select-sector").each(function(){
        var strId = $(this).val();
        arrayIds.push(strId);
    });

    //each row
    $('#dt-select tbody tr').each(function() {
        var personId = $(this).find("td").eq(0).html();
        arrayPersonId.push(personId);    
    });

    var strJson = JSON.stringify(arrayIds);
    console.log(strJson);

    var strJsonPid = JSON.stringify(arrayPersonId);
    console.log(strJsonPid);

    if(arrayIds.length > 0){
      $("#hidden-ids").val(strJson);
      $("#hidden-person-ids").val(strJsonPid);
      $("#hidden-btn-pick").val("1");

      document.forms["my-save-form-sector"].submit();
    }
    else{
      //my-smallmsg-error
      alert("Something went wrong");
    }
});

//previous page function
$("#id_back_btn").on("click", function(){
  window.location.replace("addevaluated.php");
});


</script>