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
 * PHPUnit verbal feedback generator tests.
 *
 * @package    mod_verbalfeedback
 * @copyright  2021 Luca Bösch <luca.boesch@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_verbalfeedback;

/**
 * PHPUnit verbal feedback generator testcase.
 *
 * @package    mod_verbalfeedback
 * @copyright  2021 Luca Bösch <luca.boesch@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class generator_test extends \advanced_testcase {
    /**
     * Test the verbalfeedback test data generator
     *
     * @covers \mod_verbalfeedback_generator
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_generator() {
        global $DB;

        $this->resetAfterTest(true);

        $this->assertEquals(0, $DB->count_records('verbalfeedback'));

        $course = $this->getDataGenerator()->create_course();

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_verbalfeedback');
        $this->assertInstanceOf('mod_verbalfeedback_generator', $generator);
        $this->assertEquals('verbalfeedback', $generator->get_modulename());

        $generator->create_instance(['course' => $course->id]);
        $generator->create_instance(['course' => $course->id]);
        $verbalfeedback = $generator->create_instance(['course' => $course->id]);
        $this->assertEquals(3, $DB->count_records('verbalfeedback'));

        $cm = get_coursemodule_from_instance('verbalfeedback', $verbalfeedback->id);
        $this->assertEquals($verbalfeedback->id, $cm->instance);
        $this->assertEquals('verbalfeedback', $cm->modname);
        $this->assertEquals($course->id, $cm->course);

        $context = \context_module::instance($cm->id);
        $this->assertEquals($verbalfeedback->cmid, $context->instanceid);

        // Test gradebook integration using low level DB access - DO NOT USE IN PLUGIN CODE!
        $verbalfeedback = $generator->create_instance(['course' => $course->id, 'assessed' => 1, 'grade' => 80]);

        $gitem = $DB->get_record('grade_items', ['courseid' => $course->id, 'itemtype' => 'mod',
            'itemmodule' => 'verbalfeedback', 'iteminstance' => $verbalfeedback->id, ], );

        $this->assertNotEmpty($gitem);
        $this->assertEquals(80, $gitem->grademax);
        $this->assertEquals(0, $gitem->grademin);
        $this->assertEquals(GRADE_TYPE_VALUE, $gitem->gradetype);
    }
}
