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
 * Verbalfeedback items management page.
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

use mod_verbalfeedback\repository\instance_repository;

require_once("../../config.php");

$cmid = required_param('id', PARAM_INT);
$itemid = optional_param('itemid', 0, PARAM_INT);
$makeavailable = optional_param('makeavailable', false, PARAM_BOOL);

$viewurl = new moodle_url('view.php');
$viewurl->param('id', $cmid);

if ($cmid == 0) {
    throw new moodle_exception('errorverbalfeedbacknotfound', 'mod_verbalfeedback', $viewurl);
}

$PAGE->set_url('/mod/verbalfeedback/edit_instance.php', ['id' => $cmid]);

if (!$cm = get_coursemodule_from_id('verbalfeedback', $cmid)) {
    throw new moodle_exception('invalidcoursemodule');
}

if (!$course = $DB->get_record("course", ["id" => $cm->course])) {
    throw new moodle_exception('coursemisconf');
}

require_login($course, true, $cm);

$instancerepository = new instance_repository();

if (!$instance = $instancerepository->get_by_id($cm->instance)) {
    throw new moodle_exception('errorverbalfeedbacknotfound', 'mod_verbalfeedback', $viewurl);
}

if (optional_param('savechanges', false, PARAM_BOOL) && confirm_sesskey()) {
    // If rescaling is required save the new maximum.
    $maxgrade = unformat_float(optional_param('maxgrade', '', PARAM_RAW_TRIMMED), true);
    if (is_float($maxgrade) && $maxgrade >= 0) {
        \mod_verbalfeedback\utils\instance_utils::verbalfeedback_set_grade($maxgrade, $instance);

        verbalfeedback_grade_item_update($instance);
        verbalfeedback_update_grades($instance, 0, true);
    }

    redirect(new moodle_url('/mod/verbalfeedback/edit_instance.php', ['id' => $cmid]));
}

// Check capability to edit items.
$context = context_module::instance($cm->id);
if (!\mod_verbalfeedback\utils\user_utils::can_edit_items($instance->get_id(), $context)) {
    throw new moodle_exception('nocaptoedititems', 'mod_verbalfeedback', $viewurl);
}

$question = '';
$questiontype = 0;
$questioncategory = 0;

$PAGE->navbar->add(get_string('titlemanageitems', 'verbalfeedback'));
$PAGE->set_heading($course->fullname);
$PAGE->set_title($instance->get_name());

echo $OUTPUT->header();
// Print the main part of the page.
echo $OUTPUT->heading(format_string($instance->get_name()));
echo $OUTPUT->heading(get_string('edititems', 'mod_verbalfeedback'), 3);

$viewurl = new moodle_url('/mod/verbalfeedback/view.php', ['id' => $cm->id]);
$previewurl = new moodle_url('/mod/verbalfeedback/questionnaire.php', ['instance' => $instance->get_id(), 'preview' => true]);
$maxgrade = $instance->get_grade();

// Check if we can make the activity available from here.
$instanceready = $instancerepository->is_ready($instance->get_id());
$makeavailableurl = null;
if (!$instanceready) {
    // Check if we can make the instance available to the respondents.
    if ($instancerepository->has_items($instance->get_id())) {
        $makeavailableurl = clone $viewurl;
        $makeavailableurl->param('makeavailable', true);
    }
}

// Verbalfeedback item list.
$itemslist = new mod_verbalfeedback\output\list_verbalfeedback_items(
    $cmid,
    $course->id,
    $instance->get_id(),
    $viewurl,
    $previewurl,
    $makeavailableurl,
    $maxgrade
);
$itemslistoutput = $PAGE->get_renderer('mod_verbalfeedback');
echo $itemslistoutput->render($itemslist);

echo $OUTPUT->footer();
