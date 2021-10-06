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
//require_once('print/outevaluator_cohorts.php');
require_login();

$PAGE->set_url(new moodle_url('/local/pdi/addevaluated.php'));
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
//no envio dos salvar
if(isset($_POST['hidden-ids'])){

  $idArray = json_decode($_POST['hidden-ids']); //ids do cohort
  $usernameArray = json_decode($_POST['hidden-usernames']); //username de avaliador
  $btnPick = $_POST['hidden-btn-pick'];

  $i = 0;
  $nUpdated = 0;

  foreach($idArray as $r){
    if(!is_null($r) and $r != "0"){
      
      //fazer o update do cohort desse avaliador nesse processo
      $username = $usernameArray[$i];

      //current trial
      $timeCreated = $_SESSION['mytime'];
      $rSQL = "SELECT * FROM {local_pdi_trial} WHERE createdby = '$USER->id' and timecreated = $timeCreated";
      $resultado = $DB->get_records_sql($rSQL);
      $trialID;
      foreach($resultado as $t){$trialID = $t->id;}

      $cSQL = "SELECT tv.id, tv.timemod, ev.id as evID, ev.mdlid, tv.trialid ,tv.cohortid FROM {local_pdi_evaluator} ev
              LEFT JOIN {local_pdi_trial_evaluator} tv
              ON tv.evaluatorid = ev.id
              LEFT JOIN mdl_user
              ON mdl_user.id = ev.mdlid
              WHERE tv.trialid = '$trialID'
              AND mdl_user.username = '$username'";
      $cRes = $DB->get_records_sql($cSQL);

      $id; //id pk da tabela
      $evid; //evaluator id
      $cid = $r; //cohort id
      $oldcid;
      foreach($cRes as $c){
        $id = $c->id;
        $evid = $c->evid;
        $oldcid = $c->cohortid;
      }

      //o update só ocorre se o coorte mudar mesmo na seleção
      if($oldcid != $r){
        $now = time();

        $updCohort = new stdClass();
        $updCohort->id = $id;
        $updCohort->cohortid = $cid;
        $updCohort->timemod = $now;


        $DB->update_record('local_pdi_trial_evaluator', $updCohort);

        $nUpdated++;
      }

    }
    $i++;
  }

  //which button was pick
  if($nUpdated > 0){
    if($btnPick == 1){
      redirect($CFG->wwwroot . '/local/pdi/selectsectors.php', $nUpdated . ' change(s) saved!');
    }
    else{
      redirect($CFG->wwwroot . '/local/pdi/addevaluated.php', $nUpdated . ' change(s) saved!');
    }
  }
  else if($btnPick == 1){
    redirect($CFG->wwwroot . '/local/pdi/selectsectors.php');
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

//////////////////fetch mdb datatable

//current trial
$timeCreated = $_SESSION['mytime'];
  $rSQL = "SELECT * FROM {local_pdi_trial} WHERE createdby = '$USER->id' and timecreated = $timeCreated";
  $resultado = $DB->get_records_sql($rSQL);

  $trialID;
  foreach($resultado as $t){$trialID = $t->id;}


////avaliadores desse processo
$aSQL = "SELECT evaluator.id as evaid, evaluator.evarole, evaluator.evasector, evaluator.evatimeadd, evaluator.mdlid, trev.id as trevID, trev.trialid, trev.cohortid,
mdl_user.id, mdl_user.username, mdl_user.email, mdl_user.firstname, mdl_user.lastname, mdl_user.institution, mdl_cohort.name as cohortname
FROM mdl_local_pdi_evaluator evaluator
LEFT JOIN mdl_local_pdi_trial_evaluator trev
ON trev.evaluatorid = evaluator.id
LEFT JOIN mdl_user
ON evaluator.mdlid = mdl_user.id
LEFT JOIN mdl_cohort
ON mdl_cohort.id = trev.cohortid
WHERE trev.trialid = $trialID
GROUP BY evaluator.id";

$dtRes = $DB->get_records_sql($aSQL);


//faz a lista para a tabela
foreach($dtRes as $user){
  $userfullname = "$user->firstname" . " " . "$user->lastname";

        //verifica os coortes
      $html_cortes_oec = "<select name=\"select-cohort\" class=\"select-cohort\">";
      $sql_oec = "SELECT * FROM {cohort}";
      $res_oec = $DB->get_records_sql($sql_oec);
      $html_cortes_oec .= "
              <option value=\"0\" disabled selected>-</option>";
      if(is_null($user->cohortid) == false){

        $html_cortes_oec .= "
              <option selected title='saved' class='my-selected-opt' 
              value=\"$user->cohortid\">$user->cohortname</option>";
      }
      foreach($res_oec as $r){
          $html_cortes_oec .= "
              <option value=\"$r->id\">$r->name</option>";
      }
      $html_cortes_oec .= "</select>";


  $outevacohort_list[] = array("$user->username", $userfullname, "$user->email", "$html_cortes_oec");
}


////////////////////////////////////////
//actual page for admin
echo "<div id='myblue-bg'>";
echo "<span><a href='createtrial.php' class='pdi-nostyle my-marginr'>back</a></span>";
echo "<span><a href='index.php' class='pdi-nostyle my-marginr'>dashboard</a></span>";
echo "<div class='mypush'><span class='mylogo'>PDI</span></div>";
echo "</div><br>";

echo "<div id='mygrey-bg'>"; //grey bg starts


echo "<h1>Step 2 - Evaluated</h1>";
echo "<footer role='button' class='btn my-belowh1'>
Select the groups to be evaluated
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
<span class=\"my-circle\" style=\"background-color: var(--myprimary);\">2</span>
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
echo "<br><br><br>";


//hidden div manual selection
echo "<div>";
echo "<table id=\"dt-select\" class=\"table mydark-table my-pointer my-highlight\" cellspacing=\"0\" width=\"100%\">
<thead>
  <tr>
    <th>Username</th>
    <th>Full name</th>
    <th>Email</th>
    <th>Selection</th>
  </tr>
</thead>
<tfoot>
  <tr>
    <th>Username</th>
    <th>Full name</th>
    <th>Email</th>
    <th>Selection</th>
  </tr>
</tfoot>
</table>";
echo "</div>";

echo "<br>";
echo "<input id='btn-create-cohort' type='button' class='my-primary-btn my-large-input' value='Create a cohort'>";
echo "<hr>";

//bottom buttons

echo "<div id='div-save-buttons'>";

echo "<form id=\"my-id-form\" name=\"my-id-form\" method=\"post\" action=\"addevaluated.php\">";
echo "<input type=\"hidden\" name=\"hidden-ids\" id=\"hidden-ids\" value=\"\">";
echo "<input type=\"hidden\" name=\"hidden-btn-pick\" id=\"hidden-btn-pick\" value=\"\">";
echo "<input type=\"hidden\" name=\"hidden-usernames\" id=\"hidden-usernames\" value=\"\">";
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

var dataSet = <?= json_encode($outevacohort_list, JSON_UNESCAPED_UNICODE) ?>;  

var table = $('#dt-select').DataTable({
data: dataSet,
"pageLength": 10,
columns: [
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
title: "Selection"
}
],
dom: 'Bfrtip',
select: 'single',
});
//fim table


//btn create cohort
$( "#btn-create-cohort" ).on( "click", function() {
  window.location.href = "createcohort.php";  
});

/*
//pop up help
$( ".my-help-pop" ).hover(
  function() {
    $( this ).append( $( "<span class='help-div'>Lorem ipsum dolor Lorem ipsum Lorem ipsun Lorem dolor Lorem ameno</span>" ) );
  }, function() {
    $( this ).find( "span" ).last().remove();
  }
);
*/

});


