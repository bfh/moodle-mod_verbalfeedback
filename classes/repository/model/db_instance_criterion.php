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
 * @copyright  2021 Kevin Tippenhauer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_verbalfeedback\repository\model;

use mod_verbalfeedback\model\instance_criterion;

/**
 * The database instance criterion class
 */
class db_instance_criterion {
    /**
     * @var
     */
    public $id;
    /**
     * @var
     */
    public $paramtemplatecriterionid;
    /**
     * @var
     */
    public $categoryid;
    /**
     * @var
     */
    public $position;
    /**
     * @var
     */
    public $weight;

    /**
     * Return a instance criterion database object
     *
     * @param instance_criterion $criterion
     * @param int $categoryid The category id
     * @return db_instance_criterion
     */
    public static function from_instance_criterion(instance_criterion $criterion, int $categoryid) {
        $dbo = new db_instance_criterion();
        $dbo->id = $criterion->get_id();
        $dbo->paramtemplatecriterionid = $criterion->get_parametrized_template_criterion_id();
        $dbo->categoryid = $categoryid;
        $dbo->position = $criterion->get_position();
        $dbo->weight = $criterion->get_weight();
        return $dbo;
    }

    /**
     * Returns a instance criterion when a database object given
     *
     * @param object $dbo The database object
     * @return instance_criterion
     */
    public static function to_instance_criterion($dbo) : instance_criterion {
        $criterion = new instance_criterion();
        if (isset($dbo->id)) {
            $criterion->set_id($dbo->id);
        }
        if (isset($dbo->paramtemplatecriterionid)) {
            $criterion->set_parametrized_template_criterion_id($dbo->paramtemplatecriterionid);
        }
        if (isset($dbo->position)) {
            $criterion->set_position($dbo->position);
        }
        if (isset($dbo->weight)) {
            $criterion->set_weight($dbo->weight);
        }
        return $criterion;
    }
}
