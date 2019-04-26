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
 * @copyright  2019 Eduardo Cruz <eduardo.cruz@ufabc.edu.br>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

//moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

// include css
$style = '/mod/logla/style.css';
$PAGE->requires->css($style);


class view_results_form extends moodleform {

    /** @var stdClass the logla record that contains */
    public $logla;


    //Add elements to form
    public function definition() {

        global $DB, $PAGE, $USER, $OUTPUT, $COURSE;
        $id = optional_param('id', 0, PARAM_INT);

        //create an logla objetct of instance
        $loglaresult = $DB->get_record('logla', array('coursemodule'=>$id));

        // update results of all instances before show result
        // logla_user_grades($loglaresult, 1);
        
        // inicialize mform
        $mform = $this->_form;  
        
        //add section header
        $header1 = $loglaresult->name;
        $header1 .= ' Result';
        $mform->addElement('header', 'loglafieldset', $header1);
        
        // binds this instance to the logla id
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $id);

        // if user can edit logla then show all results
        if ($PAGE->user_allowed_editing()) {

            // print results in the table           
            $mform->addElement('html', '<div>');
            $mform->addElement('html', '<table>');
            $mform->addElement('html', '<tr>');
            $mform->addElement('html', '<th>Userid</th>');
            $mform->addElement('html', '<th>Username</th>');
            $mform->addElement('html', '<th>Firstname</th>');
            $mform->addElement('html', '<th>Lastname</th>');
            $mform->addElement('html', '<th>E-mail</th>');
            $mform->addElement('html', '<th>KMA</th>');
            $mform->addElement('html', '<th>SAA</th>');
            $mform->addElement('html', '<th>KMB</th>');
            $mform->addElement('html', '<th>SAB</th>');
            $mform->addElement('html', '</tr>');

            // SQL query to select tables logla_user_grades and user
            $sql  = 'SELECT mdl_logla_user_grades.userid, mdl_user.username, mdl_user.firstname, mdl_user.lastname,';
            $sql .= ' mdl_user.email, mdl_logla_user_grades.kmagrade, mdl_logla_user_grades.saagrade,';
            $sql .= ' mdl_logla_user_grades.kmbgrade, mdl_logla_user_grades.sabgrade';
            $sql .= ' FROM mdl_logla_user_grades';
            $sql .= ' INNER JOIN mdl_user ON mdl_logla_user_grades.userid = mdl_user.ID';
            $sql .= ' WHERE mdl_logla_user_grades.idlogla = ? AND mdl_logla_user_grades.enable = 1';
            $rs = $DB->get_recordset_sql($sql, array($loglaresult->id));
            // print results in the table      
            foreach ($rs as $record) {
                $mform->addElement('html', '<tr>');
                $mform->addElement('html', "<td>$record->userid</td>");
                $mform->addElement('html', "<td>$record->username</td>");
                $mform->addElement('html', "<td>$record->firstname</td>");
                $mform->addElement('html', "<td>$record->lastname</td>");
                $mform->addElement('html', "<td>$record->email</td>");
                $mform->addElement('html', "<td>$record->kmagrade</td>");
                $mform->addElement('html', "<td>$record->saagrade</td>");
                $mform->addElement('html', "<td>$record->kmbgrade</td>");
                $mform->addElement('html', "<td>$record->sabgrade</td>");
                $mform->addElement('html', '</tr>');
            }
            $rs->close();
            
            // SQL query to average pre-kma and pos-kma grade per user on this logla instance
            $sql  = 'SELECT AVG(mdl_logla_user_grades.kmagrade) AS kmagrade,';
            $sql .= ' AVG(mdl_logla_user_grades.saagrade) AS saagrade,';
            $sql .= ' AVG(mdl_logla_user_grades.kmbgrade) AS kmbgrade,';
            $sql .= ' AVG(mdl_logla_user_grades.sabgrade) AS sabgrade';
            $sql .= ' FROM mdl_logla_user_grades';
            $sql .= ' WHERE mdl_logla_user_grades.idlogla = ? AND mdl_logla_user_grades.enable = 1';
            $kmaavg = $DB->get_record_sql($sql, array($loglaresult->id));

            // print results in the table 
            $mform->addElement('html', '<tfoot>');
            $mform->addElement('html', '<tr>');
            $mform->addElement('html', "<td>Average</td>");
            $mform->addElement('html', "<td></td><td></td><td></td><td></td>");
            $mform->addElement('html', "<td>$kmaavg->kmagrade</td>");
            $mform->addElement('html', "<td>$kmaavg->saagrade</td>");
            $mform->addElement('html', "<td>$kmaavg->kmbgrade</td>");
            $mform->addElement('html', "<td>$kmaavg->sabgrade</td>");
            $mform->addElement('html', '</tr>');
            $mform->addElement('html', '</tfoot>');
            $mform->addElement('html', '</table>');
            $mform->addElement('html', '</div>');

            //add section header
            $header2 = 'General Metacognition Results';
            $mform->addElement('header', 'loglafieldset', $header2);

            // SQL query to average pre-kma and pos-kma grade per user on all logla instances
            $sql  = 'SELECT mdl_logla_user_grades.userid, mdl_user.username, mdl_user.firstname,';
            $sql .= ' mdl_user.lastname,  mdl_user.email, AVG(mdl_logla_user_grades.kmagrade) AS kmagrade,';
            $sql .= ' AVG(mdl_logla_user_grades.saagrade) AS saagrade,';
            $sql .= ' AVG(mdl_logla_user_grades.kmbgrade) AS kmbgrade,';
            $sql .= ' AVG(mdl_logla_user_grades.sabgrade) AS sabgrade';
            $sql .= ' FROM mdl_logla_user_grades';
            $sql .= ' INNER JOIN mdl_user ON mdl_logla_user_grades.userid = mdl_user.ID';
            $sql .= ' WHERE mdl_logla_user_grades.enable = 1';
            $sql .= ' GROUP BY mdl_logla_user_grades.userid';
   
            // Auxiliary variables
            $prekmaavg = 0;
            $poskmaavg = 0;
            $prekmbavg = 0;
            $poskmbavg = 0;
            $iprekmaavg = 0;
            $iposkmaavg = 0;
            $iprekmbavg = 0;
            $iposkmbavg = 0;

            // print results in the table 
            $mform->addElement('html', '<div>');
            $mform->addElement('html', '<table>');
            $mform->addElement('html', '<tr>');
            $mform->addElement('html', '<th>Userid</th>');
            $mform->addElement('html', '<th>Username</th>');
            $mform->addElement('html', '<th>Firstname</th>');
            $mform->addElement('html', '<th>Lastname</th>');
            $mform->addElement('html', '<th>E-mail</th>');
            $mform->addElement('html', '<th>KMA</th>');
            $mform->addElement('html', '<th>SAA</th>');
            $mform->addElement('html', '<th>KMB</th>');
            $mform->addElement('html', '<th>SAB</th>');
            $mform->addElement('html', '</tr>');
            $rs = $DB->get_recordset_sql($sql);
            foreach ($rs as $record) {
                $mform->addElement('html', '<tr>');
                $mform->addElement('html', "<td>$record->userid</td>");
                $mform->addElement('html', "<td>$record->username</td>");
                $mform->addElement('html', "<td>$record->firstname</td>");
                $mform->addElement('html', "<td>$record->lastname</td>");
                $mform->addElement('html', "<td>$record->email</td>");
                $mform->addElement('html', "<td>$record->kmagrade</td>");
                $mform->addElement('html', "<td>$record->saagrade</td>");
                $mform->addElement('html', "<td>$record->kmbgrade</td>");
                $mform->addElement('html', "<td>$record->sabgrade</td>");
                $mform->addElement('html', '</tr>');

                // Checks if result is not null
                if ($record->kmagrade != null){
                    $prekmaavg += $record->kmagrade;
                    ++$iprekmaavg;
                }
                if ($record->saagrade != null){
                    $poskmaavg += $record->saagrade;  
                    ++$iposkmaavg;        
                }
                if ($record->kmbgrade != null){
                    $prekmbavg += $record->kmbgrade;
                    ++$iprekmbavg;
                }
                if ($record->sabgrade != null){
                    $poskmbavg += $record->sabgrade;  
                    ++$iposkmbavg;        
                }
            }
            
            $rs->close();

            // checks if the result is not null to average
            if($prekmaavg != 0){
                $prekmaavg = $prekmaavg / $iprekmaavg;
            }
            if($poskmaavg != 0){
                $poskmaavg = $poskmaavg / $iposkmaavg;
            }
            if($prekmbavg != 0){
                $prekmbavg = $prekmbavg / $iprekmbavg;
            }
            if($poskmbavg != 0){
                $poskmbavg = $poskmbavg / $iposkmbavg;
            }

            // print results in the table 
            $mform->addElement('html', '<tfoot>');
            $mform->addElement('html', '<tr>');
            $mform->addElement('html', "<td>Average</td>");
            $mform->addElement('html', "<td></td><td></td><td></td><td></td>");
            $mform->addElement('html', "<td>$prekmaavg</td>");
            $mform->addElement('html', "<td>$poskmaavg</td>");
            $mform->addElement('html', "<td>$prekmbavg</td>");
            $mform->addElement('html', "<td>$poskmbavg</td>");
            $mform->addElement('html', '</tr>');
            $mform->addElement('html', '</tfoot>');
            $mform->addElement('html', '</table>');
            $mform->addElement('html', '</div>');

            $redirect = '<a href="/course/view.php?id='.$COURSE->id.'">';
            $mform->addElement('html', $redirect);
            $mform->addElement('button', 'finish', get_string('finish', 'logla'));
            $redirect = '</a>';
            $mform->addElement('html', $redirect);
 
        } 
    }
}
    