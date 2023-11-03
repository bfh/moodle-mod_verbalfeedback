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

use mod_verbalfeedback\model\instance;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/mod/verbalfeedback/lib.php');

/**
 * PHPUnit verbal feedback generator testcase.
 *
 * @package    mod_verbalfeedback
 * @copyright  2021 Luca Bösch <luca.boesch@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class verbalfeedback_test extends \advanced_testcase {

    /**
     * @var core_course_category Course category used for testing
     */
    protected $category;

    /**
     * @var $course Course used for testing
     */
    protected $course;

    /**
     * @var int Course id used for testing
     */
    protected $courseid;

    /**
     * @var \core_user Teacher used for testing
     */
    protected $teacher;

    /**
     * @var array<\core_user> Array of students used for testion
     */
    protected $students;
    /**
     * @var instance Verbal feedback used for testing
     */
    protected $verbalfeedback;
    /**
     * @var int Verbal feedback id used for testing
     */
    protected $verbalfeedbackid;
    /**
     * @var string Verbal feedback name used for testing
     */
    protected $verbalfeedbackname;

    /**
     * Setup verbalfeedback.
     */
    public function setUp(): void {
        global $DB;
        $this->category = $this->getDataGenerator()->create_category();
        $this->course = $this->getDataGenerator()->create_course(['category' => $this->category->id]);
        $this->courseid = $this->course->id;
        $this->verbalfeedback = $this->getDataGenerator()->create_module('verbalfeedback', ['course' => $this->course->id]);
        $this->verbalfeedbackid = $this->verbalfeedback->id;
        $this->verbalfeedbackname = $this->verbalfeedback->name;
        $cm = $DB->get_record('course_modules', ['id' => $this->verbalfeedback->cmid], '*', MUST_EXIST);

        $this->create_and_enrol_users();
    }

    /** Creating 10 students and 1 teacher. */
    protected function create_and_enrol_users() {
        $this->students = [];
        for ($i = 0; $i < 10; $i++) {
            $this->students[] = $this->getDataGenerator()->create_and_enrol($this->course, 'student');
        }

        $this->teacher = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
    }

    /**
     * Test a verbalfeedback instance
     *
     * @covers \mod_verbalfeedback\model\instance
     */
    public function test_verbalfeedback() {
        $this->resetAfterTest();
        $this->assertEquals('Verbal feedback 1', $this->verbalfeedback->name);
    }

    /**
     * Test the verbalfeedback verbalfeedback_get_user_grades function
     *
     * @covers ::verbalfeedback_get_user_grades
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_verbalfeedback_get_user_grade() {
        $this->resetAfterTest();
        $this->assertEquals([], verbalfeedback_get_user_grades($this->verbalfeedback));
    }
}
