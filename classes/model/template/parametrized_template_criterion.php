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
 * Class containing the verbal feedback parametrized template criterion.
 *
 * @package    mod_verbalfeedback
 * @copyright  2021 Kevin Tippenhauer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_verbalfeedback\model\template;

use mod_verbalfeedback\model\localized_string;
use stdClass;

/**
 * The parametrized template criterion class
 */
class parametrized_template_criterion {
    /** @var int The id */
    public $id;
    /** @var int The parametrized template criterion id */
    public $criterionid;
    /** @var int The position */
    public $position;
    /** @var float The weight */
    public $weight;

    /**
     * The parametrized template criterion class constructor
     *
     * @param int $id The id
     * @param int $criterionid The parametrized template criterion id
     * @param int $position The position
     * @param float $weight The weight
     */
    public function __construct(int $id = 0, int $criterionid = 0, int $position = 0, float $weight = 0.0) {
        $this->id = $id;
        $this->criterionid = $criterionid;
        $this->position = $position;
        $this->weight = $weight;
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
     * Sets the template criterion id
     *
     * @param int $criterionid The template criterion id
     */
    public function set_template_criterion_id(int $criterionid) {
        $this->criterionid = $criterionid;
    }

    /**
     * Gets the template criterion id
     *
     * @return int The template criterion id
     */
    public function get_template_criterion_id(): int {
        return $this->criterionid;
    }

    /**
     * Sets the parametrized template criterion position
     *
     * @param int $position The parametrized template criterion position
     */
    public function set_position(int $position) {
        $this->position = $position;
    }

    /**
     * Gets the template criterion position
     *
     * @return int The template criterion position
     */
    public function get_position(): int {
        return $this->position;
    }

    /**
     * Sets the weight
     *
     * @param float $weight
     */
    public function set_weight(float $weight) {
        $this->weight = $weight;
    }

    /**
     * Gets the weight
     *
     * @return float
     */
    public function get_weight(): float {
        return $this->weight;
    }
}
