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
 * PHPUnit tests for API helper functions.
 *
 * @package    mod_verbalfeedback
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_verbalfeedback;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/mod/verbalfeedback/lib.php');

use mod_verbalfeedback\repository\tables;

/**
 * Tests for mod_verbalfeedback\api
 */
final class api_test extends \advanced_testcase {
    /**
     * Test getting instance and checking if it's ready.
     * @covers \mod_verbalfeedback\api::get_instance
     * @covers \mod_verbalfeedback\api::is_ready
     */
    public function test_get_instance_and_is_ready(): void {
        $this->resetAfterTest();

        $category = $this->getDataGenerator()->create_category();
        $course = $this->getDataGenerator()->create_course(['category' => $category->id]);
        $vf = $this->getDataGenerator()->create_module('verbalfeedback', ['course' => $course->id]);

        $instance = api::get_instance($vf->id);
        $this->assertNotEmpty($instance);
        $this->assertEquals($vf->id, $instance->id);

        // Using get_instance cached value, so is_ready should return true.
        $this->assertTrue(api::is_ready($vf->id));
    }

    /**
     * Test getting instance by item and category ID.
     * @covers \mod_verbalfeedback\api::get_instance_by_itemid
     * @covers \mod_verbalfeedback\api::get_instance_by_categoryid
     */
    public function test_get_instance_by_item_and_category_id(): void {
        global $DB;
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $vf = $this->getDataGenerator()->create_module('verbalfeedback', ['course' => $course->id]);

        $catid = $DB->insert_record(tables::INSTANCE_CATEGORY_TABLE, (object)[
            'instanceid' => $vf->id,
            'position' => 0,
            'weight' => 1.0,
        ]);

        $critid = $DB->insert_record(tables::INSTANCE_CRITERION_TABLE, (object)[
            'categoryid' => $catid,
            'position' => 0,
            'weight' => 1.0,
        ]);

        $bycrit = api::get_instance_by_itemid($critid);
        $this->assertNotEmpty($bycrit);
        $this->assertEquals($vf->id, $bycrit->id);

        $bycat = api::get_instance_by_categoryid($catid);
        $this->assertNotEmpty($bycat);
        $this->assertEquals($vf->id, $bycat->id);
    }

    /**
     * Test getting fields and scales.
     * @covers \mod_verbalfeedback\api::get_fields_for_participants
     * @covers \mod_verbalfeedback\api::get_scales
     */
    public function test_get_fields_and_scales(): void {
        $this->resetAfterTest();

        $fields = api::get_fields_for_participants();
        $this->assertIsArray($fields);
        $this->assertContains('id', $fields);
        $this->assertContains('firstname', $fields);

        $scales = api::get_scales();
        $this->assertIsArray($scales);
        // 6 numeric scales + 1 not applicable
        $this->assertCount(7, $scales);
        $this->assertArrayHasKey('scale', get_object_vars($scales[0]));
    }

    /**
     * Test getting participants and counting submission info.
     * @covers \mod_verbalfeedback\api::get_participants
     */
    public function test_get_participants_and_count_submission_info(): void {
        global $DB;
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $vf = $this->getDataGenerator()->create_module('verbalfeedback', ['course' => $course->id]);

        // Create and enrol two students.
        $studenta = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $studentb = $this->getDataGenerator()->create_and_enrol($course, 'student');

        // Create a submission from A -> B.
        $subid = $DB->insert_record(tables::SUBMISSION_TABLE, (object)[
            'instanceid' => $vf->id,
            'fromuserid' => $studenta->id,
            'touserid' => $studentb->id,
            'status' => api::STATUS_PENDING,
        ]);

        $participants = api::get_participants($vf->id, $studenta->id, []);
        $this->assertIsArray($participants);
        $this->assertArrayHasKey($studentb->id, $participants);
        $this->assertEquals($subid, $participants[$studentb->id]->submissionid);
        $this->assertEquals(api::STATUS_PENDING, $participants[$studentb->id]->submissionstatus);
    }

