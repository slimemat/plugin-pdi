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

if(isset($_POST['hidden-anstatus-id']))
{
    $anstatusID = $_POST['hidden-anstatus-id'];

    //recuperar dados de setor, processo e quem respondeu
    $sqlAnstatus = "SELECT * FROM {local_pdi_answer_status}
    WHERE id = '$anstatusID'";

    $resAnstatus = $DB->get_records_sql($sqlAnstatus);

    $answeredbyid = '';
    $sectorid = '';
    $trialid = '';
    foreach($resAnstatus as $ra){
        $answeredbyid = $ra->userid;
        $sectorid = $ra->sectorid;
        $trialid = $ra->idtrial;
    }

    //usar os dados recuperados para trazer a questões do avaliador atual
    //(desse processo e desse setor equivalente)

    $sqlPerguntas = "SELECT q.id, q.name, q.questiontext, q.qtype, qindb.databaseid, smdb.smemberid, sm.sectorid, sm.trialid
                        FROM {local_pdi_question} q
                        LEFT JOIN {local_pdi_questindb} qindb
                        ON qindb.questionid = q.id
                        LEFT JOIN {local_pdi_sect_mem_db} smdb
                        on smdb.dbid = qindb.databaseid
                        LEFT JOIN {local_pdi_sector_member} sm
                        ON sm.id = smdb.smemberid
                        LEFT JOIN {local_pdi_answer_trial} anstrial
                        on anstrial.answeredbyid = '$answeredbyid'
                        WHERE anstrial.sectorid = '$sectorid' AND sm.trialid ='$trialid' AND sm.userid = '$USER->id'
                        GROUP BY q.id";
    
    $resPerguntas = $DB->get_records_sql($sqlPerguntas);

    //construção de html
    $htmlBlock = "<div class='quest-container'>";
    $htmlInside = "";

    foreach($resPerguntas as $r)
    {
        $qtitulo = $r->name;
        $qtype = $r->qtype;
        $qid = $r->id;
        $trialid = $r->trialid;
        $qsector = $r->sectorid;

        $htmlInside .= "
            <span class='my-qtitle'>$qtitulo</span> <br>
            <label class='my-label-resp'>resposta:</label> <br>
        ";

        

    }

    $htmlBlock .= $htmlInside;
    $htmlBlock .= "</div>";
    //form oculto
    $htmlBlock .= "
    <form id='frm-quest-answer' name='frm-quest-answer' class='my-hidden' method='POST' action=''>
    <input type=\"hidden\" name=\"hidden-questid\" id=\"hidden-questid\" value=\"\">
    <input type=\"hidden\" name=\"hidden-answeredby\" id=\"hidden-answeredby\" value=\"$USER->id\">
    <input type=\"hidden\" name=\"hidden-qtrialid\" id=\"hidden-qtrialid\" value=\"$trialid\">
    <input type=\"hidden\" name=\"hidden-qsector\" id=\"hidden-qsector\" value=\"\">
    <input type=\"hidden\" name=\"hidden-qanswer\" id=\"hidden-qanswer\" value=\"\">
    </form>";

    echo "$htmlBlock";

}

