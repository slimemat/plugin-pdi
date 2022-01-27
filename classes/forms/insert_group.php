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
 
class insert_group extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG, $DB;
 
        $mform = $this->_form; // Don't forget the underscore! 
 
        $mform->addElement('text', 'cohortname', "Group name:", "required placeholder='cohort name'"); // Add elements to your form
        $mform->setType('cohortname', PARAM_NOTAGS);                   //Set type of element
        $mform->setDefault('cohortname', '');        //Default value

        $mform->addElement('textarea', 'cohortdesc', "Group description:", "required placeholder='cohort description'"); // Add elements to your form
        $mform->setType('cohortdesc', PARAM_NOTAGS);                   //Set type of element
        $mform->setDefault('cohortdesc', '');        //Default value

        $choices = array();

        $sql = "SELECT lpv.id, u.username 
        FROM {local_pdi_evaluator} lpv 
        INNER JOIN {user} u 
        ON u.id = lpv.mdlid";

        $res = $DB->get_records_sql($sql);

        foreach($res as $r){
            $id = $r->id;
            $name = $r->username;
            $choices["$id"] = "$name";
        }

        $mform->addElement('select', 'cohorteva', "Add one evaluator:", $choices); // Add elements to your form
        $mform->setDefault('cohorteva', '0');


        //normally you use add_action_buttons instead of this code
        $buttonarray=array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', "create");
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);

        
    }
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}