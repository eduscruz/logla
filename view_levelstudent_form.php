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


class view_levelstudent_form extends moodleform {

    //Add elements to form
    public function definition() {

        global $DB, $PAGE, $USER, $OUTPUT, $COURSE;
        $id = optional_param('id', 0, PARAM_INT);

        //create an logla objetct of instance
        $loglaresult = $DB->get_record('logla', array('coursemodule'=>$id));

        //create an logla_user_grades objetct of instance
        $logla_user_result = $DB->get_record('logla_user_grades', array('idlogla'=>$loglaresult->id, 'userid'=>$USER->id));

        // inicialize mform
        $mform = $this->_form;  
        
        // binds this instance to the logla coursemodule
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $id);
   
        // binds this instance to the  user id
        $mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_INT);
        $mform->setDefault('userid', $USER->id);

        // if exists result in logla_user_grades
        if ($logla_user_result) {

            if ($logla_user_result->prekmagrade) {
                if ($logla_user_result->prekmagrade < (-0.25)) {
                   $prekma = 'low';
                   $kmaknowledge = 'does not understand well';
                }
                elseif (($logla_user_result->prekmagrade >= (-0.25)) && (($logla_user_result->prekmagrade <= (0.5)))) {
                    $prekma = 'Average';
                    $kmaknowledge = 'reasonably';
                    
                }
                else {
                    $prekma = 'High';
                    $kmaknowledge = 'understands very well';
                }

                $text1 = '<strong>How is your accuracy in estimating your knowledge (metacognition)?</strong><br><br>
                You demonstrated <strong>';
                $text1 .= $prekma;
                $text1 .= ' </strong>accuracy in judging their knowledge in this course.
                            Keep in mind that this is only an approximate analysis, 
                            but this suggests that you are <strong>';
                $text1 .= $kmaknowledge;
                $text1 .= ' </strong>what you "KNOW" and "DO NOT KNOW". Check the feedback in your solution and try to identify your learning gaps.';
                echo $OUTPUT->box($text1);
            }
            
            if ($logla_user_result->prekmbgrade) {
                if ($logla_user_result->prekmbgrade < (-0.25)) {
                   $prekmb = 'Pessimistic';
                   $kmbknowledge = 'MORE';
                }
                elseif (($logla_user_result->prekmbgrade >= (-0.25)) && (($logla_user_result->prekmbgrade <= (0.25)))) {
                    $prekmb = 'Random';
                    $kmbknowledge = 'EXACTLY';
                }
                else {
                    $prekmb = 'Optimistic';
                    $kmbknowledge = 'LESS';
                }

                $text2 = '<strong>How is your tendency in estimating your knowledge? Are you being optimistic or pessimistic?</strong><br><br>';
                $text2 .= 'On average you are being <strong>';
                $text2 .= $prekmb;
                $text2 .= '</strong> in your assessment of your knowledge your skills in problem solving. This means that you imagine that you know <strong>';
                $text2 .= $kmbknowledge;
                $text2 .= ' </strong>than you demonstrate.';
                echo $OUTPUT->box($text2);
            }

        }
        else{
            echo $OUTPUT->box('error: logla_user_grades record found');
        }

        $redirect = '<a href="/course/view.php?id='.$COURSE->id.'">';
        $mform->addElement('html', $redirect);
        $mform->addElement('button', 'finish', "Finish");
        $redirect = '</a>';
        $mform->addElement('html', $redirect);
        
    }
}
