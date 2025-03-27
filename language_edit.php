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

use mod_verbalfeedback\forms\language_edit_form;
use mod_verbalfeedback\repository\language_repository;
use mod_verbalfeedback\model\language;

require_once(__DIR__ . '/../../config.php');

require_login();

$id = optional_param('id', 0, PARAM_INT);

if ($id == 0) {
    $id = null;
}

$languagerepository = new language_repository();

$pageurl = new moodle_url('/mod/verbalfeedback/language_edit.php');
if ($id) {
    $pageurl->param('id', $id);
}
$PAGE->set_url($pageurl);
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Verbalfeedback settings');
$PAGE->set_heading(get_string('editlanguage', 'verbalfeedback'));
$PAGE->set_pagelayout('admin');
$mform = new language_edit_form();

// Form processing and displaying is done here.
if ($mform->is_cancelled()) {
    // Handle form cancel operation, if cancel button is present on form.
    redirect(new moodle_url('/mod/verbalfeedback/language_list.php'));
} else if ($formdata = $mform->get_data()) {
    // In this case you process validated data. $mform->get_data() returns data posted in form.

    if ($formdata->id == 0) {
        $formdata->id = null;
    }

    $language = view_model_to_language($formdata);
    $languagerepository->save($language);

    redirect(new moodle_url('/mod/verbalfeedback/language_list.php'));
} else {
    // This branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    // or on the first display of the form.
    $language = null;
    if ($id == 0) {
        $language = new language(null);
    } else {
        $language = $languagerepository->get_by_id($id);
    }

    // Set default data (if any).
    $mform->set_data(language_to_view_model($language));

    // Display the form.
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}

/**
 * Maps a language according to the requirements of the language_edit_form.
 *
 * @param language $language The language object
 * @return stdClass The language model
 */
function language_to_view_model(language $language) {
    $object = new stdClass();
    $object->id = $language->get_id();
    $object->language = $language->get_language();
    return $object;
}

/**
 * Maps the template_category_edit_form data to a language model.
 *
 * @param object $viewmodel The language model
 * @return language The language object
 */
function view_model_to_language($viewmodel): language {
    $language = new language();
    if (isset($viewmodel->id)) {
        $language->set_id($viewmodel->id);
    }
    if (isset($viewmodel->language)) {
        $language->set_language($viewmodel->language);
    }

    return $language;
}
