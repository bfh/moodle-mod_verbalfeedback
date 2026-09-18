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
 * Privacy provider tests for mod_verbalfeedback.
 *
 * @package    mod_verbalfeedback
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_verbalfeedback\privacy;

use core_privacy\local\request\approved_userlist;
use mod_verbalfeedback\api;
use mod_verbalfeedback\repository\tables;

/**
 * Tests for mod_verbalfeedback\privacy\provider.
 *
 * @covers \mod_verbalfeedback\privacy\provider
 */
final class provider_test extends \advanced_testcase {
    /**
     * Insert a submission and a matching response for the given users.
     *
     * @param int $instanceid The verbalfeedback instance ID.
     * @param int $fromuserid The user giving the feedback.
     * @param int $touserid The user receiving the feedback.
     * @param int $criterionid The criterion the response belongs to.
     * @return array [submissionid, responseid]
     */
    protected function create_feedback(int $instanceid, int $fromuserid, int $touserid, int $criterionid): array {
        global $DB;

        $submissionid = $DB->insert_record(tables::SUBMISSION_TABLE, (object)[
            'instanceid' => $instanceid,
            'fromuserid' => $fromuserid,
            'touserid' => $touserid,
            'status' => api::STATUS_COMPLETE,
        ]);

        $responseid = $DB->insert_record(tables::RESPONSE_TABLE, (object)[
            'instanceid' => $instanceid,
            'submissionid' => $submissionid,
            'criterionid' => $criterionid,
            'fromuserid' => $fromuserid,
            'touserid' => $touserid,
            'value' => 3,
        ]);

        return [$submissionid, $responseid];
    }

    /**
     * Test that delete_data_for_users removes data for the approved users in the
     * given context, whether they are the respondent or the recipient, while
     * leaving the data of other users and other instances untouched.
     *
     * @covers \mod_verbalfeedback\privacy\provider::delete_data_for_users
     */
    public function test_delete_data_for_users(): void {
        global $DB;
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $vf1 = $this->getDataGenerator()->create_module('verbalfeedback', ['course' => $course->id]);
        $vf2 = $this->getDataGenerator()->create_module('verbalfeedback', ['course' => $course->id]);

        $cm1 = get_coursemodule_from_instance('verbalfeedback', $vf1->id);
        $context1 = \context_module::instance($cm1->id);

        // Create a category + criterion in each instance so responses can reference it.
        $crit1 = $this->create_criterion($vf1->id);
        $crit2 = $this->create_criterion($vf2->id);

        // Create users.
        $usera = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $userb = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $userc = $this->getDataGenerator()->create_and_enrol($course, 'student');

        // Feedback in the first instance.
        // A -> B: A is the respondent.
        $this->create_feedback($vf1->id, $usera->id, $userb->id, $crit1);
        // C -> A: A is the recipient.
        $this->create_feedback($vf1->id, $userc->id, $usera->id, $crit1);
        // B -> C: does not involve A, must be kept.
        $this->create_feedback($vf1->id, $userb->id, $userc->id, $crit1);

        // Feedback in the second instance involving A, must be kept (different context).
        $this->create_feedback($vf2->id, $usera->id, $userb->id, $crit2);

        // Sanity check the initial data.
        $this->assertEquals(3, $DB->count_records(tables::SUBMISSION_TABLE, ['instanceid' => $vf1->id]));
        $this->assertEquals(3, $DB->count_records(tables::RESPONSE_TABLE, ['instanceid' => $vf1->id]));
        $this->assertEquals(1, $DB->count_records(tables::SUBMISSION_TABLE, ['instanceid' => $vf2->id]));

        // Delete data for user A in the first instance's context.
        $approveduserlist = new approved_userlist($context1, 'mod_verbalfeedback', [$usera->id]);
        provider::delete_data_for_users($approveduserlist);

        // In the first instance, only the B -> C feedback should remain.
        $submissions = $DB->get_records(tables::SUBMISSION_TABLE, ['instanceid' => $vf1->id]);
        $this->assertCount(1, $submissions);
        $remaining = reset($submissions);
        $this->assertEquals($userb->id, $remaining->fromuserid);
        $this->assertEquals($userc->id, $remaining->touserid);

        $responses = $DB->get_records(tables::RESPONSE_TABLE, ['instanceid' => $vf1->id]);
        $this->assertCount(1, $responses);
        $remaining = reset($responses);
        $this->assertEquals($userb->id, $remaining->fromuserid);
        $this->assertEquals($userc->id, $remaining->touserid);

        // No submission/response referencing user A should be left in the first instance.
        $this->assertEquals(0, $DB->count_records_select(
            tables::SUBMISSION_TABLE,
            'instanceid = :instanceid AND (fromuserid = :from OR touserid = :to)',
            ['instanceid' => $vf1->id, 'from' => $usera->id, 'to' => $usera->id]
        ));
        $this->assertEquals(0, $DB->count_records_select(
            tables::RESPONSE_TABLE,
            'instanceid = :instanceid AND (fromuserid = :from OR touserid = :to)',
            ['instanceid' => $vf1->id, 'from' => $usera->id, 'to' => $usera->id]
        ));

        // The second instance must be untouched.
        $this->assertEquals(1, $DB->count_records(tables::SUBMISSION_TABLE, ['instanceid' => $vf2->id]));
        $this->assertEquals(1, $DB->count_records(tables::RESPONSE_TABLE, ['instanceid' => $vf2->id]));
    }

