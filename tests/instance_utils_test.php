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
 * PHPUnit tests for the group scoping of the participants list.
 *
 * @package    mod_verbalfeedback
 * @copyright  2026 BFH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_verbalfeedback;

use mod_verbalfeedback\utils\instance_utils;
use PHPUnit\Framework\Attributes\CoversClass;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/mod/verbalfeedback/lib.php');

/**
 * Tests for the group handling that drives which students a user sees on view.php.
 *
 * The participants list must mirror core activities such as mod/assign: the group
 * filter is derived from the activity's effective group mode, never from the raw course
 * group mode.
 */
#[CoversClass(instance_utils::class)]
#[CoversClass(api::class)]
final class instance_utils_test extends \advanced_testcase {
    /**
     * Returns the course module record and course object for a verbal feedback instance.
     *
     * @param object $vf The verbal feedback instance as returned by the generator.
     * @return array [stdClass $cm, stdClass $course]
     */
    private function get_cm_and_course($vf): array {
        $cm = get_coursemodule_from_instance('verbalfeedback', $vf->id);
        $course = get_course($cm->course);
        return [$cm, $course];
    }

    /**
     * When neither the course nor the activity use groups, no group filter is applied and the
     * user sees every student in the course.
     *
     * @covers ::get_current_group
     */
    public function test_no_groups_shows_all_students(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $vf = $this->getDataGenerator()->create_module('verbalfeedback', ['course' => $course->id]);
        [$cm, $course] = $this->get_cm_and_course($vf);

        $teacher = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');
        $student1 = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $student2 = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $student3 = $this->getDataGenerator()->create_and_enrol($course, 'student');

        $this->setUser($teacher);

        // No group mode anywhere, so there is no group filter.
        $currentgroup = instance_utils::get_current_group($cm, $course);
        $this->assertSame(0, $currentgroup);

        $participants = api::get_participants($vf->id, $teacher->id, ['group' => $currentgroup]);
        $this->assertCount(3, $participants);
        $this->assertArrayHasKey($student1->id, $participants);
        $this->assertArrayHasKey($student2->id, $participants);
        $this->assertArrayHasKey($student3->id, $participants);
    }

    /**
     * The course is in separate groups mode (not forced), but the verbal feedback activity
     * itself uses no groups. A teacher without the accessallgroups capability, who is a member
     * of a single group, must still see every student of the activity, not only the members of
     * their own group.
     *
     * @covers ::get_current_group
     */
    public function test_activity_without_groups_on_separate_groups_course_shows_all_students(): void {
        $this->resetAfterTest();

        // Course uses separate groups, but it is NOT forced on activities.
        $course = $this->getDataGenerator()->create_course([
            'groupmode' => SEPARATEGROUPS,
            'groupmodeforce' => 0,
        ]);
        // The activity itself uses no groups.
        $vf = $this->getDataGenerator()->create_module('verbalfeedback', [
            'course' => $course->id,
            'groupmode' => NOGROUPS,
        ]);
        [$cm, $course] = $this->get_cm_and_course($vf);

        $even = $this->getDataGenerator()->create_group(['courseid' => $course->id, 'name' => 'even']);
        $odd = $this->getDataGenerator()->create_group(['courseid' => $course->id, 'name' => 'odd']);

        // Non-editing teacher: does NOT hold moodle/site:accessallgroups by default.
        $teacher = $this->getDataGenerator()->create_and_enrol($course, 'teacher');
        $this->getDataGenerator()->create_group_member(['groupid' => $even->id, 'userid' => $teacher->id]);

        $evenstudent1 = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $evenstudent2 = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $oddstudent1 = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $oddstudent2 = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $this->getDataGenerator()->create_group_member(['groupid' => $even->id, 'userid' => $evenstudent1->id]);
        $this->getDataGenerator()->create_group_member(['groupid' => $even->id, 'userid' => $evenstudent2->id]);
        $this->getDataGenerator()->create_group_member(['groupid' => $odd->id, 'userid' => $oddstudent1->id]);
        $this->getDataGenerator()->create_group_member(['groupid' => $odd->id, 'userid' => $oddstudent2->id]);

        $this->setUser($teacher);

        // The activity has no groups, so no group filter must be applied.
        $currentgroup = instance_utils::get_current_group($cm, $course);
        $this->assertSame(0, $currentgroup);

        // The teacher sees students from both groups, not just their own "even" group.
        $participants = api::get_participants($vf->id, $teacher->id, ['group' => $currentgroup]);
        $this->assertCount(4, $participants);
        $this->assertArrayHasKey($evenstudent1->id, $participants);
        $this->assertArrayHasKey($evenstudent2->id, $participants);
        $this->assertArrayHasKey($oddstudent1->id, $participants);
        $this->assertArrayHasKey($oddstudent2->id, $participants);
    }

