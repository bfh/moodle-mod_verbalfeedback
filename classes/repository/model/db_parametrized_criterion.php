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
 * Class for performing DB actions for the verbal feedback activity module.
 *
 * @package    mod_verbalfeedback
 * @copyright  2021 Kevin Tippenhauer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_verbalfeedback\repository\model;

use mod_verbalfeedback\model\template\parametrized_template_criterion;

/**
 * The database parametrized criterion class
 */
class db_parametrized_criterion {
    /** @var int Primary key. */
    public $id;

    /** @var int Foreign key category id. */
    public $categoryid;

    /** @var int The criterion id. */
    public $criterionid;

    /** @var int The position of the criterion. */
    public $position;

    /** @var float The weight of the criterion  */
    public $weight;

    /**
     * Return a parametrized template criterion database object
     *
     * @param parametrized_template_criterion $criterion
     * @param int $categoryid The category id
     * @return db_parametrized_criterion
     */
    public static function from_parametrized_criterion(parametrized_template_criterion $criterion, int $categoryid) {
        $dbo = new db_parametrized_criterion();
        $dbo->id = $criterion->get_id();
        $dbo->categoryid = $categoryid;
        $dbo->criterionid = $criterion->get_template_criterion_id();
        $dbo->position = $criterion->get_position();
        $dbo->weight = $criterion->get_weight();
        return $dbo;
    }

    /**
     * Returns a parametrized template criterion when a database object given
     *
     * @param object $dbo The database object
     * @return parametrized_template_criterion
     */
    public static function to_parametrized_criterion($dbo): parametrized_template_criterion {
        $paramcriterion = new parametrized_template_criterion();
        if (isset($dbo->id)) {
            $paramcriterion->set_id($dbo->id);
        }
        if (isset($dbo->criterionid)) {
            $paramcriterion->set_template_criterion_id($dbo->criterionid);
        }
        if (isset($dbo->position)) {
            $paramcriterion->set_position($dbo->position);
        }
        if (isset($dbo->weight)) {
            $paramcriterion->set_weight($dbo->weight);
        }
        return $paramcriterion;
    }
}
