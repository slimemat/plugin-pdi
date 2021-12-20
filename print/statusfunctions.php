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


 //função para retornar a primeira estrutura html com dados da aba status
 function stsPrimeiroBloco($alunoid, $trialid, $sectorid){
    
    global $USER, $DB, $CFG;

    //var
    $blocoHTML = "";
    $statusPDI = "";

    //current user id (logged in)
    $evaluatorid = $USER->id;

    //***** PARTE ALUNO *****/
    $sqlAluno = "SELECT u.id, u.firstname, u.lastname, u.username, u.email 
                    FROM {user} u WHERE u.id = '$alunoid'";
    $resAluno = $DB->get_records_sql($sqlAluno);

    //var aluno
    $alunoFirstname = $resAluno[$alunoid]->firstname;
    $alunoLastname = $resAluno[$alunoid]->lastname;
    $alunoUsername = $resAluno[$alunoid]->username;
    $alunoEmail = $resAluno[$alunoid]->email;

    


    //***** PARTE AVALIADOR*****/
    $sqlAvaliador = "SELECT u.id, u.firstname, u.lastname, u.username, u.email 
                        FROM {user} u WHERE u.id ='$evaluatorid'";
    $resAvaliador = $DB->get_records_sql($sqlAvaliador);

    //var do avaliador
    $avaFirstname = $resAvaliador[$evaluatorid]->firstname;
    $avaLastname = $resAvaliador[$evaluatorid]->lastname;
    $avaUsername = $resAvaliador[$evaluatorid]->username;
    $avaEmail = $resAvaliador[$evaluatorid]->email;


    //***** COMPARAR progresso do pdi *****/

    //aluno
    $sqlSttAluno = "SELECT ast.userid, ast.idtrial, ast.sectorid, ast.isfinished, ast.timecreated, ast.timemodified, ast.id
                     FROM {local_pdi_answer_status} ast
                     WHERE ast.userid = '$alunoid' and ast.idtrial = '$trialid' AND ast.sectorid = '$sectorid'";

    $resSttAluno = $DB->get_records_sql($sqlSttAluno);
   
    if(count($resSttAluno)>0){
       $isfinished = $resSttAluno[$alunoid]->isfinished;
       if($isfinished == '1'){
            $statusPDI = "respondido apenas";
       }


       //avaliador
       $sqlSttAv = "SELECT anst.evaluatedid, anst.secmemberid, anst.isfinished, sm.sectorid, sm.userid, sm.trialid 
                     FROM {local_pdi_evanswer_status} anst
                     LEFT JOIN {local_pdi_sector_member} sm
                     ON sm.id = anst.secmemberid
                     WHERE anst.evaluatedid = '$alunoid' AND sm.userid = '$evaluatorid' and sm.trialid = '$trialid' and sm.sectorid = '$sectorid'";

       $resSttAv = $DB->get_records_sql($sqlSttAv);

       if(count($resSttAv)>0){
         $isfinished_av = $resSttAv[$alunoid]->isfinished;
         if($isfinished_av == '1'){
              $statusPDI = "respondido e avaliado";
         }
      }

    }else{
       $statusPDI = "não respondido";
    }


    //part 1 bloco
    $blocoHTML .= "
    <div class=\"d-flex bd-highlight my-padding-sm\" style=\"background-color: var(--mysecondary)\">

      <div class='flex-grow-1'>
         <img src=\"http://localhost/moodle/user/pix.php/$alunoid/f1.jpg\" class=\"my-circle\">
         <h5 class='my-label-bg margin-top my-white'>$alunoFirstname $alunoLastname</h5>
      </div>
      <div class='my-white'>
         <span>Avaliador: $avaFirstname $avaLastname</span> <br>
         <span>Status PDI: $statusPDI</span>
      </div>

   </div>";


   //part 2 abrir div
   $blocoHTML .= "  
   <div class=\"shadow-sm p-3 mb-5 bg-body rounded\">
   <span class='my-label'>Comparar respostas</span> <br>

   <div class='d-flex'>

     <div class='my-scroll2 rounded border border-dark'>


     ";

    
    //***** questões *****/

    $sqlQDB = "SELECT anstri.id, anstri.idquestion, anstri.answer, anstri.sectorid, anstri.timecreated, q.name qname, q.questiontext, q.qtype, qa.answer qa_answer, qa.fraction nota
               FROM {local_pdi_answer_trial} anstri
               LEFT JOIN {local_pdi_question} q
               ON q.id = anstri.idquestion
               LEFT JOIN {local_pdi_question_answers} qa
               ON qa.id = anstri.answer
               WHERE anstri.answeredbyid = '$alunoid' and anstri.idtrial = '$trialid' and anstri.sectorid = '$sectorid'
               ORDER BY anstri.idquestion";

    $resQDB = $DB->get_records_sql($sqlQDB);

    foreach($resQDB as $r){

      //o que o avaliador respondeu NESSA questão
         $sqlQav = "SELECT anstri.idquestion, anstri.id, anstri.answeredbyid, anstri.idanstatus, anstri.answer anstri_answer, q.name, q.qtype, q.category, qa.question, qa.answer qa_answer, qa.fraction, ast.userid, ast.sectorid, ast.idtrial
               FROM {local_pdi_evanswer_trial} anstri
               LEFT JOIN {local_pdi_question} q
               ON q.id = anstri.idquestion
               LEFT JOIN {local_pdi_question_answers} qa
               ON qa.id = anstri.answer
               LEFT JOIN {local_pdi_answer_status} ast
               ON ast.id = anstri.idanstatus
               WHERE anstri.answeredbyid = '$evaluatorid' and ast.idtrial = '$trialid' AND ast.sectorid = '$sectorid' AND anstri.idquestion = '$r->idquestion' AND ast.userid = '$alunoid'"; 
         $resQav = $DB->get_records_sql($sqlQav);

      foreach($resQav as $rq){

         //categiria DESSA questão
         $sqlCat = "SELECT qcat.id, qcat.name from {local_pdi_question_categ} qcat
                     WHERE qcat.id = '$rq->category'";
         $resCat = $DB->get_records_sql($sqlCat);

         if(count($resCat)>0){
            $strCategoria = $resCat[$rq->category]->name;
         }
         else{
            $strCategoria = "<span title='sem categoria'>-</span>"; //categoria vazia
         }

         //se o tipo da pergunta NÃO for escrita, pegar a resposta
         $qtype = $rq->qtype;
         $respostaAv = ""; 
         $respostaAluno = "";

         if($qtype == 'range' or $qtype == 'multichoice'){
            $respostaAv = $rq->qa_answer . " (" . $rq->fraction . ")";
            $respostaAluno = $r->qa_answer . " (" . $r->nota . ")";
         }
         else{
            $respostaAv = $rq->anstri_answer;
            $respostaAluno = $r->answer;
         }

         //parte do card de questões dentro do scroll
         $blocoHTML .= "
         <div class=\"my-margin-l qblock2 shadow-sm p-3 mb-5 rounded\"> 
            <h6 class='my-label my-bold'>Pergunta:</h6>
            <h5 class='my-med-bold'>$rq->name</h5>
            <hr>
            <div class='d-flex'>
               <div class=\"flex-fill\"><span class='my-label my-mention'>resposta avaliador:</span></div>
               <div class=\"w-100 my-font-family\"><span>$respostaAv</span></div>
            </div>
            <hr>
            <div class='d-flex'>
               <div class=\"flex-fill\"><span class='my-label my-mention'>resposta avaliado:</span></div>
               <div class=\"w-100 my-font-family\"><span>$respostaAluno</span></div>
            </div>
            <span class='my-mention' 
               style='display: block; 
               margin-top: -10px; 
               text-align: right;'>$strCategoria</span>
         </div>";

      }
      

    }
    if(count($resQav)<1 or count($resQDB)<1){
      $blocoHTML .= "
      <div class=\"card\">
      <div class=\"card-body my-bg-light\">
        <h5 class=\"card-title\">Vazio...</h5>
        <p class=\"card-text my-font-family\">Ainda não há o respostas salvas para comparar.</p>
      </div>
    </div>";
   }
      
    $blocoHTML .= "
    </div>

    <div class='p-5 mx-auto' style=\"\">
      <a href='#div-reuniao'><button type=\"button\" class=\"btn btn-primary btn-lg\">Marcar reunião</button></a> <br><br>
      <a href='#div-objetivos'><button type=\"button\" class=\"btn btn-primary btn-lg\">Anotar objetivos</button></a> <br><br>
      <button type=\"button\" class=\"btn btn-primary btn-lg\">Concluir</button> <br><br>
    </div>

   </div>
   </div>";

   
   //*** form para criar um course com o plugin congrea ***/
   $sqlVeCat = "SELECT * FROM mdl_course_categories cc WHERE cc.name = 'categoria pdi' and cc.idnumber = 'pdi_hidden_key'";
   $resVeCat = $DB->get_records_sql($sqlVeCat);
   $htmlFormCourse = '';

   if(count($resVeCat) == 0){
      $addCat = new stdClass();
      $addCat->name = "categoria pdi";
      $addCat->idnumber = "pdi_hidden_key";

      $res = $DB->insert_record('course_categories', $addCat);
      echo "<script>console.log('pdi inseriu categoria:' + $res)</script>";

      $sqlVeCat = "SELECT * FROM mdl_course_categories cc WHERE cc.name = 'categoria pdi' and cc.idnumber = 'pdi_hidden_key'";
      $resVeCat = $DB->get_records_sql($sqlVeCat);

      $resVeCat = array_values($resVeCat);
      $resVeCat = $resVeCat[0];
      $idcoursecat = $resVeCat->id;

      $htmlFormCourse = "
      <form id='form-create-course' data-id-coursecat='$idcoursecat'>
         <div class=\"mb-3\">
            <label for=\"input-nome-course\" class=\"form-label\">Nome das reuniões</label>
            <input type=\"text\" class=\"form-control rounded\" id=\"input-nome-course\" placeholder=\"Reuniões PDI Processo Moodle\" autocomplete=\"off\" maxlength=\"30\">
         </div>
         <button id=\"btn-add-course\" type=\"button\" class=\"btn btn-primary\">Criar</button>
      </form>
      ";


   }
   else{
      echo "<script>console.log('pdi: categoria do plugin já criada')</script>";

      $resVeCat = array_values($resVeCat);
      $resVeCat = $resVeCat[0];
      $idcoursecat = $resVeCat->id;

      $htmlFormCourse = "
      <form id='form-create-course' data-id-coursecat='$idcoursecat'>
         <div class=\"mb-3\">
            <label for=\"input-nome-course\" class=\"form-label\">Nome das reuniões</label>
            <input type=\"text\" class=\"form-control rounded\" id=\"input-nome-course\" placeholder=\"Reuniões PDI Processo Moodle\" autocomplete=\"off\" maxlength=\"30\">
         </div>
         <button id=\"btn-add-course\" type=\"button\" class=\"btn btn-primary\">Criar</button>
      </form>
      ";
   }

//************************* **************************//
   //pdi_trial_evaluator pegar o id
   $trial_ev_id = "";
   $sqlGetTrevId = "SELECT trev.id trevid, trev.cohortid, ev.id evid, ev.mdlid FROM {local_pdi_trial_evaluator} trev
                     LEFT JOIN {local_pdi_evaluator} ev
                     ON ev.id = trev.evaluatorid
                     WHERE trev.trialid = '$trialid' AND ev.mdlid = '$USER->id'";
   $resGetTrevId = $DB->get_records_sql($sqlGetTrevId);
   $resGetTrevId = array_values($resGetTrevId);
   $resGetTrevId = $resGetTrevId[0];

   $cohortTrial = $resGetTrevId->cohortid;
   $trial_ev_id = $resGetTrevId->trevid;


   //verificar se já existe um curso para a reunião congrea
   $sqlVerCourse = "SELECT c.id cid, c.category ccat, c.fullname cname, c.shortname cshortname, c.startdate cstart, c.enddate cend, c.visible,
                     cc.id ccid, cc.name ccname, cff.id cffid, cff.shortname cffshortname, cfd.id cfdid, cfd.charvalue cfdcharvalue, cfd.value cfdvalue
                     FROM {course} c
                     LEFT JOIN {course_categories} cc
                     ON cc.id = c.category
                     LEFT JOIN {customfield_field} cff
                     ON cff.shortname = 'pdi_trial_evaluator'
                     LEFT JOIN {customfield_data} cfd
                     ON cfd.fieldid = cff.id AND cfd.instanceid = c.id
                     WHERE cc.name = 'categoria pdi' AND cc.idnumber = 'pdi_hidden_key' AND cfd.value = '$trial_ev_id'
                     ";
   $resVerCourse = $DB->get_records_sql($sqlVerCourse);

   if(count($resVerCourse) < 1){
      $htmlReuniao = $htmlFormCourse;
   }
   else{

      $resVerCourse = array_values($resVerCourse);
      $resVerCourse = $resVerCourse[0];

      $courseid = $resVerCourse->cid;
      $categoryid = $resVerCourse->ccat;
      $cname = $resVerCourse->cname;
      $cshortname = $resVerCourse->cshortname;
      $cstart = $resVerCourse->cstart;
      $cend = $resVerCourse->cend;
      $cvisible = $resVerCourse->visible;

      $htmlReuniao = "<span class=\"badge bg-secondary\">Reunião criada</span>
                     <br>
                     <h5 class='my-font-family'>$cname</h5>
                     <button type=\"button\" class=\"btn btn-primary btn-sm\" id='btn-ver-reuniao' data-cid='$courseid'>Ver reunião</button>                     
      ";
      //ver se o curso terá opção de ocultar ou mostrar
      if($cvisible == 1){
         $htmlReuniao .= "<button type=\"button\" class=\"btn btn-secondary btn-sm\" id='btn-ocultar-curso' data-cid='$courseid' title='ocultar'> visibilidade: <i class=\"far fa-eye\"></i></button>";
      }else{
         $htmlReuniao .= "<button type=\"button\" class=\"btn btn-secondary btn-sm\" id='btn-ocultar-curso' data-cid='$courseid' title='mostrar'> visibilidade: <i class=\"far fa-eye-slash\"></i></button>";
      }


   }
   


   //marcar e objetivos
   $blocoHTML .= "
   <div id='my-tab2-inner3'>
      <!--Archor points-->
      <div id='div-reuniao'>
         <div class=\"my-bg-secondary my-padding-xsm\"><span class='my-font-family my-qtitle my-white'>
            <i class=\"fas fa-video mx-3\"></i>Marcar reunião</span>
         </div>
         <div class=\"card-body shadow-sm p-3 mb-5 bg-body rounded\">
               <div class=\"\">

                  <small class='my-font-family text-muted'>Deve-se utilizar o componente 'cursos' do moodle para marcar reuniões</small>
                  $htmlReuniao

               </div>
         </div>
      </div>

      <div id='div-objetivos'>
         <div class=\"my-bg-secondary my-padding-xsm\"><span class='my-font-family my-qtitle my-white'>
            <i class=\"fas fa-rocket mx-3\"></i>Objetivos</span>
         </div>
         <div class=\"card-body shadow-sm p-3 mb-5 bg-body rounded\">
            <div class=\"\">
               <form id='form-goal'>
                  <div class=\"mb-3\">
                     <label for=\"input-nome-goal\" class=\"form-label\">Nome do objetivo</label>
                     <input type=\"text\" class=\"form-control rounded\" id=\"input-nome-goal\" placeholder=\"Competência exemplo...\" autocomplete=\"off\" maxlength=\"30\">
                  </div>
                  <div class=\"mb-3\">
                     <label for=\"input-desc-goal\" class=\"form-label\" maxlength=\"30\">Descrição</label>
                     <textarea class=\"form-control rounded\" id=\"input-desc-goal\" rows=\"3\" maxlength=\"256\"></textarea>
                  </div>

                  <input type=\"hidden\" id=\"hidden-aluno-id\" value=\"$alunoid\">
                  <input type=\"hidden\" id=\"hidden-sector-id\" value=\"$sectorid\">
                  <input type=\"hidden\" id=\"hidden-trial-id\" value=\"$trialid\">               

                  <button id='btn-add-goal' type=\"button\" class=\"btn btn-primary\">Adicionar objetivo</button>
               </form>
            </div>


            <div id=\"horizontal-scroll\" class='my-scroll-h row my-bg-light'>

               <div id=\"div-cards\" class=\"\"></div>

                              

            </div>

         </div>


      </div>

   </div>";
    
    echo $blocoHTML;
    
 }


 function inserirObjetivo($title, $desc, $alunoid, $trialid, $sectorid){

   global $USER, $DB;

   //filtrando as var
   $title = strip_tags($title, '<b>');
   $desc = strip_tags($desc, '<b>');
      
      //verificar se já existem gravações na tabela local_pdi_answer_status do aluno
      $sqlVer = "SELECT s.userid, s.id FROM {local_pdi_answer_status} s
                  WHERE s.userid = '$alunoid' AND s.idtrial = '$trialid' AND s.sectorid ='$sectorid'";
      $resVer = $DB->get_records_sql($sqlVer);

      $anstatusid = $resVer[$alunoid]->id;

      
      if($anstatusid == null){
         echo "Falha ao adicionar!\nA pessoa em questão não finalizou suas respostas";
         return;
      }
      else{

         $addGoal = new stdClass();
         $addGoal->createdbyid = $USER->id;
         $addGoal->idanstatus = $anstatusid;
         $addGoal->title = $title;
         $addGoal->description = $desc;
         $addGoal->status = "criado";
         $addGoal->timecreated = time();
         $addGoal->timemodified = time();

         $resGoal = $DB->insert_record('local_pdi_goals', $addGoal);
         
         if($resGoal){
            echo "ok";
         }

      }

 }


 function fetchBlocosObjetivo($alunoid, $trialid, $sectorid){
    global $USER, $DB;

   //retorna um bloco html
   $htmlBlock = "";

   //pegar o id da tabelas answer_status do aluno
   $sqlVer = "SELECT s.userid, s.id FROM {local_pdi_answer_status} s
                  WHERE s.userid = '$alunoid' AND s.idtrial = '$trialid' AND s.sectorid ='$sectorid'";
   $resVer = $DB->get_records_sql($sqlVer);

   $anstatusid = $resVer[$alunoid]->id;

   if($anstatusid == null){
      return "<div class=\"card-body my-bg-light\">
                  <h5 class=\"card-title\">Vazio...</h5>
                  <p class=\"card-text my-font-family\">É preciso esperar esta pessoa responder.</p>
               </div>";
   }
   else{
      $sql = "SELECT * FROM {local_pdi_goals} a
               WHERE a.idanstatus = '$anstatusid' and a.createdbyid = '$USER->id'
               ORDER BY a.timecreated DESC";
   
      $res = $DB->get_records_sql($sql);

      foreach($res as $r){

         $htmlAcordeon = alunoGoalReply($r->id);

         $rId = $r->id;
         $rTitle = $r->title;
         $rDesc = $r->description;

         $rTitle = strip_tags($rTitle);
         $rDesc = strip_tags($rDesc);


         $htmlBlock .= "<div class=\"align-top bg-white mb-2 mr-2 my-padding-sm rounded\" style=\"width: 18rem; display: inline-block\"> 
                           <h5 id=\"h-goal-$rId\" class=\"card-title my-bold lbl-obj-onoff\">$rTitle</h5> 

                           <div class=\"mb-3\">
                              <label id=\"lbl-input-$rId\" for=\"input-edit-$rId\" class=\"form-label hidden\">Editar nome:</label>
                              <input type=\"text\" id=\"input-edit-$rId\" class=\"form-control rounded hidden\" value=\"$rTitle\" maxlength=\"30\">      
                           </div>

                           <p id=\"p-goal-$rId\" class=\"card-text lbl-obj-onoff\" style=\"white-space: pre-wrap;\">$rDesc</p>

                           <div class=\"mb-3\">
                              <label id=\"lbl-text-$rId\" for=\"text-edit-$rId\" class=\"form-label hidden\">Editar descrição:</label>
                              <textarea id=\"text-edit-$rId\" class=\"form-control rounded hidden\" rows=\"3\" maxlength=\"256\">$rDesc</textarea>
                           </div>

                           <button type=\"button\" id=\"btn-edit-goal-$rId\" class=\"btn btn-primary btn-edit-goal\" data-idgoal=\"$rId\">
                           <i class=\"fas fa-pencil-alt\"></i>
                           </button>

                           <button type=\"button\" id=\"btn-cancel-goal-$rId\" class=\"btn btn-primary btn-cancel-goal hidden\" data-idgoal=\"$rId\">
                           <i class=\"fas fa-times\"></i>
                           </button>

                           <button type=\"button\" id=\"btn-save-goal-$rId\" class=\"btn btn-success btn-save-goal hidden\" data-idgoal=\"$rId\">
                           <i class=\"far fa-save\"></i>
                           </button>

                           <hr>
                           $htmlAcordeon
                           

                        </div>";
      }

      return $htmlBlock;

   }
   



 }


 function updateGoalText($idgoal, $txttitle, $txtdesc){
   global $DB;

   $upGoal = new stdClass();
   $upGoal->id = $idgoal;
   $upGoal->title = strip_tags($txttitle);
   $upGoal->description = strip_tags($txtdesc);

   $res = $DB->update_record("local_pdi_goals", $upGoal);

   return $res;

 }

 function alunoGoalReply($idgoal){
    //esse é o POV do avaliador
    global $DB, $USER;

    $sql = "SELECT gf.*, u.firstname, u.lastname FROM {local_pdi_goals_feedback} gf
             LEFT JOIN {user} u ON u.id = gf.createdbyid
             WHERE gf.goalid = '$idgoal'";
    $res = $DB->get_records_sql($sql);
 
   $htmlGoalReply = "<div class=\"acordeon\">";
 
   foreach($res as $r){
       $idfeedback = $r->id;
       $title = strip_tags($r->title);
       $desc = $r->description;
       $timemod = $r->timemodified;
       $timemod_data = date("d/m/Y", $timemod);
       $timecreated = $r->timecreated;
       $fname = $r->firstname;
       $lname = $r->lastname;
 
       if($timecreated != $timemod){
   
         $htmlGoalReply .= "
         <div class='feedback-container' data-idfeed=\"$idfeedback\">
            <div class=\"acordeon-header\">

               <span id=\"title-fbid-$idfeedback\" class='mylabel-onoff'>$title</span>

            </div>
            <div class=\"acordeon-content\">
               <div class=\"mb-3\">  
                  <div><small class=\"text-muted\">$fname $lname:</small></div>

                     <span id=\"desc-fbid-$idfeedback\" class='mylabel-onoff'>$desc</span>  

                  <div><small class=\"text-muted\">$timemod_data</small></div>                        
               </div>
            </div>
         </div>";
       }
   }
                      
                        
    $htmlGoalReply .= "</div>";
 
    return $htmlGoalReply;
 }

 function alunoGoalReplyEdit($idgoal){
   //gerar de acordo com o banco depois
   global $DB, $USER;

   $sql = "SELECT gf.*, u.firstname FROM {local_pdi_goals_feedback} gf
            LEFT JOIN {user} u ON u.id = gf.createdbyid
            WHERE gf.goalid = '$idgoal' and gf.createdbyid = '$USER->id'";
   $res = $DB->get_records_sql($sql);

  $htmlGoalReply = "<div class=\"acordeon\">";

  foreach($res as $r){
      $idfeedback = $r->id;
      $title = strip_tags($r->title);
      $desc = strip_tags($r->description);
      $timemod = $r->timemodified;
      $timemod_data = date("d/m/Y", $timemod);
      $timecreated = $r->timecreated;
      $fname = $r->firstname;

      $linhaTitle = "<span id=\"title-fbid-$idfeedback\" class='mylabel-onoff'>$title</span>";
      if($timemod == $timecreated){
         $linhaTitle = "<span id=\"title-fbid-$idfeedback\" class='my-selected-opt mylabel-onoff'>$title</span>";
      }

     $htmlGoalReply .= "<div class='feedback-container' data-idfeed=\"$idfeedback\">
                           <div class=\"acordeon-header\">

                              $linhaTitle
                              <div style=\"display: none;\" id=\"div-input-title-$idfeedback\" class=\"myinput-onoff\">
                                 <input type=\"text\" class=\"form-control my-white-bg myinput-header\" id=\"input-title-$idfeedback\" value=\"$title\" maxlength=\"30\">
                              </div>

                              <div class='' style='float: right;'>
                                 <button type=\"button\" class=\"btn btn-primary btn-edit-goal\" data-idgoal=\"$idgoal\">
                                    <i class=\"fas fa-pencil-alt\"></i>
                                 </button>
                              </div>
                           </div>
                           <div class=\"acordeon-content\">
                              <div class=\"mb-3\">  
                                 <div><small class=\"text-muted\">$fname:</small></div>

                                    <span id=\"desc-fbid-$idfeedback\" class='mylabel-onoff'>$desc</span>  
                                    <div class=\"form-floating myinput-onoff\" style=\"display: none;\" id=\"div-input-desc-$idfeedback\">
                                       <textarea class=\"form-control my-white-bg\" id=\"input-desc-$idfeedback\" rows=\"5\" maxlength=\"256\">$desc</textarea>
                                 
                                       <div style=\"margin-top: 4px\">
                                          <small class=\"my-label-err-btn btn-cancelar-resp\" data-fbid=\"$idfeedback\">cancelar</small>
                                          <small class=\"my-label-btn btn-salvar-resp\" style=\"float: right;\" data-fbid=\"$idfeedback\">salvar</small>
                                       </div>
                                    </div> 

                                 <div><small class=\"text-muted\">$timemod_data</small></div>                        
                              </div>
                           </div>
                        </div>";
  }

                       

                       
                       
   $htmlGoalReply .= "</div>";

   return $htmlGoalReply;
}


 function blocosEscolherAvaliador($trialid){
    //escolher um avaliador no "my pdi" se ouver mais
    global $DB;

    //pegar o id do(s) avaliador(es) desse processo
      $sqlAvaliadores = "SELECT u.id userid, u.firstname, u.lastname, ta.trialid FROM {local_pdi_trial_evaluator} ta
      LEFT JOIN {local_pdi_evaluator} ev
      ON ev.id = ta.evaluatorid
      LEFT JOIN {user} u
      ON u.id = ev.mdlid
      WHERE ta.trialid = '$trialid'";

      $resAvaliadores = $DB->get_records_sql($sqlAvaliadores);

      //bloco
      $html = "<div class='my-margin-l'><h5 class='my-font-family my-padding-xsm'>Avaliador:</h5>";

      foreach($resAvaliadores as $ra){
         $uid = $ra->userid;
         $trialid = $ra->trialid;
         $fname = $ra->firstname;
         $lname = $ra->lastname;
         $fullname = $fname . " " . $lname;

         //pegar sectorid rápidinho
         $sqlsec = "SELECT sm.userid, sm.trialid, sm.sectorid FROM {local_pdi_sector_member} sm
                     WHERE sm.userid = '$uid' AND sm.trialid = '$trialid'";
         $resSec = $DB->get_records_sql($sqlsec);

         $sectorid = $resSec[$uid]->sectorid;


         $html .= "
               <div class=\"my-margin-box2 my-avaliador\" data-uid=\"$uid\" data-trialid=\"$trialid\" data-sectorid=\"$sectorid\">
                  <img src=\"http://localhost/moodle/user/pix.php/$uid/f1.jpg\" class=\"my-circle\">
                  <div class=\"my-sidetext\">
                        <span class=\"my-label-bg2\">$fullname</span> <br>                     
                  </div>
               </div>";
      }   

      $html .= "</div>"; 
      
      return $html;

 }

 function retornoPdiPorAvaliador($userid, $trialid){
   global $USER, $DB;

   //var
   $alunoid = $USER->id;
   $evaluatorid = $userid; //esse 'userid' recebido se refere ao avaliador
   
   //***** PARTE ALUNO *****/
   $sqlAluno = "SELECT u.id, u.firstname, u.lastname, u.username, u.email 
   FROM {user} u WHERE u.id = '$alunoid'";
   $resAluno = $DB->get_records_sql($sqlAluno);

   //var aluno
   $alunoFirstname = $resAluno[$alunoid]->firstname;
   $alunoLastname = $resAluno[$alunoid]->lastname;
   $alunoUsername = $resAluno[$alunoid]->username;
   $alunoEmail = $resAluno[$alunoid]->email;

   //***** PARTE AVALIADOR*****/
   $sqlAvaliador = "SELECT u.id, u.firstname, u.lastname, u.username, u.email 
         FROM {user} u WHERE u.id ='$evaluatorid'";
   $resAvaliador = $DB->get_records_sql($sqlAvaliador);

   //var do avaliador
   $avaFirstname = $resAvaliador[$evaluatorid]->firstname;
   $avaLastname = $resAvaliador[$evaluatorid]->lastname;
   $avaUsername = $resAvaliador[$evaluatorid]->username;
   $avaEmail = $resAvaliador[$evaluatorid]->email;

   //***** Pegar o setor rapidinho *****/
   $sqlSector = "SELECT sm.userid, sm.trialid, sm.sectorid FROM {local_pdi_sector_member} sm
                  WHERE sm.userid = '$evaluatorid' AND sm.trialid ='$trialid'";
   $resSector = $DB->get_records_sql($sqlSector);

   $sectorid = $resSector[$evaluatorid]->sectorid;

   //html
   //var
   $blocoHTML = "";
   $statusPDI = "";

   //***** COMPARAR progresso do pdi *****/

      //aluno
      $sqlSttAluno = "SELECT ast.userid, ast.idtrial, ast.sectorid, ast.isfinished, ast.timecreated, ast.timemodified, ast.id
                        FROM {local_pdi_answer_status} ast
                        WHERE ast.userid = '$alunoid' and ast.idtrial = '$trialid' AND ast.sectorid = '$sectorid'";
      $resSttAluno = $DB->get_records_sql($sqlSttAluno);
   
      if(count($resSttAluno)>0){

         $isfinished = $resSttAluno[$alunoid]->isfinished;
         if($isfinished == '1'){
               $statusPDI = "respondido apenas";
         }

         //avaliador
         $sqlSttAv = "SELECT anst.evaluatedid, anst.secmemberid, anst.isfinished, sm.sectorid, sm.userid, sm.trialid 
                     FROM {local_pdi_evanswer_status} anst
                     LEFT JOIN {local_pdi_sector_member} sm
                     ON sm.id = anst.secmemberid
                     WHERE anst.evaluatedid = '$alunoid' AND sm.userid = '$evaluatorid' and sm.trialid = '$trialid' and sm.sectorid = '$sectorid'";
         $resSttAv = $DB->get_records_sql($sqlSttAv);

         if(count($resSttAv)>0){
            $isfinished_av = $resSttAv[$alunoid]->isfinished;
            if($isfinished_av == '1'){
               $statusPDI = "respondido e avaliado";
            }
         }

    }else{
       $statusPDI = "não respondido";
    }

    //string bloco
    //part 1 bloco
    $blocoHTML .= "
    <div class=\"d-flex bd-highlight my-padding-sm\" style=\"background-color: var(--mysecondary)\">

      <div class='flex-grow-1'>
         <img src=\"http://localhost/moodle/user/pix.php/$alunoid/f1.jpg\" class=\"my-circle\">
         <h5 class='my-label-bg margin-top my-white'>$alunoFirstname $alunoLastname</h5>
      </div>
      <div class='my-white'>
         <span>Avaliador: $avaFirstname $avaLastname</span> <br>
         <span>Status PDI: $statusPDI</span>
      </div>

   </div>
   
   <br>
   ";

   //parte2

   //marcar e objetivos
   $blocoHTML .= "
   <div id='my-tab2-inner3'>
      <!--Archor points-->
      <div id='div-reuniao'>
         <div class=\"my-bg-secondary my-padding-xsm\"><span class='my-font-family my-qtitle my-white'>
            <i class=\"fas fa-video mx-3\"></i>Marcar reunião</span>
         </div>
         <div class=\"card-body shadow-sm p-3 mb-5 bg-body rounded\">
               <div class=\"\">
               
               conteúdo aqui

               </div>
         </div>
      </div>

      <div id='div-objetivos'>
         <div class=\"my-bg-secondary my-padding-xsm\"><span class='my-font-family my-qtitle my-white'>
            <i class=\"fas fa-rocket mx-3\"></i>Objetivos</span>
         </div>
         <div class=\"card-body shadow-sm p-3 mb-5 bg-body rounded\">
            <div class=\"\">
               
               <span class='my-font-family text-muted'>os objetivos criados aparecem aqui:</span>

            </div>
            <div id=\"horizontal-scroll\" class='my-scroll-h row my-bg-light'>
               <div id=\"div-cards\" class=\"\"></div>
            </div>
         </div>
      </div>
   </div>";

   
   return $blocoHTML;

 }


 function fetchBlocosObjetivoForAluno($avaliadorid, $trialid, $sectorid){
   global $USER, $DB;

   $alunoid = $USER->id;

  //retorna um bloco html
  $htmlBlock = "";

  //pegar o id da tabelas answer_status do aluno
  $sqlVer = "SELECT s.userid, s.id FROM {local_pdi_answer_status} s
                 WHERE s.userid = '$alunoid' AND s.idtrial = '$trialid' AND s.sectorid ='$sectorid'";
  $resVer = $DB->get_records_sql($sqlVer);

  $anstatusid = $resVer[$alunoid]->id;

  if($anstatusid == null){
     return "<div class=\"card-body my-bg-light\">
                 <h5 class=\"card-title\">Vazio...</h5>
                 <p class=\"card-text my-font-family\">É preciso responder primeiro.</p>
              </div>";
  }
  else{
     $sql = "SELECT * FROM {local_pdi_goals} a
              WHERE a.idanstatus = '$anstatusid' and a.createdbyid = '$avaliadorid'
              ORDER BY a.timecreated DESC";
  
     $res = $DB->get_records_sql($sql);

     if(count($res) > 0){

         foreach($res as $r){

            $htmlAcordeon = alunoGoalReplyEdit($r->id);

            $rTitle = strip_tags($r->title);
            $rDesc = strip_tags($r->description);

            $htmlBlock .= "<div class=\"align-top bg-white mb-2 mr-2 my-padding-sm rounded\" style=\"width: 18rem; display: inline-block\"> 
                              <h5 id=\"h-goal-$r->id\" class=\"card-title my-bold lbl-obj-onoff\">$rTitle</h5> 

                              <p id=\"p-goal-$r->id\" class=\"card-text lbl-obj-onoff\" style=\"white-space: pre-wrap;\">$rDesc</p>

                              <hr>
                              <div class='my-mention2'>
                                 <small class='my-label-btn btn-add-resp' data-goalid='$r->id'>adicionar resposta</small>
                              </div>
                              $htmlAcordeon
                              

                           </div>";
         }
      }else{
         $htmlBlock .= "<div class=\"align-top bg-white mb-2 mr-2 my-padding-sm rounded\" style=\"width: 18rem; display: inline-block\"> 
               <h5 id=\"h-aviso\" class=\"card-title my-bold text-muted\">Vazio...</h5> 
               <p id=\"p-aviso\" class=\"card-text text-muted\" style=\"white-space: pre-wrap;\">Os objetivos definidos aparecerão aqui.</p>
               </div>";
      }

     return $htmlBlock;

  }
  



}

