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
 * The first page to view the verbal feedback.
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_verbalfeedback\model\submission;
use mod_verbalfeedback\model\submission_status;
use mod_verbalfeedback\output\questionnaire;
use mod_verbalfeedback\repository\instance_repository;
use mod_verbalfeedback\repository\submission_repository;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/weblib.php');
require_once($CFG->libdir . '/editorlib.php');

// The verbal feedback record id.
$id = required_param('instance', PARAM_INT);
$submissionid = optional_param('submission', 0, PARAM_INT);
$preview = optional_param('preview', false, PARAM_BOOL);

if ($submissionid == 0 && !$preview) {
    \core\notification::error('If preview is not set, param \'submissionid\' is required.');
}

list ($course, $cm) = get_course_and_cm_from_instance($id, 'verbalfeedback');

require_login($course, true, $cm);

// Quick hack for issue #43.
ini_set('memory_limit', '256M');

$context = context_module::instance($cm->id);
$instancerepository = new instance_repository();
$submissionrepository = new submission_repository();

$submission = null;
$instance = null;
if ($preview) {
    if (!$instance = $instancerepository->get_by_id($cm->instance)) {
        throw new moodle_exception('errorverbalfeedbacknotfound', 'mod_verbalfeedback', $viewurl);
    }
} else {
    $submission = $submissionrepository->get_by_id($submissionid);
    $instance = $instancerepository->get_by_id($submission->get_instance_id());
}
$PAGE->set_context($context);
$PAGE->set_cm($cm, $course);
$PAGE->set_pagelayout('incourse');

$PAGE->set_url('/mod/verbalfeedback/view.php', ['id' => $cm->id]);
$PAGE->set_heading($course->fullname);
$title = format_string($instance->get_name());
$PAGE->set_title($title);

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($title));

