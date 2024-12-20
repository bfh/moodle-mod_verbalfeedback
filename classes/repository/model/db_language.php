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

use mod_verbalfeedback\model\language;

/**
 * The database language class
 */
class db_language {
    /**
     * @var int The language id
     */
    public $id;
    /**
     * @var string The language
     */
    public $language;

    /**
     * Return a language database object
     *
     * @param language $language
     * @return db_language
     */
    public static function from_language(language $language): db_language {
        $dbo = new db_language();
        $dbo->id = $language->get_id();
        $dbo->language = $language->get_language();
        return $dbo;
    }

    /**
     * Returns a language when a database object given
     *
     * @param object $dbo The database object
     * @return language
     */
    public static function to_language($dbo): language {
        $language = new language();
        if (isset($dbo->id) && !empty($dbo->id)) {
            $language->set_id($dbo->id);
        }
        if (isset($dbo->language) && !empty($dbo->language)) {
            $language->set_language($dbo->language);
        }
        return $language;
    }
}