    /**
     * When the activity is in separate groups mode and the user lacks accessallgroups, the list
     * is scoped to the user's own group: they see their group's students and none from other
     * groups.
     *
     * @covers ::get_current_group
     */
    public function test_separate_groups_without_accessallgroups_scopes_to_own_group(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        // The activity enforces separate groups.
        $vf = $this->getDataGenerator()->create_module('verbalfeedback', [
            'course' => $course->id,
            'groupmode' => SEPARATEGROUPS,
        ]);
        [$cm, $course] = $this->get_cm_and_course($vf);

        $groupa = $this->getDataGenerator()->create_group(['courseid' => $course->id, 'name' => 'Group A']);
        $groupb = $this->getDataGenerator()->create_group(['courseid' => $course->id, 'name' => 'Group B']);

        // Non-editing teacher without accessallgroups, member of Group A only.
        $teacher = $this->getDataGenerator()->create_and_enrol($course, 'teacher');
        $this->getDataGenerator()->create_group_member(['groupid' => $groupa->id, 'userid' => $teacher->id]);

        $astudent1 = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $astudent2 = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $bstudent1 = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $this->getDataGenerator()->create_group_member(['groupid' => $groupa->id, 'userid' => $astudent1->id]);
        $this->getDataGenerator()->create_group_member(['groupid' => $groupa->id, 'userid' => $astudent2->id]);
        $this->getDataGenerator()->create_group_member(['groupid' => $groupb->id, 'userid' => $bstudent1->id]);

        $this->setUser($teacher);

        // The teacher is restricted to their own Group A.
        $currentgroup = instance_utils::get_current_group($cm, $course);
        $this->assertSame((int)$groupa->id, $currentgroup);

        // Only Group A students are visible; the Group B student is not.
        $participants = api::get_participants($vf->id, $teacher->id, ['group' => $currentgroup]);
        $this->assertCount(2, $participants);
        $this->assertArrayHasKey($astudent1->id, $participants);
        $this->assertArrayHasKey($astudent2->id, $participants);
        $this->assertArrayNotHasKey($bstudent1->id, $participants);
    }

    /**
     * When the activity is in separate groups mode but the user holds accessallgroups (e.g. an
     * editing teacher), no single group is forced: they can see every student across all groups.
     *
     * @covers ::get_current_group
     */
    public function test_separate_groups_with_accessallgroups_shows_all_students(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $vf = $this->getDataGenerator()->create_module('verbalfeedback', [
            'course' => $course->id,
            'groupmode' => SEPARATEGROUPS,
        ]);
        [$cm, $course] = $this->get_cm_and_course($vf);

        $groupa = $this->getDataGenerator()->create_group(['courseid' => $course->id, 'name' => 'Group A']);
        $groupb = $this->getDataGenerator()->create_group(['courseid' => $course->id, 'name' => 'Group B']);

        // Editing teacher: holds moodle/site:accessallgroups by default.
        $teacher = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');
        $this->getDataGenerator()->create_group_member(['groupid' => $groupa->id, 'userid' => $teacher->id]);

        $astudent = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $bstudent = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $this->getDataGenerator()->create_group_member(['groupid' => $groupa->id, 'userid' => $astudent->id]);
        $this->getDataGenerator()->create_group_member(['groupid' => $groupb->id, 'userid' => $bstudent->id]);

        $this->setUser($teacher);

        // Capability accessallgroups means the default selection is "all groups" (0).
        $currentgroup = instance_utils::get_current_group($cm, $course);
        $this->assertSame(0, $currentgroup);

        $participants = api::get_participants($vf->id, $teacher->id, ['group' => $currentgroup]);
        $this->assertCount(2, $participants);
        $this->assertArrayHasKey($astudent->id, $participants);
        $this->assertArrayHasKey($bstudent->id, $participants);
    }

    /**
     * When the course forces separate groups, the activity inherits that mode even if the
     * activity's own group mode is "no groups". A teacher without accessallgroups is then
     * correctly scoped to their own group.
     *
     * @covers ::get_current_group
     */
    public function test_forced_separate_groups_scopes_to_own_group(): void {
        $this->resetAfterTest();

        // Course forces separate groups onto all activities.
        $course = $this->getDataGenerator()->create_course([
            'groupmode' => SEPARATEGROUPS,
            'groupmodeforce' => 1,
        ]);
        // Activity requests no groups, but the forced course mode overrides it.
        $vf = $this->getDataGenerator()->create_module('verbalfeedback', [
            'course' => $course->id,
            'groupmode' => NOGROUPS,
        ]);
        [$cm, $course] = $this->get_cm_and_course($vf);

        $groupa = $this->getDataGenerator()->create_group(['courseid' => $course->id, 'name' => 'Group A']);
        $groupb = $this->getDataGenerator()->create_group(['courseid' => $course->id, 'name' => 'Group B']);

        $teacher = $this->getDataGenerator()->create_and_enrol($course, 'teacher');
        $this->getDataGenerator()->create_group_member(['groupid' => $groupa->id, 'userid' => $teacher->id]);

        $astudent = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $bstudent = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $this->getDataGenerator()->create_group_member(['groupid' => $groupa->id, 'userid' => $astudent->id]);
        $this->getDataGenerator()->create_group_member(['groupid' => $groupb->id, 'userid' => $bstudent->id]);

        $this->setUser($teacher);

        // Forced group mode means the activity is effectively in separate groups.
        $currentgroup = instance_utils::get_current_group($cm, $course);
        $this->assertSame((int)$groupa->id, $currentgroup);

        $participants = api::get_participants($vf->id, $teacher->id, ['group' => $currentgroup]);
        $this->assertCount(1, $participants);
        $this->assertArrayHasKey($astudent->id, $participants);
        $this->assertArrayNotHasKey($bstudent->id, $participants);
    }
}
