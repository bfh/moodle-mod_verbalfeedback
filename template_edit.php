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
 * The main verbal feedback configuration form.
 *
 * @package     mod_verbalfeedback
 * @copyright   2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_verbalfeedback\forms\template_edit_form;
use mod_verbalfeedback\model\template\template;
use mod_verbalfeedback\repository\template_repository;

require_once(__DIR__ . '/../../config.php');

// Require own locallib.php.
require_once($CFG->dirroot . '/mod/verbalfeedback/locallib.php');

require_login();

$id = optional_param('id', 0, PARAM_INT);

if ($id == 0) {
    $id = null;
}

$templaterepository = new template_repository();

$pageurl = new moodle_url('/mod/verbalfeedback/template_edit.php');
if ($id) {
    $pageurl->param('id', $id);
}
$PAGE->set_url($pageurl);
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Verbalfeedback settings');
$PAGE->set_heading(get_string('edittemplate', 'verbalfeedback'));
$PAGE->set_pagelayout('admin');
$mform = new template_edit_form();

// Form processing and displaying is done here.
if ($mform->is_cancelled()) {
    // Handle form cancel operation, if cancel button is present on form.
    redirect(new moodle_url('/mod/verbalfeedback/template_list.php'));
} else if ($formdata = $mform->get_data()) {
    // In this case you process validated data. $mform->get_data() returns data posted in form.
    if ($formdata->id == 0) {
        $formdata->id = 0;
    }

    $template = view_model_to_template($formdata);

    $templaterepository->save($template);
    redirect(new moodle_url('/mod/verbalfeedback/template_list.php'));
} else {
    // This branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    // or on the first display of the form.
    $template = null;
    if ($id == 0) {
        $template = new template();
    } else {
        $template = $templaterepository->get_by_id($id);

        $model = template_to_view_model($template);
        $mform->set_data($model);
    }

    // Display the form.
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}
