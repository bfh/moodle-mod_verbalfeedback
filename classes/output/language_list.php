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

use renderable;
use renderer_base;
use templatable;
use stdClass;

/**
 * Class containing the admin settings that can be set for verbalfeedback.
 *
 * @package   mod_verbalfeedback
 */
class language_list implements renderable, templatable {
    /**
     * @var array
     */
    protected $languages = [];
    /**
     * @var string
     */
    protected $newlanguageurl;

    /**
     * Verbal feedback language list edit constructor.
     *
     * @param array $languages The languages
     * @throws \moodle_exception
     */
    public function __construct(array $languages) {
        $url = new \moodle_url('/mod/verbalfeedback/language_edit.php');
        $this->newlanguageurl = $url->out();

        foreach ($languages as $l) {
            $this->languages[] = new model\language_view_model($l);
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
        $data->newlanguageurl = $this->newlanguageurl;
        $data->languages = $this->languages;
        return $data;
    }
}
