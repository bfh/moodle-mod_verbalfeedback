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

namespace mod_verbalfeedback\output\model;

use mod_verbalfeedback\model\template\template;

/**
 * Class for the template view model to output template data.
 *
 * @package    mod_verbalfeedback
 * @copyright  2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
        $this->deleteurl = $url->out();
    }
}
