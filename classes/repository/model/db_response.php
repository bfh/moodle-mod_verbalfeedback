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
 * Class containing data for users that need to be given with verbalfeedback.
 *
 * @package    mod_verbalfeedback
 * @copyright  2021 Kevin Tippenhauer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_verbalfeedback\repository\model;

use mod_verbalfeedback\model\response;

/**
 * The database response class
 */
class db_response {
    /**
     * @var int
     */
    public $id = 0;
    /**
     * @var
     */
    public $submissionid;
    /**
     * @var
     */
    public $instanceid;
    /** @var int The criterion id */
    public $criterionid;
    /** @var int The rating user */
    public $fromuserid;
    /** @var int The id of the rated user */
    public $touserid;
    /** @var int|null The response value */
    public $value;
    /** @var string The student comment */
    public $studentcomment;
    /** @var string The private comment */
    public $privatecomment;

    /**
     * Return a response database object
     *
     * @param response $response
     * @param int $submissionid
     * @return db_response
     */
    public static function from_response(response $response, int $submissionid) {
        $dbo = new db_response();
        $dbo->id = $response->get_id();
        $dbo->submissionid = $submissionid;
        $dbo->instanceid = $response->get_instance_id();
        $dbo->criterionid = $response->get_criterion_id();
        $dbo->fromuserid = $response->get_from_user_id();
        $dbo->touserid = $response->get_to_user_id();
        $dbo->value = $response->get_value();
        $dbo->studentcomment = $response->get_student_comment();
        $dbo->privatecomment = $response->get_private_comment();
        return $dbo;
    }

    /**
     * Returns a response when a database object given
     *
     * @param object $dbo The database object
     * @return response
     */
    public static function to_response($dbo) : response {
        $response = new response();
        if (isset($dbo->id)) {
            $response->set_id($dbo->id);
        }
        if (isset($dbo->instanceid)) {
            $response->set_instance_id($dbo->instanceid);
        }
        if (isset($dbo->criterionid)) {
            $response->set_criterion_id($dbo->criterionid);
        }
        if (isset($dbo->fromuserid)) {
            $response->set_from_user_id($dbo->fromuserid);
        }
        if (isset($dbo->touserid)) {
            $response->set_to_user_id($dbo->touserid);
        }
        if (isset($dbo->value)) {
            $response->set_value($dbo->value);
        }
        if (isset($dbo->studentcomment)) {
            $response->set_student_comment($dbo->studentcomment);
        }
        if (isset($dbo->privatecomment)) {
            $response->set_private_comment($dbo->privatecomment);
        }
        return $response;
    }
}
