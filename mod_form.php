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
 * The main logla configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_logla
 * @copyright  2019 Eduardo Cruz <eduardo.cruz@ufabc.edu.br>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form
 *
 * @package    mod_logla
 * @copyright  2018 Eduardo Cruz <eduardo.cruz@ufabc.edu.br>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class mod_logla_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */

    public function definition() {
        global $CFG,$DB,$COURSE,$PAGE;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('loglaname', 'logla'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'loglaname', 'logla');

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        // Adding the rest of logla settings, spreading all them into this fieldset
        // ... or adding more fieldsets ('header' elements) if needed for better logic.

        //add section header
        $mform->addElement('header', 'loglafieldset', get_string('loglafieldset', 'logla'));

        // get feedback record from database
        $feedback = $DB->get_records('feedback', array('course'=>$COURSE->id)); 
        foreach ($feedback as $record) {
            $fbcombo[$record->id] = $record->name;
        }
        asort($fbcombo);

        // get assign record from database
        $assign = $DB->get_records('assign', array('course'=>$COURSE->id)); 
        foreach ($assign as $record) {
            $assingcombo[$record->id] = $record->name;
        }
        asort($assingcombo);

        // get quiz record from database
        $quiz = $DB->get_records('quiz', array('course'=>$COURSE->id)); 
        foreach ($quiz as $record) {
            $quizcombo[$record->id] = $record->name;
        }
        asort($quizcombo);

        // add pre PreMetacognition checkbox
        $mform->addElement('advcheckbox', 'PreMetacognition', get_string('PreMetacognition', 'logla'), 'Enable Pre-Metacognition', array(0,1)) ;

        // add pre selectPreMetacognition 
        $selectPre = $mform->addElement('select', 'selectPreMetacognition', get_string('PreFeedback', 'logla'), $fbcombo);
        $selectPre->setMultiple(false);
        $mform->disabledIf('selectPreMetacognition', 'PreMetacognition');
        
        
        // add pos PosMetacognition checkbox
        $mform->addElement('advcheckbox', 'PosMetacognition', get_string('PosMetacognition', 'logla'), 'Enable Pos-Metacognition', array(0,1)) ;
        
        // add pre selectPosMetacognition
        $selectPos = $mform->addElement('select', 'selectPosMetacognition', get_string('PosFeedback', 'logla'), $fbcombo);
        $selectPos->setMultiple(false);
        $mform->disabledIf('selectPosMetacognition', 'PosMetacognition');

        // add radiobox activity or quiz
        $radioarray=array();
        $radioarray[] = $mform->createElement('radio', 'selactivityquiz', '', get_string('Activity', 'logla'), 1);
        $radioarray[] = $mform->createElement('radio', 'selactivityquiz', '', get_string('Quiz', 'logla'), 0);
        $mform->addGroup($radioarray, 'radioar', get_string('table1', 'logla'), array(' '), false);
        
        // add assign activity
        $selectActivity = $mform->addElement('select', 'selectActivity', get_string('Activity', 'logla'), $assingcombo);
        $selectActivity->setMultiple(false);  
        $mform->hideIf('selectActivity', 'selactivityquiz', 'neq', 1);
        
        // add quiz activity
        $selectQuiz = $mform->addElement('select', 'selectQuiz', get_string('Quiz', 'logla'), $quizcombo);
        $selectQuiz->setMultiple(false); 
        $mform->hideIf('selectQuiz', 'selactivityquiz', 'neq', 0);

        // add right answer checkbox
        $mform->addElement('advcheckbox', 'rightanswerchk', 'Mostrar resposta correta', 'Mostrar resposta correta', array(0,1)) ;
        
        $mform->addElement('textarea', 'rightanswertxt', get_string('header4', 'logla'), 'wrap="virtual" rows="20" cols="50"');
        $mform->hideIf('rightanswertxt', 'rightanswerchk');
        
        // get course module id
        if ($PAGE->cm) {
            $coursemodule = $PAGE->cm->id; 
            //create an object from logla table
            $loglaresult = $DB->get_record('logla', array('coursemodule'=>$coursemodule));
            
            $mform->setDefault('PreMetacognition', $loglaresult->prefeedback);
            
            // verify if $loglaresult is not empty and set default value from table
            $selectPre->setSelected($loglaresult->idprefeedback);

            // verify if $loglaresult is not empty and set default value from table
            $mform->setDefault('PosMetacognition', $loglaresult->posfeedback);

            // verify if $loglaresult is not empty and set default value from table
            $selectActivity->setSelected($loglaresult->idactivity);    

            $selectQuiz->setSelected($loglaresult->idquiz);
            
            $mform->setDefault('rightanswerchk', $loglaresult->showrightans); 
            
            $mform->setDefault('rightanswertxt', $loglaresult->rightanswer); 

            // verify if $loglaresult is not empty and set default value from table
            $selectPos->setSelected($loglaresult->idposfeedback);

            $mform->setDefault('selactivityquiz', $loglaresult->activityquiz);
        }        
        
        // Add standard grading elements.
        $this->standard_grading_coursemodule_elements();
        
        // Add standard elements, common to all modules.	
        $this->standard_coursemodule_elements();
        
        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }
}