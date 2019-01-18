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

global $COURSE, $USER;

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ... logla instance ID - it should be named as the first character of the module.

if ($id) {
    $cm         = get_coursemodule_from_id('logla', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $logla  = $DB->get_record('logla', array('id' => $cm->instance), '*', MUST_EXIST);
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

// Output starts here.
echo $OUTPUT->header();

// Conditions to show the intro can change to look for own settings or whatever.
if ($logla->intro) {
    echo $OUTPUT->box(format_module_intro('logla', $logla, $cm->id), 'generalbox mod_introbox', 'loglaintro');
}

// Replace the following lines with you own code.
$loglaresult = $DB->get_record('logla', array('coursemodule'=>$id));


// logla_basic_information($logla);

// if user can edit logla then show all results
if ($PAGE->user_allowed_editing()) {

    echo $OUTPUT->heading('Userid, PreKMA, PosKMA');
    $rs = $DB->get_recordset('logla_user_grades', array('idlogla'=>$loglaresult->id));
    foreach ($rs as $record) {
        $texto = $record->userid;
        $texto .= ", ";
        $texto .= $record->pregrade;
        $texto .= ", ";
        $texto .= $record->posgrade; 
        echo $OUTPUT->heading($texto);
    }
    $rs->close();
} else {
    // if user can not edit logla then show only own result
    $user_grade_result = $DB->get_record('logla_user_grades', array('idlogla'=>$loglaresult->id, 'userid'=>$USER->id));
    echo $OUTPUT->heading('Userid, PreKMA, PosKMA');
    $texto = $user_grade_result->userid;
    $texto .= ", ";
    $texto .= $user_grade_result->pregrade;
    $texto .= ", ";
    $texto .= $user_grade_result->posgrade; 
    echo $OUTPUT->heading($texto);
}

// Finish the page.
echo $OUTPUT->footer();
