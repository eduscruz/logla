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
require_once(dirname(__FILE__).'/post_student.php');

//moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

// include css
$style = '/mod/logla/style.css';
$PAGE->requires->css($style);


class pre_student extends moodleform {

    /** @var stdClass the logla record that contains */
    public $logla;

    //Add elements to form
    public function definition() {

        global $DB, $PAGE, $USER, $OUTPUT, $COURSE;
        $id = optional_param('id', 0, PARAM_INT);

        //create an logla objetct of instance
        $loglaresult = $DB->get_record('logla', array('coursemodule'=>$id));

        //create an logla_user_grades objetct of instance
        $user_grade_result = $DB->get_record('logla_user_grades', array('idlogla'=>$loglaresult->id, 'userid'=>$USER->id));

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

        // binds this instance to the  user id
        $mform->addElement('hidden', 'loglaid');
        $mform->setType('loglaid', PARAM_INT);
        $mform->setDefault('loglaid', $loglaresult->id);

        // binds this instance to the  logla_user_grade id
        $mform->addElement('hidden', 'loglauserid');
        $mform->setType('loglauserid', PARAM_INT);

        // echo $OUTPUT->heading(get_string('header8', 'logla'));
        
        // Header 0
        $mform->addElement('header', 'loglafieldset', get_string('header8', 'logla'));
        $mform->addElement('html', '<p>'.get_string('textactivity11', 'logla').'<br>');

        // if quiz
        if(!$loglaresult->activityquiz){
            
            // get quiz answer, right answer, grade, etc
            $sql = 'SELECT
                        quiza.userid,
                        quiza.quiz,
                        quiza.id AS quizattemptid,
                        quiza.attempt,
                        quiza.sumgrades,
                        qu.preferredbehaviour,
                        qa.slot,
                        qa.behaviour,
                        qa.questionid,
                        qa.variant,
                        qa.maxmark,
                        qa.minfraction,
                        qa.flagged,
                        qas.sequencenumber,
                        qas.state,
                        qas.fraction,
                        FROM_UNIXTIME(qas.timecreated) AS timecreated,
                        qas.userid,
                        qasd.name,
                        qasd.value,
                        qa.questionsummary AS question,
                        qa.rightanswer AS rightanswer,
                        qa.responsesummary AS useranswer
                    
                    FROM mdl_quiz_attempts quiza
                    JOIN mdl_question_usages qu ON qu.id = quiza.uniqueid
                    JOIN mdl_question_attempts qa ON qa.questionusageid = qu.id
                    JOIN mdl_question_attempt_steps qas ON qas.questionattemptid = qa.id
                    LEFT JOIN mdl_question_attempt_step_data qasd ON qasd.attemptstepid = qas.id
                    
                    WHERE quiza.quiz = ? AND quiza.userid = ? AND qasd.name LIKE ?
                    
                    ORDER BY quiza.userid, quiza.attempt, qa.slot, qas.sequencenumber, qasd.name';
            
            
            $question = '';
            $useranswer = '';
            $rightanswer = '';
            $interator = 0;

            $rs = $DB->get_recordset_sql($sql, array($loglaresult->idquiz, $USER->id, '-finish'));
            foreach ($rs as $record) {
                $interator++;
                $question .= '<p>'.$interator.') '.$record->question.'<br>';
                $useranswer .= '<p>'.$interator.') '.$record->useranswer.'<br>';
                $rightanswer .= '<p>'.$interator.') '.$record->rightanswer.'<br>';
            }
            $rs->close();
            
            //add section header              
            $mform->addElement('header', 'loglafieldset', get_string('header1', 'logla'));
            $mform->addElement('html', $question);

            $mform->addElement('header', 'loglafieldset', get_string('header2', 'logla'));
            $mform->addElement('html', $useranswer);

            $mform->addElement('header', 'loglafieldset', get_string('header3', 'logla'));
            $mform->addElement('html', $rightanswer);
            
        }
        // if activity
        else{
            // get information about assignment
            $assign = $DB->get_record('assign', array('id'=>$loglaresult->idactivity));
            
            $sql =  'SELECT	grade.id, grade.assignment, grade.userid, grade.timecreated, 
                    grade.timemodified, grade.grader,grade. grade, grade.attemptnumber,
                    submission.assignment, submission.submission, submission.onlinetext, submission.onlineformat
                    FROM mdl_assign_grades AS grade
                    INNER JOIN mdl_assignsubmission_onlinetext AS submission
                    WHERE grade.assignment = ? AND grade.userid = ?';

            $assignanswer = $DB->get_record_sql($sql, array($loglaresult->idactivity, $USER->id)); 

            $question = $assign->intro;
            $useranswer = $assignanswer->onlinetext;
            $rightanswer = $loglaresult->rightanswer;

            $mform->addElement('html', '<p>'.get_string('question1', 'logla').$question);
            $mform->addElement('html', '<p>'.$useranswer);

            $mform->addElement('header', 'loglafieldset', get_string('header4', 'logla'));
            $mform->addElement('html', '<p>'.$rightanswer);
        }

        // Header self regulation 1
        $mform->addElement('header', 'loglafieldset', get_string('header5', 'logla'));

        // add radiobox selfregulation
        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'selfregulation1', '', get_string('textactivity8', 'logla'), 1);
        $radioarray[] = $mform->createElement('radio', 'selfregulation1', '', get_string('textactivity9', 'logla'), 2);
        $radioarray[] = $mform->createElement('radio', 'selfregulation1', '', get_string('textactivity10', 'logla'), 3);
        $mform->addGroup($radioarray, 'sr1', get_string('textactivity7', 'logla'), array(' '), false);

        // if($user_grade_result->sr1){
        if($user_grade_result){
            $mform->setDefault('selfregulation1', $user_grade_result->sr1);
        }
        else{
            $mform->setDefault('selfregulation1', 1);
        }

        /* $data = $this->_customdata;
        if ($data['posfeedback']) {
            
            $mform->addElement('button', 'next1', 'proximo');
            // $this->add_action_buttons(); 

            $toform = array('prefeedback' => true);
            //Form processing and displaying is done here
            if ($this->next1) {
                //In this case you process validated data. $mform->get_data() returns data posted in form.
                echo $OUTPUT->box('Pegou dados pos');
                // $post_student_form = new post_student(null, $toform);
                // $post_student_form->display();
            }
        } else {
            // summit button
            // $this->add_action_buttons();  
        } */
        
        $this->add_action_buttons(); 


        
    }
    
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }

}