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

use mod_verbalfeedback\model\subrating;

/**
 * The database instance subrating class
 */
class db_instance_subrating {
    /** @var int The id */
    public $id = 0;
    /** @var The criterion id */
    public $criterionid;

    /**
     * Return a instance subrating database object
     *
     * @param subrating $subrating
     * @param int $criterionid
     * @return db_instance_subrating
     */
    public static function from_subrating(subrating $subrating, int $criterionid) {
        $dbo = new db_instance_subrating();
        $dbo->id = $subrating->get_id();
        $dbo->criterionid = $criterionid;
        return $dbo;
    }

    /**
     * Returns a subrating when a database object given
     *
     * @param object $dbo The database object
     * @return subrating
     */
    public static function to_subrating($dbo) : subrating {
        $subrating = new subrating();
        if (isset($dbo->id)) {
            $subrating->set_id($dbo->id);
        }
        return $subrating;
    }
}
