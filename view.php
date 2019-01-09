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

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ... logla instance ID - it should be named as the first character of the module.

if ($id) {
    $cm         = get_coursemodule_from_id('logla', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    // $logla  = $DB->get_record('logla', array('id' => $cm->instance), '*', MUST_EXIST);
    $logla  = 1;
} else if ($n) {
    $logla  = $DB->get_record('logla', array('id' => $n), '*', MUST_EXIST);
    $logla  = 2;
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

// Output starts here.
echo $OUTPUT->header();

// Conditions to show the intro can change to look for own settings or whatever.
if ($logla->intro) {
    echo $OUTPUT->box(format_module_intro('logla', $logla, $cm->id), 'generalbox mod_introbox', 'loglaintro');
}

// Replace the following lines with you own code.


echo $OUTPUT->heading($_POST["selectPreMetacognition"]);

// Replace the following lines with you own code.
$loglaresult = $DB->get_records('logla', array('coursemodule'=>$id));


echo $OUTPUT->heading('Resultados');
echo $OUTPUT->heading('id');
echo $OUTPUT->heading($id);
echo $OUTPUT->heading('Name');
echo $OUTPUT->heading($loglaresult[1]->name);
echo $OUTPUT->heading('Intro');
echo $OUTPUT->heading($loglaresult[1]->intro);
echo $OUTPUT->heading('Pre-Feedback');
echo $OUTPUT->heading($loglaresult[1]->prefeedback);
echo $OUTPUT->heading('Pos-Feeedback');
echo $OUTPUT->heading($loglaresult[1]->posfeedback);
echo $OUTPUT->heading('ID Pre-Feedback');
echo $OUTPUT->heading($loglaresult[1]->idprefeedback);
echo $OUTPUT->heading('ID Pos-Feeedback');
echo $OUTPUT->heading($loglaresult[1]->idposfeedback);
echo $OUTPUT->heading('ID Activity');
echo $OUTPUT->heading($loglaresult[1]->idactivity);
echo $OUTPUT->heading('ID Quiz');
echo $OUTPUT->heading($loglaresult[1]->idquiz);
echo $OUTPUT->heading('Average Pre-Feedback');
echo $OUTPUT->heading($loglaresult[1]->prefeedbackavg);
echo $OUTPUT->heading('Average Pos-Feeedback');
echo $OUTPUT->heading($loglaresult[1]->posfeedbackavg);




// Finish the page.
echo $OUTPUT->footer();