    /**
     * Test getting participants with various filters for user selection.
     * @covers \mod_verbalfeedback\api::get_participants
     */
    public function test_get_participants_with_multiple_students(): void {
        global $DB;
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $vf = $this->getDataGenerator()->create_module('verbalfeedback', ['course' => $course->id]);

        // Create and enrol multiple students with distinct names.
        $john = $this->getDataGenerator()->create_and_enrol($course, 'teacher', ['firstname' => 'John', 'lastname' => 'Doe']);
        $jane = $this->getDataGenerator()->create_and_enrol($course, 'student', ['firstname' => 'Jane', 'lastname' => 'Smith']);
        $bob = $this->getDataGenerator()->create_and_enrol($course, 'student', ['firstname' => 'Bob', 'lastname' => 'Jones']);
        $alice = $this->getDataGenerator()->create_and_enrol($course, 'student', ['firstname' => 'Alice', 'lastname' => 'Brown']);

        // Create submissions with different statuses.
        $DB->insert_record(tables::SUBMISSION_TABLE, (object)[
            'instanceid' => $vf->id,
            'fromuserid' => $john->id,
            'touserid' => $jane->id,
            'status' => api::STATUS_PENDING,
        ]);
        $DB->insert_record(tables::SUBMISSION_TABLE, (object)[
            'instanceid' => $vf->id,
            'fromuserid' => $john->id,
            'touserid' => $bob->id,
            'status' => api::STATUS_IN_PROGRESS,
        ]);
        $DB->insert_record(tables::SUBMISSION_TABLE, (object)[
            'instanceid' => $vf->id,
            'fromuserid' => $john->id,
            'touserid' => $alice->id,
            'status' => api::STATUS_COMPLETE,
        ]);

        // Test without filters - should return all participants except John.
        $participants = api::get_participants($vf->id, $john->id, []);
        $this->assertCount(3, $participants);

        // Test filter by user search - should find Jane.
        $participants = api::get_participants($vf->id, $john->id, ['usersearch' => 'Jane']);
        $this->assertCount(1, $participants);
        $this->assertArrayHasKey($jane->id, $participants);

        // Test filter by user search - should find users with 'Smith' in their name.
        $participants = api::get_participants($vf->id, $john->id, ['usersearch' => 'Smith']);
        $this->assertCount(1, $participants);
        $this->assertArrayHasKey($jane->id, $participants);

        // Test filter by user search - should find users with 'o' in their name.
        $participants = api::get_participants($vf->id, $john->id, ['usersearch' => 'o']);
        $this->assertCount(2, $participants);
        $this->assertArrayHasKey($alice->id, $participants);
        $this->assertArrayHasKey($bob->id, $participants);

        // Test filter by firstname initial.
        $participants = api::get_participants($vf->id, $john->id, ['tifirst' => 'B']);
        $this->assertCount(1, $participants);
        $this->assertArrayHasKey($bob->id, $participants);

        // Test filter by nonexistent firstname initial.
        $participants = api::get_participants($vf->id, $john->id, ['tifirst' => 'Z']);
        $this->assertCount(0, $participants);

        // Test filter by lastname initial.
        $participants = api::get_participants($vf->id, $john->id, ['tilast' => 'B']);
        $this->assertCount(1, $participants);
        $this->assertArrayHasKey($alice->id, $participants);

        // Test filter by nonexistent lastname initial.
        $participants = api::get_participants($vf->id, $john->id, ['tilast' => 'Z']);
        $this->assertCount(0, $participants);

        // Test filter by status - pending.
        $participants = api::get_participants($vf->id, $john->id, ['status' => api::STATUS_PENDING]);
        $this->assertCount(1, $participants);
        $this->assertArrayHasKey($jane->id, $participants);

        // Test filter by status - in progress.
        $participants = api::get_participants($vf->id, $john->id, ['status' => api::STATUS_IN_PROGRESS]);
        $this->assertCount(1, $participants);
        $this->assertArrayHasKey($bob->id, $participants);

        // Test filter by status - complete.
        $participants = api::get_participants($vf->id, $john->id, ['status' => api::STATUS_COMPLETE]);
        $this->assertCount(1, $participants);
        $this->assertArrayHasKey($alice->id, $participants);

        // Test filter by userid.
        $participants = api::get_participants($vf->id, $john->id, ['userid' => $alice->id]);
        $this->assertCount(1, $participants);
        $this->assertArrayHasKey($alice->id, $participants);
    }

