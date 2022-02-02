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

$PAGE->set_url(new moodle_url('/local/pdi/createquestiondb.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title("PDI Admin");
$PAGE->set_heading('PDI Admin');
$PAGE->requires->jquery();
//$PAGE->requires->js(new moodle_url($CFG->dirrroot . '/local/pdi/scripts/pdiscript.js'));

global $USER, $DB;


//verifica se o logado é adm
verifyAdm($USER->username);

///////////////inserir competencia
if(isset($_POST['txtCatname'])){
    $txtCatName = $_POST['txtCatname'];

    $addCat = new stdClass();
    $addCat->name = $txtCatName;
    $addCat->contextid = 1;
    $addCat->info = "created in the plugin";
    $addCat->infoformat = 0;
    $addCat->stamp = "". time() ."";
    $addCat->parent = 0;
    $addCat->sortorder = 0;
    $addCat->idnumber = null;

    $sql = "SELECT * FROM {local_pdi_question_categ} WHERE name='$txtCatName' AND contextid= 1";
    $res = $DB->get_records_sql($sql);

    if(count($res) > 0){
        echo "<script>alert('There is already a competency with this name!');</script>";
    }
    else{
        $DB->insert_record('local_pdi_question_categ', $addCat);
        redirect($CFG->wwwroot . '/local/pdi/createquestiondb.php', 'Competency saved!');
    }

}

if(isset($_POST['select-cat-del'])){
    $catId = $_POST['select-cat-del'];

    //doesn't actually delete, just changes the context to 0
    $cadDisable = new stdClass();
    $cadDisable->id = $catId;
    $cadDisable->contextid = 0;

    $DB->update_record('local_pdi_question_categ', $cadDisable);
    
}

/////////////////////////////database
$dbRes = $DB->get_records('local_pdi_question_db');

$htmlDBOptions = "";
foreach($dbRes as $db){
    $htmlDBOptions .= "<option value='$db->id'>$db->name</option>";
    //o resto está na parte da página
} 


///////////////////////////////////competencias
$compRes = $DB->get_records('local_pdi_question_categ');
//var_dump($compRes);
//die;

$htmlOptions = "";
foreach($compRes as $cr){
    if($cr->contextid != "0"){
        $htmlOptions .= "<option value=\"$cr->id\">$cr->name</option>";
    }
}

$dropdownCategory = "                    
<label for='select-cat' class='my-label'>Competency</label> <br>
<select id='select-cat' name=\"select-cat\" class=\"my-large-input select-cat\">
    <option value=\"-1\" disabled>Select</option>
    <option value=\"0\" selected>General</option>
    $htmlOptions
</select> 

<button type='button' id='btn-edit-cat' class='btn'><i class=\"far fa-edit\"></i></button>

<div id='hidden-cat-div' class='my-hidden'>

<form></form>

<form id='frmAddCat' name='frmAddCat' method='post' action='createquestiondb.php'>

    <input type='text' id='txtCatname' name='txtCatname' placeholder='Competency name' class='margin-top' required>
    <button type='button' id='btn-criar-cat' class='btn myenable-btn'><i class=\"fas fa-plus\"></i></button>

</form>

<form id='frmDelCat' name='frmDelCat' method='post' action='createquestiondb.php' class=''>

    <select id='select-cat-del' name=\"select-cat-del\" class='margin-top'>
    <option value=\"-1\" disabled selected></option>
    $htmlOptions
    </select> 
    <button type='button' id='btn-del-cat' class='btn mydisable-btn'><i class=\"fas fa-trash\"></i></button>

</form>

</div>

<br><br>
";

/////////////////////////////////////////
//php strings with different question creation forms

//short answer must have: question name, question text, right answer(s)
$frmShortAns = "
<form id='frmShortAns' name='frmlShortAns' method='post' action='#' class='myFrmAns'>
    <label for='txtQname1'>Enunciado questão (avaliador)</label> <br>
    <textarea class='form-control' type='text' id='txtQname1' name='txtQname1' placeholder='pergunta (curta) que o avaliador lê'></textarea> <br>

    <label for='txtQText1'>Enunciado questão (funcionário)</label> <br>
    <textarea class=\"form-control\" id=\"txtQText1\" name='txtQText1' rows=\"6\" placeholder='pergunta (curta) que o funcionário lê'></textarea> <br>

    <div>
     <div class='div-scroll' id='div-scroll-short'>
      <table>
        <tr>
            <td class='td-bigger'>
                <label for='txtQanswer1'>Answer 1</label> <br>
                <input class='txtQanswer short-opt' type='text' id='txtQanswer1' name='txtQanswer1'> <br>
            </td>
            <td class='td-smaller'>
                <label for='selQvalue1'>Value</label> <br>
                <select class='custom-select my-sel-short' name='selQvalue1'>
                <option value=\"0.0\">None</option>
                <option value=\"1.0\" selected=''>100%</option>
                <option value=\"0.9\">90%</option>
                <option value=\"0.8333333\">83,33333%</option>
                <option value=\"0.8\">80%</option>
                <option value=\"0.75\">75%</option>
                <option value=\"0.7\">70%</option>
                <option value=\"0.6666667\">66,66667%</option>
                <option value=\"0.6\">60%</option>
                <option value=\"0.5\">50%</option>
                <option value=\"0.4\">40%</option>
                <option value=\"0.3333333\">33,33333%</option>
                <option value=\"0.3\">30%</option>
                <option value=\"0.25\">25%</option>
                <option value=\"0.2\">20%</option>
                <option value=\"0.1666667\">16,66667%</option>
                <option value=\"0.1428571\">14,28571%</option>
                <option value=\"0.125\">12,5%</option>
                <option value=\"0.1111111\">11,11111%</option>
                <option value=\"0.1\">10%</option>
                <option value=\"0.05\">5%</option>
                </select> 
            </td>
        </tr>
        <tr>
            <td class='td-bigger'>
                <label for='txtQanswer2'>Answer 2</label> <br>
                <input class='txtQanswer short-opt' type='text' id='txtQanswer2' name='txtQanswer2'> <br>
            </td>
            <td class='td-smaller2'>
                <label for='selQvalue2'>Value</label> <br>
                <select class='custom-select my-sel-short' name='selQvalue2'>
                <option value=\"0.0\">None</option>
                <option value=\"1.0\" selected=''>100%</option>
                <option value=\"0.9\">90%</option>
                <option value=\"0.8333333\">83,33333%</option>
                <option value=\"0.8\">80%</option>
                <option value=\"0.75\">75%</option>
                <option value=\"0.7\">70%</option>
                <option value=\"0.6666667\">66,66667%</option>
                <option value=\"0.6\">60%</option>
                <option value=\"0.5\">50%</option>
                <option value=\"0.4\">40%</option>
                <option value=\"0.3333333\">33,33333%</option>
                <option value=\"0.3\">30%</option>
                <option value=\"0.25\">25%</option>
                <option value=\"0.2\">20%</option>
                <option value=\"0.1666667\">16,66667%</option>
                <option value=\"0.1428571\">14,28571%</option>
                <option value=\"0.125\">12,5%</option>
                <option value=\"0.1111111\">11,11111%</option>
                <option value=\"0.1\">10%</option>
                <option value=\"0.05\">5%</option>
                </select> 
            </td>
        </tr>
      </table>

      <div id='tbl-limit-short'></div> <br>

     </div>

      <button type='button' class='btn myenable-btn' id='btn-add-ans-short'>add 1 more answer</button>

      <button type='button' class='btn mydisable-btn' id='btn-remove-ans-short'>remove last</button>

    </div>

</form>
";

//Essay answer must have: Nome e texto da questão
$frmEssayAns = "
<form id='frmEssayAns' name='frmlEssayAns' method='post' action='#' class='myFrmAns'>
    <label for='txtQname2'>Enunciado questão (avaliador)</label> <br>
    <textarea class='form-control' type='text' id='txtQname2' name='txtQname2' placeholder='pergunta (dissertação) que o avaliador lê'></textarea> <br>

    <label for='txtQText2'>Enunciado questão (funcionário)</label> <br>
    <textarea class=\"form-control\" id=\"txtQText2\" name='txtQText2' rows=\"6\" placeholder='pergunta (dissertação) que o funcionário lê'></textarea> <br>

</form>
";

//multiple choice answer must have: nome, texto, opções com peso da nota
$frmMultiAns = "
<form id='frmMultiAns' name='frmlMultiAns' method='post' action='#' class='myFrmAns'>
    <label for='txtQname3'>Enunciado questão (avaliador)</label> <br>
    <textarea class='form-control' type='text' id='txtQname3' name='txtQname3' placeholder='pergunta (multi) que o avaliador lê'></textarea> <br>

    <label for='txtQText3'>Enunciado questão (funcionário)</label> <br>
    <textarea class=\"form-control\" id=\"txtQText3\" name='txtQText3' rows=\"6\" placeholder='pergunta (multi) que o funcionário lê'></textarea> <br>

    <div>
     <div class='div-scroll' id='div-scroll'>
      <table id='tbl-multi'>
        <tr>
            <td class='td-bigger'>
                <label for='txtQanswer3'>Option 1</label> <br>
                <input class='txtQanswer mult-opt' type='text' name='txtQanswer3'> <br>
            </td>
            <td class='td-smaller'>
                <label for='selQvalue1'>Value</label> <br>
                <select class='custom-select my-sel-multi' name='selQvalue1'>
                <option value=\"0.0\">None</option>
                <option value=\"1.0\" selected=''>100%</option>
                <option value=\"0.9\">90%</option>
                <option value=\"0.8333333\">83,33333%</option>
                <option value=\"0.8\">80%</option>
                <option value=\"0.75\">75%</option>
                <option value=\"0.7\">70%</option>
                <option value=\"0.6666667\">66,66667%</option>
                <option value=\"0.6\">60%</option>
                <option value=\"0.5\">50%</option>
                <option value=\"0.4\">40%</option>
                <option value=\"0.3333333\">33,33333%</option>
                <option value=\"0.3\">30%</option>
                <option value=\"0.25\">25%</option>
                <option value=\"0.2\">20%</option>
                <option value=\"0.1666667\">16,66667%</option>
                <option value=\"0.1428571\">14,28571%</option>
                <option value=\"0.125\">12,5%</option>
                <option value=\"0.1111111\">11,11111%</option>
                <option value=\"0.1\">10%</option>
                <option value=\"0.05\">5%</option>
                </select> 
            </td>
        </tr>
        <tr>
            <td class='td-bigger'>
                <label for='txtQanswer2'>Option 2</label> <br>
                <input class='txtQanswer mult-opt' type='text' name='txtQanswer2'> <br>
            </td>
            <td class='td-smaller2'>
                <label for='selQvalue2'>Value</label> <br>
                <select class='custom-select my-sel-multi' name='selQvalue2'>
                <option value=\"0.0\">None</option>
                <option value=\"1.0\" selected=''>100%</option>
                <option value=\"0.9\">90%</option>
                <option value=\"0.8333333\">83,33333%</option>
                <option value=\"0.8\">80%</option>
                <option value=\"0.75\">75%</option>
                <option value=\"0.7\">70%</option>
                <option value=\"0.6666667\">66,66667%</option>
                <option value=\"0.6\">60%</option>
                <option value=\"0.5\">50%</option>
                <option value=\"0.4\">40%</option>
                <option value=\"0.3333333\">33,33333%</option>
                <option value=\"0.3\">30%</option>
                <option value=\"0.25\">25%</option>
                <option value=\"0.2\">20%</option>
                <option value=\"0.1666667\">16,66667%</option>
                <option value=\"0.1428571\">14,28571%</option>
                <option value=\"0.125\">12,5%</option>
                <option value=\"0.1111111\">11,11111%</option>
                <option value=\"0.1\">10%</option>
                <option value=\"0.05\">5%</option>
                </select> 
            </td>
        </tr>
        <tr>
            <td class='td-bigger'>
                <label for='txtQanswer3'>Option 3</label> <br>
                <input class='txtQanswer mult-opt' type='text' name='txtQanswer3'> <br>
            </td>
            <td class='td-smaller3'>
                <label for='selQvalue3'>Value</label> <br>
                <select class='custom-select my-sel-multi' name='selQvalue3' id='selQvalue3'>
                <option value=\"0.0\">None</option>
                <option value=\"1.0\" selected=''>100%</option>
                <option value=\"0.9\">90%</option>
                <option value=\"0.8333333\">83,33333%</option>
                <option value=\"0.8\">80%</option>
                <option value=\"0.75\">75%</option>
                <option value=\"0.7\">70%</option>
                <option value=\"0.6666667\">66,66667%</option>
                <option value=\"0.6\">60%</option>
                <option value=\"0.5\">50%</option>
                <option value=\"0.4\">40%</option>
                <option value=\"0.3333333\">33,33333%</option>
                <option value=\"0.3\">30%</option>
                <option value=\"0.25\">25%</option>
                <option value=\"0.2\">20%</option>
                <option value=\"0.1666667\">16,66667%</option>
                <option value=\"0.1428571\">14,28571%</option>
                <option value=\"0.125\">12,5%</option>
                <option value=\"0.1111111\">11,11111%</option>
                <option value=\"0.1\">10%</option>
                <option value=\"0.05\">5%</option>
                </select> 
            </td>
        </tr>
        <tr>
            <td class='td-bigger'>
                <label for='txtQanswer4'>Option 4</label> <br>
                <input class='txtQanswer mult-opt' type='text' name='txtQanswer4'> <br>
            </td>
            <td class='td-smaller4'>
                <label for='selQvalue4'>Value</label> <br>
                <select class='custom-select my-sel-multi' name='selQvalue4' id='selQvalue4'>
                <option value=\"0.0\">None</option>
                <option value=\"1.0\" selected=''>100%</option>
                <option value=\"0.9\">90%</option>
                <option value=\"0.8333333\">83,33333%</option>
                <option value=\"0.8\">80%</option>
                <option value=\"0.75\">75%</option>
                <option value=\"0.7\">70%</option>
                <option value=\"0.6666667\">66,66667%</option>
                <option value=\"0.6\">60%</option>
                <option value=\"0.5\">50%</option>
                <option value=\"0.4\">40%</option>
                <option value=\"0.3333333\">33,33333%</option>
                <option value=\"0.3\">30%</option>
                <option value=\"0.25\">25%</option>
                <option value=\"0.2\">20%</option>
                <option value=\"0.1666667\">16,66667%</option>
                <option value=\"0.1428571\">14,28571%</option>
                <option value=\"0.125\">12,5%</option>
                <option value=\"0.1111111\">11,11111%</option>
                <option value=\"0.1\">10%</option>
                <option value=\"0.05\">5%</option>
                </select> 
            </td>
        </tr>
      </table>

      <div id='tbl-limit-multi'></div> <br>

     </div>

      <button type='button' class='btn myenable-btn' id='btn-add-ans-multi'>add 1 more answer</button>

      <button type='button' class='btn mydisable-btn' id='btn-remove-ans-multi'>remove last</button>
      

    </div>

</form>
";

//range question must have: name, text and radio buttons (will work as essay question, no correct answer)
$frmRangeAns= "
<form id='frmRangeAns' name='frmlRangeAns' method='post' action='#' class='myFrmAns'>
    <label for='txtQname4'>Enunciado questão (avaliador)</label> <br>
    <textarea class='form-control' type='text' id='txtQname4' name='txtQname4' placeholder='pergunta (escala) que o avaliador lê'></textarea> <br>

    <label for='txtQText4'>Enunciado questão (funcionário)</label> <br>
    <textarea class=\"form-control\" id=\"txtQText4\" name='txtQText4' rows=\"6\" placeholder='pergunta (escala) que o funcionário lê\n\nDe 1 a 5, como VOCÊ se avalia?'></textarea> <br>

    <div>
     <div class='div-scroll' id='div-scroll-range'>
      <table id='tbl-range'>
        <tr>
            <td class='td-bigger'>
                <label for='txtQanswer4'>Scale 1</label> <br>
                <input class='txtQanswer range-opt' type='text' name='txtQanswer4' placeholder='ex: extremely unlikely'> <br>
            </td>
            <td class='td-smaller'>
                <label for='selQvalue4'>Number</label> <br>
                <select class='custom-select my-sel-range' name='selQvalue4'>
                <option value=\"1\" selected=''>1</option>
                </select> 
            </td>
        </tr>
        <tr>
            <td class='td-bigger'>
                <label for='txtQanswer4'>Scale 2</label> <br>
                <input class='txtQanswer range-opt' type='text' name='txtQanswer4' placeholder='ex: unlikely'> <br>
            </td>
            <td class='td-smaller2'>
                <label for='selQvalue4'>Number</label> <br>
                <select class='custom-select my-sel-range' name='selQvalue4'>
                <option value=\"2\" selected=''>2</option>
                </select> 
            </td>
        </tr>
        <tr>
            <td class='td-bigger'>
                <label for='txtQanswer4'>Scale 3</label> <br>
                <input class='txtQanswer range-opt' type='text' name='txtQanswer4'> <br>
            </td>
            <td class='td-smaller3'>
                <label for='selQvalue4'>Number</label> <br>
                <select class='custom-select my-sel-range' name='selQvalue4'>
                <option value=\"3\" selected=''>3</option>
                </select> 
            </td>
        </tr>
      </table>

      <div id='tbl-limit-range'></div> <br>

     </div>

      <button type='button' class='btn myenable-btn' id='btn-add-ans-range'>inscrease scale</button>

      <button type='button' class='btn mydisable-btn' id='btn-remove-ans-range'>decrease</button>
      

    </div>


</form>

";


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
echo "<span><a href='selectquestionsdb.php' class='pdi-nostyle my-marginr'>back</a></span>";
echo "<span><a href='index.php' class='pdi-nostyle my-marginr'>dashboard</a></span>";
echo "<div class='mypush'><span class='mylogo'>PDI</span></div>";
echo "</div><br>";

echo "<div id='mygrey-bg'>"; //grey bg starts


echo "<h1>Step 4.1 - Create Question Database</h1>";
echo "<footer class='my-belowh1'>Type the name and add questions
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
<span class=\"my-circle\" 
onclick='window.location.href = \"selectsectors.php?stepnav\"'>3</span>
<footer>step 3</footer>
</div>";

echo "<div class='my-circle-div'>
<span class=\"my-circle\" 
onclick='window.location.href = \"selectquestionsdb.php?stepnav\"' 
style=\"background-color: var(--myprimary);\">4</span>
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
echo "<br><br>";

//create database
echo "
    <div>
        <form id='frmAddDb' name='frmAddDb' method='post' action='createquestiondb.php'>
            <label for='database-name' class='my-label'>Database name</label> <br>
            <input type='text' id='database-name' class='my-large-input' data-id='0'>    
            <button type='button' id='btn-criar-db' class='btn myenable-btn'><i class=\"fas fa-chevron-down\"></i></button>
        </form>

        <div id='hidden-db-div' class='my-hidden'>
            <label for='select-db' class='my-label'>Select Existing DB</label> <br>
            <select id=\"select-db\" name=\"select-db\" class=\"my-large-input my-disabled\">
                <option value=\"0\" disabled selected>to create a new, just write above</option>
                $htmlDBOptions
                <div id='div-opt-select'></div>
                
            </select>
        </div>

        <form id='frm-hidden-db-select' class='my-hidden'>
            <input type='hidden' name='dbhidden-name' id='dbhidden-name' value=''>
            <input type='hidden' name='dbhidden-id' id='dbhidden-id' value='0'>
        </form>
        
    </div>
";

echo "<br>";

//div for seeing/creating questions
echo "<div>
<input type='button' value='view questions' class='my-secondary-btn my-btn-pad my-large-input' id='btn-view'>
<input type='button' value='create questions' class='my-secondary-btn my-btn-pad my-large-input' id='btn-create'>
</div>";

echo "
    <div id='question-view-div' class='my-inside-container my-hidden'>
            Select a database to display its questions: <br><br>

            <div class='mx-auto' style='max-width: 800px'>
            <div id='return-quest-div'></div>
            </div>

    </div>
    <div id='question-create-div' class='my-inside-container my-hidden'>
            <div class='div-mywhite'>
                <form id=\"my-type-form\" name=\"my-type-form\" method=\"post\" action=\"createquestiondb.php\">

                    $dropdownCategory

                    <label for='select-type' class='my-label'>Question type</label> <br>
                    <select id=\"select-type\" name=\"select-type\" class=\"my-large-input\">
                        <option value=\"0\" disabled>-</option>
                        <option value=\"1\">short answer</option>
                        <option value=\"2\" selected>essay answer</option>
                        <option value=\"3\">multiple choice</option>
                        <option value=\"4\">range answer (1 - 10)</option>
                    </select>
                </form>

                <hr>

                <div id='div-type-short' class='question-type-container my-hidden'>$frmShortAns</div>
                <div id='div-type-essay' class='question-type-container my-hidden'>$frmEssayAns</div>
                <div id='div-type-multi' class='question-type-container my-hidden'>$frmMultiAns</div>
                <div id='div-type-range' class='question-type-container my-hidden'>$frmRangeAns</div>


                <br><br><br>
                <input type='button' id='btn-create-question' class='div-save-btn my-primary-btn my-marginlauto btn-create-question' value='create question'>

                <div id='return-div' class='margin-top'></div>

            </div>
    </div>
";


//bottom buttons

echo "<div id='div-save-buttons'>";


echo "<input type='button' id='id_back_btn' class='div-save-btn my-grey-btn'
value='Back'>";
echo "<input type='button' id='id_save_next_btn' class='div-save-btn my-primary-btn my-marginlauto'
value='Step 4'>";

echo "</div>";


//formulario escondido com os valores das questões
//former action: createquestiondb.php?criarq
echo "<form id=\"my-hidden-qform\" name=\"my-hidden-qform\" method=\"post\" action=\"\">";
echo "<input type=\"hidden\" name=\"hidden-qname\" id=\"hidden-qname\" value=\"\">";
echo "<input type=\"hidden\" name=\"hidden-qtext\" id=\"hidden-qtext\" value=\"\">";
echo "<input type=\"hidden\" name=\"hidden-qtype\" id=\"hidden-qtype\" value=\"\">";
echo "<input type=\"hidden\" name=\"hidden-qcat\" id=\"hidden-qcat\" value=\"\">";
echo "<input type=\"hidden\" name=\"hidden-qanswers\" id=\"hidden-qanswers\" value=\"\">";
echo "<input type=\"hidden\" name=\"hidden-qvalues\" id=\"hidden-qvalues\" value=\"\">";
//echo "<input type=\"hidden\" name=\"hidden-mytime\" id=\"hidden-mytime\" value=\"".$_SESSION['mytime']."\">";
echo "<input type=\"hidden\" name=\"hidden-mytime\" id=\"hidden-mytime\" value=\"".$_SESSION['edittrialid']."\">";
echo "<input type=\"hidden\" name=\"hidden-qdbname\" id=\"hidden-qdbname\" value=\"\">";
echo "<input type=\"hidden\" name=\"hidden-qdbid\" id=\"hidden-qdbid\" value=\"0\">";
echo "</form>";



//popup msg
echo "<div id ='my-smallmsg-error' class='my-smallmsg-error'>Question NAME too short!</div>";
echo "<div id ='my-smallmsg-error2' class='my-smallmsg-error'>Question TEXT too short!</div>";
echo "<div id ='my-smallmsg-error3' class='my-smallmsg-error'>Option choice can't be empty!</div>";
echo "<div id ='my-smallmsg-error4' class='my-smallmsg-error'>At least one option must worth 100%!</div>";
echo "<div id ='my-smallmsg-error5' class='my-smallmsg-error'>You cannot remove any more options!</div>";
echo "<div id ='my-smallmsg-error-short' class='my-smallmsg-error'>Answer too short!</div>";
echo "<div id ='my-smallmsg-error-not' class='my-smallmsg-error'>Not enough characters!</div>";
echo "<div id ='my-smallmsg-success' class='my-smallmsg-success'>Sending...</div>";
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

//onready para salvar e continuar
$(document).ready(function() {


$("#btn-create").attr('style', 'background-color: var(--myprimary) !important');
$("#question-create-div").show();

//nav menor
$( ".my-secondary-btn" ).on( "click", function() {
  var element = $(this);
  var idElement = element.attr('id');

  $(".my-secondary-btn").attr('style', 'background-color: var(--mysecondary) !important');
  element.attr('style', 'background-color: var(--myprimary) !important');
  
  ///////////
  //switch NAVEGAÇÃO 
  switch(idElement) 
  {
    case "btn-view":
      //show this div
      $(".my-inside-container").hide();
      $("#question-view-div").show();

      var databasename = ""+ $("#database-name").val() +"";
      $("#dbhidden-name").val(databasename);

      var databaseid = ""+ $("#database-name").attr("data-id") + "";
      $("#dbhidden-id").val(databaseid);


      //ajax
        var dados = $("#frm-hidden-db-select").serialize();

        $.ajax({
            method: 'POST',
            url: 'print/select_qreturn.php',
            data: dados,

            beforeSend: function(){$("#return-quest-div").html("loading...");}
        })
        .done(function(msg){
            $("#return-quest-div").html(msg);
        })
        .fail(function(){
            $("#return-quest-div").html("Couldn't load any questions!");
        });

      break;

    case "btn-create":
      //show
      $(".my-inside-container").hide();
      $("#question-create-div").show();
      break;

    default:
      alert("Error");
      //but still show some default div
      $(".my-inside-container").hide();
      $("#question-view-div").show();
      break;
  }
});


///////////
//navegação do SELECT tipo de questão

$("#div-type-essay").show();

$( "#select-type" ).on( "change", function() {
  var element = $(this);
  var optValue = this.value;

  //switch SELECT
  switch(optValue) 
  {
    case "1":
        //show this div
        $(".question-type-container").hide();
        $("#div-type-short").show();
        break;

    case "2":
        //show
        $(".question-type-container").hide();
        $("#div-type-essay").show();
        break;

    case "3":
        //show
        $(".question-type-container").hide();
        $("#div-type-multi").show();
        break;

    case "4":
        //show
        $(".question-type-container").hide();
        $("#div-type-range").show();
        break;

    default:
        alert("Error");
        //but still show some default div
        $(".question-type-container").hide();
        $("#div-type-essay").show();
        break;
  }
  
});

///select db onchange
$( "#select-db" ).on( "change", function() {

    var txtDb = $("#select-db option:selected").text();
    var valueDb = $("#select-db").val();
    
    $("#database-name").val(""+ txtDb);
    $("#database-name").attr("data-id", valueDb);
    
    var idDb = ""+ $("#database-name").attr("data-id") + "";

    dbclickstate = false;
    $("#hidden-db-div").hide(300);
    $("#btn-criar-db").html("<i class=\"fas fa-chevron-down\"></i>");

    //ajax repetido
    var databasename = ""+ $("#database-name").val() +"";
      $("#dbhidden-name").val(databasename);

      var databaseid = ""+ $("#database-name").attr("data-id") + "";
      $("#dbhidden-id").val(databaseid);

      //ajax
        var dados = $("#frm-hidden-db-select").serialize();

        $.ajax({
            method: 'POST',
            url: 'print/select_qreturn.php',
            data: dados,

            beforeSend: function(){$("#return-quest-div").html("loading...");}
        })
        .done(function(msg){
            $("#return-quest-div").html(msg);
        })
        .fail(function(){
            $("#return-quest-div").html("Couldn't load any questions!");
        });

});


//db on type somth different
$( "#database-name" ).on( "input", function() {

var valueDb = "0";
$("#database-name").attr("data-id", valueDb);

//ajax repetido
var databasename = ""+ $("#database-name").val() +"";

      $("#dbhidden-name").val(databasename);

      var databaseid = ""+ $("#database-name").attr("data-id") + "";
      $("#dbhidden-id").val(databaseid);

      //ajax
        var dados = $("#frm-hidden-db-select").serialize();

        $.ajax({
            method: 'POST',
            url: 'print/select_qreturn.php',
            data: dados,

            beforeSend: function(){$("#return-quest-div").html("loading...");}
        })
        .done(function(msg){
            $("#return-quest-div").html(msg);
        })
        .fail(function(){
            $("#return-quest-div").html("Couldn't load any questions!");
        });


});

$(".select-cat").on("change", function(){

var nome = $(this).val();
console.log("valor: "+ nome);

});

///////////


//CREATE GROUP BTN
$( "#id_save_next_btn" ).on( "click", function() {
    window.location.replace("selectquestionsdb.php");

});


//BROWSE DB BTN
var dbclickstate = false;
$( "#btn-criar-db" ).on( "click", function() {

    dbclickstate = !dbclickstate;

    if(dbclickstate){
        $("#hidden-db-div").show(300);
        $("#btn-criar-db").html("<i class=\"fas fa-chevron-up\"></i>");
    }
    else{
        $("#hidden-db-div").hide(300);
        $("#btn-criar-db").html("<i class=\"fas fa-chevron-down\"></i>");
    }
});


//EDIT COMPETENCYS (CATEGORIES)
var clickstate = false;
$("#btn-edit-cat").on("click", function(){

    clickstate = !clickstate;

    if(clickstate){
        $("#hidden-cat-div").show(300);
        $("#btn-edit-cat").html("<i class=\"fas fa-chevron-up\"></i>");
    }
    else{
        $("#hidden-cat-div").hide(300);
        $("#btn-edit-cat").html("<i class=\"far fa-edit\">");
    }

});

//create new competency (category)
$("#btn-criar-cat").on("click", function(){
    
    var txtCateg = ""+ $("#txtCatname").val() + "";
    if(txtCateg.length < 4){
        $("#txtCatname").focus();
        $("#my-smallmsg-error-not").fadeIn(200);
        $("#my-smallmsg-error-not").css("display", "flex");
        $("#my-smallmsg-error-not").delay(2000).fadeOut(400);

    }
    else{

        //$('#frmAddCat').submit();
        document.getElementById('frmAddCat').submit();
        $("#btn-criar-cat").attr("disabled","disabled");
    }

});

//delete competency
$("#btn-del-cat").on("click", function(){
    //
    var idCat = ""+ $("#select-cat-del").val() +"";
    if(idCat > 0){
        if (confirm('Delete this item?')) {

            document.getElementById('frmDelCat').submit();
            $("#btn-del-cat").attr("disabled","disabled");

        } else {
            //do nothing
        }
    }

});

  //previous page function
$("#id_back_btn").on("click", function(){
  window.location.replace("selectquestionsdb.php");
});

$(".btn-create-question").on("click", function(){
    var qtype = $("#select-type").val();
    //alert("button create question type: "+ qtype);
    /* 
        [1] = short
        [2] = essay
        [3] = multiple
        [4] = range
    */

    var dbname = ""+ $("#database-name").val() +"";
    if(dbname.length < 5){
        //my-smallmsg-error
        $("#database-name").focus();

        $("#my-smallmsg-error-not").fadeIn(200);
        $("#my-smallmsg-error-not").css("display", "flex");
        $("#my-smallmsg-error-not").delay(2000).fadeOut(400);
    }else{

        var dbid = ""+ $("#database-name").attr("data-id") + "";

        //passar valores pro form escondido
        $("#hidden-qdbname").val(dbname);
        $("#hidden-qdbid").val(dbid);
        

        switch(qtype){

            //////////////////////////////short
            case "1":
                //pegar o valor de tudo
                var qname = ""+ $("#txtQname1").val() + "";
                var qtext = ""+ $("#txtQText1").val() + "";

                if(qname.length < 5){
                    //my-smallmsg-error
                    $("#txtQname1").focus();
                    $("#my-smallmsg-error").fadeIn(200);
                    $("#my-smallmsg-error").css("display", "flex");
                    $("#my-smallmsg-error").delay(2000).fadeOut(400);
                    break;
                }
                else if(qtext.length < 12){
                    //my-smallmsg-error2
                    $("#txtQText1").focus();
                    $("#my-smallmsg-error2").fadeIn(200);
                    $("#my-smallmsg-error2").css("display", "flex");
                    $("#my-smallmsg-error2").delay(2000).fadeOut(400);
                    break;
                }

                var error_count = 0;
                $(".short-opt").each(function(){
                    var optval = "" + $(this).val() +"";
                    //console.log("opção: " + optval);

                    if(optval.length < 1){
                        //my-smallmsg-error3
                        $(this).focus();

                        $("#my-smallmsg-error-short").fadeIn(200);
                        $("#my-smallmsg-error-short").css("display", "flex");
                        $("#my-smallmsg-error-short").delay(2000).fadeOut(400);

                        error_count++;
                        return false; //saí do each
                    }
                });
                if(error_count > 0){break;}

                var selectCem = 0;
                error_count = 0;

                $(".my-sel-short").each(function(){
                    var selval = "" + $(this).val() + "";
                    
                    if(selval == "1.0"){
                        selectCem++;
                    }
                });
            if(selectCem < 1){
                    $(".my-sel-short").focus();

                    $("#my-smallmsg-error4").fadeIn(200);
                    $("#my-smallmsg-error4").css("display", "flex");
                    $("#my-smallmsg-error4").delay(2000).fadeOut(400);

                    break;
            }


                //recuperando os valores
                var nomeQue = "";
                var textoQue = "";
                var categQue = "";

                nomeQue = ""+ $("#txtQname1").val() +"";
                textoQue = ""+ $("#txtQText1").val() +"";
                categQue = ""+ $("#select-cat").val() +"";

                //lista de respostas
                const arrayRespostas = [];
                const arrayValores = [];

                //for each input
                $(".short-opt").each(function(){
                    var strResp = $(this).val();
                    arrayRespostas.push(strResp);
                });

                //for each select
                $(".my-sel-short").each(function(){
                    var strVal = $(this).val();
                    arrayValores.push(strVal);
                });

                const jsonRespostas = JSON.stringify(arrayRespostas);
                const jsonValores = JSON.stringify(arrayValores);

                console.log(nomeQue);
                console.log(textoQue);
                console.log(categQue);
                console.log(jsonRespostas);
                console.log(jsonValores);

                $("#hidden-qname").val(nomeQue);
                $("#hidden-qtext").val(textoQue);
                $("#hidden-qtype").val("1"); //short answer
                $("#hidden-qcat").val(categQue);
                $("#hidden-qanswers").val(jsonRespostas);
                $("#hidden-qvalues").val(jsonValores);

                //desativar btn por um tempinho
                var btn = $(".btn-create-question");

                btn.prop('disabled', true);
                setTimeout(function(){
                    btn.prop('disabled', false);
                }, 2000);


                //ajax
                var dados = $("#my-hidden-qform").serialize();

                $.ajax({
                    method: 'POST',
                    url: 'print/insert_qreturn.php',
                    data: dados,

                    beforeSend: function(){$("#return-div").html("loading...");}
                })
                .done(function(msg){
                    $("#return-div").html(msg);

                    //limpar os campos desse tipo de questão
                    $("#txtQname1").val("");
                    $("#txtQText1").val("");
                    //for each input clear
                    $(".short-opt").each(function(){ $(this).val(""); });
                    //for each select clear
                    $(".my-sel-short").each(function(){ var strVal = $(this).val("1.0"); });

                    //msg de sucesso popup e foco
                    $("#txtQname1").focus();
                    $("#my-smallmsg-success").fadeIn(200);
                    $("#my-smallmsg-success").css("display", "flex");
                    $("#my-smallmsg-success").delay(2000).fadeOut(400);

                    
                })
                .fail(function(){
                    $("#return-div").html("Failed to add the last question!");
                });

                break;

            //////////////////////////////essay
            case "2":
                //pegar o valor de tudo
                var qname = ""+ $("#txtQname2").val() + "";
                var qtext = ""+ $("#txtQText2").val() + "";
                var categQue = ""+ $("#select-cat").val() +"";

                if(qname.length < 5){
                    //my-smallmsg-error
                    $("#txtQname2").focus();
                    $("#my-smallmsg-error").fadeIn(200);
                    $("#my-smallmsg-error").css("display", "flex");
                    $("#my-smallmsg-error").delay(2000).fadeOut(400);
                    break;
                }
                else if(qtext.length < 12){
                    //my-smallmsg-error2
                    $("#txtQText2").focus();
                    $("#my-smallmsg-error2").fadeIn(200);
                    $("#my-smallmsg-error2").css("display", "flex");
                    $("#my-smallmsg-error2").delay(2000).fadeOut(400);
                    break;
                }

                //passando o valor para os inputs do form oculto
                $("#hidden-qname").val(qname);
                $("#hidden-qtext").val(qtext);
                $("#hidden-qtype").val("2"); //essay
                $("#hidden-qcat").val(categQue);

                //desativar o botão para evitar double-click
                var btn = $(".btn-create-question");

                btn.prop('disabled', true);
                setTimeout(function(){
                    btn.prop('disabled', false);
                }, 2000);

                //ajax
                var dados = $("#my-hidden-qform").serialize();

                $.ajax({
                    method: 'POST',
                    url: 'print/insert_qreturn.php',
                    data: dados,

                    beforeSend: function(){$("#return-div").html("loading...");}
                })
                .done(function(msg){
                    $("#return-div").html(msg);

                    //limpar os campos desse tipo de questão
                    $("#txtQname2").val("");
                    $("#txtQText2").val("");
                    
                    //msg de sucesso popup e foco
                    $("#txtQname2").focus();
                    $("#my-smallmsg-success").fadeIn(200);
                    $("#my-smallmsg-success").css("display", "flex");
                    $("#my-smallmsg-success").delay(2000).fadeOut(400);

                    
                })
                .fail(function(){
                    $("#return-div").html("Failed to add the last question!");
                });

                break;

            //////////////////////////////choice
            case "3":
                //pegar o valor de tudo
                var qname = ""+ $("#txtQname3").val() + "";
                var qtext = ""+ $("#txtQText3").val() + "";

                if(qname.length < 5){
                    //my-smallmsg-error
                    $("#txtQname3").focus();
                    $("#my-smallmsg-error").fadeIn(200);
                    $("#my-smallmsg-error").css("display", "flex");
                    $("#my-smallmsg-error").delay(2000).fadeOut(400);
                    break;
                }
                else if(qtext.length < 12){
                    //my-smallmsg-error2
                    $("#txtQText3").focus();
                    $("#my-smallmsg-error2").fadeIn(200);
                    $("#my-smallmsg-error2").css("display", "flex");
                    $("#my-smallmsg-error2").delay(2000).fadeOut(400);
                    break;
                }

                var error_count = 0;
                $(".mult-opt").each(function(){
                    var optval = "" + $(this).val() +"";
                    //console.log("opção: " + optval);

                    if(optval.length < 1){
                        //my-smallmsg-error3
                        $(this).focus();

                        $("#my-smallmsg-error3").fadeIn(200);
                        $("#my-smallmsg-error3").css("display", "flex");
                        $("#my-smallmsg-error3").delay(2000).fadeOut(400);

                        error_count++;
                        return false;
                    }
                });
                if(error_count > 0){break;}

                var selectCem = 0;
                error_count = 0;

                $(".my-sel-multi").each(function(){
                    var selval = "" + $(this).val() + "";
                    
                    if(selval == "1.0"){
                        selectCem++;
                    }
                });
            if(selectCem < 1){
                    $(".my-sel-multi").focus();

                    $("#my-smallmsg-error4").fadeIn(200);
                    $("#my-smallmsg-error4").css("display", "flex");
                    $("#my-smallmsg-error4").delay(2000).fadeOut(400);

                    break;
            }


            //recuperando os valores
                var nomeQue = "";
                var textoQue = "";
                var categQue = "";

                nomeQue = ""+ $("#txtQname3").val() +"";
                textoQue = ""+ $("#txtQText3").val() +"";
                categQue = ""+ $("#select-cat").val() +"";

                //lista de respostas
                const arrayRespostas3 = [];
                const arrayValores3 = [];

                //for each input
                $(".mult-opt").each(function(){
                    var strResp = $(this).val();
                    arrayRespostas3.push(strResp);
                });

                //for each select
                $(".my-sel-multi").each(function(){
                    var strVal = $(this).val();
                    arrayValores3.push(strVal);
                });

                const jsonRespostas3 = JSON.stringify(arrayRespostas3);
                const jsonValores3 = JSON.stringify(arrayValores3);

                console.log(nomeQue);
                console.log(textoQue);
                console.log(categQue);
                console.log(jsonRespostas3);
                console.log(jsonValores3);

                $("#hidden-qname").val(nomeQue);
                $("#hidden-qtext").val(textoQue);
                $("#hidden-qtype").val("3"); //multiple choice
                $("#hidden-qcat").val(categQue);
                $("#hidden-qanswers").val(jsonRespostas3);
                $("#hidden-qvalues").val(jsonValores3);

                //desativar btn por um tempinho
                var btn = $(".btn-create-question");

                btn.prop('disabled', true);
                setTimeout(function(){
                    btn.prop('disabled', false);
                }, 2000);


                //ajax
                var dados = $("#my-hidden-qform").serialize();

                $.ajax({
                    method: 'POST',
                    url: 'print/insert_qreturn.php',
                    data: dados,

                    beforeSend: function(){$("#return-div").html("loading...");}
                })
                .done(function(msg){
                    $("#return-div").html(msg);

                    //limpar os campos desse tipo de questão
                    $("#txtQname3").val("");
                    $("#txtQText3").val("");
                    //for each input clear
                    $(".mult-opt").each(function(){ $(this).val(""); });
                    //for each select clear
                    $(".my-sel-multi").each(function(){ var strVal = $(this).val("1.0"); });

                    //msg de sucesso popup e foco
                    $("#txtQname3").focus();
                    $("#my-smallmsg-success").fadeIn(200);
                    $("#my-smallmsg-success").css("display", "flex");
                    $("#my-smallmsg-success").delay(2000).fadeOut(400);

                    
                })
                .fail(function(){
                    $("#return-div").html("Failed to add the last question!");
                });


                
                break;

            ///////////////////////////range
            case "4":
                //pegar o valor de tudo
                var qname = ""+ $("#txtQname4").val() + "";
                var qtext = ""+ $("#txtQText4").val() + "";

                if(qname.length < 5){
                    //my-smallmsg-error
                    $("#txtQname4").focus();
                    $("#my-smallmsg-error").fadeIn(200);
                    $("#my-smallmsg-error").css("display", "flex");
                    $("#my-smallmsg-error").delay(2000).fadeOut(400);
                    break;
                }
                else if(qtext.length < 12){
                    //my-smallmsg-error2
                    $("#txtQText4").focus();
                    $("#my-smallmsg-error2").fadeIn(200);
                    $("#my-smallmsg-error2").css("display", "flex");
                    $("#my-smallmsg-error2").delay(2000).fadeOut(400);
                    break;
                }

                //
                var error_count = 0;
                $(".range-opt").each(function(){
                    var optval = "" + $(this).val() +"";
                    //console.log("opção: " + optval);

                    if(optval.length < 1){
                        //my-smallmsg-error3
                        $(this).focus();

                        $("#my-smallmsg-error-not").fadeIn(200);
                        $("#my-smallmsg-error-not").css("display", "flex");
                        $("#my-smallmsg-error-not").delay(2000).fadeOut(400);

                        error_count++;
                        return false;
                    }
                });
                if(error_count > 0){break;}


            //recuperando os valores
                var nomeQue = "";
                var textoQue = "";
                var categQue = "";

                nomeQue = ""+ $("#txtQname4").val() +"";
                textoQue = ""+ $("#txtQText4").val() +"";
                categQue = ""+ $("#select-cat").val() +"";

                //lista de respostas
                const arrayRespostas4 = [];
                const arrayValores4 = [];

                //for each input
                $(".range-opt").each(function(){
                    var strResp = $(this).val();
                    arrayRespostas4.push(strResp);
                });

                //for each select
                $(".my-sel-range").each(function(){
                    var strVal = $(this).val();
                    arrayValores4.push(strVal);
                });

                const jsonRespostas4 = JSON.stringify(arrayRespostas4);
                const jsonValores4 = JSON.stringify(arrayValores4);

                console.log(nomeQue);
                console.log(textoQue);
                console.log(categQue);
                console.log(jsonRespostas4);
                console.log(jsonValores4);

                $("#hidden-qname").val(nomeQue);
                $("#hidden-qtext").val(textoQue);
                $("#hidden-qtype").val("4"); //range
                $("#hidden-qcat").val(categQue);
                $("#hidden-qanswers").val(jsonRespostas4);
                $("#hidden-qvalues").val(jsonValores4);

                //desativar btn por um tempinho
                var btn = $(".btn-create-question");

                btn.prop('disabled', true);
                setTimeout(function(){
                    btn.prop('disabled', false);
                }, 2000);


                //ajax
                var dados = $("#my-hidden-qform").serialize();

                $.ajax({
                    method: 'POST',
                    url: 'print/insert_qreturn.php',
                    data: dados,

                    beforeSend: function(){$("#return-div").html("loading...");}
                })
                .done(function(msg){
                    $("#return-div").html(msg);

                    //limpar os campos desse tipo de questão
                    $("#txtQname4").val("");
                    $("#txtQText4").val("");
                    //for each input clear
                    $(".range-opt").each(function(){ $(this).val(""); });
                    

                    //msg de sucesso popup e foco
                    $("#txtQname4").focus();
                    $("#my-smallmsg-success").fadeIn(200);
                    $("#my-smallmsg-success").css("display", "flex");
                    $("#my-smallmsg-success").delay(2000).fadeOut(400);

                    
                })
                .fail(function(){
                    $("#return-div").html("Failed to add the last question!");
                });


                break;

            default:
                alert('Algo deu errado!');
                break;
        }

        
    }

});

//botão de adicionar mais um opção de multipla escolha
$("#btn-add-ans-multi").on("click", function(){

    //var
    var countOptions = 0;
    var optionBlock = "";
    var blockIndex = 0;
    var tblid = "";

    //verficar quantas alternativas já existem na tela
    $(".mult-opt").each(function(){countOptions++;});

    blockIndex = countOptions +1;
    tblid = "tblid"+blockIndex+"";

    //criar um bloco
    optionBlock = "<table id='"+tblid+"'><tr><td class='td-bigger'><label for='txtQanswer"+blockIndex +"'>Option "+ blockIndex +"</label> <br><input class='txtQanswer mult-opt' type='text' name='txtQanswer"+blockIndex +"'> <br></td><td class='td-smaller'><label for='selQvalue"+blockIndex+"'>Value</label> <br><select class='custom-select my-sel-multi' name='selQvalue"+blockIndex+"'><option value=\"0.0\">None</option><option value=\"1.0\" selected=''>100%</option><option value=\"0.9\">90%</option><option value=\"0.8333333\">83,33333%</option><option value=\"0.8\">80%</option><option value=\"0.75\">75%</option><option value=\"0.7\">70%</option><option value=\"0.6666667\">66,66667%</option><option value=\"0.6\">60%</option><option value=\"0.5\">50%</option><option value=\"0.4\">40%</option><option value=\"0.3333333\">33,33333%</option><option value=\"0.3\">30%</option><option value=\"0.25\">25%</option><option value=\"0.2\">20%</option><option value=\"0.1666667\">16,66667%</option><option value=\"0.1428571\">14,28571%</option><option value=\"0.125\">12,5%</option><option value=\"0.1111111\">11,11111%</option><option value=\"0.1\">10%</option><option value=\"0.05\">5%</option></select></td></tr><table>";

    //add no html
    $( "#tbl-limit-multi")
    .before( ""+ optionBlock + "");

    $("#div-scroll").animate({ scrollTop: $('#div-scroll').prop("scrollHeight")}, 1000);

});
//botão de remover uma opçao multipla escolha
$("#btn-remove-ans-multi").on("click", function(){
    //var
    var countOptions = 0;
    var blockIndex = 0;
    var tblid = "";

    //verficar quantas alternativas já existem na tela
    $(".mult-opt").each(function(){countOptions++;});

    if(countOptions < 5){
        $("#my-smallmsg-error5").fadeIn(200);
        $("#my-smallmsg-error5").css("display", "flex");
        $("#my-smallmsg-error5").delay(2000).fadeOut(400);
    }

    blockIndex = countOptions;
    tblid = "tblid"+blockIndex+"";

    $("#"+tblid+"").remove();
});


//botão adcionar resposta short answer
$("#btn-add-ans-short").on("click", function(){
    
    //var
    var countOptions = 0;
    var optionBlock = "";
    var blockIndex = 0;
    var tblid = "";

    //verficar quantas alternativas já existem na tela
    $(".short-opt").each(function(){countOptions++;});

    blockIndex = countOptions +1;
    tblid = "shorttblid"+blockIndex+"";

    //criar um bloco
    optionBlock = "<table id='"+tblid+"'><tr><td class='td-bigger'><label for='txtQanswer"+blockIndex +"'>Answer "+ blockIndex +"</label> <br><input class='txtQanswer short-opt' type='text' name='txtQanswer"+blockIndex +"'> <br></td><td class='td-smaller'><label for='selQvalue"+blockIndex+"'>Value</label> <br><select class='custom-select my-sel-short' name='selQvalue"+blockIndex+"'><option value=\"0.0\">None</option><option value=\"1.0\" selected=''>100%</option><option value=\"0.9\">90%</option><option value=\"0.8333333\">83,33333%</option><option value=\"0.8\">80%</option><option value=\"0.75\">75%</option><option value=\"0.7\">70%</option><option value=\"0.6666667\">66,66667%</option><option value=\"0.6\">60%</option><option value=\"0.5\">50%</option><option value=\"0.4\">40%</option><option value=\"0.3333333\">33,33333%</option><option value=\"0.3\">30%</option><option value=\"0.25\">25%</option><option value=\"0.2\">20%</option><option value=\"0.1666667\">16,66667%</option><option value=\"0.1428571\">14,28571%</option><option value=\"0.125\">12,5%</option><option value=\"0.1111111\">11,11111%</option><option value=\"0.1\">10%</option><option value=\"0.05\">5%</option></select></td></tr><table>";

    //add no html
    $( "#tbl-limit-short")
    .before( ""+ optionBlock + "");

    $("#div-scroll-short").animate({ scrollTop: $('#div-scroll-short').prop("scrollHeight")}, 1000);

});
//botão de remover uma opçao short answer
$("#btn-remove-ans-short").on("click", function(){
    //var
    var countOptions = 0;
    var blockIndex = 0;
    var tblid = "";

    //verficar quantas alternativas já existem na tela
    $(".short-opt").each(function(){countOptions++;});

    if(countOptions < 3){
        $("#my-smallmsg-error5").fadeIn(200);
        $("#my-smallmsg-error5").css("display", "flex");
        $("#my-smallmsg-error5").delay(2000).fadeOut(400);
    }

    blockIndex = countOptions;
    tblid = "shorttblid"+blockIndex+"";

    $("#"+tblid+"").remove();
});


//botão de adicionar mais um opção de range
$("#btn-add-ans-range").on("click", function(){

    //var
    var countOptions = 0;
    var optionBlock = "";
    var blockIndex = 0;
    var tblid = "";

    //verficar quantas alternativas já existem na tela
    $(".range-opt").each(function(){countOptions++;});

    blockIndex = countOptions +1;
    tblid = "rangetblid"+blockIndex+"";

    //criar um bloco
    optionBlock = "<table id='"+tblid+"'><tr><td class='td-bigger'><label for='txtQanswer"+blockIndex +"'>Scale "+ (blockIndex) +"</label> <br><input class='txtQanswer range-opt' type='text' name='txtQanswer"+blockIndex +"'> <br></td><td class='td-smaller'><label for='selQvalue"+blockIndex+"'>Number</label> <br><select class='custom-select my-sel-range' name='selQvalue"+blockIndex+"'><option value='"+ (blockIndex) +"' selected=''>"+ (blockIndex) +"</option></select></td></tr><table>";

    //add no html
    $( "#tbl-limit-range")
    .before( ""+ optionBlock + "");

    $("#div-scroll-range").animate({ scrollTop: $('#div-scroll-range').prop("scrollHeight")}, 1000);

});

//botão de remover uma opçao range
$("#btn-remove-ans-range").on("click", function(){
    //var
    var countOptions = 0;
    var blockIndex = 0;
    var tblid = "";

    //verficar quantas alternativas já existem na tela
    $(".range-opt").each(function(){countOptions++;});

    if(countOptions < 4){
        $("#my-smallmsg-error5").fadeIn(200);
        $("#my-smallmsg-error5").css("display", "flex");
        $("#my-smallmsg-error5").delay(2000).fadeOut(400);
    }

    blockIndex = countOptions;
    tblid = "rangetblid"+blockIndex+"";

    $("#"+tblid+"").remove();
});



}); //fim on ready




</script>