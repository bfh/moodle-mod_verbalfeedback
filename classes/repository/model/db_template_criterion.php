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

use mod_verbalfeedback\model\template\template_criterion;

/**
 * The database template criterion class
 */
class db_template_criterion {
    /**
     * @var int
     */
    public $id = 0;
    /**
     * @var string
     */
    public $placeholder = '';

    /**
     * Return a template criterion database object
     *
     * @param template_criterion $templatecriterion
     * @return db_template_criterion
     */
    public static function from_template_criterion(template_criterion $templatecriterion) {
        $dbo = new db_template_criterion();
        $dbo->id = $templatecriterion->get_id();
        $dbo->placeholder = '';
        return $dbo;
    }

    /**
     * Returns a template criterion when a database object given
     *
     * @param object $dbo The database object
     * @return template_criterion The template criterion
     */
    public static function to_template_criterion($dbo) : template_criterion {
        $templatecriterion = new template_criterion();
        if (isset($dbo->id)) {
            $templatecriterion->set_id($dbo->id);
        }
        return $templatecriterion;
    }
}
