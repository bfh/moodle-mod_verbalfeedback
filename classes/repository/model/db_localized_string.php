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

use mod_verbalfeedback\model\localized_string;

/**
 * The database localized string class
 */
class db_localized_string {
    /**
     * @var int The id
     */
    public $id;

    /**
     * @var int The type
     */
    public $typeid;

    /**
     * @var int The foreign key
     */

    public $foreignkey;

    /**
     * @var string The language id
     */
    public $languageid;

    /**
     * @var string The string
     */
    public $string;

    /**
     * @var int The instance id of the verbal feedback activity
     */
    public $instanceid;

    /**
     * Return a localized string database object
     *
     * @param localized_string $localizedstring The localized string
     * @param string $type The string type
     * @param int $foreignkey The foreign key
     * @param int $instanceid The instance id
     * @return db_localized_string
     * @throws \Exception
     */
    public static function from_localized_string(
        localized_string $localizedstring,
        string $type,
        int $foreignkey,
        int $instanceid = 0
    ): db_localized_string {
        if (!localized_string_type::exists($type)) {
            throw new \Exception("unknown localized_string_type");
        }
        $dbo = new db_localized_string();
        $dbo->id = $localizedstring->get_id();
        $dbo->typeid = localized_string_type::str2id($type);
        $dbo->foreignkey = $foreignkey;
        $dbo->languageid = $localizedstring->get_language_id();
        $dbo->string = $localizedstring->get_string();
        $dbo->instanceid = $instanceid;
        return $dbo;
    }

    /**
     * Return a localized string from a database object
     *
     * @param object $dbo The database object
     * @return localized_string The localized string
     * @throws \Exception
     */
    public static function to_localized_string($dbo): localized_string {
        if (isset($dbo->languageid) && isset($dbo->id) && isset($dbo->string)) {
            return new localized_string($dbo->languageid, $dbo->id, $dbo->string);
        } else {
            throw new \Exception("Missing id, languageid or string property on $dbo");
        }
    }
}
