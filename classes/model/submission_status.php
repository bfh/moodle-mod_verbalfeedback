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
 * Class containing data for a verbal feedback submission status.
 *
 * @package    mod_verbalfeedback
 * @copyright  2021 Kevin Tippenhauer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_verbalfeedback\model;

/**
 * The submission status class
 */
class submission_status {
    /** Status when a user has not yet provided feedback to another user. */
    const PENDING = 0;
    /** Status when a user has begun providing feedback to another user. */
    const IN_PROGRESS = 1;
    /** Status when a user has completed providing feedback to another user. */
    const COMPLETE = 2;
    /** Status when a user has declined to provide feedback to another user. */
    const DECLINED = 3;
}
