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

use mod_verbalfeedback\model\template\template_category;

/**
 * The template category view model class
 * @package    mod_verbalfeedback
 * @copyright  2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
        $this->deleteurl = $url->out();
    }
}
