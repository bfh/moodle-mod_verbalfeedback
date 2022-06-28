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

use mod_verbalfeedback\model\template\template;

/**
 * The database template class
 */
class db_template {
    /**
     * @var int The id
     */
    public $id = 0;
    /**
     * @var string The name
     */
    public $name;
    /**
     * @var string The description
     */
    public $description;

    /**
     * Return a template database object
     *
     * @param template $template The template
     * @return db_template The database object
     */
    public static function from_template(template $template) {
        $dbo = new db_template();
        $dbo->id = $template->get_id();
        $dbo->name = $template->get_name();
        $dbo->description = $template->get_description();
        return $dbo;
    }

    /**
     * Returns a template when a database object given
     *
     * @param object $dbo The database object
     * @return template The template
     */
    public static function to_template($dbo) : template {
        $template = new template();
        if (isset($dbo->id)) {
            $template->set_id($dbo->id);
        }
        if (isset($dbo->name)) {
            $template->set_name($dbo->name);
        }
        if (isset($dbo->description)) {
            $template->set_description($dbo->description);
        }
        return $template;
    }
}
