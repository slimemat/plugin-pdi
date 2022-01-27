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


function mostrarBlocosTrial($offset, $rows){
    global $USER, $DB;

    //então, usar o setor para fazer outra pesquisa (sectorid)
    $sql = "SELECT sm.id, mdb.timecreated, mdb.dbid, mdb.smemberid, sm.sectorid, sm.userid as userid_sector_member, sm.trialid,trev.cohortid, cm.userid as userid_cohort_member, t.title as trialtitle, t.timecreated ,td.isstarted as trialisstarted, td.startdate, td.enddate, us.firstname, us.lastname
    FROM {local_pdi_sect_mem_db} mdb
    LEFT JOIN {local_pdi_sector_member} sm
    ON sm.id = mdb.smemberid
    LEFT JOIN {local_pdi_trial_evaluator} trev
    ON trev.trialid = sm.trialid
    LEFT JOIN {cohort_members} cm
    ON cm.cohortid = trev.cohortid
    LEFT JOIN {local_pdi_trial} t
    ON t.id = sm.trialid
    LEFT JOIN {local_pdi_trial_detail} td
    ON td.trialid = t.id
    LEFT JOIN {user} us
    ON us.id = sm.userid
    WHERE cm.userid = \"$USER->id\"
    ORDER BY td.startdate DESC, t.timecreated DESC
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

    $respondido = 0;

    foreach($res as $r){

       $xtrialid = "$r->trialid";
       $xtrialtitle = "$r->trialtitle";
       $xdbid = "$r->dbid";
       $xsectorid ="$r->sectorid";
       $xuserid_sector_member ="$r->userid_sector_member";
       $xfullevaluatorname = "$r->firstname" . " ". "$r->lastname";
       
       $startdate = $r->startdate;
       $enddate = $r->enddate;
       $fstartdate = date('d/m/Y', $startdate);
       $fenddate = date('d/m/Y', $enddate);

       //verificar a data
       $startdate_dt = date("m/d/Y", $startdate); //só funciona nesse formato americano
       $enddate_dt = date("m/d/Y", $enddate);
       $startdate_dt = new DateTime($startdate_dt);
       $enddate_dt = new DateTime($enddate_dt);

       $unixnow = time();
       $today_dt = new DateTime(date("m/d/Y", $unixnow));

       //não cria html para os processos que não começaram
       if($today_dt < $startdate_dt){
          continue; //ignora o código a seguir e faz o proxímo do loop foreach
       }

       //não cria html para os que já passaram a data
       if($today_dt > $enddate_dt){
          continue;
       }
       

       
       //verificar se esse está marcado como respondido
       //não importa o setor, então está agrupado
       $respondido = 0;

       $respSQL = "SELECT * FROM {local_pdi_answer_status} pas
                   WHERE pas.userid = '$USER->id'
                   and pas.idtrial = '$xtrialid'
                   GROUP BY pas.userid";
       $respRes = $DB->get_records_sql($respSQL);

       if(count($respRes)>0){
          foreach($respRes as $rs){ $respondido = $rs->isfinished; }
       }
       

       if($respondido == 0){
       
          if($xtrialid != $lastTrialid){
             $blocoHtml .= "</span></span>";

             $lastFullevaluatorname = 'null';
          }
          
          if($xtrialid != $lastTrialid){

             $blocoHtml .= "  
             
             <span class='my-round-card' id='trial_$xtrialid' data-idtrial='$xtrialid'>
             <span class=\"my-circle\" style=\"background-color: var(--myerror); color: var(--myblack);\">✖</span>
             
             ";
   
             $blocoHtml .= "<span class='my-sidetext'>
             <small class='my-font-family'>$fstartdate - $fenddate</small>
             <h5 class='my-font-family' title='nome do processo'>$xtrialtitle</h5>";
             $blocoHtml .= "<span class='my-label'>Avaliadores:</span><br>";
    
             $count_trial++;
          }

          if($xfullevaluatorname != $lastFullevaluatorname){
             $blocoHtml .= "<span class='my-mention'>$xfullevaluatorname</span>";
          }
          else{
             $blocoHtml .= "</span>";
          }

          $blocoHtml .= "         
          <span class='my-hidden' id='data-block-$xtrialid'>
             <span class='processo-id-$xtrialid'>$xtrialid</span> 
             <span class='responder-db-id-$xtrialid'>$xdbid</span> 
             <span class='setor-id-$xtrialid'>$xsectorid</span>
             <span class='avaliador-id-$xtrialid'>$xuserid_sector_member</span>
          </span>
          ";

          //guardar os valores anteriores
          $lastTrialid = $xtrialid;
          $lastFullevaluatorname = $xfullevaluatorname;
          
          $count_i++;
       }

    }

    if($respondido == 0){

       $blocoHtml .= "</span></span>";
    }
    
    $blocoReturn = "
    <!--opened popup-->
             $blocoHtml                         
      <form id='frm-trial-id' name='frm-trial-id' class='my-hidden' method='POST' action=''>
         <input type=\"hidden\" name=\"hidden-trialid\" id=\"hidden-trialid\" value=\"\">
         <input type=\"hidden\" name=\"hidden-currentuserid\" id=\"hidden-currentuserid\" value=\"$USER->id\">
      </form>
    ";

    return $blocoReturn;


}


