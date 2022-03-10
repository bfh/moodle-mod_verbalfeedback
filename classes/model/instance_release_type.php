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
 * Class containing data for a verbal feedback instance release type.
 *
 * @package    mod_verbalfeedback
 * @copyright  2021 Kevin Tippenhauer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_verbalfeedback\model;

/**
 * The release type instance class
 */
class instance_release_type {
    /** Closed to participants. Participants cannot view the feedback given to them. Only those with the capability.  */
    const NONE = 0;
    /** Open to participants. Participants can view the feedback given to them any time. */
    const OPEN = 1;
    /**
     * Manual release. Participants can view the feedback given to them when released by users who have the capability to manage
     * the verbal feedback instance (e.g. teacher, manager, admin).
     */
    const MANUAL = 2;
    /** Release after the activity has closed. */
    const AFTER = 3;
}
