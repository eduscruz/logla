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


// include css
// $style = $CFG->dirroot.'/mod/logla/style.css';
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

        // TESTE MFORM
        // $mform->addElement('text', 'email', get_string('Quiz', 'logla')); // Add elements to your form
        // $mform->setType('email', PARAM_NOTAGS);                   //Set type of element
        // $mform->setDefault('email', 'Please enter email');        //Default value
        // // $mform->addElement('static', 'description', 'teste1', $this->_customdata['intro']); 
        // $mform->addElement('static', 'description', 'teste2', $id);
        
        $mform = $this->_form; // Don't forget the underscore! 

        // if user can edit logla then show all results
        if ($PAGE->user_allowed_editing()) {
            // SQL query to select tables logla_user_grades and user
            $sql  = 'SELECT mdl_logla_user_grades.userid, mdl_user.username, mdl_user.firstname, mdl_user.lastname,';
            $sql .= ' mdl_user.email, mdl_logla_user_grades.pregrade, mdl_logla_user_grades.posgrade';
            $sql .= ' FROM mdl_logla_user_grades';
            $sql .= ' INNER JOIN mdl_user ON mdl_logla_user_grades.userid = mdl_user.ID';
            $sql .= ' WHERE mdl_logla_user_grades.idlogla = ?';

            // print results of sql query
            
            
            $mform->addElement('html', '<div class="table-responsive">');
            $mform->addElement('html', '<table class="tableKMA">');
            $mform->addElement('html', '<tr>');
            $mform->addElement('html', '<th>Userid</th>');
            $mform->addElement('html', '<th>Username</th>');
            $mform->addElement('html', '<th>Firstname</th>');
            $mform->addElement('html', '<th>Lastname</th>');
            $mform->addElement('html', '<th>E-mail</th>');
            $mform->addElement('html', '<th>PreKMA</th>');
            $mform->addElement('html', '<th>PosKMA</th>');
            $mform->addElement('html', '</tr>');
            $rs = $DB->get_recordset_sql($sql, array($loglaresult->id));
            foreach ($rs as $record) {
                $mform->addElement('html', '<tr>');
                $mform->addElement('html', "<td>$record->userid</td>");
                $mform->addElement('html', "<td>$record->username</td>");
                $mform->addElement('html', "<td>$record->firstname</td>");
                $mform->addElement('html', "<td>$record->lastname</td>");
                $mform->addElement('html', "<td>$record->email</td>");
                $mform->addElement('html', "<td>$record->pregrade</td>");
                $mform->addElement('html', "<td>$record->posgrade</td>");
                $mform->addElement('html', '</tr>');
            }
            $rs->close();
            $mform->addElement('html', '</table>');
            $mform->addElement('html', '</div>');

 
        } else {
            // if user can not edit logla then show only own result
            $user_grade_result = $DB->get_record('logla_user_grades', array('idlogla'=>$loglaresult->id, 'userid'=>$USER->id));
            $user_grade_resultcount = $DB->count_records('logla_user_grades', array('idlogla'=>$loglaresult->id, 'userid'=>$USER->id));
        
            // if exist results from userid
            if($user_grade_resultcount){
                
                $texto = "Sua avaliação pre-metacognitiva foi: <br>";
                // if $user_grade_result->pregrade != null
                if($user_grade_result->pregrade != null){
                    $texto .= $user_grade_result->pregrade;
                    $texto .= "<br>";
                    // $mform->addElement('static', 'description', 'teste1', $texto);
                    echo $OUTPUT->box($texto);
                }
                else{
                    $texto .= 'nao avaliado <br>';
                    // $mform->addElement('static', 'description', 'teste2', $texto);
                    echo $OUTPUT->box($texto);
                }
                
                $texto = "Sua avaliação pos-metacognitiva foi:  ";
                // if $user_grade_result->posgrade != null
                if($user_grade_result->posgrade != null){
                    $texto .= $user_grade_result->posgrade;
                    $texto .= "<br>";
                    // $mform->addElement('static', 'description', 'teste3', $texto);
                    echo $OUTPUT->box($texto);
                }
                else{
                    $texto .= 'nao avaliado <br>';
                    // $mform->addElement('static', 'description', 'teste4', $texto);
                    echo $OUTPUT->box($texto);
                }
            } 
            // if not exist results from userid
            else {
                $texto = 'Feedback nao preenchido ainda ou nota da atividade/quiz ainda não avaliada';
                // $mform->addElement('static', 'description', 'teste5', $texto);
                echo $OUTPUT->box($texto);
            }
        }

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


