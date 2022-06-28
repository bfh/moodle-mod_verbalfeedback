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

use mod_verbalfeedback\model\submission;

/**
 * The database submission class
 */
class db_submission {
    /**
     * @var int
     */
    public $id = 0;
    /**
     * @var
     */
    public $instanceid;
    /** @var int The rating user */
    public $fromuserid;
    /** @var int The id of the rated user */
    public $touserid;
    /**
     * @var
     */
    public $status;
    /**
     * @var
     */
    public $remarks;

    /**
     * Return a submission database object
     *
     * @param submission $submission
     * @return db_submission
     */
    public static function from_submission(submission $submission) {
        $dbo = new db_submission();
        $dbo->id = $submission->get_id();
        $dbo->instanceid = $submission->get_instance_id();
        $dbo->fromuserid = $submission->get_from_user_id();
        $dbo->touserid = $submission->get_to_user_id();
        $dbo->status = $submission->get_status();
        $dbo->remarks = $submission->get_remarks();
        return $dbo;
    }

    /**
     * Returns a submission when a database object given
     *
     * @param object $dbo The database object
     * @return submission
     */
    public static function to_submission($dbo) : submission {
        $submission = new submission();
        if (isset($dbo->id)) {
            $submission->set_id($dbo->id);
        }
        if (isset($dbo->instanceid)) {
            $submission->set_instance_id($dbo->instanceid);
        }
        if (isset($dbo->fromuserid)) {
            $submission->set_from_user_id($dbo->fromuserid);
        }
        if (isset($dbo->touserid)) {
            $submission->set_to_user_id($dbo->touserid);
        }
        if (isset($dbo->status)) {
            $submission->set_status($dbo->status);
        }
        if (isset($dbo->remarks)) {
            $submission->set_remarks($dbo->remarks);
        }
        return $submission;
    }
}
