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

use mod_verbalfeedback\forms\template_category_edit_form;
use mod_verbalfeedback\model\template\template_category;

use mod_verbalfeedback\model\localized_string;
use mod_verbalfeedback\model\template\parametrized_template_criterion;
use mod_verbalfeedback\repository\template_category_repository;

require_once(__DIR__ . '/../../config.php');
require_login();

$id = optional_param('id', 0, PARAM_INT);

if ($id == 0) {
    $id = null;
}

$templatecategoryrepository = new template_category_repository();

$pageurl = new moodle_url('/mod/verbalfeedback/template_category_edit.php');
if ($id) {
    $pageurl->param('id', $id);
}
$PAGE->set_url($pageurl);
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Verbalfeedback settings');
$PAGE->set_heading(get_string('editcategory', 'verbalfeedback'));
$PAGE->set_pagelayout('admin');
$mform = new template_category_edit_form();

// Form processing and displaying is done here.
if ($mform->is_cancelled()) {
    // Handle form cancel operation, if cancel button is present on form.
    redirect(new moodle_url('/mod/verbalfeedback/template_category_list.php'));
} else if ($formdata = $mform->get_data()) {
    // In this case you process validated data. $mform->get_data() returns data posted in form.
    if ($formdata->id == 0) {
        $formdata->id = null;
    }

    $templatecategory = view_model_to_template_category($formdata);
    $templatecategoryrepository->save($templatecategory);

    redirect(new moodle_url('/mod/verbalfeedback/template_category_list.php'));
} else {
    // This branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    // or on the first display of the form.
    $templatecategory = null;
    if ($id == 0) {
        $templatecategory = new template_category();
    } else {
        $templatecategory = $templatecategoryrepository->get_by_id($id);
        $model = template_category_to_view_model($templatecategory);
        $mform->set_data($model);
    }

    // Displays the form.
    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}

/**
 * Maps a template category according to the requirements of the template_category_edit_form.
 *
 * @param template_category $templatecategory The template category
 * @return stdClass The model
 */
function template_category_to_view_model(template_category $templatecategory) {
    $model = new stdClass();
    $model->id = $templatecategory->get_id();
    $model->unique_name = $templatecategory->get_unique_name();
    $model->headers = array();
    foreach ($templatecategory->get_headers() as $header) {
        $model->headers[$header->get_language_id()]['id'] = $header->get_id();
        $model->headers[$header->get_language_id()]['language_id'] = $header->get_language_id();
        $model->headers[$header->get_language_id()]['string'] = $header->get_string();
    }

    $model->criteria = array();
    foreach ($templatecategory->get_template_criteria() as $parametrizedtemplatecriterion) {
        $propertyname = 'criterion' . $parametrizedtemplatecriterion->get_template_criterion_id();
        $model->{$propertyname} = array();
        $model->{$propertyname}['param_criterion_id'] = $parametrizedtemplatecriterion->get_id();
        $model->{$propertyname}['criterion_id'] = $parametrizedtemplatecriterion->get_template_criterion_id();
        $model->{$propertyname}['selected'] = 1;
        $model->{$propertyname}['position'] = $parametrizedtemplatecriterion->get_position();
        $model->{$propertyname}['weight'] = $parametrizedtemplatecriterion->get_weight();
    }
    return $model;
}

/**
 * Maps a template category according to the requirements of the template_category_edit_form.
 *
 * @param object $viewmodel The model
 * @return template_category The template category
 * @throws Exception
 */
function view_model_to_template_category($viewmodel) : template_category {
    $templatecategory = new template_category();
    if ($viewmodel->id == null) {
        $viewmodel->id = 0;
    }
    $templatecategory->set_id($viewmodel->id);
    $templatecategory->set_unique_name($viewmodel->unique_name);
    foreach ($viewmodel->headers as $localizedstringarray) {
        if (!isset($localizedstringarray['language_id'])) {
            throw new Exception('language_id is required!');
        }
        $localizedstring = new localized_string($localizedstringarray['language_id']);
        if (isset($localizedstringarray['id'])) {
            $localizedstring->set_id($localizedstringarray['id']);
        }
        if (isset($localizedstringarray['string'])) {
            $localizedstring->set_string($localizedstringarray['string']);
        }
        $templatecategory->add_header($localizedstring);
    }

    foreach ($viewmodel as $key => $value) {
        if (strpos($key, 'criterion') === 0 && isset($value['selected'])) {
            // Variable $value is a criterion.
            $parametrizedcriterion = new parametrized_template_criterion($value['param_criterion_id'],
            $value['criterion_id'], (int)$value['position'], $value['weight']);
            $templatecategory->add_template_criterion($parametrizedcriterion);
        }
    }
    return $templatecategory;
}