    /**
     * Test getting participants with group filter.
     * @covers \mod_verbalfeedback\api::get_participants
     */
    public function test_get_participants_with_group_filter(): void {
        global $DB;
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $vf = $this->getDataGenerator()->create_module('verbalfeedback', ['course' => $course->id]);

        // Create groups.
        $group1 = $this->getDataGenerator()->create_group(['courseid' => $course->id, 'name' => 'Group 1']);
        $group2 = $this->getDataGenerator()->create_group(['courseid' => $course->id, 'name' => 'Group 2']);

        // Create and enrol students.
        $john = $this->getDataGenerator()->create_and_enrol($course, 'teacher', ['firstname' => 'John', 'lastname' => 'Doe']);
        $jane = $this->getDataGenerator()->create_and_enrol($course, 'student', ['firstname' => 'Jane', 'lastname' => 'Smith']);
        $bob = $this->getDataGenerator()->create_and_enrol($course, 'student', ['firstname' => 'Bob', 'lastname' => 'Jones']);
        $joe = $this->getDataGenerator()->create_and_enrol($course, 'student', ['firstname' => 'Joe', 'lastname' => 'Hope']);

        // Add users to groups.
        $this->getDataGenerator()->create_group_member(['groupid' => $group1->id, 'userid' => $jane->id]);
        $this->getDataGenerator()->create_group_member(['groupid' => $group2->id, 'userid' => $bob->id]);
        $this->getDataGenerator()->create_group_member(['groupid' => $group1->id, 'userid' => $joe->id]);

        // Create submissions.
        $DB->insert_record(tables::SUBMISSION_TABLE, (object)[
            'instanceid' => $vf->id,
            'fromuserid' => $john->id,
            'touserid' => $jane->id,
            'status' => api::STATUS_PENDING,
        ]);
        $DB->insert_record(tables::SUBMISSION_TABLE, (object)[
            'instanceid' => $vf->id,
            'fromuserid' => $john->id,
            'touserid' => $bob->id,
            'status' => api::STATUS_PENDING,
        ]);
        $DB->insert_record(tables::SUBMISSION_TABLE, (object)[
            'instanceid' => $vf->id,
            'fromuserid' => $john->id,
            'touserid' => $joe->id,
            'status' => api::STATUS_PENDING,
        ]);

        // Test filter by group 1 - should return Jane and Joe Hope (John is the current user).
        $participants = api::get_participants($vf->id, $john->id, ['group' => $group1->id]);
        $this->assertCount(2, $participants);
        $this->assertArrayHasKey($jane->id, $participants);
        $this->assertArrayHasKey($joe->id, $participants);

        // Test filter by group 2 - should return Bob.
        $participants = api::get_participants($vf->id, $john->id, ['group' => $group2->id]);
        $this->assertCount(1, $participants);
        $this->assertArrayHasKey($bob->id, $participants);

        // Test filter by group with usersearch.
        $participants = api::get_participants($vf->id, $john->id, ['group' => $group1->id, 'usersearch' => 'j']);
        $this->assertCount(2, $participants);
        $this->assertArrayHasKey($jane->id, $participants);
        $this->assertArrayHasKey($joe->id, $participants);

        $participants = api::get_participants($vf->id, $john->id, ['group' => $group1->id, 'usersearch' => 'jo']);
        $this->assertCount(1, $participants);
        $this->assertArrayHasKey($joe->id, $participants);
    }

    /**
     * Test that the current user is excluded from the list of participants.
     * @covers \mod_verbalfeedback\api::get_participants
     */
    public function test_get_participants_excludes_current_user(): void {
        global $DB;
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $vf = $this->getDataGenerator()->create_module('verbalfeedback', ['course' => $course->id]);

        // Create and enrol students.
        $john = $this->getDataGenerator()->create_and_enrol($course, 'student', ['firstname' => 'John', 'lastname' => 'Doe']);
        $jane = $this->getDataGenerator()->create_and_enrol($course, 'student', ['firstname' => 'Jane', 'lastname' => 'Smith']);

        // Create submissions.
        $DB->insert_record(tables::SUBMISSION_TABLE, (object)[
            'instanceid' => $vf->id,
            'fromuserid' => $john->id,
            'touserid' => $jane->id,
            'status' => api::STATUS_PENDING,
        ]);

        // Get participants for John - should not include John himself.
        $participants = api::get_participants($vf->id, $john->id, []);
        $this->assertCount(1, $participants);
        $this->assertArrayHasKey($jane->id, $participants);
        $this->assertArrayNotHasKey($john->id, $participants);
    }

    /**
     * Test combined filters for getting participants.
     * @covers \mod_verbalfeedback\api::get_participants
     */
    public function test_get_participants_combined_filters(): void {
        global $DB;
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $vf = $this->getDataGenerator()->create_module('verbalfeedback', ['course' => $course->id]);

        // Create and enrol students with similar names.
        $alice1 = $this->getDataGenerator()->create_and_enrol($course, 'student', ['firstname' => 'Alice', 'lastname' => 'Brown']);
        $alice2 = $this->getDataGenerator()->create_and_enrol($course, 'student', ['firstname' => 'Alice', 'lastname' => 'Jones']);
        $bob = $this->getDataGenerator()->create_and_enrol($course, 'student', ['firstname' => 'Bob', 'lastname' => 'Smith']);
        $john = $this->getDataGenerator()->create_and_enrol($course, 'teacher', ['firstname' => 'John', 'lastname' => 'Doe']);

        // Create submissions.
        $DB->insert_record(tables::SUBMISSION_TABLE, (object)[
            'instanceid' => $vf->id,
            'fromuserid' => $john->id,
            'touserid' => $alice1->id,
            'status' => api::STATUS_PENDING,
        ]);
        $DB->insert_record(tables::SUBMISSION_TABLE, (object)[
            'instanceid' => $vf->id,
            'fromuserid' => $john->id,
            'touserid' => $alice2->id,
            'status' => api::STATUS_IN_PROGRESS,
        ]);
        $DB->insert_record(tables::SUBMISSION_TABLE, (object)[
            'instanceid' => $vf->id,
            'fromuserid' => $john->id,
            'touserid' => $bob->id,
            'status' => api::STATUS_COMPLETE,
        ]);

        // Test combined filters - firstname Alice and status pending.
        $participants = api::get_participants($vf->id, $john->id, ['tifirst' => 'A', 'status' => api::STATUS_PENDING]);
        $this->assertCount(1, $participants);
        $this->assertArrayHasKey($alice1->id, $participants);

        // Test combined filters - lastname Jones and status in progress.
        $participants = api::get_participants($vf->id, $john->id, ['tilast' => 'J', 'status' => api::STATUS_IN_PROGRESS]);
        $this->assertCount(1, $participants);
        $this->assertArrayHasKey($alice2->id, $participants);

        // Test combined filters - usersearch j for Alice Jones and status in progress (should return only alice2).
        $participants = api::get_participants($vf->id, $john->id, ['usersearch' => 'j', 'status' => api::STATUS_IN_PROGRESS]);
        $this->assertCount(1, $participants);
        $this->assertArrayHasKey($alice2->id, $participants);
    }

