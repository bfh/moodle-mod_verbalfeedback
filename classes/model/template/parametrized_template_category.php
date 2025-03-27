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
 * Class containing the verbal feedback parametrized template category.
 *
 * @package    mod_verbalfeedback
 * @copyright  2021 Kevin Tippenhauer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_verbalfeedback\model\template;

use mod_verbalfeedback\model\localized_string;
use stdClass;

/**
 * The parametrized template category class
 */
class parametrized_template_category {
    /** @var int The id */
    public $id;
    /** @var int The category id */
    public $categoryid;
    /** @var int The position */
    public $position;
    /** @var float The weight */
    public $weight;

    /**
     * The parametrized template category model constructor
     * @param int $id The id
     * @param int $categoryid The category id
     * @param int $position The position
     * @param float $weight The weight
     */
    public function __construct(int $id = 0, int $categoryid = 0, int $position = 0, float $weight = 0.0) {
        $this->id = $id;
        $this->categoryid = $categoryid;
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
     * Sets the template category id
     *
     * @param int $categoryid The category id
     */
    public function set_template_category_id(int $categoryid) {
        $this->categoryid = $categoryid;
    }

    /**
     * Gets the template category id
     *
     * @return int The category id
     */
    public function get_template_category_id(): int {
        return $this->categoryid;
    }

    /**
     * Sets the parametrized template category position
     *
     * @param int $position The parametrized template category position
     */
    public function set_position(int $position) {
        $this->position = $position;
    }

    /**
     * Gets the parametrized template category position
     *
     * @return int The parametrized template category position
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
