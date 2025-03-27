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
 * Class containing the verbal feedback template category.
 *
 * @package    mod_verbalfeedback
 * @copyright  2021 Kevin Tippenhauer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_verbalfeedback\model\template;

use mod_verbalfeedback\model\localized_string;

/**
 * The template category class
 */
class template_category {
    /** @var int The template category id */
    public $id = 0;
    /** @var string The template category unique name */
    public $uniquename;
    /** @var array The template category headers */
    public $headers = [];
    /** @var array The template category criteria */
    public $templatecriteria = [];

    /**
     * The template category model constructor
     *
     * @param int $id
     * @param string $uniquename
     */
    public function __construct(int $id = 0, string $uniquename = "") {
        $this->id = $id;
        $this->uniquename = $uniquename;
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
    public function get_id(): int {
        return $this->id;
    }

    /**
     * Sets a category unique name
     *
     * @param string $uniquename The category unique name
     */
    public function set_unique_name(string $uniquename) {
        $this->uniquename = $uniquename;
    }

    /**
     * Gets the category unique name
     *
     * @return string The category unique name
     */
    public function get_unique_name(): string {
        return $this->uniquename;
    }

    /**
     * Sets the category headers
     *
     * @param array $headers The category headers
     */
    public function set_headers(array $headers) {
        $this->headers = $headers;
    }

    /**
     * Adds a header to the category
     *
     * @param localized_string $header The category header
     */
    public function add_header(localized_string $header) {
        $this->headers[] = $header;
    }

    /**
     * Gets the localized template category headers
     *
     * @return array The localized template category headers
     */
    public function get_headers(): array {
        return $this->headers;
    }

    /**
     * Sets the template category criteria
     *
     * @param array $templatecriteria The template category criteria
     */
    public function set_template_criteria(array $templatecriteria) {
        $this->templatecriteria = $templatecriteria;
    }

    /**
     * Adds a parametrized template category criterion
     *
     * @param parametrized_template_criterion $templatecriteria The parametrized template category criterion
     */
    public function add_template_criterion(parametrized_template_criterion $templatecriteria) {
        $this->templatecriteria[] = $templatecriteria;
    }

    /**
     * Gets the template category criteria
     *
     * @return array The template category criteria
     */
    public function get_template_criteria(): array {
        return $this->templatecriteria;
    }
}