function mostrarTodosTrials($offset, $rows){
   global $USER, $DB;

   $blocoHtml = '';
   $blocoReturn = '';

   $sql = "SELECT t.id, t.title as trialtitle, t.timecreated ,td.isstarted as trialisstarted, td.startdate, td.enddate
   FROM {local_pdi_sect_mem_db} mdb
   LEFT JOIN {local_pdi_sector_member} sm
   ON sm.id = mdb.smemberid
   LEFT JOIN {local_pdi_trial_evaluator} trev
   ON trev.trialid = sm.trialid
   LEFT JOIN {cohort_members} cm
   ON cm.cohortid = trev.cohortid
   LEFT JOIN {local_pdi_trial} t
   ON t.id = sm.trialid
   LEFT JOIN {local_pdi_trial_detail} td
   ON td.trialid = t.id
   LEFT JOIN {user} us
   ON us.id = sm.userid
   WHERE cm.userid = \"$USER->id\"
   GROUP BY t.id
   ORDER BY td.startdate DESC, t.timecreated DESC 
   LIMIT $offset, $rows";

   $res = $DB->get_records_sql($sql);
   //var_dump($res);


   //foreach trial
   foreach($res as $r){
      $tid = $r->id;
      $ttitle = $r->trialtitle;
      $tisstarted = $r->trialisstarted;
      $tstartdate = $r->startdate;
      $tenddate = $r->enddate;

      $dateInicioF = gmdate("d/m/y", $tstartdate);
      $dateFimF = gmdate("d/m/y", $tenddate);

      $avaliadores = '';

      //verificar se esse está marcado como respondido
       $respondido = 0;
       $respSQL = "SELECT * FROM {local_pdi_answer_status} pas
                   WHERE pas.userid = '$USER->id'
                   and pas.idtrial = '$tid'
                   GROUP BY pas.userid";
       $respRes = $DB->get_records_sql($respSQL);
       if(count($respRes)>0){
          foreach($respRes as $rs){ $respondido = $rs->isfinished; }
       }

      //consultar os avaliadores
      $avaSQL = "SELECT tev.id, us.firstname, us.lastname FROM {local_pdi_trial_evaluator} tev
      LEFT JOIN {local_pdi_evaluator} ev
      ON ev.id = tev.evaluatorid
      LEFT JOIN {user} us
      ON us.id = ev.mdlid
      WHERE tev.trialid = '$tid'";
      $avaRES = $DB->get_records_sql($avaSQL);
      foreach($avaRES as $rx){
         $firstname = $rx->firstname;
         $lastname = $rx->lastname;

         $avaliadores .= "<span class='my-mention'>$firstname $lastname</span>";
      }


      //
      //datas
      $startdate = $r->startdate;
      $enddate = $r->enddate;
      $fstartdate = date('d/m/Y', $startdate);
      $fenddate = date('d/m/Y', $enddate);

      //verificar a data
      $startdate_dt = date("m/d/Y", $startdate); //só funciona nesse formato americano
      $enddate_dt = date("m/d/Y", $enddate);
      $startdate_dt = new DateTime($startdate_dt);
      $enddate_dt = new DateTime($enddate_dt);

      $unixnow = time();
      $today_dt = new DateTime(date("m/d/Y", $unixnow));

      //não cria html para os processos que não começaram
      if($today_dt < $startdate_dt){
         continue;
      }
      //



      if($respondido == 0){

         $blocoHtml .= "  
             
             <span class='my-round-card' id='trial_$tid' data-idtrial='$tid'>
             <span class=\"my-circle\" style=\"background-color: var(--myerror); color: var(--myblack);\">✖</span>
             
             ";
   
             $blocoHtml .= "<span class='my-sidetext'>
             <h5 class='my-font-family' title='nome do processo'>$ttitle</h5>";
             $blocoHtml .= "<span class='my-label'>Avaliadores:</span><br>";

             $blocoHtml .= "$avaliadores
               <br><small class='my-font-family'>$dateInicioF - $dateFimF</small>
              </span></span>";
      }
      else{
         
         $blocoHtml .= "  
             
             <span class='my-round-card' id='trial_$tid' data-idtrial='$tid'>
             <span class=\"my-circle\" style=\"background-color: var(--mysuccess); color: var(--myblack);\">✔</span>
             
             ";
   
             $blocoHtml .= "<span class='my-sidetext'>
             <h5 class='my-font-family' title='nome do processo'>$ttitle</h5>";
             $blocoHtml .= "<span class='my-label'>Avaliadores:</span><br>";

             $blocoHtml .= "$avaliadores 
               <br><small class='my-font-family'>$dateInicioF - $dateFimF</small>
             </span></span>";
      }

   }

   $blocoReturn = "$blocoHtml";

    return $blocoReturn;

}


