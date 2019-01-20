<?php
// This file is part of Moodle - http://moodle.org/
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
 * Prints a particular instance of logla
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_logla
 * @copyright  2018 Eduardo Cruz <eduardo.cruz@ufabc.edu.br>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Replace logla with the name of your module and remove this line.


//moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class view_kma_results_form extends moodleform {

    /** @var stdClass the logla record that contains */
    public $logla;


    //Add elements to form
    public function definition() {

        global $DB;
        $id = optional_param('id', 0, PARAM_INT);

        $loglaresult = $DB->get_record('logla', array('coursemodule'=>$id));


        // SQL query to select tables logla_user_grades and user
        $sql  = 'SELECT mdl_logla_user_grades.userid, mdl_user.username, mdl_user.firstname, mdl_user.lastname,';
        $sql .= ' mdl_user.email, mdl_logla_user_grades.pregrade, mdl_logla_user_grades.posgrade';
        $sql .= ' FROM mdl_logla_user_grades';
        $sql .= ' INNER JOIN mdl_user ON mdl_logla_user_grades.userid = mdl_user.ID';
        $sql .= ' WHERE mdl_logla_user_grades.idlogla = ?';

        // print results of sql query
        
        $mform = $this->_form; // Don't forget the underscore! 
        $mform->addElement('html', '<div class="table-responsive">');
        $mform->addElement('html', '<table class="table">');
        $mform->addElement('html', '<tr>');
        $mform->addElement('html', '<th>Userid</th)');
        $mform->addElement('html', '<th>Username</th)');
        $mform->addElement('html', '<th>Firstname</th)');
        $mform->addElement('html', '<th>Lastname</th)');
        $mform->addElement('html', '<th>E-mail</th)');
        $mform->addElement('html', '<th>PreKMA</th)');
        $mform->addElement('html', '<th>PosKMA</th)');
        $mform->addElement('html', '</tr>');
        $rs = $DB->get_recordset_sql($sql, array($loglaresult->id));
        foreach ($rs as $record) {
            $mform->addElement('html', '<tr>');
            $mform->addElement('html', "<td>$record->userid</td)");
            $mform->addElement('html', "<td>$record->username</td)");
            $mform->addElement('html', "<td>$record->firstname</td)");
            $mform->addElement('html', "<td>$record->lastname</td)");
            $mform->addElement('html', "<td>$record->email</td)");
            $mform->addElement('html', "<td>$record->pregrade</td)");
            $mform->addElement('html', "<td>$record->posgrade</td)");
            $mform->addElement('html', '</tr>');
        }
        $rs->close();
        $mform->addElement('html', '</tr>');
        $mform->addElement('html', '</table>');
        $mform->addElement('html', '</div>');

        $mform->addElement('text', 'email', get_string('Quiz', 'logla')); // Add elements to your form
        $mform->setType('email', PARAM_NOTAGS);                   //Set type of element
        $mform->setDefault('email', 'Please enter email');        //Default value
        // $mform->addElement('static', 'description', 'teste1', $this->_customdata['intro']); 
        $mform->addElement('static', 'description', 'teste2', $id); 
    }

    /*
    public function __construct($logla){
        global $CFG;
 
        $mform = $this->_form; // Don't forget the underscore! 

        if ($this->logla->intro) {
            // echo $OUTPUT->box(format_module_intro('logla', $logla, $cm->id), 'generalbox mod_introbox', 'loglaintro');      
        }
    }
    */

    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }

    // function setdata(stdClass $logla){
    //     $this->logla = $logla;
    // }
}


