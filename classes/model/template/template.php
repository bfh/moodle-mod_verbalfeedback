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
 * Class containing the verbal feedback template.
 *
 * @package    mod_verbalfeedback
 * @copyright  2021 Kevin Tippenhauer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_verbalfeedback\model\template;

/**
 * The template class
 */
class template {
    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    private $description;
    /**
     * @var array
     */
    public $templatecategories = array();

    /**
     * The template class constructor
     *
     * @param int $id
     * @param string $name
     * @param string $description
     */
    public function __construct(int $id = 0, string $name = "", string $description = "") {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
    }

    /**
     * Sets the id.
     *
     * @param int $id The id.
     */
    public function set_id(int $id) {
        $this->id = $id;
    }

    /**
     * Gets the id.
     *
     * @return int The id.
     */
    public function get_id() : int {
        return $this->id;
    }

    /**
     * Sets the template name.
     *
     * @param string $name The template name.
     */
    public function set_name(string $name) {
        $this->name = $name;
    }

    /**
     * Gets the template name.
     *
     * @return string The template name.
     */
    public function get_name() : string {
        return $this->name;
    }

    /**
     * Sets the template descriptions
     *
     * @param string $description The template descriptions
     */
    public function set_description(string $description) {
        $this->description = $description;
    }

    /**
     * Gets a localized template description for the given language string
     *
     * @return string The template description
     */
    public function get_description() : string {
        return $this->description;
    }

    /**
     * Sets the categories to this template
     *
     * @param array $templatecategories The template categories
     */
    public function set_template_categories(array $templatecategories) {
        $this->templatecategories = $templatecategories;
    }

    /**
     * Adds a parametrized template category
     *
     * @param parametrized_template_category $templatecategory The parametrized template category
     */
    public function add_template_category(parametrized_template_category $templatecategory) {
        $this->templatecategories[] = $templatecategory;
    }

    /**
     * Gets the categories associated with this template
     *
     * @return parametrized_template_category[] The parametrized template categories
     */
    public function get_template_categories() : array {
        return $this->templatecategories;
    }
}