    /**
     * Test saving responses and updating status.
     * @covers \mod_verbalfeedback\api::save_responses
     */
    public function test_save_responses_and_status_update(): void {
        global $DB;
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $vf = $this->getDataGenerator()->create_module('verbalfeedback', ['course' => $course->id]);

        $from = $this->getDataGenerator()->create_and_enrol($course, 'teacher');
        $to = $this->getDataGenerator()->create_and_enrol($course, 'student');

        // Create category + criterion.
        $catid = $DB->insert_record(tables::INSTANCE_CATEGORY_TABLE, (object)[
            'instanceid' => $vf->id,
            'position' => 0,
            'weight' => 1.0,
        ]);
        $critid = $DB->insert_record(tables::INSTANCE_CRITERION_TABLE, (object)[
            'categoryid' => $catid,
            'position' => 0,
            'weight' => 1.0,
        ]);

        // Create an empty submission via repository.
        $submission = new \mod_verbalfeedback\model\submission();
        $submission->set_instance_id($vf->id);
        $submission->set_from_user_id($from->id);
        $submission->set_to_user_id($to->id);
        $repo = new \mod_verbalfeedback\repository\submission_repository();
        $subid = $repo->save($submission);

        // Act as the from user.
        $this->setUser($from);

        $responses = [
            [
                'criterionid' => $critid,
                'value' => 3,
                'studentcomment' => 'Good',
                'privatecomment' => 'Private',
            ],
        ];

        $result = api::save_responses($vf->id, $subid, $to->id, $responses);
        $this->assertTrue($result);

        $saved = $repo->get_by_id($subid);
        $this->assertEquals(\mod_verbalfeedback\model\submission_status::IN_PROGRESS, $saved->get_status());
        $this->assertNotEmpty($saved->get_responses());
        $r = $saved->get_responses()[0];
        $this->assertEquals(3, $r->get_value());
        $this->assertEquals('Good', $r->get_student_comment());
    }

    /**
     * Test updating weights and counting awaiting feedback.
     * @covers \mod_verbalfeedback\api::update_item_multiplier
     * @covers \mod_verbalfeedback\api::update_category_percentage
     * @covers \mod_verbalfeedback\api::count_users_awaiting_feedback
     */
    public function test_update_weights_and_count_awaiting(): void {
        global $DB;
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $vf = $this->getDataGenerator()->create_module('verbalfeedback', ['course' => $course->id]);

        $catid = $DB->insert_record(tables::INSTANCE_CATEGORY_TABLE, (object)[
            'instanceid' => $vf->id,
            'position' => 0,
            'weight' => 0.2,
        ]);
        $critid = $DB->insert_record(tables::INSTANCE_CRITERION_TABLE, (object)[
            'categoryid' => $catid,
            'position' => 0,
            'weight' => 1.0,
        ]);

        $this->assertTrue(api::update_item_multiplier($critid, 2.5));
        $this->assertEquals(2.5, $DB->get_field(tables::INSTANCE_CRITERION_TABLE, 'weight', ['id' => $critid]));

        $this->assertTrue(api::update_category_percentage($catid, 0.5));
        $this->assertEquals(0.5, $DB->get_field(tables::INSTANCE_CATEGORY_TABLE, 'weight', ['id' => $catid]));

        // Count users awaiting feedback should return 0 for a fresh instance/user.
        $user = $this->getDataGenerator()->create_user();
        $count = api::count_users_awaiting_feedback($vf->id, $user->id);
        $this->assertEquals(0, $count);
    }
}
