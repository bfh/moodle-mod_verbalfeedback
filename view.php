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
 * Prints an instance of mod_verbalfeedback.
 *
 * @package     mod_verbalfeedback
 * @copyright   2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_verbalfeedback\model\instance;
use mod_verbalfeedback\repository\instance_repository;
use mod_verbalfeedback\utils\instance_utils;
use mod_verbalfeedback\utils\user_utils;

require_once(__DIR__ . '/../../config.php');
require_once('lib.php');

$id = required_param('id', PARAM_INT);
$makeavailable = optional_param('makeavailable', false, PARAM_BOOL);
$release = optional_param('release', -1, PARAM_INT);
list ($course, $cm) = get_course_and_cm_from_cmid($id, 'verbalfeedback');

require_login($course, true, $cm);

$instancerepository = new instance_repository();

// Quick hack for issue #43.
ini_set('memory_limit', '256M');

$context = context_module::instance($cm->id);

$instance = $instancerepository->get_by_id($cm->instance);
$PAGE->set_context($context);
$PAGE->set_cm($cm, $course);
$PAGE->set_pagelayout('incourse');

// If we got this far, we can consider the activity "viewed".
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$PAGE->set_url('/mod/verbalfeedback/view.php', ['id' => $cm->id]);
$title = format_string($instance->get_name());
$PAGE->set_title($title);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();

$canparticipate = user_utils::can_participate($instance, $USER->id);
$canrespond = user_utils::can_respond($instance, $USER->id);
$canviewallreports = user_utils::can_view_all_reports($context, $USER->id);

if (is_string($canparticipate)) {
    echo $OUTPUT->heading(get_string('students'), 3);
    \core\notification::warning($canparticipate);
    echo $OUTPUT->footer();
    return;
}

if ($canparticipate === true && ($canrespond === true || $canviewallreports === true)) {
    echo $OUTPUT->heading(get_string('students'), 3);
}

if ($release != -1) {
    // Toggle the released flag.
    \mod_verbalfeedback\api::toggle_released_flag($instance, $release);
}

// Edit items.
$instanceready = $instancerepository->is_ready($instance->get_id());
$canedit = user_utils::can_edit_items($instance, $context);
$hideallparticipants = !has_capability('moodle/site:accessallgroups', $context);
echo $OUTPUT->box(groups_print_activity_menu($cm, $PAGE->url, true, $hideallparticipants));

if ($canedit) {
    $edititemsurl = new moodle_url('edit_instance.php');
    $edititemsurl->param('id', $cm->id);
    echo html_writer::link($edititemsurl, get_string('edititems', 'verbalfeedback'),
        ['class' => 'btn btn-primary mr-1']);
}

// If the user has edit capabilities and the instance is not ready, create the "make available"
// button or show a warning that the instance has no criteria yet.
if ($canedit && !$instanceready && !$makeavailable) {
    // Check if we can make the instance available to the respondents.
    if ($instancerepository->has_items($instance->get_id())) {
        $url = $PAGE->url;
        $url->param('makeavailable', true);
        echo html_writer::link($url, get_string('makeavailable'),
            ['class' => 'btn btn-secondary pull-right']);
    } else {
        \core\notification::warning(get_string('noitemsyet', 'mod_verbalfeedback'));
    }
}

// Process "make available" button click event.
if ($canedit && !$instanceready && $makeavailable) {
    if ($instancerepository->has_items($instance->get_id())) {
        if (instance_utils::make_ready($instance)) {
            \core\notification::success(get_string('instancenowready', 'mod_verbalfeedback'));
            // Instance is now ready once made available.
            $instanceready = true;
        }
    }
}

// If instance is ready, render participant list.
if ($instanceready) {
    // Whether to include self in the participants list.
    $includeself = false;
    if (($canrespond === true) || (has_capability('moodle/site:config', $context))) {
        try {
            $participantslistrenderer = $PAGE->get_renderer('mod_verbalfeedback');
            draw_participants_list($instance, $USER->id, $canparticipate, $canviewallreports, $participantslistrenderer);
        } catch (moodle_exception $e) {
            \core\notification::error($e->getMessage());
        }
    } else {
        // User can not respond.
        if (user_utils::can_view_own_report($instance, $context)) {
            draw_view_own_report_button($instance->get_id(), $USER->id);
        } else {
            // Results are not yet released to students.
            \core\notification::error(get_string('instancenotyetopen', 'mod_verbalfeedback'));
        }
    }
} else {
    // Show error to respondents that indicate that the activity is not yet ready.
    if ($canparticipate === true) {
        if (($canrespond === true) && ($canedit === true)) {
            // A person who can grade, e.g. a teacher.
            \core\notification::error(get_string('instancenotready', 'mod_verbalfeedback'));
        } else {
            // A person who can grade, e.g. a student.
            \core\notification::error(get_string('instancenotreadystudents', 'mod_verbalfeedback'));
        }
    }
}

echo $OUTPUT->footer();

/**
 * Draws the button for students to view their own report.
 *
 * @param int $instanceid The verbal feedback instance id.
 * @param int $userid The user id
 * @throws coding_exception
 */
function draw_view_own_report_button($instanceid, $userid) {
    $reportsurl = new moodle_url('/mod/verbalfeedback/report.php');
    $reportsurl->params([
        'instance' => $instanceid,
        'touser' => $userid,
    ]);

    $feedbackreport = html_writer::link($reportsurl, get_string('viewfeedbackreport', 'verbalfeedback'),
        ['class' => 'btn btn-secondary']);
    echo html_writer::div($feedbackreport, 'text-right');
};


/**
 * Draws the participants list on view.php.
 *
 * @param instance $instance The verbal feedback activity instance.
 * @param int $currentuserid The user ID.
 * @param bool $canparticipate Whether the user can participate in this activity or not.
 * @param bool $canviewallreports Whether the user can view the reports of all enrolled users within the activity.
 * @param mixed $participantslistrenderer The renderer for the participants list.
 */
function draw_participants_list(instance $instance, $currentuserid, $canparticipate,
$canviewallreports, $participantslistrenderer) {

    // Generate statuses if you can respond to the feedback.
    \mod_verbalfeedback\api::generate_verbalfeedback_feedback_states($instance->get_id(), $currentuserid);
    // Check if instance is already open.
    $isopen = $instance->is_open(true);
    if ($isopen !== true) {
        // Show warning.
        \core\notification::warning($isopen);
        // Ensure that $isopen is a boolean. is_open() can return a string in some cases.
        $isopen = false;
    }
    $participants = \mod_verbalfeedback\api::get_participants($instance->get_id(), $currentuserid);

    // Verbalfeedback To-do list.
    if ($canparticipate === true) {
        $participantslist =
            new mod_verbalfeedback\output\list_participants($instance->get_id(), $currentuserid, $participants, $canviewallreports,
                $isopen);
        echo $participantslistrenderer->render($participantslist);
    }
}