//só alunos chamarão essa função
function insertGoalFeedback($goalid){
   global $DB, $USER;

   $createdby = $USER->id; //sempre o aluno
   $tempo = time();
   $title = "[oculto] Pronto para editar";
   $description = "";



   $addFeed = new stdClass();
   $addFeed->createdbyid = $createdby;
   $addFeed->goalid = $goalid;
   $addFeed->title = $title;
   $addFeed->description = $description;
   $addFeed->timecreated = $tempo;
   $addFeed->timemodified = $tempo;

   //status recebe o id da inserção
   $status = $DB->insert_record('local_pdi_goals_feedback', $addFeed);

   return $status;

}

//só Alunos chamarão essa função
function updateFeedback($idfeedback, $title, $desc){
   global $DB;
   
   $updateFb = new stdClass();
   $updateFb->id = $idfeedback;
   $updateFb->title = $title;
   $updateFb->description = $desc;
   $updateFb->timemodified = time();

   $res = $DB->update_record('local_pdi_goals_feedback', $updateFb);

   return $res;

}


//função dos avaliadores
function criarCourseCatpdi($coursecatid, $coursename, $trialid){
   global $DB, $USER, $CFG;

   require_once('../../../config.php');
   require_once($CFG->dirroot.'/course/lib.php');

   //setup vars
   $uid = $USER->id;
   $evaluatorid = null; //ok

   $coursename = trim($coursename);

   $strShortname = preg_replace ('/[^\p{L}\p{N}]/u', '_', $coursename);
   $strShortname = trim($strShortname);
   $strShortname = "pdi_" . $strShortname . "";

   $timecreated = time();
   $timemodified = time();

   $trial_evaluator_id = "";//receberá o id do local_pdi_trial_evaluator
   //para isso, necessário trialid e evaluatorid

   $sqlEvaluator = "SELECT trev.id trevid, trev.cohortid, ev.id evid, ev.mdlid FROM {local_pdi_trial_evaluator} trev
                     LEFT JOIN {local_pdi_evaluator} ev
                     ON ev.id = trev.evaluatorid
                     WHERE trev.trialid = '$trialid' AND ev.mdlid = '$uid'";
   $resEvaluator = $DB->get_records_sql($sqlEvaluator);

   $resEvaluator = array_values($resEvaluator);
   $evaluatorid = $resEvaluator[0]->evid;
   $trialevaluatorid = $resEvaluator[0]->trevid; 


   //verificar no db onde o course custom field está

   //criar caso não exista
   $customFID = null;
   
   $sqlCourseCfield = "SELECT * FROM {customfield_field} cf 
   WHERE cf.shortname = 'pdi_trial_evaluator' AND cf.sortorder IS NULL";
   $resCourseCfield = $DB->get_records_sql($sqlCourseCfield);

   if(count($resCourseCfield) < 1){
      $addCourseCfield = new stdClass();
      $addCourseCfield->shortname = "pdi_trial_evaluator";
      $addCourseCfield->name = "PDI campo controle 1";
      $addCourseCfield->type = "text";
      $addCourseCfield->timecreated = time();
      $addCourseCfield->timemodified = time();

      $addRes = $DB->insert_record('customfield_field', $addCourseCfield); //id do campo custom
      $customFID = $addRes;
   }
   else{
      $resCourseCfield = array_values($resCourseCfield);
      $customFID = $resCourseCfield[0]->id;
   }
   //passou aqui, verificou se criou o custom field para cursos


   //AGORA, criar o curso com as definições necessárias e passar o pdi_trial_evaluator no custom field
      $shortnameinicial = $strShortname;
      $shortnamefinal = $strShortname;
      $attempts = 0;
      do {

      try
      {
         $erroCatch = false;

         $criarCurso = new stdClass();
         $criarCurso->category = $coursecatid;
         $criarCurso->fullname = $coursename;
         $criarCurso->shortname = $shortnamefinal;
      
         $resCriarC = create_course($criarCurso);

      } catch (Exception $e) {
            $attempts++;
            $erroCatch = true;
            $shortnamefinal = $shortnameinicial . "_$attempts";
            usleep(100000); // 0,1 segundos
            continue;
      }

      break;

      } while($erroCatch == true);


   //atribuir custom field
   $addCustomData = new stdClass();
   $addCustomData->fieldid = $customFID;
   $addCustomData->instanceid = $resCriarC->id;
   $addCustomData->charvalue = "pdi_trial_evaluator_$trialevaluatorid"; //id do pdi_trial_evaluator
   $addCustomData->value = $trialevaluatorid; //id do pdi_trial_evaluator
   $addCustomData->valueformat = 0;
   $addCustomData->timecreated = time();
   $addCustomData->timemodified = time();

   //var_dump($addCustomData);

   $resCustomField = $DB->insert_record('customfield_data', $addCustomData);

   //adicionar membros do cohort ao curso
   //selecionar os dados
   if($resCustomField > 0 ){
      $sqlMembers = "SELECT cm.id cmid, cm.cohortid, cm.userid, trev.id trevid, trev.trialid 
         FROM {local_pdi_trial_evaluator} trev
         LEFT JOIN {cohort_members} cm
         ON cm.cohortid = trev.cohortid
         WHERE trev.id = '$trialevaluatorid'";
      $resMembers = $DB->get_records_sql($sqlMembers);

      foreach($resMembers as $r){
         $studentid = $r->userid;
         $roleid = 5; //student

         check_enrol($shortnamefinal, $studentid, $roleid);
      }

      //adicionar o avaliador
      check_enrol($shortnamefinal, $USER->id, 3); //3 = editing teacher
      

   }

   return "ok";
}