function searchStudentTrials($pesquisa){
   global $USER, $DB;

   $blocoHtml = '';
   $blocoReturn = '';

   $sql = "SELECT t.id, t.title as trialtitle, td.isstarted as trialisstarted, td.startdate, td.enddate
   FROM {local_pdi_sect_mem_db} mdb
   LEFT JOIN {local_pdi_sector_member} sm
   ON sm.id = mdb.smemberid
   LEFT JOIN {local_pdi_trial_evaluator} trev
   ON trev.trialid = sm.trialid
   LEFT JOIN {cohort_members} cm
   ON cm.cohortid = trev.cohortid
   LEFT JOIN {local_pdi_trial} t
   ON t.id = sm.trialid
   LEFT JOIN {local_pdi_trial_detail} td
   ON td.trialid = t.id
   LEFT JOIN {user} us
   ON us.id = sm.userid
   WHERE cm.userid = \"$USER->id\" AND t.title LIKE '%$pesquisa%'
   GROUP BY t.id
   LIMIT 0, 6";

   $res = $DB->get_records_sql($sql);
   //var_dump($res);


   //foreach trial
   foreach($res as $r){
      $tid = $r->id;
      $ttitle = $r->trialtitle;
      $tisstarted = $r->trialisstarted;
      $tstartdate = $r->startdate;
      $tenddate = $r->enddate;

      $dateInicioF = gmdate("d/m/y", $tstartdate);
      $dateFimF = gmdate("d/m/y", $tenddate);

      $avaliadores = '';

      //verificar se esse está marcado como respondido
       $respondido = 0;
       $respSQL = "SELECT * FROM {local_pdi_answer_status} pas
                   WHERE pas.userid = '$USER->id'
                   and pas.idtrial = '$tid'
                   GROUP BY pas.userid";
       $respRes = $DB->get_records_sql($respSQL);
       if(count($respRes)>0){
          foreach($respRes as $rs){ $respondido = $rs->isfinished; }
       }

      //consultar os avaliadores
      $avaSQL = "SELECT tev.id, us.firstname, us.lastname FROM {local_pdi_trial_evaluator} tev
      LEFT JOIN {local_pdi_evaluator} ev
      ON ev.id = tev.evaluatorid
      LEFT JOIN {user} us
      ON us.id = ev.mdlid
      WHERE tev.trialid = '$tid'";
      $avaRES = $DB->get_records_sql($avaSQL);
      foreach($avaRES as $rx){
         $firstname = $rx->firstname;
         $lastname = $rx->lastname;

         $avaliadores .= "<span class='my-mention'>$firstname $lastname</span>";
      }

      if($respondido == 0){

         $blocoHtml .= "
             
             <span class='my-round-card' id='trial_$tid' data-idtrial='$tid'>
             <span class=\"my-circle\" style=\"background-color: var(--myerror); color: var(--myblack);\">✖</span>
             
             ";
   
             $blocoHtml .= "<span class='my-sidetext'>
             <h5 class='my-font-family' title='nome do processo'>$ttitle</h5>";
             $blocoHtml .= "<span class='my-label'>Avaliadores:</span><br>";

             $blocoHtml .= "$avaliadores 
             <br><small class='my-font-family'>$dateInicioF - $dateFimF</small>
             </span></span>";
      }
      else{
         
         $blocoHtml .= "  
             
             <span class='my-round-card' id='trial_$tid' data-idtrial='$tid'>
             <span class=\"my-circle\" style=\"background-color: var(--mysuccess); color: var(--myblack);\">✔</span>
             
             ";
   
             $blocoHtml .= "<span class='my-sidetext'>
             <h5 class='my-font-family' title='nome do processo'>$ttitle</h5>";
             $blocoHtml .= "<span class='my-label'>Avaliadores:</span><br>";

             $blocoHtml .= "$avaliadores
               <br><small class='my-font-family'>$dateInicioF - $dateFimF</small>
              </span></span>";
      }

   }

   $blocoReturn = "$blocoHtml";

    return $blocoReturn;

}

