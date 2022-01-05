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

require_once('../../../config.php');

global $USER, $DB;

if(isset($_POST['hidden-start']))
{
    date_default_timezone_set('America/Sao_Paulo');
  //trial
  $trialid = $_SESSION['edittrialid'];

  //trecho ambíguo, porém ainda não alterar
  //$timeCreated = $_POST['hidden-mytime']; ---->
  $rSQL = "SELECT * FROM {local_pdi_trial} WHERE createdby = '$USER->id' and id = $trialid";
  $resultado = $DB->get_records_sql($rSQL);
  $trialID;
  foreach($resultado as $t){$trialID = $t->id;}
  //---->

  $trialName = $_POST['hidden-name'];
  $starttime = $_POST['hidden-start'];
  $endtime = $_POST['hidden-end'];
  $evtype = $_POST['hidden-type'];
  $started = $_POST['hidden-started'];
  $unix = time();

  $addTrialDetail = new stdClass();
  $addTrialDetail->trialid = $trialID;
  $addTrialDetail->startdate = $starttime;
  $addTrialDetail->enddate = $endtime;
  $addTrialDetail->evtype = $evtype;
  $addTrialDetail->isstarted = $started;
  $addTrialDetail->timecreated = $unix;
  $addTrialDetail->timemod = $unix;

  //atualizar nome
  $updName = new stdClass();
  $updName->id = $trialID;
  $updName->title = $trialName;

  //see if it already exists
  $sqlE = "SELECT * FROM {local_pdi_trial_detail} WHERE trialid = '$trialID'";
  $resE = $DB->get_records_sql($sqlE);

    if(count($resE) < 1){

        //atualizar nome
        $update = $DB->update_record('local_pdi_trial', $updName);

        //detalhes
        $result = $DB->insert_record('local_pdi_trial_detail', $addTrialDetail);
        
        if($result > 0){
            echo "ok";
        }
    }  
    else{

        $detailID;
        foreach($resE as $re){$detailID = $re->id;}


        //nome
        //atualizar nome
        $update = $DB->update_record('local_pdi_trial', $updName);

        //detalhes
        $addTrialDetail->id = $detailID;
        $res = $DB->update_record('local_pdi_trial_detail', $addTrialDetail);

        if($res){
            echo "Atualizado!";
        }
        
    }  
  
}

