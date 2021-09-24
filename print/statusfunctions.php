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

    //***** FIM ALUNO *****/


    //***** PARTE AVALIADOR E PDI PROGRESSO *****/
    $sqlAvaliador = "SELECT u.id, u.firstname, u.lastname, u.username, u.email 
                        FROM {user} u WHERE u.id ='$evaluatorid'";
    $resAvaliador = $DB->get_records_sql($sqlAvaliador);

    //var do avaliador
    $avaFirstname = $resAvaliador[$evaluatorid]->firstname;
    $avaLastname = $resAvaliador[$evaluatorid]->lastname;
    $avaUsername = $resAvaliador[$evaluatorid]->username;
    $avaEmail = $resAvaliador[$evaluatorid]->email;


    echo "AVALIADOR NAME $avaFirstname<br>Cortesia de statusfunctions.php!";
    
    
 }