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

$PAGE->set_url(new moodle_url('/local/pdi/pdiadmin.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title("PDI Admin");
$PAGE->set_heading('PDI Admin');
$PAGE->requires->jquery();
//$PAGE->requires->js(new moodle_url('/local/pdi/scripts/pdiscript.js'));

global $USER, $DB;

//form instance

//verifica se o logado é adm
verifyAdm($USER->username);


/////////////////////////////////////////
//verifica se o post foi feito
if(isset($_POST['hidden-ids'])){
  $idArray = json_decode($_POST['hidden-ids']);
  $btnPick = $_POST['hidden-btn-pick'];
  //$timeCreated = $_SESSION['mytime'];
  $trialid = $_SESSION['edittrialid'];


  //verifica a trial criada no momento
  $tSql = "SELECT id, title, timecreated FROM {local_pdi_trial}
  WHERE createdby = $USER->id and id = $trialid";
  $tRes = $DB->get_records_sql($tSql);
  $trialID = "";
  foreach($tRes as $t){$trialID = $t->id;}
  
  
  foreach($idArray as $r){
    $sql = "SELECT id, username, email FROM {user} WHERE id = '$r'";
    $res = $DB->get_records_sql($sql);

    foreach($res as $row){

      $vSql = "SELECT id, evarole FROM {local_pdi_evaluator} WHERE mdlid = $row->id";
      $vRes = $DB->get_records_sql($vSql);

      if(count($vRes) < 1){

        $postId = $row->id;
        $postEmail = $row->email;
        $postUsername = $row->username;

        $addEvaluator = new stdClass();
        $addEvaluator->evarole = "evaluator";
        $addEvaluator->evasector = "";
        $addEvaluator->evatimeadd = time();
        $addEvaluator->mdlid = $postId;

        $DB->insert_record('local_pdi_evaluator', $addEvaluator);

        //add o avaliador a trial
        $evaID;
        $evTrialTime = time();

        $vSql = "SELECT id, evarole FROM {local_pdi_evaluator} WHERE mdlid = $row->id";
        $vRes = $DB->get_records_sql($vSql);

        foreach($vRes as $v){$evaID = $v->id;}

        $addEvTrial = new stdClass();
        $addEvTrial->trialid = $trialID;
        $addEvTrial->evaluatorid = $evaID;
        $addEvTrial->cohortid = "0";
        $addEvTrial->timecreated = $evTrialTime;
        $addEvTrial->timemod = $evTrialTime;

        $DB->insert_record('local_pdi_trial_evaluator', $addEvTrial);
      }
      else{
        //add o avaliador a trial
        $evaID;
        $evTrialTime = time();
        foreach($vRes as $v){$evaID = $v->id;}

        $addEvTrial = new stdClass();
        $addEvTrial->trialid = $trialID;
        $addEvTrial->evaluatorid = $evaID;
        $addEvTrial->cohortid = "0";
        $addEvTrial->timecreated = $evTrialTime;
        $addEvTrial->timemod = $evTrialTime;

        $DB->insert_record('local_pdi_trial_evaluator', $addEvTrial);
      }
        
      
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
//verifica os coortes
$html_cortes = "";

$sql = "SELECT * FROM {cohort}";
$res = $DB->get_records_sql($sql);
foreach($res as $r){
    $html_cortes .= "
    <div class='my-corte-preview' data-id='$r->id'>
        <h1>$r->name</h1>
        $r->description
    </div>";
}

///////////////////
//verifica os membros de um coorte
$json_status = "";
if(isset($_REQUEST['out_cohortID']) and $_REQUEST['out_cohortID'] != ""){

$out_cohortID = $_REQUEST['out_cohortID'];

//pré-página
$outCohortUsers_sql = "SELECT cm.userid, u.firstname, u.lastname 
FROM {cohort} c
INNER JOIN {cohort_members} cm
ON cm.cohortid = c.id 
INNER JOIN {user} u
ON u.id = cm.userid
WHERE c.id = '$out_cohortID'";

$outCohortUsers_res = $DB->get_records_sql($outCohortUsers_sql);

$status = array();
foreach($outCohortUsers_res as $rr){
    $userid = $rr->userid;
    $userfname = $rr->firstname;
    $userlname = $rr->lastname;

    $status[] = array("userid"=>"$userid", "firstname"=>"$userfname", "lastname"=>"$userlname");
}

$json_status = json_encode($status, JSON_UNESCAPED_UNICODE);

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
$trialid = 0;
if(isset($_SESSION['authadm']) and $_SESSION['authadm'] == 'yes'){

  if(isset($_REQUEST['newtrial']) and $_REQUEST['newtrial'] == "new"){

    sleep(1); //delay de 1 sec
    //cria o processo com dados genéricos
    $mytime = time();
    $myusername = $USER->username;
    $mytitle = substr($myusername, 0, 8) . $mytime;

    $_SESSION['mytime'] = $mytime;

    $insertTrial = new stdClass();
    $insertTrial->createdby = $USER->id;
    $insertTrial->title = "$mytitle";
    $insertTrial->timecreated = $mytime;
    $insertTrial->timemod = $mytime;

    $trialid = $DB->insert_record('local_pdi_trial', $insertTrial);
    $_SESSION['edittrialid'] = $trialid;

  }
  else if(isset($_REQUEST['edittrial'])){
    //edittrial deve conter o id da trial que está sendo editada
    sleep(1);

    //recupera os dados da url trial
    $trialid = $_REQUEST['edittrial'];
    $_SESSION['edittrialid'] = $trialid;


  }
  else{
    $trialid = $_SESSION['edittrialid'];
  }

  //$timeCreated = $_SESSION['mytime'];
  $rSQL = "SELECT * FROM {local_pdi_trial} WHERE createdby = '$USER->id' and id = '$trialid'";
  $resultado = $DB->get_records_sql($rSQL);

  $trialID;
  foreach($resultado as $t){$trialID = $t->id;}

    //////print da lista de avaliadores disponiveis
    //////TABELA MDB DATATABLE fetch
  $dtSql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.institution,
  lpev.id as evID, lptv.id as triEvID, lptv.trialid
  FROM {user} u
  LEFT JOIN {local_pdi_evaluator} lpev 
  ON u.id = lpev.mdlid 
  LEFT JOIN {local_pdi_trial_evaluator} lptv 
  ON lptv.evaluatorid = lpev.id
  WHERE lptv.trialid IS NULL
  AND u.email != 'root@localhost'
  OR lptv.trialid != $trialID";
  $dtRes = $DB->get_records_sql($dtSql);


  //sql de exclusão
  $xSQL = "SELECT * FROM {local_pdi_trial_evaluator}
            WHERE trialid = $trialID";
  $xRes = $DB->get_records_sql($xSQL);

  $arrayEvid = array();
  foreach($xRes as $xr){
      $id = $xr->evaluatorid;
      array_push($arrayEvid, $id);
  }

  $dtLista;
  //pagina
  foreach($dtRes as $user){
    if(in_array($user->evid, $arrayEvid) == false){
      $userfullname = "$user->firstname" . " " . "$user->lastname";
      $dtLista[] = array("$user->id", "$user->username", "$user->email", "$user->institution", "$userfullname");
    }
  }

  /////




////////////////////////////////////////
//actual page for admin
echo "<div id='myblue-bg'>";
echo "<span><a href='index.php' class='pdi-nostyle my-marginr'>back</a></span>";
echo "<span><a href='index.php' class='pdi-nostyle my-marginr'>dashboard</a></span>";
echo "<div class='mypush'><span class='mylogo'>PDI</span></div>";
echo "</div><br>";

echo "<div id='mygrey-bg'>"; //grey bg starts

echo "<h1>Step 1 - Evaluators</h1>";
echo "<footer class='my-belowh1'>Select the evaluator(s) to add
<a tabindex=\"0\" class=\"btn mybelow1\" role=\"button\" data-toggle=\"popover\" data-placement='bottom' data-trigger=\"focus\" title=\"x\" data-content=\"And here's a tip on how to do something.\"><i class=\"far fa-question-circle my-help-pop\"></i></a>
</footer>";

/////
echo "<div id='my-steps'>"; //steps

echo "<div class='my-circle-div'>
<span class=\"my-circle\" onclick='window.location.href = \"createtrial.php?stepnav\"'
style=\"background-color: var(--myprimary);\">1</span>
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

/////buttons cohort and select manually
echo "<div id='div-two-buttons'>";

echo "<input type='button' id='btn-select-manually' class='child-twob my-marginr'
value='Select Manually'>";
echo "<input type='button' id='btn-select-cohort' class='child-twob'
value='Select Cohort'>";

echo "</div>";
/////
echo "<br>";

//hidden div cohort
echo "<div id='my-hidden-popup'>";
echo "$html_cortes";
echo "</div>";

//hidden div manual selection
echo "<div id='my-hidden-manual'>";
echo "<table id=\"dt-select\" class=\"table mydark-table my-pointer my-highlight\" cellspacing=\"0\" width=\"100%\">
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

echo "<hr>";
echo "<span id='span-evaluators'>Evaluators selected: </span>";

//bottom buttons

echo "<div id='div-save-buttons'>";

echo "<form id=\"my-id-form\" name=\"my-id-form\" method=\"post\" action=\"createtrial.php\">";
echo "<input type=\"hidden\" name=\"hidden-ids\" id=\"hidden-ids\" value=\"\">";
echo "<input type=\"hidden\" name=\"hidden-btn-pick\" id=\"hidden-btn-pick\" value=\"\">";
echo "</form>";

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

//abrir coortes
$(document).ready(function() {

var click_state = false;

//this btn click
$( "#btn-select-cohort" ).on( "click", function() {

  click_state = true;
  if(click_state){
    $("#my-hidden-popup").fadeIn(200);
    $("#my-hidden-popup").css("display", "flex");
    
    //hide other table
    $("#my-hidden-manual").css("display", "none");
    $("#my-hidden-manual").fadeOut(200);

  }
});

//else click manual select
$( "#btn-select-manually" ).on( "click", function() {

click_state = false;
if(!click_state){
  $("#my-hidden-popup").css("display", "none");
  $("#my-hidden-popup").fadeOut(200);

  $("#my-hidden-manual").fadeIn(200);
  $("#my-hidden-manual").css("display", "block");
}
});

});


////////tabela de todos moodle users
var dataSet = <?= json_encode($dtLista, JSON_UNESCAPED_UNICODE) ?>; //valor chamado do php

$(document).ready(function () {
var table = $('#dt-select').DataTable({
data: dataSet,
'createdRow': function( row, data, dataIndex ) {
      		$(row).attr('id', 'rowid_' + data[0]);
  		},
"pageLength": 10,
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
});

//////
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

});


//após a criação da tabela
$(document).ready(function() {

var mJson = '<?php echo($json_status); ?>'; //json string

//if there is a value already
if(mJson){
var mObj = JSON.parse(mJson); //json obj
console.log(mObj);

//provavelment subtrair 1 para pegar o index certo
objLenght = (Object.keys(mObj).length);

var jsUserid;
var jsFirstname;
var jsLastname;

for(var i=0; i < objLenght; i++){
    jsUserid = (mObj[i].userid);
    jsFirstname = (mObj[i].firstname);
    jsLastname = (mObj[i].lastname);

    $( "#span-evaluators" )
    .after( "<span class='selected-block' onclick='removeThis(this)' style='background-color: var(--mysecondary); cursor: pointer;' data-id='"+jsUserid +"'>"+ jsFirstname + " "+ jsLastname +"</span>" );
    
    $("#rowid_"+jsUserid).hide();
}

}

//cohort selection click 
click_statexx = true;
$( ".my-corte-preview" ).on( "click", function() {

  if(click_statexx){
    var cohortId = $(this).data("id"); 
    var cohortName = $(this).find('> h1').text();

    $( "#span-evaluators" )
    .after( "<span class='selected-block' data-id='"+cohortId +"'>"+ cohortName +"</span>" );
    
    window.location.href = "createtrial.php?out_cohortID="+cohortId+"";  
  }
});

});

//onready para salvar e continuar
$(document).ready(function() {

  //SAVE AND NEXT BTN
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
    console.log(strJson);

    var mytime = "";

    if(arrayIds.length > 0){
      $("#hidden-ids").val(strJson);
      $("#hidden-btn-pick").val("1");

      document.forms["my-id-form"].submit();
    }
    else{
      //my-smallmsg-error
      $("#my-smallmsg-error").fadeIn(200);
      $("#my-smallmsg-error").css("display", "flex");
      $("#my-smallmsg-error").delay(2000).fadeOut(400);
    }

  });

  //ONLY SAVE BTN
  $( "#id_save_btn" ).on( "click", function() {
    
    const arrayIds= [];

    $('.selected-block').each(function(){
      var thisBlock = $(this);
      var strId = thisBlock.attr('data-id');

      //console.log(thisBlock.attr('data-id'));
      arrayIds.push(strId);
    });

    //console.log(arrayIds);
    var strJson = JSON.stringify(arrayIds);
    console.log(strJson);

    var mytime = "";

    if(arrayIds.length > 0){
      $("#hidden-ids").val(strJson);
      $("#hidden-btn-pick").val("0");

      document.forms["my-id-form"].submit();
    }
    else{
      //my-smallmsg-error
      $("#my-smallmsg-error").fadeIn(200);
      $("#my-smallmsg-error").css("display", "flex");
      $("#my-smallmsg-error").delay(2000).fadeOut(400);
    }

  });

});

//remove block on the botton onclick
function removeThis(param) {

$(param).remove();
console.log("remove this");
}

</script>