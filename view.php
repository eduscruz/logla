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

// Replace logla with the name of your module and remove this line.

global $COURSE, $USER;

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/view_kma_results_form.php');

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ... logla instance ID - it should be named as the first character of the module.

if ($id) {
    $cm         = get_coursemodule_from_id('logla', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $logla      = $DB->get_record('logla', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $logla      = $DB->get_record('logla', array('id'=>$n),'*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $logla->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('logla', $logla->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}


require_login($course, true, $cm);

$event = \mod_logla\event\course_module_viewed::create(array(
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $logla);
$event->trigger();

// Print the page header.
$PAGE->set_url('/mod/logla/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($logla->name));
$PAGE->set_heading(format_string($course->fullname));

/*
 * Other things you may want to set - remove if not needed.
 * $PAGE->set_cacheable(false);
 * $PAGE->set_focuscontrol('some-html-id');
 * $PAGE->add_body_class('logla-'.$somevar);
 */
$PAGE->set_cacheable(false);

// Output starts here.
echo $OUTPUT->header();

// Conditions to show the intro can change to look for own settings or whatever.
if ($logla->intro) {
    echo $OUTPUT->box(format_module_intro('logla', $logla, $cm->id), 'generalbox mod_introbox', 'loglaintro');
}


// Get data from logla where id equals id instance
$loglaresult = $DB->get_record('logla', array('coursemodule'=>$id));

// $mform = new view_kma_results_form('/mod/logla/process.php');
$mform = new view_kma_results_form();

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
    // $returnurl = '/course/view.php?id='.$id;
    // redirect($returnurl);
    echo $OUTPUT->box('cancel');
} 
else if ($fromform = $mform->get_data()) {
    //In this case you process validated data. $mform->get_data() returns data posted in form.
    echo $OUTPUT->box('pegou dados');
} else if ($mform->is_submitted()) {
    // In the simplest case just redirect to the view page.
    echo $OUTPUT->box('submitido');
} else {
    // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    // or on the first display of the form.
    $mform->display();
}






// Finish the page.
echo $OUTPUT->footer();

function logla_basic_information(stdClass $logla, $id){
    // basic information about logla
    global $DB, $OUTPUT;
	$loglaresult = $DB->get_record('logla', array('coursemodule'=>$id));
    $information ='<br><br>Information about this logla instance';
    $information .="<br><br>Id: ";
    $information .=$id;
    $information .='<br>Name: ';
    $information .=$loglaresult->name;
    $information .='<br>Intro: ';
    $information .=$loglaresult->intro;
    $information .='<br>Pre-Feedback: ';
    $information .=$loglaresult->prefeedback;
    $information .='<br>Pos-Feeedback: ';
    $information .=$loglaresult->posfeedback;
    $information .='<br>ID Pre-Feedback: ';
    $information .=$loglaresult->idprefeedback;
    $information .='<br>ID Pos-Feeedback: ';
    $information .=$loglaresult->idposfeedback;
    $information .='<br>Activity or Quiz: ';
    $information .=$loglaresult->activityquiz;
    $information .='<br>ID Activity: ';
    $information .=$loglaresult->idactivity;
    $information .='<br>ID Quiz: ';
    $information .=$loglaresult->idquiz;
    $information .='<br>Average Pre-Feedback: ';
    $information .=$loglaresult->prefbkmaavg;
    $information .='<br>Average Pos-Feeedback: ';
    $information .=$loglaresult->posfbkmaavg;

    return $information;
}
