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

use mod_verbalfeedback\model\template\template_criterion;
use mod_verbalfeedback\repository\language_repository;
use renderable;
use renderer_base;
use templatable;
use stdClass;

/**
 * Class containing the admin settings that can be set for verbalfeedback.
 *
 * @package   mod_verbalfeedback
 */
class template_criterion_list implements renderable, templatable {
    /**
     * @var array
     */
    protected $templatecriteria = [];

    /**
     * @var
     */
    protected $newtemplatecriterionurl;

    /**
     * Verbal feedback admin settings constructor.
     * @param array $templatecriteria The template criterion data
     * @throws \moodle_exception
     */
    public function __construct(array $templatecriteria) {

        $url = new \moodle_url('/mod/verbalfeedback/template_criterion_edit.php');
        $this->newtemplatecriterionurl = $url->out();

        foreach ($templatecriteria as $tc) {
            $this->templatecriteria[] = new template_criterion_view_model($tc);
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
        $data->newtemplatecriterionurl = $this->newtemplatecriterionurl;
        $data->templatecriteria = $this->templatecriteria;
        return $data;
    }
}

/**
 * The template criterion view model class
 */
class template_criterion_view_model {
    /**
     * @var int
     */
    public $id;
    /**
     * @var
     */
    public $text;
    /**
     * @var string
     */
    public $editurl;
    /**
     * @var
     */
    public $deleteurl;

    /** @var int */
    public $subratingcount;

    /**
     * The template criterion view model class constructor
     *
     * @param template_criterion $criteria
     * @throws \moodle_exception
     */
    public function __construct(template_criterion $criteria) {
        $languagerepository = new language_repository();
        $this->id = $criteria->get_id();
        $currentlanguage = current_language();

        foreach ($criteria->get_descriptions() as $s) {
            $descriptionlanguage = $languagerepository->get_by_id($s->get_language_id());
            if ($descriptionlanguage->get_language() == $currentlanguage) {
                $this->text = $s->get_string();
                break;
            }
        }

        $this->subratingcount = count($criteria->get_subratings());

        $url = new \moodle_url('/mod/verbalfeedback/template_criterion_edit.php', ["id" => $criteria->get_id()]);
        $this->editurl = $url->out();

        $url = new \moodle_url('/mod/verbalfeedback/template_criterion_delete.php', ["id" => $criteria->get_id()]);
        $this->deleteurl = $url->out();
    }
}
