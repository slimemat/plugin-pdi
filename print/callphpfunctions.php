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
require_once('fetchforevaluator.php');
require_once('trialsfunctions.php');
require_once('statusfunctions.php');

if(isset($_POST['function'])){

    global $USER, $DB;

    $myfunction = $_POST['function'];

    if($myfunction == 0){

        $offset = $_POST['offset'];
        $rows = $_POST['rows'];


        $return = fetchTrials($offset, $rows);

        echo $return;
    }
    else if($myfunction == 1){
        $offset = $_POST['offset'];
        $rows = $_POST['rows'];

        $return = mostrarTodosTrials($offset, $rows);

        echo $return;
    }
    else if($myfunction == 2){
        $pesquisa = $_POST['pesquisa'];

        $return = searchStudentTrials($pesquisa);

        echo $return;
    }
    else if($myfunction == 3){
        $alunoid = $_POST['alunoid'];
        $sectorid = $_POST['sectorid'];
        $trialid = $_POST['trialid'];

        $return = stsPrimeiroBloco($alunoid, $trialid, $sectorid);

        echo $return;

    }
    else if($myfunction == 4){
        //var de key
        $alunoid = $_POST['alunoid'];
        $sectorid = $_POST['sectorid'];
        $trialid = $_POST['trialid'];
        //var de conteudo
        $title = $_POST['title'];
        $desc = $_POST['desc'];

        $return = inserirObjetivo($title, $desc, $alunoid, $trialid, $sectorid);

        echo $return;
    }
    else if($myfunction == 5){
        //var de key
        $alunoid = $_POST['alunoid'];
        $sectorid = $_POST['sectorid'];
        $trialid = $_POST['trialid'];

        $return = fetchBlocosObjetivo($alunoid, $trialid, $sectorid);

        echo $return;

    }
    else if($myfunction == 6){
        //var keys
        $idgoal = $_POST['idgoal'];
        $txttitle = $_POST['txttitle'];
        $txtdesc = $_POST['txtdesc'];

        $return = updateGoalText($idgoal, $txttitle, $txtdesc);

        echo $return;
    }
    else if($myfunction == 7){
        //var
        $userid = $_POST['userid'];
        $trialid = $_POST['trialid'];

        $return = retortoPdiPorAvaliador($userid, $trialid);

        echo $return;
    }



}



