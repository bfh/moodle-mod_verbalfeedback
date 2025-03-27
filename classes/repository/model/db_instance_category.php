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

use mod_verbalfeedback\model\instance_category;

/**
 * The database instance category class
 */
class db_instance_category {
    /**
     * @var int The id of the category
     */
    public $id;
    /**
     * @var int The id of the instance
     */
    public $instanceid;
    /**
     * @var int The id of the category
     */
    public $paramtemplatecategoryid;
    /**
     * @var int The position of the category
     */
    public $position;
    /**
     * @var float The weight of the category
     */
    public $weight;

    /**
     * Return a instance category database object
     *
     * @param instance_category $category
     * @param int $instanceid
     * @return db_instance_category
     */
    public static function from_instance_category(instance_category $category, int $instanceid) {
        $dbo = new db_instance_category();
        $dbo->id = $category->get_id();
        $dbo->instanceid = $instanceid;
        $dbo->paramtemplatecategoryid = $category->get_parametrized_template_category_id();
        $dbo->position = $category->get_position();
        $dbo->weight = $category->get_weight();
        return $dbo;
    }

    /**
     * Returns a instance category when a database object given
     *
     * @param object $dbo The database object
     * @return instance_category
     */
    public static function to_instance_category($dbo): instance_category {
        $category = new instance_category();
        if (isset($dbo->id)) {
            $category->set_id($dbo->id);
        }
        if (isset($dbo->paramtemplatecategoryid)) {
            $category->set_parametrized_template_category_id($dbo->paramtemplatecategoryid);
        }
        if (isset($dbo->position)) {
            $category->set_position($dbo->position);
        }
        if (isset($dbo->weight)) {
            $category->set_weight($dbo->weight);
        }
        return $category;
    }
}