if ($preview) {
    $edititemsurl = new moodle_url('/mod/verbalfeedback/edit_instance.php', ['id' => $cm->id]);
    $previewinfo = get_string('previewinfo', 'verbalfeedback', $edititemsurl->out());
    \core\notification::info($previewinfo);
    $userheading = [
        'heading' => 'Max Muster',
        'user' => null,
        'usercontext' => null,
    ];
    $contextheader = $OUTPUT->context_header($userheading, 3);
    $container = html_writer::div($contextheader, 'card-body');
    echo html_writer::div($container, 'card');

    $submission = new submission();
    $submission->set_status(submission_status::IN_PROGRESS);
    $submission->set_instance_id($instance->get_id());
    $submission->set_from_user_id(0); // A non-existing user.
    $questionnairedata = new questionnaire($context->id, $submission, true);

    // Iterate and drop criteria with weight 0.
    // First, let's filter our set of criteria inside the categories.

    foreach ($questionnairedata->categories as $category) {
        $filteredcriteria = array_filter($category->criteria, function($criterion) {
            // Adding our criteria for a valid category.
            return
                property_exists($criterion, 'weight') // The property weight exists.
                && $criterion->weight != "0.00"; // And it's not '0.00'.
        });
        // If you want to keep the index of the entry just remove the next line.
        $filteredcriteria = array_values($filteredcriteria);
        // Overwrite the original criteria with the filtered set.
        $category->criteria = $filteredcriteria;
    }

    // Iterate and drop categories with weight 0.
    // Then, let's filter our set of categories.

    $filteredcategories = array_filter($questionnairedata->categories, function($category) {
        // Adding our criteria for a valid category.
        return
            property_exists($category, 'weight') // The property weight exists.
            && $category->weight != "0.00"; // And it's not '0.00'.
    });

    // If you want to keep the index of the entry just remove the next line.
    $filteredcategories = array_values($filteredcategories);
    // Overwrite the original categories with the filtered set.
    $questionnairedata->categories = $filteredcategories;

    $renderer = $PAGE->get_renderer('mod_verbalfeedback');

    echo $renderer->render($questionnairedata);
    add_editors_for_comments($questionnairedata, $context);
} else {
    // Check if instance is already open.
    $openmessage = $instance->is_open(true);
    $isready = $instance->is_ready();
    if ($isready && $openmessage === true) {
        // Render user heading.
        if ($submission->get_to_user_id() > 0) {
            $touser = core_user::get_user($submission->get_to_user_id());
            $userheading = [
                'heading' => fullname($touser),
                'user' => $touser,
                'usercontext' => context_user::instance($submission->get_to_user_id()),
            ];

            $contextheader = $OUTPUT->context_header($userheading, 3);
            $container = html_writer::div($contextheader, 'card-body');
            echo html_writer::div($container, 'card');
        }

        // Set status to in progress if pending.
        if ($submission->get_status() == submission_status::PENDING) {
            $submission->set_status(submission_status::PENDING);
            $submissionrepository->save($submission);
        }

        // Verbalfeedback question list.
        $questionnairedata = new questionnaire($context->id, $submission);


        // Iterate and drop criteria with weight 0.
        // First, let's filter our set of criteria inside the categories.

        foreach ($questionnairedata->categories as $category) {
            $filteredcriteria = array_filter($category->criteria, function($criterion) {
                // Adding our criteria for a valid category.
                return
                    property_exists($criterion, 'weight') // The property weight exists.
                    && $criterion->weight != "0.00"; // And it's not '0.00'.
            });
            // If you want to keep the index of the entry just remove the next line.
            $filteredcriteria = array_values($filteredcriteria);
            // Overwrite the original criteria with the filtered set.
            $category->criteria = $filteredcriteria;
        }

        // Iterate and drop categories with weight 0.
        // Then, let's filter our set of categories.

        $filteredcategories = array_filter($questionnairedata->categories, function($category) {
            // Adding our criteria for a valid category.
            return
                property_exists($category, 'weight') // The property weight exists.
                && $category->weight != "0.00"; // And it's not '0.00'.
        });

        // If you want to keep the index of the entry just remove the next line.
        $filteredcategories = array_values($filteredcategories);
        // Overwrite the original categories with the filtered set.
        $questionnairedata->categories = $filteredcategories;

        $renderer = $PAGE->get_renderer('mod_verbalfeedback');
        echo $renderer->render($questionnairedata);

        add_editors_for_comments($questionnairedata, $context);

    } else {
        if ($isready) {
            $message = get_string('instancenotready', 'mod_verbalfeedback');
        } else {
            $message = $openmessage;
        }
        \core\notification::error($message);
        $viewurl = new moodle_url('/mod/verbalfeedback/view.php', ['id' => $cm->id]);
        echo html_writer::link($viewurl, get_string('backtoverbalfeedbackdashboard', 'mod_verbalfeedback'));
    }
}

echo $OUTPUT->footer();

/**
 * Adds an atto editor field for comments
 *
 * @param array $questionnairedata The questionnaire data
 * @param object $context The context
 */
function add_editors_for_comments($questionnairedata, $context) {
    $fromuserid = "";
    $touserid = "";

    if (isset($questionnairedata->fromuserid)) {
        $fromuserid = $questionnairedata->fromuserid;
    }
    if (isset($questionnairedata->touserid)) {
        $touserid = $questionnairedata->touserid;
    }

    foreach ($questionnairedata->categories as $category) {
        foreach ($category->criteria as $criteria) {
            $criteriaid = $criteria->id;

            $studentcommentelementid = "student-comment-{$criteriaid}-{$fromuserid}-{$touserid}";
            $editor = editors_get_preferred_editor();
            $editor->use_editor($studentcommentelementid, ['context' => $context, 'autosave' => false]);

            $privatecommentelementid = "private-comment-{$criteriaid}-{$fromuserid}-{$touserid}";
            $editor = editors_get_preferred_editor();
            $editor->use_editor($privatecommentelementid, ['context' => $context, 'autosave' => false]);
        }
    }

}
