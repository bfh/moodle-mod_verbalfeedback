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
 * Class containing data for users that need to be given with verbalfeedback.
 *
 * @package    mod_verbalfeedback
 * @copyright  2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_verbalfeedback\output;

defined('MOODLE_INTERNAL') || die();

use mod_verbalfeedback\api;
use mod_verbalfeedback\helper;
use mod_verbalfeedback\model\template\template_category;
use renderable;
use renderer_base;
use templatable;
use stdClass;
use moodle_url;
use moodle_exception;

/**
 * Class containing the admin settings that can be set for verbalfeedback.
 *
 * @package   mod_verbalfeedback
 */
class template_category_list implements renderable, templatable {
    /**
     * @var array
     */
    protected $templatecategories = [];
    /**
     * @var string
     */
    protected $newtemplatecategoryurl;

    /**
     * Verbal feedback admin settings constructor.
     *
     * @param array $templatecategories The template category list data
     * @throws moodle_exception
     */
    public function __construct(array $templatecategories) {
        $url = new \moodle_url('/mod/verbalfeedback/template_category_edit.php');
        $this->newtemplatecategoryurl = $url->out();

        foreach ($templatecategories as $c) {
            $this->templatecategories[] = new template_category_view_model($c);
        }
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output The renderer.
     * @return stdClass|array
     */
    public function export_for_template(renderer_base $output) {
        verbalfeedback_urls::construct_static();

        $data = new stdClass();
        $data->templatelisturl = verbalfeedback_urls::get_template_list_url();
        $data->templatecategorylisturl = verbalfeedback_urls::get_template_category_list_url();
        $data->templatecriterialisturl = verbalfeedback_urls::get_template_criterion_list_url();
        $data->languagelisturl = verbalfeedback_urls::get_language_list_url();
        $data->newtemplatecategoryurl = $this->newtemplatecategoryurl;
        $data->templatecategories = $this->templatecategories;
        return $data;
    }
}

/**
 * The template category view model class
 */
class template_category_view_model {
    /** @var int The category id */
    public $id;
    /** @var string The category edit url */
    public $editurl;
    /** @var string The category delete url */
    public $deleteurl;
    /** @var string */
    public $uniquename;
    /** @var int|null Nomber of criterias. */
    public $criteriacount;

    /**
     * The template category view model class constructor
     *
     * @param template_category $category
     * @throws moodle_exception
     */
    public function __construct(template_category $category) {
        $this->id = $category->get_id();
        $this->uniquename = $category->get_unique_name();
        $this->criteriacount = count($category->get_template_criteria());

        $url = new \moodle_url('/mod/verbalfeedback/template_category_edit.php', ["id" => $category->get_id()]);
        $this->editurl = $url->out();

        $url = new \moodle_url('/mod/verbalfeedback/template_category_delete.php', ["id" => $category->get_id()]);
        $this->deleteurl = $url->out();;
    }
}
