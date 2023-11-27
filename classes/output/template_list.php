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

use mod_verbalfeedback\model\template\template;
use renderable;
use renderer_base;
use templatable;
use stdClass;

/**
 * Class containing the admin settings that can be set for verbalfeedback.
 *
 * @package   mod_verbalfeedback
 */
class template_list implements renderable, templatable {
    /** @var array The template */
    protected $templates = [];
    /** @var string The template list url */
    protected $templatelisturl;
    /** @var string The template category list url */
    protected $templatecategorylisturl;
    /** @var string The template criteria list url */
    protected $templatecriterialisturl;
    /** @var string The new template url */
    protected $newtemplateurl;

    /**
     * Verbal feedback admin settings constructor.
     *
     * @param array $templates The templates data
     * @throws \moodle_exception
     */
    public function __construct(array $templates) {

        $url = new \moodle_url('/mod/verbalfeedback/template_edit.php');
        $this->newtemplateurl = $url->out();

        foreach ($templates as $t) {
            $this->templates[] = new template_view_model($t);
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
        $data->newtemplateurl = $this->newtemplateurl;
        $data->templates = $this->templates;
        return $data;
    }
}

/**
 * The template view model class
 */
class template_view_model {
    /** @var int The id */
    public $id;
    /** @var string The name */
    public $name;
    /** @var string The description */
    public $description;
    /** @var int Number of categories */
    public $categorycount;
    /** @var string The edit url */
    public $editurl;
    /** @var string The delete url */
    public $deleteurl;
    /** @var string The download url */
    public $downloadurl;

    /**
     * The template view model class constructor
     *
     * @param template $template The template
     * @throws \moodle_exception
     */
    public function __construct(template $template) {
        $this->id = $template->get_id();
        $this->name = $template->get_name();
        $this->description = $template->get_description();
        $this->categorycount = count($template->get_template_categories());

        $url = new \moodle_url('/mod/verbalfeedback/template_edit.php', ["id" => $template->get_id()]);
        $this->editurl = $url->out();

        $url = new \moodle_url('/mod/verbalfeedback/template_download.php', ["id" => $template->get_id()]);
        $this->downloadurl = $url->out();

        $url = new \moodle_url('/mod/verbalfeedback/template_delete.php', ["id" => $template->get_id()]);
        $this->deleteurl = $url->out();;
    }
}
