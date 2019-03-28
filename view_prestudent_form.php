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

        global $DB, $PAGE, $USER, $OUTPUT, $COURSE;
        $id = optional_param('id', 0, PARAM_INT);

        //create an logla objetct of instance
        $loglaresult = $DB->get_record('logla', array('coursemodule'=>$id));

        //create an logla_user_grades objetct of instance
        $user_grade_result = $DB->get_record('logla_user_grades', array('idlogla'=>$loglaresult->id, 'userid'=>$USER->id));
        $user_grade_resultcount = $DB->count_records('logla_user_grades', array('idlogla'=>$loglaresult->id, 'userid'=>$USER->id));

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

        // if pos-feedback is set show awnser of student and corret result
        if($loglaresult->posfeedback){

            echo $OUTPUT->heading(get_string('header8', 'logla'));
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
                    $question .= '<br><p>'.$interator.') '.$record->question;
                    $useranswer .= '<br><p>'.$interator.') '.$record->useranswer;
                    $rightanswer .= '<br><p>'.$interator.') '.$record->rightanswer;
                }
                $rs->close();
                
                //add section header              
                $mform->addElement('header', 'loglafieldset', get_string('header1', 'logla'));
                $mform->addElement('html', $question.'<br>');

                $mform->addElement('header', 'loglafieldset', get_string('header2', 'logla'));
                $mform->addElement('html', '<p>'.$useranswer.'<br>');

                $mform->addElement('header', 'loglafieldset', get_string('header3', 'logla'));
                $mform->addElement('html', '<p>'.$rightanswer);
                
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
            $radioarray[] = $mform->createElement('radio', 'selfregulation1', '', get_string('textactivity8', 'logla'), 0);
            $radioarray[] = $mform->createElement('radio', 'selfregulation1', '', get_string('textactivity9', 'logla'), 1);
            $radioarray[] = $mform->createElement('radio', 'selfregulation1', '', get_string('textactivity10', 'logla'), 2);
            $mform->addGroup($radioarray, 'sr1', get_string('textactivity7', 'logla'), array(' '), false);

            // if($user_grade_result->sr1){
            if($user_grade_result){
                $mform->setDefault('selfregulation1', $user_grade_result->sr1);
            }
            else{
                $mform->setDefault('selfregulation1', 1);
            }

        }

        
        // $user_grade_result = $DB->get_record('logla_user_grades', array('idlogla'=>$loglaresult->id, 'userid'=>$USER->id));
        // $user_grade_resultcount = $DB->count_records('logla_user_grades', array('idlogla'=>$loglaresult->id, 'userid'=>$USER->id));

        // binds this instance to the  logla_user_grade id
        $mform->addElement('hidden', 'loglauserid');
        $mform->setType('loglauserid', PARAM_INT);
        if ($user_grade_result) {
            $mform->setDefault('loglauserid', $user_grade_result->id);        
        }
        else{
            $mform->setDefault('loglauserid', 0);
        }
  
        //add section header
        $mform->addElement('header', 'loglafieldset', get_string('header6', 'logla'));

        echo $OUTPUT->heading(get_string('header9', 'logla'));
        $mform->addElement('html', '<p>'.get_string('textactivity12', 'logla').'<br>');

        // insert table results
        $mform->addElement('html', '<div>');
        $mform->addElement('html', '<table>');
        $mform->addElement('html', '<tr>');
        $mform->addElement('html', '<th>'.get_string('table1', 'logla').'</th>');
        $mform->addElement('html', '<th>'.get_string('table2', 'logla').'</th>');
        $mform->addElement('html', '<th>'.get_string('table3', 'logla').'</th>');
        $mform->addElement('html', '<th>'.get_string('table4', 'logla').'</th>');
        $mform->addElement('html', '<th>'.get_string('table5', 'logla').'</th>');
        $mform->addElement('html', '<th>'.get_string('table6', 'logla').'</th>');
        $mform->addElement('html', '</tr>');
        
        // SQL query to select tables logla_user_grades and user
        $sql = 'SELECT 
                    g.id,g.idlogla,g.userid,g.prekmagrade,g.poskmagrade,
                    g.prekmbgrade,g.poskmbgrade,l.coursemodule,
                    l.prefeedback,l.posfeedback,l.idprefeedback,
                    l.idposfeedback,l.activityquiz,l.idactivity,l.idquiz
               FROM mdl_logla_user_grades AS g
               INNER JOIN mdl_logla AS l ON g.idlogla = l.id
               INNER JOIN mdl_course_modules AS c ON  c.id = l.coursemodule 
               WHERE g.userid = ? AND c.course = ?';
        
        $rs = $DB->get_recordset_sql($sql, array($USER->id, $COURSE->id));
        // if exists any result from rs
        if ($rs) {
            foreach ($rs as $record) {
                
                $mform->addElement('html', '<tr>');
                
                // if recordset is activity
                if ($record->activityquiz) {
                    
                    // get activity result
                    $activity = $DB->get_record('assign', array('id' => $record->idactivity));
                    $mform->addElement('html', "<td>$activity->name</td>");
                    $activityresult = $DB->get_record('assign_grades', array('assignment' => $record->idactivity, 'userid' => $USER->id));
                    // verify is $activityresult is not empty
                    if ($activityresult) {
                        $valuegrade = convertgradenum($activityresult->grade);
                        $mform->addElement('html', "<td>".convertgrade($activityresult->grade)."</td>");
                    } else {
                        $mform->addElement('html', "<td>Empty</td>");
                    }
                    
                    
                }
                // if recordset is quiz
                else {
                    
                    $quiz = $DB->get_record('quiz', array('id' => $record->idquiz));
                    $mform->addElement('html', "<td>$quiz->name</td>");
                    $quizresult = $DB->get_record('quiz_grades', array('quiz' => $record->idquiz, 'userid' => $USER->id));
                    if ($quizresult) {
                        $valuegrade = convertgradenum($quizresult->grade*10.0);
                        $mform->addElement('html', "<td>".convertquiz($quizresult->grade)."</td>");
                    } else {
                        $mform->addElement('html', "<td>Empty</td>");
                    }
                    
                }
                
                // if recordset is set as prefeedback
                if ($record->prefeedback) {
                    
                    // get prefeedback results
                    $resultprefb = $DB->get_record('feedback_completed', array('feedback'=>$record->idprefeedback, 'userid'=>$USER->id));
                    // verify if is not empty
                    if ($resultprefb) {
                        $mform->addElement('html', "<td>".convertfeedback($resultprefb->anonymous_response)."</td>");
                        if ($valuegrade != $resultprefb->anonymous_response) {
                            $difference = abs(($valuegrade - $resultprefb->anonymous_response)/2)*100.0;
                        } else {
                            $difference = 0;
                        }
                    } else {
                        $difference = 'Error: prefeedback empty<td>Error: prefeedback empty</td>';
                    }
                    
                    $mform->addElement('html', "<td>$difference %</td>");
                }
                else {
                    // insert empty cell
                    $mform->addElement('html', "<td></td><td></td>");
                }
                
                // if recordset is set as prefeedback
                if ($record->posfeedback) {
                    
                    // get posfeedback results
                    $resultposfb = $DB->get_record('feedback_completed', array('feedback'=>$record->idposfeedback, 'userid'=>$USER->id));
                    // verify if $resultposfb is not empty
                    if ($resultposfb) {
                        $mform->addElement('html', "<td>".convertfeedback($resultposfb->anonymous_response)."</td>");
                        if ($valuegrade != $resultposfb->anonymous_response) {
                            $difference = abs(($valuegrade - $resultposfb->anonymous_response)/2)*100.0;
                        } else {
                            $difference = 0;
                        }
                    } else {
                        $difference = 'Error: posfeedback empty<td>Error: posfeedback empty</td>';
                    }
                    
                    
                    $mform->addElement('html', "<td>$difference %</td>");
                }
                else {
                    // insert empty cell
                    $mform->addElement('html', "<td></td><td></td>");
                }
                
                $mform->addElement('html', '</tr>');
            }
            $rs->close();
            $mform->addElement('html', '</div>');
            $mform->addElement('html', '</table>');   
        }
        else {
            $mform->addElement('html', '<tr><td>AINDA NAO FEZ NENHUMA AVALIACAO DO LOGLA</td></tr>');
            $mform->addElement('html', '</div>');
            $mform->addElement('html', '</table>');
        }
        
        $sql ='SELECT 	
                    AVG(g.prekmagrade) AS avgprekma,
                    AVG(g.poskmagrade) AS avgposkma,
                    AVG(g.prekmbgrade) AS avgprekmb,
                    AVG(g.poskmbgrade) AS avgposkmb
                    FROM mdl_logla_user_grades AS g
                    INNER JOIN mdl_logla AS l ON g.idlogla = l.id
                    INNER JOIN mdl_course_modules AS c ON  c.id = l.coursemodule 
                    WHERE g.userid = ? AND c.course = ?';

        $muavg = $DB->get_record_sql($sql, array($USER->id, $COURSE->id));
        $mform->addElement('html', '<br><br><strong><p>'.get_string('textactivity13', 'logla').$muavg->avgprekma.get_string('textactivity14', 'logla').$muavg->avgprekmb);
        $mform->addElement('html', '<p>'.get_string('textactivity15', 'logla').$muavg->avgposkma.get_string('textactivity16', 'logla').$muavg->avgposkmb.'</strong>');
        
        // add header activity;
        $mform->addElement('header', 'tablekmakmb','Tabela');
       
        $tableKMA = "<div>
                <table>
                    <tr>
                        <th>
                            Valor do KMA
                        </th>
                        <th>
                            Classificação
                        </th>
                        <th>
                            Interpretação
                        </th>
                    </tr>
                    <tr>
                        <td>
                            [-1 , -0,25)
                        </td>
                        <td>
                            KMA Baixo
                        </td>
                        <td>
                            O aluno não estima corretamente seu conhecimento na maioria das situações
                        </td>
                    </tr>
                    <tr>
                        <td>
                            [-0,25 , 0,50)
                        </td>
                        <td>
                            KMA Médio
                        </td>
                        <td>
                            O aluno às vezes calcula corretamente, mas faz estimativas frequentemente erradas ou completamente erradas
                        </td>
                    </tr>
                    <tr>
                        <td>
                            [0,50 , 1]
                        </td>
                        <td>
                            KMA Alto
                        </td>
                        <td>
                            Aluno na maioria das vezes faz estimativa correta de seu conhecimento
                        </td>
                    </tr>
                </table>
            </div>";

        $tableKMB = "<div>
                <table>
                    <tr>
                        <th>
                        Valor do KMB
                        </th>
                        <th>
                            Classificação
                        </th>
                        <th>
                            Interpretação
                        </th>
                    </tr>
                    <tr>
                        <td>
                            KMA Alto
                        </td>
                        <td>
                            Realista
                        </td>
                        <td>
                            O aluno faz uma estimativa precisa de seu conhecimento, tendo um alto KMA
                        </td>
                    </tr>
                    <tr>
                        <td>
                            [0,25 , 1]
                        </td>
                        <td>
                            Otimista
                        </td>
                        <td>
                            O aluno tende a estimar que pode resolver os problemas, mas ele não consegue na maioria das vezes
                        </td>
                    </tr>
                    <tr>
                        <td>
                            [-1 , -0,25]
                        </td>
                        <td>
                            Pessimista
                        </td>
                        <td>
                            Aluno tende a estimar que não pode resolver os problemas, mas depois ele consegue
                        </td>
                    </tr>
                    <tr>
                        <td>
                            (-0,25 , 0,25)
                        </td>
                        <td>
                            Aleatório
                        </td>
                        <td>
                            Estimativas do aluno sobre o seu conhecimento são tão otimistas quanto pessimistas
                        </td>
                    </tr>
                </table>
            </div>";                        

        $mform->addElement('html', '<br><br>'.$tableKMA);            
        $mform->addElement('html', '<br><br>'.$tableKMB);
       
        $mform->closeHeaderBefore('tablekmakmb');

        // add header activity;
        $mform->addElement('header', 'loglafieldset', get_string('header7', 'logla'));

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