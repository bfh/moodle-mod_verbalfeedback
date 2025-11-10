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
 * The verbalfeedback local library
 *
 * @package     mod_verbalfeedback
 * @copyright   2022 Luca Bösch <luca.boesch@bfh.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_verbalfeedback\repository\language_repository;
use mod_verbalfeedback\model\template\template;
use mod_verbalfeedback\model\template\template_category;
use mod_verbalfeedback\model\template\template_criterion;
use mod_verbalfeedback\model\localized_string;
use mod_verbalfeedback\model\subrating;
use mod_verbalfeedback\model\template\parametrized_template_criterion;
use mod_verbalfeedback\model\template\parametrized_template_category;

/**
 * Maps a template category according to the requirements of the template_category_edit_form.
 *
 * @param template_category $templatecategory The template category
 * @return stdClass The view model
 */
function template_category_to_view_model(template_category $templatecategory) {
    $model = new stdClass();
    $model->id = $templatecategory->get_id();
    $model->unique_name = $templatecategory->get_unique_name();
    $model->headers = [];
    foreach ($templatecategory->get_headers() as $header) {
        $model->headers[$header->get_language_id()]['id'] = $header->get_id();
        $model->headers[$header->get_language_id()]['language_id'] = $header->get_language_id();
        $model->headers[$header->get_language_id()]['string'] = $header->get_string();
    }

    $model->criteria = [];
    foreach ($templatecategory->get_template_criteria() as $parametrizedtemplatecriterion) {
        $propertyname = 'criterion' . $parametrizedtemplatecriterion->get_template_criterion_id();
        $model->{$propertyname} = [];
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
 * @param object $viewmodel The view model
 * @return template_category The template category
 * @throws Exception
 */
function view_model_to_template_category($viewmodel): template_category {
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
            $parametrizedcriterion = new parametrized_template_criterion(
                $value['param_criterion_id'],
                $value['criterion_id'],
                (int)$value['position'],
                $value['weight']
            );
            $templatecategory->add_template_criterion($parametrizedcriterion);
        }
    }
    return $templatecategory;
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

            // Do not continue if language is not maintained!
            if (!is_null($title)) {
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
    }
    return $model;
}

/**
 * Return a template criterion when given a view model
 *
 * @param object $viewmodel The view model
 * @return template_criterion The template criterion
 * @throws Exception
 */
function view_model_to_template_criterion($viewmodel): template_criterion {
    $languagerepository = new language_repository();
    $templatecriterionmodel = new template_criterion();
    if (isset($viewmodel->id)) {
        $templatecriterionmodel->set_id($viewmodel->id);
    }
    if (isset($viewmodel->localized_strings)) {
        foreach ($viewmodel->localized_strings as $localizedstring) {
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

    for ($i = 0; $i < $viewmodel->subrating_repeats; $i++) {
        $isemptysubrating = true;
        $subratingmodel = new subrating();
        $subratingmodel->set_id($viewmodel->subrating_id[$i]);
        if ($subratingmodel->get_id() != 0 || $subratingmodel->get_id() != null) {
            $isemptysubrating = false;
        }

        foreach ($languagerepository->get_all() as $language) {
            $languagestring = $language->get_language();

            $titlemodel = new localized_string(
                $viewmodel->{'subrating_title_' . $languagestring . '_language_id'}[$i],
                $viewmodel->{'subrating_title_' . $languagestring . '_id'}[$i],
                $viewmodel->{'subrating_title_' . $languagestring . '_string'}[$i]
            );

            if ($titlemodel->get_string() != "") {
                $isemptysubrating = false;
            }
            $subratingmodel->add_title($titlemodel);
            $descriptionmodel = new localized_string(
                $viewmodel->{'subrating_description_' . $languagestring . '_language_id'}[$i],
                $viewmodel->{'subrating_description_' . $languagestring . '_id'}[$i],
                $viewmodel->{'subrating_description_' . $languagestring . '_string'}[$i]
            );

            if ($descriptionmodel->get_string() != "") {
                $isemptysubrating = false;
            }
            $subratingmodel->add_description($descriptionmodel);

            $verynegativemodel = new localized_string(
                $viewmodel->{'subrating_verynegative_' . $languagestring . '_language_id'}[$i],
                $viewmodel->{'subrating_verynegative_' . $languagestring . '_id'}[$i],
                $viewmodel->{'subrating_verynegative_' . $languagestring . '_string'}[$i]
            );

            if ($verynegativemodel->get_string() != "") {
                $isemptysubrating = false;
            }
            $subratingmodel->add_verynegative($verynegativemodel);

            $negativemodel = new localized_string(
                $viewmodel->{'subrating_negative_' . $languagestring . '_language_id'}[$i],
                $viewmodel->{'subrating_negative_' . $languagestring . '_id'}[$i],
                $viewmodel->{'subrating_negative_' . $languagestring . '_string'}[$i]
            );

            if ($negativemodel->get_string() != "") {
                $isemptysubrating = false;
            }
            $subratingmodel->add_negative($negativemodel);

            $positivemodel = new localized_string(
                $viewmodel->{'subrating_positive_' . $languagestring . '_language_id'}[$i],
                $viewmodel->{'subrating_positive_' . $languagestring . '_id'}[$i],
                $viewmodel->{'subrating_positive_' . $languagestring . '_string'}[$i]
            );

            if ($positivemodel->get_string() != "") {
                $isemptysubrating = false;
            }
            $subratingmodel->add_positive($positivemodel);

            $verypositivemodel = new localized_string(
                $viewmodel->{'subrating_verypositive_' . $languagestring . '_language_id'}[$i],
                $viewmodel->{'subrating_verypositive_' . $languagestring . '_id'}[$i],
                $viewmodel->{'subrating_verypositive_' . $languagestring . '_string'}[$i]
            );

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

/**
 * Maps a template according to the requirements of the template_edit_form.
 *
 * @param template $template The verbal feedback template.
 * @return stdClass The view model
 */
function template_to_view_model(template $template) {
    $model = new stdClass();
    $model->id = $template->get_id();
    $model->name = $template->get_name();
    $model->description = $template->get_description();
    $model->headers = [];

    $model->categories = [];
    foreach ($template->get_template_categories() as $parametrizedtemplatecategory) {
        $propertyname = 'category' . $parametrizedtemplatecategory->get_template_category_id();
        $model->{$propertyname} = [];
        $model->{$propertyname}['param_category_id'] = $parametrizedtemplatecategory->get_id();
        $model->{$propertyname}['category_id'] = $parametrizedtemplatecategory->get_template_category_id();
        $model->{$propertyname}['selected'] = 1;
        $model->{$propertyname}['position'] = $parametrizedtemplatecategory->get_position();
        $model->{$propertyname}['weight'] = $parametrizedtemplatecategory->get_weight();
    }
    return $model;
}

/**
 * Maps a template according to the requirements of the template_edit_form.
 *
 * @param object $viewmodel The view model
 * @return template The template
 */
function view_model_to_template($viewmodel): template {
    $template = new template();
    $template->set_id($viewmodel->id);
    $template->set_name($viewmodel->name);
    $template->set_description($viewmodel->description);

    foreach ($viewmodel as $key => $value) {
        if (strpos($key, 'category') === 0 && isset($value['selected'])) {
            // Variable $value is a category.
            $parametrizedcategory = new parametrized_template_category(
                $value['param_category_id'],
                $value['template_category_id'],
                (int)$value['position'],
                $value['weight']
            );
            $template->add_template_category($parametrizedcategory);
        }
    }
    return $template;
}
