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


    global $DB;

    //pegar o valor da trial atual
  //$timeCreated = $_SESSION['mytime'];
  $trialid = $_SESSION['edittrialid'];
  $rSQL = "SELECT * FROM {local_pdi_trial} WHERE createdby = '$USER->id' and id = $trialid";
  $resultado = $DB->get_records_sql($rSQL);
  $trialID;

  foreach($resultado as $t){$trialID = $t->id;}


    //fazer a inserção de valores 0 nos setores de quem não foi atribuido ainda
    $xxxSql = "SELECT ev.mdlid, tv.trialid, tv.evaluatorid
    FROM {local_pdi_trial_evaluator} tv
    LEFT JOIN {local_pdi_evaluator} ev
    ON tv.evaluatorid = ev.id
    WHERE tv.trialid = '$trialID'";

    $xxxResult = $DB->get_records_sql($xxxSql);

    foreach($xxxResult as $xtv){
        $yyySql = "SELECT * FROM {local_pdi_sector_member} sm
        WHERE sm.trialid = '$xtv->trialid'
        AND sm.userid = '$xtv->mdlid'";

        $yyyResult = $DB->get_records_sql($yyySql);

        //se não existir setor, adicionar zerado
        if(count($yyyResult) < 1){
            $yAddSector = new stdClass();
            $yAddSector->timecreated = time();
            $yAddSector->sectorid = 0;
            $yAddSector->userid = $xtv->mdlid;
            $yAddSector->trialid = $trialID;

            $DB->insert_record('local_pdi_sector_member', $yAddSector);
        }
    }



    //começa
    $sql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, lpsm.sectorid, lps.sectorname, lpsm.trialid, lptv.trialid as trevid
    FROM {user} u
    RIGHT JOIN {local_pdi_evaluator} lpv
    ON u.id = lpv.mdlid
    LEFT JOIN {local_pdi_sector_member} lpsm
    ON lpsm.userid = u.id
    LEFT JOIN {local_pdi_sector} lps
    ON lps.id = lpsm.sectorid
    LEFT JOIN {local_pdi_trial_evaluator} lptv
    ON lptv.trialid = lpsm.trialid
    WHERE lptv.trialid = '$trialID'
    GROUP BY u.id
    
    ";

    $outputuser_sel_avUsers = $DB->get_records_sql($sql);

    if(count($outputuser_sel_avUsers) < 1){
        $sql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, lpsm.sectorid, lps.sectorname, lpsm.trialid, lptv.trialid as trevid
        FROM {user} u 
        RIGHT JOIN {local_pdi_evaluator} lpv
        ON u.id = lpv.mdlid
        LEFT JOIN {local_pdi_trial_evaluator} lptv
        ON lptv.evaluatorid = lpv.id
        LEFT JOIN {local_pdi_sector_member} lpsm
        ON lpsm.trialid = lptv.trialid
        LEFT JOIN {local_pdi_sector} lps
        ON lps.id = lpsm.sectorid
        WHERE lptv.trialid = '$trialID'
        OR lpsm.trialid = '$trialID'
        GROUP BY u.id";

        $outputuser_sel_avUsers = $DB->get_records_sql($sql);
    }

    $trial_sector_list;

    //verifica sectors
    $html_sector_ous = "<select name=\"select-sector\" class=\"select-sector\">
    <option value=\"0\" disabled selected>-</option>";

    $sql_ous = "SELECT * FROM {local_pdi_sector}";
    $res_ous = $DB->get_records_sql($sql_ous);

    foreach($outputuser_sel_avUsers as $user){
        $userfullname = "$user->firstname" . " " . "$user->lastname";
    
        //fazer um select já com os dados necessários de um foreach de uma vez só
        $html_sector_ous = "<select name=\"select-sector\" class=\"select-sector\">
        <option value=\"0\" disabled selected>-</option>";
    
        //var_dump($user);
        if(!is_null($user->sectorname)){
            $html_selected_ous = "
            <option title='saved' class='my-selected-opt' selected value=\"$user->sectorid\">$user->sectorname</option>
            ";
        }
        if($user->sectorid == 0){
            $html_selected_ous = "
            <option title='saved' class='my-selected-opt' selected value=\"0\">-</option>
            ";
        }
        foreach($res_ous as $r){
            $html_sector_ous .= "
            <option value=\"$r->id\">$r->sectorname</option>
            ";
        }
        
        $html_sector_ous .= $html_selected_ous;
        $html_sector_ous .= "</select>";
    
        $trial_sector_list[] = array("$user->id", "$user->username", $userfullname, "$user->email", "$html_sector_ous");
    }
    


