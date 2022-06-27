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
 * The verbalfeedback criterion edit form.
 *
 * @package     mod_verbalfeedback
 * @copyright   2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_verbalfeedback;

use mod_verbalfeedback\forms\template_criterion_edit_form;
use mod_verbalfeedback\model\template\template_criterion;
use mod_verbalfeedback\repository\template_criterion_repository;

require_once(__DIR__ . '/../../config.php');

// Require own locallib.php.
require_once($CFG->dirroot . '/mod/verbalfeedback/locallib.php');

require_login();

$id = optional_param('id', 0, PARAM_INT);

if ($id == 0) {
    $id = null;
}
$templatecriterionrepository = new template_criterion_repository();

$pageurl = new \moodle_url('/mod/verbalfeedback/template_criterion_edit.php');
if ($id) {
    $pageurl->param('id', $id);
}
$PAGE->set_url($pageurl);
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Verbalfeedback settings');
$PAGE->set_heading(get_string('editcriterion', 'verbalfeedback'));
$PAGE->set_pagelayout('admin');

$mform = null;

// Create $mform with the correct number of repeated elements.
if ($id !== null) {
    $criterion = $templatecriterionrepository->get_by_id($id);
    $subratingcount = count($criterion->get_subratings());
    $mform = new template_criterion_edit_form($subratingcount);
} else {
    $mform = new template_criterion_edit_form();
}

// Form processing and displaying is done here.
if ($mform->is_cancelled()) {
    // Handle form cancel operation, if cancel button is present on form.
    redirect(new \moodle_url('/mod/verbalfeedback/template_criterion_list.php'));
} else if ($formdata = $mform->get_data()) {

    if ($formdata->id == 0) {
        $formdata->id = null;
    }
    $templatecriterion = view_model_to_template_criterion($formdata);

    $templatecriterionrepository->save($templatecriterion);
    redirect(new \moodle_url('/mod/verbalfeedback/template_criterion_list.php'));

} else {
    // This branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    // or on the first display of the form.
    $templatecriterion = null;
    if ($id == 0) {
        $templatecriterion = new template_criterion();
    } else {
        $templatecriterion = $templatecriterionrepository->get_by_id($id);

        // A special mapping method is needed to ensure that the localized strings are shown in the correct textarea.
        $viewmodel = template_criterion_to_view_model($templatecriterion);
        $mform->set_data($viewmodel);
    }

    // Displays the form.
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}
