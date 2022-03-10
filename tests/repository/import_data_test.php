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
class mod_verbalfeedback_import_data_test extends advanced_testcase {

    /**
     * Tests data import
     *
     * @throws dml_transaction_exception
     */
    public function test_import(): void {
        global $DB;
        $this->resetAfterTest(true);

        // $language_import = function($value, $tag, $flags) {
        //   $lang_repo = new language_repository();
        //   if($value['id'] == null) {
        //     $lang = new language(null, $value['language']);
        //     $id = $lang_repo->save($lang);
        //     $value['id'] = $id;
        //   } else {
        //     $lang = new language($value['id'], $value['language']);
        //     $lang_repo->save($lang);
        //   }
        //   return $value;
        // };

        // $criterion_import = function($value, $tag, $flags) {
        //   $criterion_repo = new template_criterion_repository();
        //   $criterion = new template_criterion($value['id']);
        //   foreach($value['texts'] as $text) {
        //     $local_string = new localized_string($text['language']['id'], 0, $text['text']);
        //     $criterion->add_description($local_string);
        //   }

        //   if($criterion->get_id() == null) {
        //     $id = $criterion_repo->save($criterion);
        //     $value['id'] = $id;
        //   } else {
        //     $criterion_repo->save($criterion);
        //   }
        //   return $value;
        // };

        // $category_import = function($value, $tag, $flags) {
        //   $category_repo = new template_category_repository();
        //   $category = new template_category($value['id'], $value['unique_name']);
        //   foreach($value['headers'] as $arr_headers) {
        //     $header = new localized_string($arr_headers['language']['id'], 0, $arr_headers['text']);
        //     $category->add_header($header);
        //   }

        //   foreach($value['criteria'] as $arr_criteria) {
        //     var_dump($arr_criteria);
        //     $criteria = new parametrized_template_criterion(0, $arr_criteria['id'], $arr_criteria['position'], $arr_criteria['weight']);
        //     $category->add_template_criterion($criteria);
        //   }

        //   if($category->get_id() == null) {
        //     $id = $category_repo->save($category);
        //     $value['id'] = $id;
        //   } else {
        //     $category_repo->save($category);
        //   }
        //   return $value;
        // };

        // $template_import = function($value, $tag, $flags) {
        //   $template_repo = new template_repository();
        //   $template = new template($value['id'], $value['name'], $value['description']);
        //   foreach($value['categories'] as $arr_category) {
        //     $category = new parametrized_template_category(0, $arr_category['id'], $arr_category['position'], $arr_category['weight']);
        //     $template->add_template_category($category);
        //   }

        //   if($template->get_id() == null) {
        //     $id = $template_repo->save($template);
        //     $value['id'] = $id;
        //   } else {
        //     $template_repo->save($template);
        //   }
        //   return $value;
        // };

        // $ndocs = 0;
        // yaml.so
        // $import_data = yaml_parse_file('./mod/verbalfeedback/db/default.yaml', 0, $ndocs, array('!language' => $language_import, '!criterion' => $criterion_import, '!category' => $category_import, '!template' => $template_import));

        // Symfony/YAML
        // $value = Yaml::parseFile('./mod/verbalfeedback/db/default.yaml', Yaml::PARSE_OBJECT);
        // $value["languages"][0]["id"] = 2;
        // var_dump($value);

        // SPYC

        $lang_repo = new language_repository();
        $criterion_repo = new template_criterion_repository();
        $category_repo = new template_category_repository();
        $template_repo = new template_repository();

        //dallgoot/yaml
        $importdata = Yaml::parseFile('./mod/verbalfeedback/db/default.yaml');
        foreach ($importdata->languages as $yamllang) {
            if ($yamllang->id == null) {
                $lang = new language(null, $yamllang->language);
                $id = $lang_repo->save($lang);
                $yamllang->id = $id;
            } else {
                $lang = new language($yamllang->id, $yamllang->language);
                $lang_repo->save($lang);
            }
        }

        foreach ($importdata->criteria as $yamlcriteria) {
            $criterion = new template_criterion((int)$yamlcriteria->id);
            foreach ($yamlcriteria->texts as $text) {
                $local_string = new localized_string($text->language->id, 0, $text->text);
                $criterion->add_description($local_string);
            }

            if ($criterion->get_id() == null) {
                $id = $criterion_repo->save($criterion);
                $yamlcriteria->id = $id;
            } else {
                $criterion_repo->save($criterion);
            }
        }

        foreach ($importdata->categories as $yamlcategory) {
            $category = new template_category((int)$yamlcategory->id, $yamlcategory->unique_name);
            foreach ($yamlcategory->headers as $arr_headers) {
                $header = new localized_string($arr_headers->language->id, 0, $arr_headers->text);
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
                $id = $category_repo->save($category);
                $yamlcategory->id = $id;
            } else {
                $category_repo->save($category);
            }
        }

        foreach ($importdata->templates as $yamltemplate) {
            if (isset($yamltemplate->id)) {
                $template = new template($yamltemplate->id, $yamltemplate->name, $yamltemplate->description);
            } else {
                $template = new template(0, $yamltemplate->name, $yamltemplate->description);
            }
            foreach ($yamltemplate->categories as $arr_category) {
                if (isset($arr_category->id)) {
                    $category = new parametrized_template_category(0, $arr_category->id, $arr_category->position,
                        $arr_category->weight);
                } else {
                    $category = new parametrized_template_category(0, 0, $arr_category->position,
                        $arr_category->weight);
                }
                $template->add_template_category($category);
            }

            if ($template->get_id() == null) {
                $id = $template_repo->save($template);
                $yamltemplate->id = $id;
            } else {
                $template_repo->save($template);
            }
        }
    }
}
