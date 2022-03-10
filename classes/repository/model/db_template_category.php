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

use mod_verbalfeedback\model\template\template_category;

/**
 * The database template category class
 */
class db_template_category {
    /**
     * @var int
     */
    public $id = 0;
    /**
     * @var
     */
    public $uniquename;

    /**
     * Return a template category database object
     *
     * @param template_category $templatecategory
     * @return db_template_category
     */
    public static function from_template_category(template_category $templatecategory) {
        $dbo = new db_template_category();
        $dbo->id = $templatecategory->get_id();
        $dbo->uniquename = $templatecategory->get_unique_name();
        return $dbo;
    }

    /**
     * Returns a template category when a database object given
     *
     * @param object $dbo The database object
     * @return template_category The template category
     */
    public static function to_template_category($dbo) : template_category {
        $templatecategory = new template_category();
        if (isset($dbo->id)) {
            $templatecategory->set_id($dbo->id);
        }
        if (isset($dbo->uniquename)) {
            $templatecategory->set_unique_name($dbo->uniquename);
        }
        return $templatecategory;
    }
}
