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
    
    global $USER, $DB;

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
         <h5 class='my-label-bg margin-top'>$alunoFirstname $alunoLastname</h5>
      </div>
      <div class=''>
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
            $strCategoria = " "; //categoria vazia
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

      if(count($resQav)<1){
         $blocoHTML .= "
         <div class=\"card\">
         <div class=\"card-body my-bg-light\">
           <h5 class=\"card-title\">Vazio...</h5>
           <p class=\"card-text my-font-family\">Ainda não há o respostas salvas para comparar.</p>
         </div>
       </div>";
      }

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
    
    echo $blocoHTML;
    
 }