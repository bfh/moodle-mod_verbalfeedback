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

// Let codechecker ignore the sniff for this file for nullable types since the super method of
// create_instance is not yet rewritten and mod_attendance_generator::create_instance must have an identical signature.
// phpcs:disable PHPCompatibility.FunctionDeclarations.RemovedImplicitlyNullableParam.Deprecated

/**
 * mod_verbalfeedback data generator class.
 *
 * @package mod_verbalfeedback
 * @category test
 * @copyright 2021 Luca Bösch <luca.boesch@bfh.ch>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_verbalfeedback_generator extends testing_module_generator {
    /**
     * Create a verbal feedback instance for testing purposes
     * @param null $record A verbal feedback record
     * @param array|null $options An array of options
     * @return stdClass
     * @throws coding_exception
     */
    public function create_instance($record = null, array $options = null) {
        global $DB;

        $record = (object)(array)$record;

        if (!isset($record->timemodified)) {
            $record->timemodified = time();
        }

        // Allow the template to be referenced by its name (e.g. "Default template") in Behat
        // feature files, resolving it here to the numeric id expected by the module.
        if (isset($record->template) && !is_number($record->template)) {
            // The template "name" column is a TEXT field, which cannot be compared directly
            // in a WHERE clause (e.g. on PostgreSQL), so use sql_compare_text().
            $select = $DB->sql_compare_text('name') . ' = ' . $DB->sql_compare_text(':name');
            $record->template = (int)$DB->get_field_select(
                \mod_verbalfeedback\repository\tables::TEMPLATE_TABLE,
                'id',
                $select,
                ['name' => $record->template],
                MUST_EXIST
            );
        }

        return parent::create_instance($record, (array)$options);
    }

    /**
     * Create a completed feedback submission (one response per criterion) for an instance.
     *
     * This mimics a reviewer (fromuser) who finished rating another participant (touser),
     * without having to click through the whole questionnaire in the UI. It is primarily
     * used by the Behat data generator to set up the reviewers of a report.
     *
     * Expected $record fields:
     *  - instanceid int The verbal feedback instance id.
     *  - fromuserid int The id of the reviewer giving the feedback.
     *  - touserid   int The id of the rated participant.
     *  - rating     int (optional) The value used for every response, defaults to 5.
     *  - status     int (optional) The submission status, defaults to COMPLETE.
     *
     * @param array|stdClass $record The submission record.
     * @return \mod_verbalfeedback\model\submission The saved submission.
     */
    public function create_submission($record) {
        $record = (object)(array)$record;

        if (!isset($record->instanceid)) {
            throw new coding_exception('The submission record must specify an instanceid.');
        }
        if (!isset($record->fromuserid) || !isset($record->touserid)) {
            throw new coding_exception('The submission record must specify a fromuserid and a touserid.');
        }

        $instanceid = (int)$record->instanceid;
        $fromuserid = (int)$record->fromuserid;
        $touserid = (int)$record->touserid;
        $rating = isset($record->rating) ? (int)$record->rating : 5;
        $status = isset($record->status)
            ? (int)$record->status
            : \mod_verbalfeedback\model\submission_status::COMPLETE;

        $instance = \mod_verbalfeedback\repository\instance_repository::get_by_id($instanceid);

        // Build a response for every criterion of every category of the instance.
        $responses = [];
        foreach ($instance->get_categories() as $category) {
            foreach ($category->get_criteria() as $criterion) {
                $responses[] = new \mod_verbalfeedback\model\response(
                    0,
                    $instanceid,
                    $criterion->get_id(),
                    $fromuserid,
                    $touserid,
                    $rating,
                    'Behat generated public comment.',
                    'Behat generated private comment.'
                );
            }
        }

        $submission = new \mod_verbalfeedback\model\submission(
            0,
            $instanceid,
            $fromuserid,
            $touserid,
            $status,
            '',
            $responses
        );

        $repo = new \mod_verbalfeedback\repository\submission_repository();
        $submissionid = $repo->save($submission);

        return $repo->get_by_id($submissionid);
    }
}
