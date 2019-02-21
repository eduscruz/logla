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


class view_kma_results_form extends moodleform {

    /** @var stdClass the logla record that contains */
    public $logla;


    //Add elements to form
    public function definition() {

        global $DB, $PAGE, $USER, $OUTPUT;
        $id = optional_param('id', 0, PARAM_INT);

        //create an logla objetct of instance
        $loglaresult = $DB->get_record('logla', array('coursemodule'=>$id));

        // update results of all instances before show result
        logla_user_grades($loglaresult, 1);
        
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
     
        // if user can not edit logla then show only own result
        $user_grade_result = $DB->get_record('logla_user_grades', array('idlogla'=>$loglaresult->id, 'userid'=>$USER->id));
        $user_grade_resultcount = $DB->count_records('logla_user_grades', array('idlogla'=>$loglaresult->id, 'userid'=>$USER->id));
   
        // if exist results from userid
        if($user_grade_resultcount){
            
            $mform->addElement('html', '<div>');
            $mform->addElement('html', '<table>');
            $mform->addElement('html', '<tr>');
            $mform->addElement('html', '<th>Pre KMA</th>');
            $mform->addElement('html', '<th>Pos KMA</th>');
            $mform->addElement('html', '<th>Pre KMA</th>');
            $mform->addElement('html', '<th>Pos KMA</th>');
            $mform->addElement('html', '</tr>');
            $mform->addElement('html', '<tr>');
            $mform->addElement('html', "<td>$user_grade_result->prekmagrade</td>");
            $mform->addElement('html', "<td>$user_grade_result->poskmagrade</td>");
            $mform->addElement('html', "<td>$user_grade_result->prekmbgrade</td>");
            $mform->addElement('html', "<td>$user_grade_result->poskmbgrade</td>");
            $mform->addElement('html', '</tr>');
            $mform->addElement('html', '</div>');
            $mform->addElement('html', '</table>');

        }
        // if not exist results from userid
        else {
            $texto = 'Feedback nao preenchido ainda ou nota da atividade/quiz ainda não avaliada';
            $mform->addElement('static', 'description', 'teste5', $texto);
            // echo $OUTPUT->box($texto);
        }

        //add section header
        $header2 = 'General Metacognition Results';
        $mform->addElement('header', 'loglafieldset', $header2);

        // SQL query to select tables logla_user_grades and user
        $sql  = 'SELECT AVG(mdl_logla_user_grades.prekmagrade) AS prekmagrade, AVG(mdl_logla_user_grades.poskmagrade) AS poskmagrade,';
        $sql .= ' AVG(mdl_logla_user_grades.prekmbgrade) AS prekmbgrade, AVG(mdl_logla_user_grades.poskmbgrade) AS poskmbgrade';
        $sql .= ' FROM mdl_logla_user_grades';
        $sql .= ' INNER JOIN mdl_user ON mdl_logla_user_grades.userid = mdl_user.ID';
        $sql .= ' WHERE mdl_logla_user_grades.userid = ?';
        $sql .= ' GROUP BY mdl_logla_user_grades.userid';

        // print results of sql query
        $kmageneral = $DB->get_record_sql($sql, array($USER->id));

        // print average results from user

        // $texto = "Sua avaliação pre-metacognitiva geral foi:  ";
        // $mform->addElement('static', 'description', $texto, $kmageneral->prekmagrade);
        // $texto = "Sua avaliação pos-metacognitiva geral foi:  ";
        // $mform->addElement('static', 'description', $texto, $kmageneral->poskmagrade);

        $mform->addElement('html', '<div>');
        $mform->addElement('html', '<table>');
        $mform->addElement('html', '<tr>');
        $mform->addElement('html', '<th>Pre KMA</th>');
        $mform->addElement('html', '<th>Pos KMA</th>');
        $mform->addElement('html', '<th>Pre KMA</th>');
        $mform->addElement('html', '<th>Pos KMA</th>');
        $mform->addElement('html', '</tr>');
        $mform->addElement('html', '<tr>');
        $mform->addElement('html', "<td>$kmageneral->prekmagrade</td>");
        $mform->addElement('html', "<td>$kmageneral->poskmagrade</td>");
        $mform->addElement('html', "<td>$kmageneral->prekmbgrade</td>");
        $mform->addElement('html', "<td>$kmageneral->poskmbgrade</td>");
        $mform->addElement('html', '</tr>');
        $mform->addElement('html', '</div>');
        $mform->addElement('html', '</table>');

        // summit button
        $this->add_action_buttons();    
        
       
    }

    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }

}