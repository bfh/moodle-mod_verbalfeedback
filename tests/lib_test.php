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
}
