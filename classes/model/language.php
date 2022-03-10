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
 * Class containing data for a verbal feedback language.
 *
 * @package    mod_verbalfeedback
 * @copyright  2021 Kevin Tippenhauer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_verbalfeedback\model;

/**
 * The language class
 */
class language {
    /** @var int|null The language id */
    private $id = null;
    /** @var string The language string */
    private $language;

    /**
     * The class constructor
     *
     * @param int|null $id
     * @param string $language
     */
    public function __construct(?int $id = null, string $language = '') {
        $this->id = $id;
        $this->language = $language;
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
    public function get_id() : ?int {
        return $this->id;
    }

    /**
     * Sets the language.
     *
     * @param string $language The language.
     */
    public function set_language(string $language) {
        $this->language = $language;
    }

    /**
     * Gets the language.
     *
     * @return string The language
     */
    public function get_language() : string {
        return $this->language;
    }
}