//previous page function
$("#id_back_btn").on("click", function(){
  window.location.replace("createtrial.php");
});


//ONLY SAVE BTN
$( "#id_save_btn" ).on( "click", function() {
    
    const arrayIds= [];
    const arrayUsername= [];
    
    //for each selectbox
    $(".select-cohort").each(function(){
        var strId = $(this).val();
        arrayIds.push(strId);
    });

    //each row
    $('#dt-select tbody tr').each(function() {
        var username = $(this).find("td").eq(0).html();
        arrayUsername.push(username);    
    });

    //json stringfy
    var strJson = JSON.stringify(arrayIds);
    console.log(strJson);
    var strJsonUser = JSON.stringify(arrayUsername);
    console.log(strJsonUser);

    //send
    if(arrayIds.length > 0){
      $("#hidden-ids").val(strJson);
      $("#hidden-usernames").val(strJsonUser);
      $("#hidden-btn-pick").val("0");

      document.forms["my-id-form"].submit();
    }
    else{
      //my-smallmsg-error
      alert("Something went wrong");
    }

  });

  //save and next
  $( "#id_save_next_btn" ).on( "click", function(){

    const arrayIds= [];
    const arrayUsername= [];
    
    //for each selectbox
    $(".select-cohort").each(function(){
        var strId = $(this).val();
        arrayIds.push(strId);
    });

    //each row
    $('#dt-select tbody tr').each(function() {
        var username = $(this).find("td").eq(0).html();
        arrayUsername.push(username);    
    });

    //json stringfy
    var strJson = JSON.stringify(arrayIds);
    console.log(strJson);
    var strJsonUser = JSON.stringify(arrayUsername);
    console.log(strJsonUser);

    //send
    if(arrayIds.length > 0){
      $("#hidden-ids").val(strJson);
      $("#hidden-usernames").val(strJsonUser);
      $("#hidden-btn-pick").val("1");

      document.forms["my-id-form"].submit();
    }
    else{
      //my-smallmsg-error
      alert("Something went wrong");
    }
  });


</script>