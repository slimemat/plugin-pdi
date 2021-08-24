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
 
class insert_adm extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG;
 
        $mform = $this->_form; // Don't forget the underscore! 
 
        $mform->addElement('text', 'username', "Current username:", "required placeholder='username'"); // Add elements to your form
        $mform->setType('username', PARAM_NOTAGS);                   //Set type of element
        $mform->setDefault('username', '');        //Default value

        $mform->addElement('text', 'useremail', "Current email:", "required placeholder='email@example.com' type='email'"); // Add elements to your form
        $mform->setType('useremail', PARAM_NOTAGS);                   //Set type of element
        $mform->setDefault('useremail', '');        //Default value

        $mform->addElement('text', 'usercompany', "Current company:", "required placeholder='company name'"); // Add elements to your form
        $mform->setType('usercompany', PARAM_NOTAGS);                   //Set type of element
        $mform->setDefault('usercompany', '');        //Default value

        $choices = array();
        $choices['0'] = "pdi admin";

        $mform->addElement('select', 'userrole', "Plugin user role:", $choices); // Add elements to your form
        $mform->setDefault('userrole', '0');

        //normally you use add_action_buttons instead of this code
        $buttonarray=array();
        $buttonarray[] = $mform->createElement('reset', 'resetbutton', "reset", "class= 'btn'");
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', "Add");
        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);

        
    }
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}