<html>
<head>
<link rel="stylesheet" href="styles/pdistyle.css">
<link rel="stylesheet" href="styles/pdinonav.css">
<!-- DataTables CSS -->
<link href="bootstrap/css/addons/datatables.min.css" rel="stylesheet">

<!-- DataTables Select CSS -->
<link href="bootstrap/css/addons/datatables-select.min.css" rel="stylesheet">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.11.2/css/all.css">

<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate"/>

<meta http-equiv="Pragma" content="no-cache"/>

<meta http-equiv="Expires" content="0"/>

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

use core_table\external\dynamic\get;

require_once('../../config.php');
require_once('lib.php');
require_once('print/outputmoodleusers.php');
require_once($CFG->dirroot . '/local/pdi/classes/forms/insert_adm.php');
require_login();

$PAGE->set_url(new moodle_url('/local/pdi/webadmin.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title("Web Admin");
$PAGE->set_heading('Web Admin');
$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url('/local/pdi/scripts/pdiscript.js'));

global $DB;

//form instance
$mform = new insert_adm();

//form setup
if($mform->is_cancelled()){
    //do something...
}
else if($fromform = $mform->get_data()){
    
    $addAdmin = new stdClass();

    $addAdmin->username = $fromform->username;
    $addAdmin->useremail = $fromform->useremail;
    $addAdmin->usercompany = $fromform->usercompany;
    $addAdmin->userrole = $fromform->userrole;

    $websql = "SELECT username, useremail FROM {local_pdi_user} WHERE username = '$fromform->username' or useremail = '$fromform->useremail'";
    $alreadyExist = $DB->get_records_sql($websql);

    $sql = "SELECT `email`, `username` FROM {user} WHERE email = '$fromform->useremail' and username = '$fromform->username'";
    $res = $DB->get_records_sql($sql);

    if(count($alreadyExist) > 0){
      $strAlreadyExists = get_string('already_exists_check', 'local_pdi');
      redirect($CFG->wwwroot . '/local/pdi/webadmin.php', $strAlreadyExists);
    }
    else if(count($res) > 0){
      $DB->insert_record('local_pdi_user', $addAdmin);
    
      $strCheckResults = get_string('check_table_results', 'local_pdi');
      redirect($CFG->wwwroot . '/local/pdi/webadmin.php', $strCheckResults);
    }
    else{
      $strCheckUser = get_string('invalid_user_check', 'local_pdi');
      redirect($CFG->wwwroot . '/local/pdi/webadmin.php', $strCheckUser);
    }
    
}


//disable adm setup
if(isset($_GET['disableid'])){ 
  $thisid = $_GET['disableid'];
  
  $updateUser = new stdClass();
  $updateUser->id = $thisid;
  $updateUser->userrole = "disabled";

  $DB->update_record('local_pdi_user', $updateUser);
  $strAdmDisabled = get_string('admin_disabled', 'local_pdi');
  redirect($CFG->wwwroot . '/local/pdi/webadmin.php', $strAdmDisabled);
}
if(isset($_GET['enableid'])){
  $thisid = $_GET['enableid'];
  
  $updateUser = new stdClass();
  $updateUser->id = $thisid;
  $updateUser->userrole = "0";

  $DB->update_record('local_pdi_user', $updateUser);
  $strAdmEnabled = get_string('admin_enabled', 'local_pdi');
  redirect($CFG->wwwroot . '/local/pdi/webadmin.php', $strAdmEnabled);
}
if(isset($_GET['deleteid'])){
  $thisid = $_GET['deleteid'];

  $select = "id = $thisid";
  $DB->delete_records_select('local_pdi_user', $select);
  $strAdmDeleted = get_string('admin_deleted', 'local_pdi');
  redirect($CFG->wwwroot . '/local/pdi/webadmin.php', $strAdmDeleted);
}

//page setup
//btns
$strCollapse = get_string('collapse', 'local_pdi');
$strQuickAdd = get_string('quickadd', 'local_pdi');

$btn_adm_select_table = "<span type='button' id='btn-collap-select' class='btn-pdicollapse' value='collapse'>$strCollapse</span>";
$btn_user_select_table = "<span type='button' id='btn-collap-datatb' class='btn-pdicollapse' value='collapse'>$strCollapse</span>";
$btn_quick_add = "<span type='button' id='btn-quick-add' class='btn-quick-add' value='quick_add'>$strQuickAdd</span>";

//first table
$admins = $DB->get_records('local_pdi_user');
$html_table_body = "";

//str
$strDisable = get_string('disable', 'local_pdi');
$strEnable = get_string('enable', 'local_pdi');
$strDelete = get_string('delete', 'local_pdi');

foreach($admins as $adm){
  $userid = $adm->id;
  $username = $adm->username;
  $useremail = $adm->useremail;
  $usercompany = $adm->usercompany;
  $userrole = $adm->userrole;
  if($userrole == "0"){
    $userrole = "PDI Adm";
  

    $html_table_body .= 
    "<tr>
    <th scope=\"row\">$userid</th>
      <td>$username</td>
      <td>$useremail</td>
      <td>$usercompany</td>
      <td>$userrole</td>

      <td class='remove-btn'>
      <a href='webadmin.php?disableid=$userid' 
      class='mydisable-btn'>$strDisable</a></td>
      
    </tr>";
  }
  else{
    $html_table_body .= 
    "<tr class='my-disabled'>
    <th scope=\"row\">$userid</th>
      <td>$username</td>
      <td>$useremail</td>
      <td>$usercompany</td>
      <td>$userrole</td>

      <td class='remove-btn'>
      <a href='webadmin.php?enableid=$userid' 
      class='myenable-btn'>$strEnable</a>

      <a href='#delete' onclick='deletePerson($userid)' 
      class='mydisable-btn'>$strDelete</a>
      </td>
      
    </tr>";
  }

}

$adm_select_table = "<table id='pditable-select' class=\"table mydark-table\">
<thead>
  <tr>
    <th scope=\"col\">Id</th>
    <th scope=\"col\">Username</th>
    <th scope=\"col\">Email</th>
    <th scope=\"col\">Company</th>
    <th scope=\"col\">Role</th>
    <th></th>
  </tr>
</thead>
<tbody>
  $html_table_body
</tbody>
</table>";

//str
$strInstalled = get_string('installed', 'local_pdi');
$strIfHaveNoKeys = get_string('if_have_no_keys', 'local_pdi');
$strHere = get_string('here', 'local_pdi');
$strPluginNotDetected = get_string('congrea_not_detected', 'local_pdi');
$strAfterDownloadingGoTo = get_string('after_download_go_to', 'local_pdi');
$strPluginInstallerTab = get_string('plugin_installer_tab', 'local_pdi');
$strToProceed = get_string('to_proceed', 'local_pdi');
$strAfterTheInstallation = get_string('after_install_complete', 'local_pdi');
$strPluginSettings = get_string('plugin_settings', 'local_pdi');
$strMissingKeys = get_string('missing_keys', 'local_pdi');

$strCongraStatus = verifyCongreaStatus();

//plugin concrea verify
$congrea = $DB->get_records('modules', ['name' => 'congrea']);
if(count($congrea)>0){ 
  $urlCongrea = $CFG->wwwroot . '/admin/settings.php?section=modsettingcongrea';

  $key = get_config('mod_congrea', 'cgapi');
  $secret = get_config('mod_congrea', 'cgsecretpassword');
  if(strlen($key) < 1 or strlen($secret) < 1){
      $congreaDiv = "<div class=\"card-body\">
                      <h4><span class=\"badge bg-light text-dark rounded\">$strInstalled <i class=\"fas fa-check\"></i></span></h4>
                      <h4><span class=\"badge bg-warning text-dark rounded\">$strMissingKeys <i class=\"fas fa-exclamation-triangle\"></i></span></h4>
                      <p>$strIfHaveNoKeys <a href=\"$urlCongrea\" class=\"link-primary\" target=\"_blank\">$strHere</a>.</p>
                    </div>                
  ";
  }else{
      $congreaDiv = "<div class=\"card-body\"><h4><span class=\"badge bg-light text-dark rounded\">$strInstalled <i class=\"fas fa-check\"></i></span></h4>
      <a href=\"$urlCongrea\" class=\"link-primary\" target=\"_blank\">$strPluginSettings</a>
      <p>Congrea: $strCongraStatus</p>
    </div>                
    ";
  }
}
else{
  $urlPluginTab = $CFG->wwwroot . "/admin/tool/installaddon/index.php";
  $urlCongrea = "https://moodle.org/plugins/download.php/25256/mod_congrea_moodle311_2021100700.zip";
  $congreaDiv = "<div class=\"card-body\">
                    <div class=\"alert alert-warning\" role=\"alert\">
                      $strPluginNotDetected <a href=\"$urlCongrea\" class=\"link-primary\">$strHere</a>.
                    </div>
                  <p>$strAfterDownloadingGoTo <a href=\"$urlPluginTab\">$strPluginInstallerTab</a> $strToProceed. $strAfterTheInstallation</p>
                </div>                
  "; 
}


//page
echo $OUTPUT->header();

echo "<div id='myblue-bg'>";
echo "<span><a href='index.php' class='pdi-nostyle my-marginr'>back</a></span>";
echo "<span><a href='index.php' class='pdi-nostyle my-marginr'>dashboard</a></span>";
echo "<span><a href='#newadminsid' class='pdi-nostyle'>add admin</a></span>";
echo "<div class='mypush'><span class='mylogo'>PDI</span></div>";
echo "</div><br>";

echo "<div id='mygrey-bg'>"; //grey bg starts

//mostrar para os adms do moodle apenas
if(has_capability('moodle/site:config', context_system::instance())){

echo "<h4>".get_string("current_admins", "local_pdi")."</h4>";
echo $btn_adm_select_table;
echo "<div id='hide_select_div'>" 
. $adm_select_table . 
"</div>";

echo "<br><hr>";

//tabela de inserir adms
echo "<h4 id='newadminsid'>".get_string("new_admins", "local_pdi")."</h4>";
echo "<footer>".get_string("if_you_dont_know", "local_pdi")."</footer>";
echo "<footer>". get_string("check_out_below", "local_pdi") ."</footer>";
echo "<br><br>";

$mform->display();

echo "<br><hr>";

//see available users
echo "<h4 id='checkouthere'>Moodle users</h4>";
echo $btn_user_select_table;
echo $btn_quick_add;

echo "<div id='divdttable'>";
echo "<table id=\"dt-select\" class=\"table mydark-table my-pointer\" cellspacing=\"0\" width=\"100%\">
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
</table>
</div>";

echo "<br><hr>";

//str
$strMeetingsPlugin = get_string('meetings_plugin', 'local_pdi');
$strCopiedToForm = get_string('copied_to_form', 'local_pdi');
$strNoOneIsSelected = get_string('no_one_selected', 'local_pdi');

echo "<h4 id='congrea-div'>$strMeetingsPlugin</h4>";
echo "$congreaDiv";


echo "<div id='my-smallmsg'>$strCopiedToForm</div>";
echo "<div id ='my-smallmsg-error' class='my-smallmsg-error'>$strNoOneIsSelected</div>";
echo "<div id='my-emptymsg' class='my-smallmsg-error'>You must fill in this!</div>";

} //fechando o if de capability
else{
  redirect($CFG->wwwroot . '/my/index.php');
}

echo "</div>"; //div mygrey-bg ends

//js do bootstrap
echo "
<script src=\"bootstrap/js/addons/datatables.min.js\" type=\"text/javascript\"></script>
<script src=\"bootstrap/js/addons/datatables-select.min.js\" type=\"text/javascript\"></script>";

echo $OUTPUT->footer();

?>

<script>

var dataSet = <?= json_encode($outmoodle_lista, JSON_UNESCAPED_UNICODE) ?>; //valor chamado do php

$(document).ready(function () {
var table = $('#dt-select').DataTable({
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
select: 'single',
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
    var username = obj[1];
    var email = obj[2];
    var company = obj[3];
    var fullname = obj[4];

    $("#id_username").val(username);
    $("#id_useremail").val(email);
    $("#id_usercompany").val(company);

    
      $("#my-smallmsg").fadeIn(200);
      $("#my-smallmsg").css("display", "flex");
      $("#my-smallmsg").delay(1400).fadeOut(400);
    
    
} )
.on( 'deselect', function ( e, dt, type, indexes ) {
    
} );


});

//tabela dos grupos (simples)
$(document).ready(function() {
  var groupstable = $("#pditable-select").DataTable({
  "bLengthChange": false
});
});

//click do campo

$(document).ready(function() {

  /*
  var click_state = false;
  $( "#dt-select tbody tr" ).on( "click", function() {


    var currentRow= $(this).closest("tr");      
    var col1= currentRow.find("td:eq(1)").text(); // get current row 1st TD value
    var col2= currentRow.find("td:eq(2)").text(); // get current row 2nd TD value
    var col3= currentRow.find("td:eq(3)").text(); // get current row 3rd TD value

    if($("#id_username").val() == col1){
      click_state = true;
    }
    else{
      click_state = false;
    }

    $("#id_username").val(col1);
    $("#id_useremail").val(col2);
    $("#id_usercompany").val(col3);

    if(!click_state){
      $("#my-smallmsg").fadeIn(200);
      $("#my-smallmsg").css("display", "flex");
      $("#my-smallmsg").delay(1400).fadeOut(400);
    }
    
  });
  */

  //quick add
  $("#btn-quick-add").on("click", function() {
    if($("#id_username").val() == "" || $("#id_useremail").val() == ""){
      //my-smallmsg-error
      $("#my-smallmsg-error").fadeIn(200);
      $("#my-smallmsg-error").css("display", "flex");
      $("#my-smallmsg-error").delay(1000).fadeOut(400);
    }
    else if($("#id_usercompany").val() == ""){
      $("#id_usercompany").focus();
      
      //field company empty
      $("#my-emptymsg").fadeIn(200);
      $("#my-emptymsg").css("display", "flex");
      $("#my-emptymsg").delay(1400).fadeOut(600);
    }
    else{
      //moodle form id
      $("#id_submitbutton").click();
    }

  });
  
});

$(document).ready(function() {

$( "#my-smallmsg" ).on( "click", function() {
  $(this).css("display", "none");
});

});

//are you sure you want to delete
function deletePerson(userid) {
  var r = confirm("This admin will be deleted!");
  if (r == true) {
    window.location.href = 'webadmin.php?deleteid='+ userid;
  } else {
    //nothing
  }
}

</script>
