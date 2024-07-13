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
 * Code to be executed after the plugin's database scheme has been installed is defined here.
 *
 * @package     mod_verbalfeedback
 * @category    upgrade
 * @copyright   2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/verbalfeedback/db/upgradelib.php');

use mod_verbalfeedback\model\language;
use mod_verbalfeedback\model\localized_string;
use mod_verbalfeedback\model\template\parametrized_template_category;
use mod_verbalfeedback\model\template\parametrized_template_criterion;
use mod_verbalfeedback\model\template\template;
use mod_verbalfeedback\model\template\template_category;
use mod_verbalfeedback\model\template\template_criterion;
use mod_verbalfeedback\repository\language_repository;
use mod_verbalfeedback\repository\template_category_repository;
use mod_verbalfeedback\repository\template_criterion_repository;
use mod_verbalfeedback\repository\template_repository;
use mod_verbalfeedback\model\subrating;
use mod_verbalfeedback\helper;

/**
 * Install the plugin.
 * @return bool
 */
function xmldb_verbalfeedback_install() {
    global $CFG;
    $yamlpath = $CFG->dirroot . '/mod/verbalfeedback/db/default.yaml';
    if (mod_verbalfeedback_ends_with(getcwd(), 'admin/tool/phpunit/cli')) {
        // Do not execute install.php for phpunit tests.
        return true;
    }

    $languagerepository = new language_repository();
    $criteriarepository = new template_criterion_repository();
    $categoryrepository = new template_category_repository();
    $templaterepository = new template_repository();

    $importdata = helper::parseyamlfile($yamlpath);
    foreach ($importdata->languages as $yamllang) {
        if ($yamllang->id == null) {
            $lang = new language(0, $yamllang->language);
            $id = $languagerepository->save($lang);
            $yamllang->id = $id;
        } else {
            $lang = new language($yamllang->id, $yamllang->language);
            $languagerepository->save($lang);
        }
    }

    foreach ($importdata->criteria as $yamlcriterion) {
        $yamlcriterion->id = mod_verbalfeedback_replace_null_with_zero($yamlcriterion->id);
        $criterion = new template_criterion($yamlcriterion->id);
        foreach ($yamlcriterion->texts as $text) {
            $localizedstring = new localized_string($text->language->id, 0, $text->text);
            $criterion->add_description($localizedstring);
        }
        if (isset($yamlcriterion->subratings)) {
            foreach ($yamlcriterion->subratings as $yamlsubrating) {
                $yamlsubrating->id = mod_verbalfeedback_replace_null_with_zero($yamlsubrating->id);
                $subrating = new subrating($yamlsubrating->id);
                foreach ($yamlsubrating->title as $title) {
                    $localizedstring = new localized_string($title->language->id, 0, $title->text);
                    $subrating->add_title($localizedstring);
                }
                foreach ($yamlsubrating->description as $description) {
                    $localizedstring = new localized_string($description->language->id, 0, $description->text);
                    $subrating->add_description($localizedstring);
                }
                foreach ($yamlsubrating->verynegative as $verynegative) {
                    $localizedstring = new localized_string($verynegative->language->id, 0, $verynegative->text);
                    $subrating->add_verynegative($localizedstring);
                }
                foreach ($yamlsubrating->negative as $negative) {
                    $localizedstring = new localized_string($negative->language->id, 0, $negative->text);
                    $subrating->add_negative($localizedstring);
                }
                foreach ($yamlsubrating->positive as $positive) {
                    $localizedstring = new localized_string($positive->language->id, 0, $positive->text);
                    $subrating->add_positive($localizedstring);
                }
                foreach ($yamlsubrating->verypositive as $verypositive) {
                    $localizedstring = new localized_string($verypositive->language->id, 0, $verypositive->text);
                    $subrating->add_verypositive($localizedstring);
                }

                $criterion->add_subrating($subrating);
            }
        }

        if ($criterion->get_id() == 0) {
            $id = $criteriarepository->save($criterion);
            $yamlcriterion->id = $id;
        } else {
            $criteriarepository->save($criterion);
        }
    }

    foreach ($importdata->categories as $yamlcategory) {
        $yamlcategory->id = mod_verbalfeedback_replace_null_with_zero($yamlcategory->id);
        $category = new template_category($yamlcategory->id, $yamlcategory->unique_name);
        foreach ($yamlcategory->headers as $headers) {
            $header = new localized_string($headers->language->id, 0, $headers->text);
            $category->add_header($header);
        }

        foreach ($yamlcategory->criteria as $criterion) {
            // Use of $criterion->criterion because '<<' does not work with this yaml parser.
            $parametrizedcriterion = new parametrized_template_criterion(0, $criterion->criterion->id, $criterion->position,
                $criterion->weight);
            $category->add_template_criterion($parametrizedcriterion);
        }

        if ($category->get_id() == 0) {
            $id = $categoryrepository->save($category);
            $yamlcategory->id = $id;
        } else {
            $categoryrepository->save($category);
        }
    }

    foreach ($importdata->templates as $yamltemplate) {
        $yamltemplate->id = mod_verbalfeedback_replace_null_with_zero($yamltemplate->id);
        $template = new template($yamltemplate->id, $yamltemplate->name, $yamltemplate->description);
        foreach ($yamltemplate->categories as $category) {
            // Use of $category->category->id because '<<' does not work with this yaml parser.
            $parametrizedcategory = new parametrized_template_category(0, $category->category->id, $category->position,
                $category->weight);
            $template->add_template_category($parametrizedcategory);
        }

        if ($template->get_id() == 0) {
            $id = $templaterepository->save($template);
            $yamltemplate->id = $id;
        } else {
            $templaterepository->save($template);
        }
    }

    return true;
}