    /**
     * Test that delete_data_for_users only removes data for the approved users
     * and keeps the data of the other listed participants.
     *
     * @covers \mod_verbalfeedback\privacy\provider::delete_data_for_users
     */
    public function test_delete_data_for_users_subset_of_users(): void {
        global $DB;
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $vf = $this->getDataGenerator()->create_module('verbalfeedback', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('verbalfeedback', $vf->id);
        $context = \context_module::instance($cm->id);
        $crit = $this->create_criterion($vf->id);

        $usera = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $userb = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $userc = $this->getDataGenerator()->create_and_enrol($course, 'student');

        $this->create_feedback($vf->id, $usera->id, $userb->id, $crit);
        $this->create_feedback($vf->id, $userb->id, $userc->id, $crit);

        // Delete data for user A only.
        $approveduserlist = new approved_userlist($context, 'mod_verbalfeedback', [$usera->id]);
        provider::delete_data_for_users($approveduserlist);

        // The B -> C feedback (not involving A) must be kept.
        $this->assertEquals(1, $DB->count_records(tables::SUBMISSION_TABLE, ['instanceid' => $vf->id]));
        $this->assertEquals(1, $DB->count_records(tables::RESPONSE_TABLE, ['instanceid' => $vf->id]));
    }

    /**
     * Test that delete_data_for_users does nothing when the context is not a
     * module context.
     *
     * @covers \mod_verbalfeedback\privacy\provider::delete_data_for_users
     */
    public function test_delete_data_for_users_invalid_context(): void {
        global $DB;
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $vf = $this->getDataGenerator()->create_module('verbalfeedback', ['course' => $course->id]);
        $crit = $this->create_criterion($vf->id);

        $usera = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $userb = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $this->create_feedback($vf->id, $usera->id, $userb->id, $crit);

        // Use a course context instead of a module context.
        $coursecontext = \context_course::instance($course->id);
        $approveduserlist = new approved_userlist($coursecontext, 'mod_verbalfeedback', [$usera->id]);
        provider::delete_data_for_users($approveduserlist);

        // Nothing should have been deleted.
        $this->assertEquals(1, $DB->count_records(tables::SUBMISSION_TABLE, ['instanceid' => $vf->id]));
        $this->assertEquals(1, $DB->count_records(tables::RESPONSE_TABLE, ['instanceid' => $vf->id]));
    }

    /**
     * Create a category and criterion for the given instance and return the
     * criterion ID.
     *
     * @param int $instanceid The verbalfeedback instance ID.
     * @return int The created criterion ID.
     */
    protected function create_criterion(int $instanceid): int {
        global $DB;

        $catid = $DB->insert_record(tables::INSTANCE_CATEGORY_TABLE, (object)[
            'instanceid' => $instanceid,
            'position' => 0,
            'weight' => 1.0,
        ]);
        return $DB->insert_record(tables::INSTANCE_CRITERION_TABLE, (object)[
            'categoryid' => $catid,
            'position' => 0,
            'weight' => 1.0,
        ]);
    }
}
