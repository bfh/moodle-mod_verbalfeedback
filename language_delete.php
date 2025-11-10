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
 * The verbalfeedback language delete script.
 *
 * @package     mod_verbalfeedback
 * @copyright   2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_verbalfeedback\forms\language_delete_form;
use mod_verbalfeedback\repository\language_repository;

require_once(__DIR__ . '/../../config.php');
require_login();

$id = required_param('id', PARAM_INT);

$languagerepository = new language_repository();

$pageurl = new moodle_url('/mod/verbalfeedback/language_delete.php');
if ($id) {
    $pageurl->param('id', $id);
}
$PAGE->set_url($pageurl);
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Verbalfeedback settings');
$PAGE->set_heading(get_string('deletelanguage', 'verbalfeedback'));
$PAGE->set_pagelayout('admin');
$mform = new language_delete_form();

// Form processing and displaying is done here.
if ($mform->is_cancelled()) {
    // Handle form cancel operation, if cancel button is present on form.
    redirect(new moodle_url('/mod/verbalfeedback/language_list.php'));
} else if ($formdata = $mform->get_data()) {
    // In this case you process validated data. $mform->get_data() returns data posted in form.
    $languagerepository->delete_by_id($formdata->id);

    redirect(new moodle_url('/mod/verbalfeedback/language_list.php'));
} else {
    // This branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    // or on the first display of the form.

    $language = $languagerepository->get_by_id($id);

    // Set default data (if any).
    $viewmodel = to_view_model_from_language($language);
    $mform->set_data($viewmodel);

    // Displays the form.
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}

/**
 * Maps a language according to the requirements of the language_delete_form.
 *
 * @param language $language The language
 * @return stdClass The model
 */
function to_view_model_from_language(mod_verbalfeedback\model\language $language) {
    $model = new stdClass();
    $model->id = $language->get_id();
    $model->name = $language->get_language();
    return $model;
}
