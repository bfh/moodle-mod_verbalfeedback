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
 * Class containing the verbal feedback template criterion.
 *
 * @package    mod_verbalfeedback
 * @copyright  2021 Kevin Tippenhauer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_verbalfeedback\model\template;

use mod_verbalfeedback\model\localized_string;
use mod_verbalfeedback\model\subrating;

/**
 * The template criterion class
 */
class template_criterion {
    /** @var int The template criterion id */
    public $id = 0;
    /** @var array|mixed The template criterion descriptions */
    public $descriptions = [];
    /** @var array|mixed The template criterion subratings */
    public $subratings = [];

    /**
     * The template criterion model constructor
     *
     * @param int $id
     * @param array $descriptions
     * @param array $subratings
     */
    public function __construct(int $id = 0, $descriptions = [], $subratings = []) {
        $this->id = $id;
        $this->descriptions = $descriptions;
        $this->subratings = $subratings;
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
     * Sets the template criterion descriptions
     *
     * @param array $descriptions The template criterion descriptions
     */
    public function set_descriptions(array $descriptions) {
        $this->descriptions = $descriptions;
    }

    /**
     * Adds a localized description to the template criterion.
     *
     * @param localized_string $description The template criterion description
     */
    public function add_description(localized_string $description) {
        $this->descriptions[] = $description;
    }

    /**
     * Gets the criteria descriptions
     *
     * @return array<int, localized_string> The localized strings.
     */
    public function get_descriptions(): array {
        return $this->descriptions;
    }

    /**
     * Set the criteria's subratings
     *
     * @param array $subratings The criteria's subratings
     */
    public function set_subratings(array $subratings) {
        $this->subratings = $subratings;
    }

    /**
     * Add a criteria's subrating
     *
     * @param subrating $subrating The criteria's subrating
     */
    public function add_subrating(subrating $subrating) {
        $this->subratings[] = $subrating;
    }

    /**
     * Get the criteria's subratings
     *
     * @return array The criteria's subratings
     */
    public function get_subratings(): array {
        return $this->subratings;
    }
}
