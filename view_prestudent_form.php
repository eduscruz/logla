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


class view_prestudent_form extends moodleform {

    /** @var stdClass the logla record that contains */
    public $logla;

    //Add elements to form
    public function definition() {

        global $DB, $PAGE, $USER, $OUTPUT;
        $id = optional_param('id', 0, PARAM_INT);

        //create an logla objetct of instance
        $loglaresult = $DB->get_record('logla', array('coursemodule'=>$id));

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

        // if pos-feedback is not set show awnser of student and corret result
        if(!$loglaresult->posfeedback){

            //add section header                -------------------  traduzir
            $header1 = 'Your answer:';
            $mform->addElement('header', 'loglafieldset', $header1);

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
                        
                        WHERE quiza.id = ? AND quiza.userid = ? AND qasd.name LIKE ?
                        
                        ORDER BY quiza.userid, quiza.attempt, qa.slot, qas.sequencenumber, qasd.name';
                
                // get results from quiz
                $quizresult = $DB->get_record_sql($sql, array($loglaresult->idquiz, $USER->id, '-finish'));
                $question = $quizresult->question;
                $useranswer = $quizresult->useranswer;
                $rightanswer = $quizresult->rightanswer;
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
                $rightanswer = $loglaresult->intro;
            }
            
            $mform->addElement('html', '<p>The question: '.$question);
            $mform->addElement('html', '<p>'.$useranswer);

            $header4 = 'Right answer:';
            $mform->addElement('header', 'loglafieldset', $header4);
            $mform->addElement('html', '<p>'.$rightanswer);
        }

        // // add name logla header
        // $header1 = $loglaresult->name;
        // $header1 .= ' Result';
        // $mform->addElement('header', 'loglafieldset', $header1);

        
        $user_grade_result = $DB->get_record('logla_user_grades', array('idlogla'=>$loglaresult->id, 'userid'=>$USER->id));
        $user_grade_resultcount = $DB->count_records('logla_user_grades', array('idlogla'=>$loglaresult->id, 'userid'=>$USER->id));

        // binds this instance to the  logla_user_grade id
        $mform->addElement('hidden', 'loglauserid');
        $mform->setType('loglauserid', PARAM_INT);
        if ($user_grade_result) {
            $mform->setDefault('loglauserid', $user_grade_result->id);        
        }
        else{
            $mform->setDefault('loglauserid', 0);
        }
   
        /*
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
        */

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

        // if $kmageneral is not empty
        if ($kmageneral) {
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
        }
        else {
            $mform->addElement('html', '<p> AINDA NAO FEZ NENHUMA AVALIACAO DO LOGLA');
        }

        
        // add header activity
        $header3 = 'Acitivity';
        $mform->addElement('header', 'loglafieldset', $header3);
       
        $mform->addElement('html', '<p>'.get_string('textactivity1', 'logla'));
        $mform->addElement('html', '<p>'.get_string('textactivity2', 'logla'));
        
        // add radiobox previous avaliation
        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'selactprevious', '', get_string('high', 'logla'), 0);
        $radioarray[] = $mform->createElement('radio', 'selactprevious', '', get_string('medium', 'logla'), 1);
        $radioarray[] = $mform->createElement('radio', 'selactprevious', '', get_string('low', 'logla'), 2);
        $mform->addGroup($radioarray, 'mcp1', get_string('textactivity4', 'logla'), array(' '), false);
        if($user_grade_result){
            $mform->setDefault('selactprevious', $user_grade_result->mcp1);
        }
        else{
            $mform->setDefault('selactprevious', 2);
        }
        
        // add radiobox real status
        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'realstatus', '', get_string('high', 'logla'), 0);
        $radioarray[] = $mform->createElement('radio', 'realstatus', '', get_string('medium', 'logla'), 1);
        $radioarray[] = $mform->createElement('radio', 'realstatus', '', get_string('low', 'logla'), 2);
        $mform->addGroup($radioarray, 'performace1',  get_string('textactivity5', 'logla'), array(' '), false);
        if($user_grade_result){
            $mform->setDefault('realstatus', $user_grade_result->performace1);
        }
        else{
            $mform->setDefault('realstatus', 2);
        }
        

        $mform->addElement('html', '<p>'.get_string('textactivity3', 'logla'));
        
        // add radiobox selfregulation
        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'selfregulation', '', get_string('decreasing', 'logla'), 0);
        $radioarray[] = $mform->createElement('radio', 'selfregulation', '', get_string('increasing', 'logla'), 1);
        $radioarray[] = $mform->createElement('radio', 'selfregulation', '', get_string('constant', 'logla'), 2);
        $radioarray[] = $mform->createElement('radio', 'selfregulation', '', get_string('random', 'logla'), 3);
        $radioarray[] = $mform->createElement('radio', 'selfregulation', '', get_string('undefined', 'logla'), 4);
        $mform->addGroup($radioarray, 'ep1', get_string('textactivity6', 'logla'), array(' '), false);
        if($user_grade_result){
            $mform->setDefault('selfregulation', $user_grade_result->ep1);
        }
        else{
            $mform->setDefault('selfregulation', 4);
        }
        
        // summit button
        $this->add_action_buttons();    
    }
    
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}