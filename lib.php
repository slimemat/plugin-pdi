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

 //show plugin item on the menu
 function local_pdi_extend_navigation(global_navigation $navigation){

    $main_node = $navigation->add(get_string('plugintitle', 'local_pdi'), '/local/pdi/');
    $main_node->nodetype = 1;
    $main_node->collapse = false;
    $main_node->forceopen = true;
    $main_node->isexpandable = false;
    $main_node->showinflatnavigation = true;

 }

 function local_pdi_before_standard_html_head(){
   if (isloggedin() && !isguestuser()) {
      global $PAGE;
      $var = $PAGE->bodyid;
      
      if($var == "page-my-index"){

         $PAGE->requires->js('/local/pdi/scripts/pdipopup.js');

         return "
         \n<link rel=\"stylesheet\" href=\"../local/pdi/styles/pdipopup.css\"> 
         \n<link rel=\"stylesheet\" href=\"https://use.fontawesome.com/releases/v5.11.2/css/all.css\">";
      }
   }
 }

 //chunk of html at the start of documents
 function local_pdi_before_footer(){
   if (isloggedin() && !isguestuser()) {
      global $PAGE, $USER, $DB;
      $var = $PAGE->bodyid;
      
      if($var == "page-my-index"){


         notificarAvaliador();
         //esse abaixo retorna o setor que o avaliador do corte participa, busca por id de membro
         //no coorte

         //então, usar o setor para fazer outra pesquisa (sectorid)
         $sql = "SELECT mdb.timecreated, mdb.dbid, mdb.smemberid, sm.sectorid, sm.userid as userid_sector_member, sm.trialid,trev.cohortid, cm.userid as userid_cohort_member, t.title as trialtitle, td.isstarted as trialisstarted, td.startdate, td.enddate, us.firstname, us.lastname
         FROM mdl_local_pdi_sect_mem_db mdb
         LEFT JOIN mdl_local_pdi_sector_member sm
         ON sm.id = mdb.smemberid
         LEFT JOIN mdl_local_pdi_trial_evaluator trev
         ON trev.trialid = sm.trialid
         LEFT JOIN mdl_cohort_members cm
         ON cm.cohortid = trev.cohortid
         LEFT JOIN mdl_local_pdi_trial t
         ON t.id = sm.trialid
         LEFT JOIN mdl_local_pdi_trial_detail td
         ON td.trialid = t.id
         LEFT JOIN mdl_user us
         ON us.id = sm.userid
         WHERE cm.userid = \"$USER->id\"
         ";

         $res = $DB->get_records_sql($sql);
         //var_dump($res);
         
         //setup var
         $blocoHtml = '';
         $count_i = 0;
         $count_trial = 0;

         $xtrialid = '';
         $xtrialtitle = '';
         $xdbid = '';
         $xsectorid ='';
         $xuserid_sector_member ='';
         $xfullevaluatorname = '';

         $lastTrialid = 'null';
         $lastFullevaluatorname = 'null';

         foreach($res as $r){

            $xtrialid = "$r->trialid";
            $xtrialtitle = "$r->trialtitle";
            $xdbid = "$r->dbid";
            $xsectorid ="$r->sectorid";
            $xuserid_sector_member ="$r->userid_sector_member";
            $xfullevaluatorname = "$r->firstname" . " ". "$r->lastname";

            
            //verificar se esse está marcado como respondido
            //não importa o setor, então está agrupado
            $respondido = 0;

            $respSQL = "SELECT * FROM mdl_local_pdi_answer_status pas
                        WHERE pas.userid = '$USER->id'
                        and pas.idtrial = '$xtrialid'
                        GROUP BY pas.userid";
            $respRes = $DB->get_records_sql($respSQL);

            if(count($respRes)>0){
               foreach($respRes as $rs){ $respondido = $rs->isfinished; }
            }
            

            if($respondido == 0){
            
               if($xtrialid != $lastTrialid){
                  $blocoHtml .= "</span>";

                  $lastFullevaluatorname = 'null';
               }
               
               if($xtrialid != $lastTrialid){

                  $blocoHtml .= "<span class='my-round-card' data-idtrial='$xtrialid' id='trial_$xtrialid'>";
                  $blocoHtml .= "<h5 class='my-font-family' title='nome do processo'>$xtrialtitle</h5>";
                  $blocoHtml .= "<span class='my-label'>Avaliadores:</span><br>";
         
                  $count_trial++;
               }

               if($xfullevaluatorname != $lastFullevaluatorname){
                  $blocoHtml .= "<span class='my-mention'>$xfullevaluatorname</span>";
               }

               $blocoHtml .= "
               <span class='my-hidden' id='data-block-$xtrialid'>
                  <span class='processo-id-$xtrialid'>$xtrialid</span> 
                  <span class='responder-db-id-$xtrialid'>$xdbid</span> 
                  <span class='setor-id-$xtrialid'>$xsectorid</span>
                  <span class='avaliador-id-$xtrialid'>$xuserid_sector_member</span>
               </span>";

               //guardar os valores anteriores
               $lastTrialid = $xtrialid;
               $lastFullevaluatorname = $xfullevaluatorname;
               
               $count_i++;
            }
         }

         if($respondido == 0){

            $blocoHtml .= "</span>";
         }
         

         return "
         <!--opened popup-->
         <div id='div-popup' class='r-popup-frame'>
            <div class='r-popup'>
            
               <div id='myblue-bg'>
                  <span class='mylogo'>PDI</span>
                  <div class='mypush my-marginr' style='color: var(--white);'><span>tela de processos</span></div>
                  <span id='btn-minimize-pop' class='pdi-nostyle'><i class=\"fas fa-minus\"></i></span>
               </div>
               
               <div class='my-scroll' id='scroll-div'>
                  $blocoHtml
               </div>

               <div class='my-scroll my-hidden' id='scroll-div-2'>
               </div>

               <form id='frm-trial-id' name='frm-trial-id' class='my-hidden' method='POST' action=''>
                  <input type=\"hidden\" name=\"hidden-trialid\" id=\"hidden-trialid\" value=\"\">
                  <input type=\"hidden\" name=\"hidden-currentuserid\" id=\"hidden-currentuserid\" value=\"$USER->id\">
               </form>

               <div class='grey-bottom'>
                  <input type='button' id='btn_salvar' class='my-grey-btn my-marginr my-marginlauto'
                     value='Salvar' disabled>
                  <input type='button' id='btn_finalizar' class='my-primary-btn my-marginr'
                     value='Finalizar' disabled>
               </div>
            
            </div>
         </div>

         <!--closed popup-->

         <div id='div-closed-popup' class='my-closed-popup'>
            <div id='msg-popover' class='my-popover-msg'>Clique para responder ($count_trial)</div>
            <div id='popover-arrow' class=\"arrow-right\"></div>

            <div id='btn-popover' class='my-circle-div'>
               <span class=\"my-circle\">PDI</span>
            </div>
         </div>
         ";   
      }
      
   }
   else{
      return false;
   }
   
 }

