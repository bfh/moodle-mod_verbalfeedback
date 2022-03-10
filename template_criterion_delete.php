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
 * The main verbalfeedback configuration form.
 *
 * @package     mod_verbalfeedback
 * @copyright   2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_verbalfeedback\forms\template_criterion_delete_form;

use mod_verbalfeedback\model\template\template_criterion;
use mod_verbalfeedback\repository\template_criterion_repository;

require_once(__DIR__ . '/../../config.php');
require_login();

$id = required_param('id', PARAM_INT);

$templatecriterionrepository = new template_criterion_repository();

$pageurl = new moodle_url('/mod/verbalfeedback/template_criterion_delete.php');
if ($id) {
    $pageurl->param('id', $id);
}
$PAGE->set_url($pageurl);
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Verbalfeedback settings');
$PAGE->set_heading(get_string('deletecriterion', 'verbalfeedback'));
$PAGE->set_pagelayout('admin');
$mform = new template_criterion_delete_form();

// Form processing and displaying is done here.
if ($mform->is_cancelled()) {
    // Handle form cancel operation, if cancel button is present on form.
    redirect(new moodle_url('/mod/verbalfeedback/template_criterion_list.php'));
} else if ($formdata = $mform->get_data()) {
    // In this case you process validated data. $mform->get_data() returns data posted in form.
    $templatecriterionrepository->delete($formdata->id);

    redirect(new moodle_url('/mod/verbalfeedback/template_criterion_list.php'));
} else {
    // This branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    // or on the first display of the form.

    $templatecriterion = $templatecriterionrepository->get_by_id($id);

    // Set default data (if any).
    $mform->set_data(to_template_criterion_edit_view_model($templatecriterion));

    // Displays the form.
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}

/**
 * Maps a template criterion according to the requirements by the template_criterion_edit_form.
 *
 * @param template_criterion $templatecriterion The template criterion
 * @return array The model
 */
function to_template_criterion_edit_view_model(template_criterion $templatecriterion) {
    $model = [];
    $model['id'] = $templatecriterion->get_id();
    foreach ($templatecriterion->get_descriptions() as $s) {
        $model['localized_strings'][$s->get_language_id()]['id'] = $s->get_id();
        $model['localized_strings'][$s->get_language_id()]['language_id'] = $s->get_language_id();
        $model['localized_strings'][$s->get_language_id()]['string'] = $s->get_string();
    }
    return $model;
}
