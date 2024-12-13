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
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_verbalfeedback\repository;

defined('MOODLE_INTERNAL') || die();

use mod_verbalfeedback\model\submission;
use mod_verbalfeedback\model\submission_status;
use mod_verbalfeedback\repository\model\db_response;
use mod_verbalfeedback\repository\model\db_submission;

require_once(__DIR__ . '/../../lib.php');


/**
 * The submission repository class.
 */
class submission_repository {

    /**
     * Inserts (submission->id = 0) or updates a submission.
     *
     * @param submission $submission The submission object.
     * @return int The id of the submission.
     * @throws dml_exception A DML specific exception is thrown for any errors.
     */
    public function save(submission $submission) {
        global $DB;
        $dbosubmission = db_submission::from_submission($submission);

        // Update the users grades.
        if ($submission->status == submission_status::COMPLETE) {
            verbalfeedback_update_grades(instance_repository::get_by_id($submission->instanceid), $submission->touserid);
        }

        if ($submission->get_id() == 0) {
            $id = $DB->insert_record(tables::SUBMISSION_TABLE, $dbosubmission);
            $submission->set_id($id);
        } else {
            $DB->update_record(tables::SUBMISSION_TABLE, $dbosubmission);
        }

        foreach ($submission->get_responses() as $response) {
            $dboresponse = db_response::from_response($response, $submission->get_id());
            if ($response->get_id() == 0) {
                $id = $DB->insert_record(tables::RESPONSE_TABLE, $dboresponse);
                $response->set_id($id);
            } else {
                $DB->update_record(tables::RESPONSE_TABLE, $dboresponse);
            }
        }
        return $submission->get_id();
    }

    /**
     * Deletes submission records in the database
     * @param int $id The submission id
     * @return bool True, if successful
     */
    public function delete_by_id(int $id): bool {
        global $DB;
        return $DB->delete_records(tables::LANGUAGE_TABLE, ['id' => $id]);
    }

    /**
     * Gets the submission for the given id
     * @param int $id The submission id
     * @return submission|null The submission
     */
    public function get_by_id(int $id): submission {
        global $DB;
        $dbo = $DB->get_record(tables::SUBMISSION_TABLE, ["id" => $id]);
        $submission = db_submission::to_submission($dbo);

        $dboresponses = $DB->get_records(tables::RESPONSE_TABLE, ["submissionid" => $submission->get_id()]);
        foreach ($dboresponses as $dboresponse) {
            $response = db_response::to_response($dboresponse);
            $submission->add_response($response);
        }
        return $submission;
    }

    /**
     * Gets a submission by instanceid, fromuserid and touserid
     * @param int $instanceid The instance id.
     * @param int $fromuserid The id of the submission autor.
     * @param int $touserid The id of the rated user.
     * @return submission|null The submission.
     */
    public function get_by_instance_and_fromuser_and_touser(int $instanceid, int $fromuserid, int $touserid): submission {
        global $DB;
        $dbo = $DB->get_record(tables::SUBMISSION_TABLE, ["instanceid" => $instanceid, "fromuserid" => $fromuserid,
            "touserid" => $touserid, ], );
        $submission = db_submission::to_submission($dbo);

        $dboresponses = $DB->get_records(tables::RESPONSE_TABLE, ["submissionid" => $submission->get_id()]);
        foreach ($dboresponses as $dboresponse) {
            $response = db_response::to_response($dboresponse);
            $submission->add_response($response);
        }
        return $submission;
    }

    /**
     * Gets all submissions by instanceid and touserid.
     * @param int $instanceid The instance id.
     * @param int $touserid The id of the rated user.
     * @return array<submission>|null The submission.
     */
    public function get_by_instance_and_touser(int $instanceid, int $touserid): array {
        global $DB;
        $dbos = $DB->get_records(tables::SUBMISSION_TABLE, ["instanceid" => $instanceid, "touserid" => $touserid]);

        $submissions = [];
        foreach ($dbos as $dbosubmission) {
            $submission = db_submission::to_submission($dbosubmission);

            $dboresponses = $DB->get_records(tables::RESPONSE_TABLE, ["submissionid" => $submission->get_id()]);
            foreach ($dboresponses as $dboresponse) {
                $response = db_response::to_response($dboresponse);
                $submission->add_response($response);
            }
            $submissions[] = $submission;
        }
        return $submissions;
    }

    /**
     * Deletes all submissions with the given instance id.
     * @param int $instanceid The instance id.
     */
    public function delete_by_instance(int $instanceid) {
        global $DB;
        $dbos = $DB->get_records(tables::SUBMISSION_TABLE, ["instanceid" => $instanceid]);
        foreach ($dbos as $dbosubmission) {
            $DB->delete_records(tables::RESPONSE_TABLE, ["submissionid" => $dbosubmission->id]);
        }
        $DB->delete_records(tables::SUBMISSION_TABLE, ["instanceid" => $instanceid]);
    }
}
