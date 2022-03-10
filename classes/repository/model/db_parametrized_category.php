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

use mod_verbalfeedback\model\template\parametrized_template_category;

/**
 * The database parametrized category class
 */
class db_parametrized_category {
    /**
     * @var
     */
    public $id;
    /**
     * @var
     */
    public $templateid;
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
     * Return a parametrized template category database object
     *
     * @param parametrized_template_category $category
     * @param int $templateid
     * @return db_parametrized_category
     */
    public static function from_parametrized_category(parametrized_template_category $category, int $templateid) {
        $dbo = new db_parametrized_category();
        $dbo->id = $category->get_id();
        $dbo->templateid = $templateid;
        $dbo->categoryid = $category->get_template_category_id();
        $dbo->position = $category->get_position();
        $dbo->weight = $category->get_weight();
        return $dbo;
    }

    /**
     * Returns a parametrized template category when a database object given
     *
     * @param object $dbo The database object
     * @return parametrized_template_category
     */
    public static function to_parametrized_category($dbo) : parametrized_template_category {
        $category = new parametrized_template_category();
        if (isset($dbo->id)) {
            $category->set_id($dbo->id);
        }
        if (isset($dbo->categoryid)) {
            $category->set_template_category_id($dbo->categoryid);
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