function getOneStudentTrial($trialid){
   global $DB;

   $sql = "SELECT t.*, td.startdate, td.enddate, td.evtype, td.isstarted 
   FROM {local_pdi_trial} t
   LEFT JOIN {local_pdi_trial_detail} td
   ON td.trialid = t.id
   WHERE t.id = '$trialid'";

   $res = $DB->get_records_sql($sql);

   return $res;

}


function getNameAndDetails($trialid){
   global $DB;

   $sql = "SELECT t.id, t.title, td.startdate, td.enddate, td.evtype 
            FROM {local_pdi_trial} t
            LEFT JOIN {local_pdi_trial_detail} td
            ON td.trialid = t.id
            WHERE t.id = '$trialid'";
   $res = $DB->get_records_sql($sql);
   $res = $res["$trialid"];
   $res = json_encode($res);

   return $res;
}

function deleteTrial($trialid){
   global $DB;

   $sql = "SELECT t.id, t.trialid, t.isstarted
            FROM {local_pdi_trial_detail} t
            WHERE t.trialid = $trialid";
   $res = $DB->get_records_sql($sql);
   $res = array_values($res);
   $res = $res[0];

   //se não foi possível pegar os detalhes, apagar agora
   if($res == null){
      $select0 = "id = $trialid";
      $resdel0 = $DB->delete_records_select('local_pdi_trial', $select0);

      return $resdel0;
   }

   $id = $res->id;
   $isstarted = $res->isstarted;

   if($isstarted){
      //colocar para zero como forma de desativar
      $updateTD = new stdClass();
      $updateTD->id = $id;
      $updateTD->isstarted = 0;

      $resup = $DB->update_record('local_pdi_trial_detail', $updateTD);

      return $resup;

   }else{
      //se o isstarted não for true
      $select = "id = $id";
      $resdel = $DB->delete_records_select('local_pdi_trial_detail', $select);

      $select2 = "id = $trialid";
      $resdel2 = $DB->delete_records_select('local_pdi_trial', $select2);

      return $resdel2;
   }

}