function notificarAvaliador(){
   //verificar se é avaliador de algum processo
   /** Toda pessoa que avalia tem dados na tabela avaliador */
   global $USER, $DB;

   $sql = "SELECT * FROM {local_pdi_evaluator} ev
            WHERE ev.mdlid = '$USER->id'";
   $res = $DB->get_records_sql($sql);

   //pessoa está como avaliador
   if(count($res)>0){

      $sql= "SELECT ans.id anstatus, sm.userid as evaluatorid, sm.sectorid, sm.trialid ,ans.userid answeredby, ans.isfinished 
      FROM {local_pdi_sector_member} sm
      INNER JOIN {local_pdi_answer_status} ans
      ON ans.idtrial = sm.trialid and ans.sectorid = sm.sectorid
      WHERE sm.userid = '$USER->id' and ans.isfinished = '1'";
      $res = $DB->get_records_sql($sql);

      $count_answered_forms = count($res);
      $strMessage = "Você tem ". $count_answered_forms . " questionário(s) <a href='../local/pdi/' class='my-logo-font'>PDI</a> para avaliar";

      if($count_answered_forms > 0){
         \core\notification::info($strMessage);
      }

   }

}


/*
 function randomPassword() {
   $alphabet = 'abcdefghijklmnopqrstuvwxyz1234567890';
   $randompass = array(); 
   $alphaLength = strlen($alphabet) - 1; 
   for ($i = 0; $i < 8; $i++) {
       $n = rand(0, $alphaLength);
       $randompass[] = $alphabet[$n];
   }
   return implode($randompass); //turn the array into a string
}
*/

function verifyAdm($usernameAdm){
   global $DB;

   $userLogado = $usernameAdm;

   $sql2 = "SELECT `username`, `userrole` FROM `mdl_local_pdi_user` WHERE username = '$userLogado'";
   $res2 = $DB->get_records_sql($sql2);

   $userSql = $res2["$userLogado"];

   //verifica se o email cadastrado é o mesmo do usuário logado
   if($userSql->username == $userLogado and $userSql->userrole == "0"){
      $_SESSION['authadm'] = "yes";
      return "yes";
   }
   else{
      return "no";
   }
}

