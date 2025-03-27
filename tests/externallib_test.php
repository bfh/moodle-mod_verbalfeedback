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
 * External functions test for verbalfeedback plugin.
 *
 * @package    mod_verbalfeedback
 * @copyright  2022 Luca Bösch <luca.boesch@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_verbalfeedback;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/lib/external/externallib.php');

/**
 * This class contains the test cases for webservices.
 *
 * @package    mod_verbalfeedback
 * @copyright  2022 Luca Bösch <luca.boesch@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class externallib_test extends \externallib_advanced_testcase {
    /** @var core_course_category */
    protected $category;
    /** @var stdClass */
    protected $course;
    /** @var stdClass */
    protected $teacher;
    /** @var array */
    protected $students;

    /**
     * Setup verbalfeedback.
     */
    public function setUp(): void {
        parent::setUp();
        $this->category = $this->getDataGenerator()->create_category();
        $this->course = $this->getDataGenerator()->create_course(['category' => $this->category->id]);
        $this->getDataGenerator()->create_module('verbalfeedback', ['course' => $this->course->id]);

        $this->create_and_enrol_users();

        $this->setUser($this->teacher);
    }

    /**
     * Creating 10 students and 1 teacher.
     */
    protected function create_and_enrol_users() {
        $this->students = [];
        for ($i = 0; $i < 10; $i++) {
            $this->students[] = $this->getDataGenerator()->create_and_enrol($this->course, 'student');
        }

        $this->teacher = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
    }

    /**
     * Test the function to match a model to its verbal feedback instance.
     *
     * @covers ::mod_verbalfeedback_view_model_to_instance
     */
    public function test_mod_verbalfeedback_view_model_to_instance(): void {
        $this->resetAfterTest();

        $instance = mod_verbalfeedback_view_model_to_instance((object)['course' => $this->course->id]);
        $this->assertInstanceOf('mod_verbalfeedback\model\instance', $instance);
    }
}
