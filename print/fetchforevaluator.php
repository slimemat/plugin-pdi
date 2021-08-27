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


//função para retornar os blocos de processos para um avaliador
function fetchTrials(){

    global $USER, $DB;

    $sql = "SELECT t.id, t.title, t.timecreated, t.timemod, td.startdate, td.enddate, td.evtype, td.isstarted, ev.mdlid FROM mdl_local_pdi_trial t
            LEFT JOIN {local_pdi_trial_detail} td
            ON td.trialid = t.id
            LEFT JOIN {local_pdi_trial_evaluator} tev
            ON tev.trialid = t.id
            LEFT JOIN {local_pdi_evaluator} ev
            ON tev.evaluatorid = ev.id
            WHERE ev.mdlid = '$USER->id'";
    $res = $DB->get_records_sql($sql);

    $blocoHtml = "";
    foreach($res as $r){

        $trialid = $r->id;
        $titulo = $r->title;
        $datacriacao = $r->timecreated;
        $datamod = $r->timemod;
        $dataInicio = ($r->startdate) * 1;
        $dataFim = ($r->enddate) * 1;
        $evtype = $r->evtype;
        $is_started = $r->isstarted;

        $dateInicioF = gmdate("d/m/y", $dataInicio);
        $dateFimF = gmdate("d/m/y", $dataFim);


        //se for verdade, o ícone é VERDE (--mySUCCESS)
        if($is_started == '1'){
            $blocoHtml .= 
            "<div class='my-margin-box' id='youev-$trialid' data-id='$trialid'>
                <span class=\"my-circle\" style=\"background-color: var(--mysuccess); color: var(--myblack);\">✔</span>
                <div class='my-sidetext'>
                    <span class='my-circle-title'>$titulo</span>
                    <p>$dateInicioF - $dateFimF</p>
                    <p>12/30 forms not answered (exemplo)</p>
                </div>
            </div>";
        }else{
            $blocoHtml .= 
            "<div class='my-margin-box' id='youev-$trialid' data-id='$trialid'>
                <span class=\"my-circle\" style=\"background-color: var(--myerror); color: var(--myblack);\">✖</span>
                <div class='my-sidetext'>
                    <span class='my-circle-title'>$titulo</span>
                    <p>$dateInicioF - $dateFimF</p>
                    <p>12/30 forms not answered (exemplo)</p>
                </div>
            </div>";
        }
    }

    return $blocoHtml;

    
}

