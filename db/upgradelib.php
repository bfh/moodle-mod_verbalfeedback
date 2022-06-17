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
 * Plugin upgrade helper functions are defined here.
 *
 * @package     mod_verbalfeedback
 * @category    upgrade
 * @copyright   2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Whether a string ends with a substring
 *
 * @param string $haystack The string to look up in
 * @param string $needle The string to look up
 * @return bool Whether it ends with that substring
 */
function mod_verbalfeedback_ends_with($haystack, $needle) {
    $length = strlen($needle);
    return $length > 0 ? substr($haystack, -$length) === $needle : true;
}

/**
 * Replace null with 0
 *
 * @param int|null $x The int or null
 * @return int The int
 */
function mod_verbalfeedback_replace_null_with_zero(?int $x) : int {
    if ($x == null) {
        return 0;
    }
    return $x;
}
