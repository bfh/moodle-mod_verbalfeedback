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
 * Class containing data for a verbal feedback localized string.
 *
 * @package    mod_verbalfeedback
 * @copyright  2021 Kevin Tippenhauer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_verbalfeedback\model;

/**
 * The localized string class
 */
class localized_string {
    /** @var int The id */
    public $id = 0;
    /** @var int The language id */
    public $languageid;
    /** @var string The string */
    public $string = '';

    /**
     * The localized string class constructor
     *
     * @param int $languageid
     * @param int $id
     * @param string $string
     */
    public function __construct(int $languageid, int $id = 0, string $string = '') {
        $this->languageid = $languageid;
        $this->id = $id;
        $this->string = $string;
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
    public function get_id() : int {
        return $this->id;
    }

    /**
     * Sets the language.
     *
     * @param int $languageid The language.
     */
    public function set_language_id(int $languageid) {
        $this->languageid = $languageid;
    }

    /**
     * Gets the language.
     *
     * @return language The language.
     */
    public function get_language_id() : int {
        return $this->languageid;
    }

    /**
     * Sets the string.
     *
     * @param string $string The string.
     */
    public function set_string(string $string) {
        $this->string = $string;
    }

    /**
     * Gets the string.
     *
     * @return string The string.
     */
    public function get_string() : string {
        return $this->string;
    }
}
