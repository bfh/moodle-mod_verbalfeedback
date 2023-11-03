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
 * Class for performing DB actions for the verbal feedback activity module.
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_verbalfeedback;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once('./mod/verbalfeedback/classes/vendor/autoload.php');

use Dallgoot\Yaml;

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

/**
 * A PHPunit test class to test data import
 */
class import_data_test extends \advanced_testcase {

    /**
     * Tests data import
     *
     * @covers \mod_verbalfeedback\repository\language_repository::save
     * @covers \mod_verbalfeedback\repository\template_category_repository::save
     * @covers \mod_verbalfeedback\repository\template_criterion_repository::save
     * @covers \mod_verbalfeedback\repository\template_repository::save
     * @throws dml_transaction_exception
     */
    public function test_import(): void {
        $this->resetAfterTest(true);

        $langrepo = new language_repository();
        $categoryrepo = new template_category_repository();
        $criterionrepo = new template_criterion_repository();
        $templaterepo = new template_repository();

        // Test dallgoot/yaml.
        $importdata = Yaml\Yaml::parseFile('./mod/verbalfeedback/db/default.yaml');
        foreach ($importdata->languages as $yamllang) {
            if ($yamllang->id == null) {
                $lang = new language(null, $yamllang->language);
                $id = $langrepo->save($lang);
                $yamllang->id = $id;
            } else {
                $lang = new language($yamllang->id, $yamllang->language);
                $langrepo->save($lang);
            }
        }

        foreach ($importdata->criteria as $yamlcriteria) {
            $criterion = new template_criterion((int)$yamlcriteria->id);
            foreach ($yamlcriteria->texts as $text) {
                $localstring = new localized_string($text->language->id, 0, $text->text);
                $criterion->add_description($localstring);
            }

            if ($criterion->get_id() == null) {
                $id = $criterionrepo->save($criterion);
                $yamlcriteria->id = $id;
            } else {
                $criterionrepo->save($criterion);
            }
        }

        foreach ($importdata->categories as $yamlcategory) {
            $category = new template_category((int)$yamlcategory->id, $yamlcategory->unique_name);
            foreach ($yamlcategory->headers as $arrheaders) {
                $header = new localized_string($arrheaders->language->id, 0, $arrheaders->text);
                $category->add_header($header);
            }

            foreach ($yamlcategory->criteria as $criterion) {
                if (isset($criterion->id)) {
                    $criteria = new parametrized_template_criterion(0, $criterion->id, $criterion->position, $criterion->weight);
                } else {
                    $criteria = new parametrized_template_criterion(0, 0, $criterion->position, $criterion->weight);
                }
                $category->add_template_criterion($criteria);
            }

            if ($category->get_id() == null) {
                $id = $categoryrepo->save($category);
                $yamlcategory->id = $id;
            } else {
                $categoryrepo->save($category);
            }
        }

        foreach ($importdata->templates as $yamltemplate) {
            if (isset($yamltemplate->id)) {
                $template = new template($yamltemplate->id, $yamltemplate->name, $yamltemplate->description);
            } else {
                $template = new template(0, $yamltemplate->name, $yamltemplate->description);
            }
            foreach ($yamltemplate->categories as $arrcategory) {
                if (isset($arrcategory->id)) {
                    $category = new parametrized_template_category(0, $arrcategory->id, $arrcategory->position,
                        $arrcategory->weight);
                } else {
                    $category = new parametrized_template_category(0, 0, $arrcategory->position,
                        $arrcategory->weight);
                }
                $template->add_template_category($category);
            }

            if ($template->get_id() == null) {
                $id = $templaterepo->save($template);
                $yamltemplate->id = $id;
            } else {
                $templaterepo->save($template);
            }
        }
    }
}
