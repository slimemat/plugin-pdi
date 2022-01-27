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
 */

//moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");
 
class insert_member extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG, $DB, $USER;
 
        $mform = $this->_form; // Don't forget the underscore! 
 
        $mform->addElement('text', 'studname', "Student username:", "required placeholder='username'"); // Add elements to your form
        $mform->setType('studname', PARAM_NOTAGS);                   //Set type of element
        $mform->setDefault('studname', '');        //Default value

        //receber valores do nome do grupo dessa pessoa
        $sql_im = "SELECT lpg.groupname FROM {local_pdi_group} lpg 
                    INNER JOIN {local_pdi_user} lpu 
                    ON lpg.userid = lpu.id
                    WHERE lpu.username = '$USER->username'";

        $res_im = $DB->get_records_sql($sql_im);
        
        $choices = array();
        foreach($res_im as $r){
            $groupname_im = $r->groupname;
            
            $choices[$groupname_im] = "$groupname_im";
        }

        $mform->addElement('select', 'groupnameselect', "Group name:", $choices); // Add elements to your form
        $mform->setDefault('groupnameselect', '0');

        //normally you use add_action_buttons instead of this code
        $buttonarray=array();
        $buttonarray[] = $mform->createElement('reset', 'btnresetim', "reset");
        $buttonarray[] = $mform->createElement('submit', 'btnsubmitim', "Add member");
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);

        
    }
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}