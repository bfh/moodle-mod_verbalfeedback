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
 * PHPUnit tests for mod_verbalfeedback lib functions.
 *
 * @package    mod_verbalfeedback
 * @copyright  2026 Stephan Robotta <stephan.robotta@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_verbalfeedback;

use mod_verbalfeedback\model\submission_status;
use mod_verbalfeedback\repository\tables;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/mod/verbalfeedback/lib.php');

/**
 * PHPUnit tests for mod_verbalfeedback lib functions.
 *
 * @package    mod_verbalfeedback
 * @copyright  2026 Stephan Robotta <stephan.robotta@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class lib_test extends \advanced_testcase {
    /**
     * Create a verbalfeedback instance in the given course and populate it with
     * a full chain of user data: a category, a criterion, a subrating, a
     * submission and a response.
     *
     * @param \stdClass $course the course to add the instance to
     * @return \stdClass the created verbalfeedback instance record
     */
    protected function create_populated_instance(\stdClass $course): \stdClass {
        global $DB;

        $instance = $this->getDataGenerator()->create_module('verbalfeedback', ['course' => $course->id]);

        $categoryid = $DB->insert_record(tables::INSTANCE_CATEGORY_TABLE, (object) [
            'instanceid' => $instance->id,
            'position' => 0,
            'weight' => 0.2,
        ]);

        $criterionid = $DB->insert_record(tables::INSTANCE_CRITERION_TABLE, (object) [
            'categoryid' => $categoryid,
            'position' => 0,
            'weight' => 0.2,
        ]);

        $DB->insert_record(tables::INSTANCE_SUBRATING_TABLE, (object) [
            'criterionid' => $criterionid,
        ]);

        $submissionid = $DB->insert_record(tables::SUBMISSION_TABLE, (object) [
            'instanceid' => $instance->id,
            'fromuserid' => 1,
            'touserid' => 2,
            'status' => 2,
        ]);

        $DB->insert_record(tables::RESPONSE_TABLE, (object) [
            'instanceid' => $instance->id,
            'submissionid' => $submissionid,
            'criterionid' => $criterionid,
            'fromuserid' => 1,
            'touserid' => 2,
            'value' => 3,
        ]);

        return $instance;
    }

    /**
     * Count every user-data record associated with the given instance.
     *
     * @param \stdClass $instance the verbalfeedback instance record
     * @return int total number of user-data records across all tables
     */
    protected function count_instance_data(\stdClass $instance): int {
        global $DB;

        $categoryids = $DB->get_fieldset_select(tables::INSTANCE_CATEGORY_TABLE, 'id', 'instanceid = ?', [$instance->id]);
        $criterionids = [];
        if ($categoryids) {
            [$insql, $params] = $DB->get_in_or_equal($categoryids);
            $criterionids = $DB->get_fieldset_select(tables::INSTANCE_CRITERION_TABLE, 'id', "categoryid $insql", $params);
        }
        $subratings = 0;
        if ($criterionids) {
            [$insql, $params] = $DB->get_in_or_equal($criterionids);
            $subratings = $DB->count_records_select(tables::INSTANCE_SUBRATING_TABLE, "criterionid $insql", $params);
        }

        return $DB->count_records(tables::RESPONSE_TABLE, ['instanceid' => $instance->id])
            + $DB->count_records(tables::SUBMISSION_TABLE, ['instanceid' => $instance->id])
            + count($categoryids)
            + count($criterionids)
            + $subratings;
    }

    /**
     * When the reset_verbalfeedback flag is not set, nothing is removed and the
     * returned status is empty.
     *
     * @covers ::verbalfeedback_reset_userdata
     */
    public function test_verbalfeedback_reset_userdata_not_requested(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $instance = $this->create_populated_instance($course);
        $before = $this->count_instance_data($instance);
        $this->assertGreaterThan(0, $before);

        $status = verbalfeedback_reset_userdata((object) ['courseid' => $course->id]);

        $this->assertSame([], $status);
        $this->assertEquals($before, $this->count_instance_data($instance));
    }

    /**
     * When the reset_verbalfeedback flag is set, all user data for the given
     * course is removed while data in other courses is kept intact.
     *
     * @covers ::verbalfeedback_reset_userdata
     */
    public function test_verbalfeedback_reset_userdata(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $othercourse = $this->getDataGenerator()->create_course();

        $instance = $this->create_populated_instance($course);
        $otherinstance = $this->create_populated_instance($othercourse);

        $this->assertGreaterThan(0, $this->count_instance_data($instance));
        $otherbefore = $this->count_instance_data($otherinstance);

        $status = verbalfeedback_reset_userdata((object) [
            'courseid' => $course->id,
            'reset_verbalfeedback' => 1,
        ]);

        // The targeted course has all its user data removed.
        $this->assertEquals(0, $this->count_instance_data($instance));

        // Data in the other course must remain untouched.
        $this->assertEquals($otherbefore, $this->count_instance_data($otherinstance));

        // A status line is returned for each removed item.
        $this->assertNotEmpty($status);
        foreach ($status as $line) {
            $this->assertArrayHasKey('item', $line);
            $this->assertArrayHasKey('error', $line);
            $this->assertFalse($line['error']);
        }
    }

    /**
     * Create a verbalfeedback instance set up for grading: a single category
     * holding $numcriteria equally weighted criteria, with the module max grade
     * fixed at 100 so that a perfect score maps to exactly 100.
     *
     * @param \stdClass $course the course to add the instance to
     * @param int $numcriteria how many criteria the single category should hold
     * @return array{0: \stdClass, 1: int[]} the instance record and the criterion ids
     */
    protected function create_grading_instance(\stdClass $course, int $numcriteria): array {
        global $DB;

        $instance = $this->getDataGenerator()->create_module('verbalfeedback', [
            'course' => $course->id,
            'grade' => 100,
        ]);
        // Make sure the maximum grade is deterministic regardless of defaults.
        $DB->set_field(tables::INSTANCE_TABLE, 'grade', 100, ['id' => $instance->id]);
        $instance->grade = 100;

        $categoryid = $DB->insert_record(tables::INSTANCE_CATEGORY_TABLE, (object) [
            'instanceid' => $instance->id,
            'position' => 0,
            'weight' => 1.0,
        ]);

        $criterionids = [];
        for ($i = 0; $i < $numcriteria; $i++) {
            $criterionids[] = (int) $DB->insert_record(tables::INSTANCE_CRITERION_TABLE, (object) [
                'categoryid' => $categoryid,
                'position' => $i,
                'weight' => 1.0,
            ]);
        }

        return [$instance, $criterionids];
    }

    /**
     * Record one reviewer's (fromuser) completed feedback about a participant
     * (touser): a submission plus one response per criterion. A null value stores
     * an N/A response.
     *
     * @param \stdClass $instance the verbalfeedback instance record
     * @param int $fromuserid the reviewer giving the feedback
     * @param int $touserid the rated participant
     * @param array $values <int, int|null> map of criterionid => response value (null means N/A)
     */
    protected function add_feedback(\stdClass $instance, int $fromuserid, int $touserid, array $values): void {
        global $DB;

        $submissionid = $DB->insert_record(tables::SUBMISSION_TABLE, (object) [
            'instanceid' => $instance->id,
            'fromuserid' => $fromuserid,
            'touserid' => $touserid,
            'status' => submission_status::COMPLETE,
        ]);

        foreach ($values as $criterionid => $value) {
            $DB->insert_record(tables::RESPONSE_TABLE, (object) [
                'instanceid' => $instance->id,
                'submissionid' => $submissionid,
                'criterionid' => $criterionid,
                'fromuserid' => $fromuserid,
                'touserid' => $touserid,
                'value' => $value,
            ]);
        }
    }

    /**
     * An N/A response (value NULL) must not be graded like a zero. A participant
     * scored 0 on a criterion must end up with a lower grade than one marked N/A
     * on that same criterion: N/A drops the criterion from the weighting instead
     * of counting as zero points.
     *
     * @covers ::verbalfeedback_get_user_grades
     */
    public function test_verbalfeedback_get_user_grades_na_is_not_zero(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        [$instance, $criterionids] = $this->create_grading_instance($course, 2);
        [$crit1, $crit2] = $criterionids;

        $reviewer = $this->getDataGenerator()->create_user();
        $scoredzero = $this->getDataGenerator()->create_user();
        $markedna = $this->getDataGenerator()->create_user();

        // Scored 5 on the first criterion and a genuine 0 on the second.
        $this->add_feedback($instance, $reviewer->id, $scoredzero->id, [$crit1 => 5, $crit2 => 0]);
        // Scored 5 on the first criterion and N/A on the second.
        $this->add_feedback($instance, $reviewer->id, $markedna->id, [$crit1 => 5, $crit2 => null]);

        $grades = verbalfeedback_get_user_grades($instance);

        // A 0 on one of two equally weighted criteria: (5 + 0) / 2 = 2.5 out of 5 => 50.
        $this->assertEqualsWithDelta(50.0, (float) $grades[$scoredzero->id]->rawgrade, 0.0001);
        // N/A drops the second criterion entirely, leaving 5 out of 5 => 100.
        $this->assertEqualsWithDelta(100.0, (float) $grades[$markedna->id]->rawgrade, 0.0001);
        // The two must not collapse to the same grade (the bug this guards against).
        $this->assertNotEquals(
            (float) $grades[$scoredzero->id]->rawgrade,
            (float) $grades[$markedna->id]->rawgrade
        );
    }

    /**
     * When several reviewers rate the same criterion, an N/A response must be
     * left out of the average rather than counted as a value. Two reviewers
     * rating 5 and N/A must average to 5, not (5 + 0) / 2.
     *
     * @covers ::verbalfeedback_get_user_grades
     */
    public function test_verbalfeedback_get_user_grades_na_excluded_from_average(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        [$instance, $criterionids] = $this->create_grading_instance($course, 1);
        [$crit1] = $criterionids;

        $reviewer1 = $this->getDataGenerator()->create_user();
        $reviewer2 = $this->getDataGenerator()->create_user();
        $participant = $this->getDataGenerator()->create_user();

        // One reviewer scores 5, the other marks N/A for the single criterion.
        $this->add_feedback($instance, $reviewer1->id, $participant->id, [$crit1 => 5]);
        $this->add_feedback($instance, $reviewer2->id, $participant->id, [$crit1 => null]);

        $grades = verbalfeedback_get_user_grades($instance);

        // Average over actual ratings only: 5 / 1 = 5 out of 5 => 100 (not 5 / 2 => 50).
        $this->assertEqualsWithDelta(100.0, (float) $grades[$participant->id]->rawgrade, 0.0001);
    }
}
