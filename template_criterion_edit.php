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

use mod_verbalfeedback\forms\template_criterion_edit_form;
use mod_verbalfeedback\model\template\template_criterion;

use mod_verbalfeedback\model\localized_string;
use mod_verbalfeedback\model\subrating;
use mod_verbalfeedback\repository\template_criterion_repository;
use mod_verbalfeedback\repository\language_repository;

require_once(__DIR__ . '/../../config.php');
require_login();

$id = optional_param('id', 0, PARAM_INT);

if ($id == 0) {
    $id = null;
}
$templatecriterionrepository = new template_criterion_repository();

$pageurl = new moodle_url('/mod/verbalfeedback/template_criterion_edit.php');
if ($id) {
    $pageurl->param('id', $id);
}
$PAGE->set_url($pageurl);
$PAGE->set_context(context_system::instance());
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
    redirect(new moodle_url('/mod/verbalfeedback/template_criterion_list.php'));
} else if ($formdata = $mform->get_data()) {

    if ($formdata->id == 0) {
        $formdata->id = null;
    }
    $templatecriterion = view_model_to_template_criterion($formdata);

    $templatecriterionrepository->save($templatecriterion);
    redirect(new moodle_url('/mod/verbalfeedback/template_criterion_list.php'));

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

/**
 * Maps a template criterion according to the requirements by the template_criterion_edit_form.
 *
 * @param template_criterion $templatecriterion The template criterion
 * @return array The model
 */
function template_criterion_to_view_model(template_criterion $templatecriterion) {
    $languagerepository = new language_repository();

    $model = [];
    $model['id'] = $templatecriterion->get_id();
    foreach ($templatecriterion->get_descriptions() as $s) {
        $model['localized_strings'][$s->get_language_id()]['id'] = $s->get_id();
        $model['localized_strings'][$s->get_language_id()]['language_id'] = $s->get_language_id();
        $model['localized_strings'][$s->get_language_id()]['string'] = $s->get_string();
    }

    $subratings = $templatecriterion->get_subratings();
    $model['subrating_repeats'] = count($subratings);
    for ($i = 0; $i < count($subratings); $i++) {
        $model['subrating_id[' . $i . ']'] = $subratings[$i]->get_id();
        foreach ($languagerepository->get_all() as $language) {
            $languagestring = $language->get_language();
            $title = $subratings[$i]->get_title($languagestring);
            $description = $subratings[$i]->get_description($languagestring);
            $verynegative = $subratings[$i]->get_verynegative($languagestring);
            $negative = $subratings[$i]->get_negative($languagestring);
            $positive = $subratings[$i]->get_positive($languagestring);
            $verypositive = $subratings[$i]->get_verypositive($languagestring);

            $model['subrating_title_' . $languagestring . '_language_id[' . $i . ']'] = $title->get_language_id();
            $model['subrating_title_' . $languagestring . '_id[' . $i . ']'] = $title->get_id();
            $model['subrating_title_' . $languagestring . '_string[' . $i . ']'] = $title->get_string();

            $model['subrating_description_' . $languagestring . '_language_id[' . $i . ']'] = $description->get_language_id();
            $model['subrating_description_' . $languagestring . '_id[' . $i . ']'] = $description->get_id();
            $model['subrating_description_' . $languagestring . '_string[' . $i . ']'] = $description->get_string();

            $model['subrating_verynegative_' . $languagestring . '_language_id[' . $i . ']'] = $verynegative->get_language_id();
            $model['subrating_verynegative_' . $languagestring . '_id[' . $i . ']'] = $verynegative->get_id();
            $model['subrating_verynegative_' . $languagestring . '_string[' . $i . ']'] = $verynegative->get_string();

            $model['subrating_negative_' . $languagestring . '_language_id[' . $i . ']'] = $negative->get_language_id();
            $model['subrating_negative_' . $languagestring . '_id[' . $i . ']'] = $negative->get_id();
            $model['subrating_negative_' . $languagestring . '_string[' . $i . ']'] = $negative->get_string();

            $model['subrating_positive_' . $languagestring . '_language_id[' . $i . ']'] = $positive->get_language_id();
            $model['subrating_positive_' . $languagestring . '_id[' . $i . ']'] = $positive->get_id();
            $model['subrating_positive_' . $languagestring . '_string[' . $i . ']'] = $positive->get_string();

            $model['subrating_verypositive_' . $languagestring . '_language_id[' . $i . ']'] = $verypositive->get_language_id();
            $model['subrating_verypositive_' . $languagestring . '_id[' . $i . ']'] = $verypositive->get_id();
            $model['subrating_verypositive_' . $languagestring . '_string[' . $i . ']'] = $verypositive->get_string();
        }
    }
    return $model;
}

/**
 * Return a template criterion when given a model
 *
 * @param object $model The model
 * @return template_criterion The template criterion
 * @throws Exception
 */
function view_model_to_template_criterion($model) : template_criterion {
    $languagerepository = new language_repository();
    $templatecriterionmodel = new template_criterion();
    if (isset($model->id)) {
        $templatecriterionmodel->set_id($model->id);
    }
    if (isset($model->localized_strings)) {
        foreach ($model->localized_strings as $localizedstring) {
            if (!isset($localizedstring['language_id'])) {
                throw new Exception('language_id is required!');
            }
            $localizedstringmodel = new localized_string($localizedstring['language_id']);
            if (isset($localizedstring['id'])) {
                $localizedstringmodel->set_id($localizedstring['id']);
            }
            if (isset($localizedstring['string'])) {
                $localizedstringmodel->set_string($localizedstring['string']);
            }

            $templatecriterionmodel->add_description($localizedstringmodel);
        }
    }

    for ($i = 0; $i < $model->subrating_repeats; $i++) {
        $isemptysubrating = true;
        $subratingmodel = new subrating();
        $subratingmodel->set_id($model->subrating_id[$i]);
        if ($subratingmodel->get_id() != 0 || $subratingmodel->get_id() != null) {
            $isemptysubrating = false;
        }

        foreach ($languagerepository->get_all() as $language) {
            $languagestring = $language->get_language();

            $titlemodel = new localized_string($model->{'subrating_title_' . $languagestring . '_language_id'}[$i],
            $model->{'subrating_title_' . $languagestring . '_id'}[$i],
            $model->{'subrating_title_' . $languagestring . '_string'}[$i]);

            if ($titlemodel->get_string() != "") {
                $isemptysubrating = false;
            }
            $subratingmodel->add_title($titlemodel);
            $descriptionmodel = new localized_string($model->{'subrating_description_' . $languagestring . '_language_id'}[$i],
            $model->{'subrating_description_' . $languagestring . '_id'}[$i],
            $model->{'subrating_description_' . $languagestring . '_string'}[$i]);

            if ($descriptionmodel->get_string() != "") {
                $isemptysubrating = false;
            }
            $subratingmodel->add_description($descriptionmodel);

            $verynegativemodel = new localized_string($model->{'subrating_verynegative_' . $languagestring . '_language_id'}[$i],
            $model->{'subrating_verynegative_' . $languagestring . '_id'}[$i],
            $model->{'subrating_verynegative_' . $languagestring . '_string'}[$i]);

            if ($verynegativemodel->get_string() != "") {
                $isemptysubrating = false;
            }
            $subratingmodel->add_verynegative($verynegativemodel);

            $negativemodel = new localized_string($model->{'subrating_negative_' . $languagestring . '_language_id'}[$i],
            $model->{'subrating_negative_' . $languagestring . '_id'}[$i],
            $model->{'subrating_negative_' . $languagestring . '_string'}[$i]);

            if ($negativemodel->get_string() != "") {
                $isemptysubrating = false;
            }
            $subratingmodel->add_negative($negativemodel);

            $positivemodel = new localized_string($model->{'subrating_positive_' . $languagestring . '_language_id'}[$i],
            $model->{'subrating_positive_' . $languagestring . '_id'}[$i],
            $model->{'subrating_positive_' . $languagestring . '_string'}[$i]);

            if ($positivemodel->get_string() != "") {
                $isemptysubrating = false;
            }
            $subratingmodel->add_positive($positivemodel);

            $verypositivemodel = new localized_string($model->{'subrating_verypositive_' . $languagestring . '_language_id'}[$i],
            $model->{'subrating_verypositive_' . $languagestring . '_id'}[$i],
            $model->{'subrating_verypositive_' . $languagestring . '_string'}[$i]);

            if ($verypositivemodel->get_string() != "") {
                $isemptysubrating = false;
            }
            $subratingmodel->add_verypositive($verypositivemodel);
        }

        if ($isemptysubrating == false) {
            $templatecriterionmodel->add_subrating($subratingmodel);
        }
    }
    return $templatecriterionmodel;
}