//function from moodle forum to enrol a user
function check_enrol($shortname, $userid, $roleid, $enrolmethod = 'manual') {
   global $DB;
   require_once('../../../config.php');

   $user = $DB->get_record('user', array('id' => $userid, 'deleted' => 0), '*', MUST_EXIST);
   $course = $DB->get_record('course', array('shortname' => $shortname), '*', MUST_EXIST);
   $context = context_course::instance($course->id);
   if (!is_enrolled($context, $user)) {
       $enrol = enrol_get_plugin($enrolmethod);
       if ($enrol === null) {
           return false;
       }
       $instances = enrol_get_instances($course->id, true);
       $manualinstance = null;
       foreach ($instances as $instance) {
           if ($instance->name == $enrolmethod) {
               $manualinstance = $instance;
               break;
           }
       }
       if ($manualinstance !== null) {
           $instanceid = $enrol->add_default_instance($course);
           if ($instanceid === null) {
               $instanceid = $enrol->add_instance($course);
           }
           $instance = $DB->get_record('enrol', array('id' => $instanceid));
       }
       $enrol->enrol_user($instance, $userid, $roleid);
   }
   return true;
}

function ocultarMostrarCurso($courseid){
   global $DB;

   $res = $DB->get_records('course', array('id'=>"$courseid"));
   $res = array_values($res);
   $res = $res[0];

   $visible = $res->visible;

   if($visible == 1){ $visible = 0; }
   else{ $visible = 1; }

   $updateC = new stdClass();
   $updateC->id = $courseid;
   $updateC->visible = $visible;

   $resUp = $DB->update_record('course', $updateC);

   if($resUp){
      return 'ok';
   }else{
      return 'erro';
   }

   

}