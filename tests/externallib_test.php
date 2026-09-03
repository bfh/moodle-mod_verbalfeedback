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
 * @runTestsInSeparateProcesses
 */
final class externallib_test extends \externallib_advanced_testcase {
    /** @var core_course_category */
    protected $category;
    /** @var stdClass */
    protected $course;
    /** @var stdClass */
    protected $verbalfeedback;
    /** @var stdClass */
    protected $teacher;
    /** @var array */
    protected $students;

    /**
     * Setup verbalfeedback.
     */
    public function setUp(): void {
        global $SCRIPT;
        parent::setUp();
        // With @runTestsInSeparateProcesses some environments error about $SCRIPT being null.
        $SCRIPT = '';
        $this->category = $this->getDataGenerator()->create_category();
        $this->course = $this->getDataGenerator()->create_course(['category' => $this->category->id]);
        $this->verbalfeedback = $this->getDataGenerator()->create_module('verbalfeedback', ['course' => $this->course->id]);

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

    /**
     * Creates a submission with a single response for the given instance and users.
     *
     * @param int $instanceid The verbal feedback instance id.
     * @param int $fromuserid The id of the responding user.
     * @param int $touserid The id of the rated user.
     * @return \mod_verbalfeedback\model\submission The saved submission (reloaded from the repository).
     */
    protected function create_submission(int $instanceid, int $fromuserid, int $touserid): \mod_verbalfeedback\model\submission {
        $response = new \mod_verbalfeedback\model\response(
            0,
            $instanceid,
            1,
            $fromuserid,
            $touserid,
            5,
            'A public comment for the student.',
            'A private comment for the teacher.'
        );
        $submission = new \mod_verbalfeedback\model\submission(
            0,
            $instanceid,
            $fromuserid,
            $touserid,
            \mod_verbalfeedback\model\submission_status::PENDING,
            '',
            [$response]
        );

        $repo = new \mod_verbalfeedback\repository\submission_repository();
        $submissionid = $repo->save($submission);

        return $repo->get_by_id($submissionid);
    }

    /**
     * A user with the view_all_reports capability (e.g. a teacher) gets the responses including the private comment.
     *
     * @covers \mod_verbalfeedback_external::get_responses
     */
    public function test_get_responses_with_view_all_reports(): void {
        $this->resetAfterTest();

        $student = $this->students[0];
        $submission = $this->create_submission($this->verbalfeedback->id, $this->teacher->id, $student->id);

        $this->setUser($this->teacher);

        // The webservice layer casts parameters to their declared type (PARAM_INT), so mirror that here.
        $result = \mod_verbalfeedback_external::get_responses(
            (int)$this->verbalfeedback->id,
            (int)$this->teacher->id,
            (int)$student->id,
            $submission->get_id()
        );
        $result = \core_external\external_api::clean_returnvalue(\mod_verbalfeedback_external::get_responses_returns(), $result);

        $this->assertCount(1, $result['responses']);
        $response = reset($result['responses']);
        $this->assertSame(5, $response['value']);
        $this->assertSame('A public comment for the student.', $response['studentcomment']);
        // The teacher may see the private comment.
        $this->assertSame('A private comment for the teacher.', $response['privatecomment']);
    }

    /**
     * The rated user (receive_rating capability) gets the responses, but the private comment is hidden.
     *
     * @covers \mod_verbalfeedback_external::get_responses
     */
    public function test_get_responses_as_rated_user(): void {
        $this->resetAfterTest();

        $student = $this->students[0];
        $submission = $this->create_submission($this->verbalfeedback->id, $this->teacher->id, $student->id);

        $this->setUser($student);

        $result = \mod_verbalfeedback_external::get_responses(
            (int)$this->verbalfeedback->id,
            (int)$this->teacher->id,
            (int)$student->id,
            $submission->get_id()
        );

        $this->assertCount(1, $result['responses']);
        $response = reset($result['responses']);
        $this->assertSame(5, $response['value']);
        $this->assertSame('A public comment for the student.', $response['studentcomment']);
        // The rated user must not see the private comment.
        $this->assertNull($response['privatecomment']);
    }

    /**
     * A user who is neither allowed to view all reports nor the rated user is denied access.
     *
     * @covers \mod_verbalfeedback_external::get_responses
     */
    public function test_get_responses_no_permission(): void {
        $this->resetAfterTest();

        $rateduser = $this->students[0];
        $otheruser = $this->students[1];
        $submission = $this->create_submission($this->verbalfeedback->id, $this->teacher->id, $rateduser->id);

        // A different student trying to read someone else's feedback.
        $this->setUser($otheruser);

        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('nopermissions', 'error'));

        \mod_verbalfeedback_external::get_responses(
            (int)$this->verbalfeedback->id,
            (int)$this->teacher->id,
            (int)$rateduser->id,
            $submission->get_id()
        );
    }

    /**
     * When the submission does not belong to the given verbal feedback instance an exception is thrown.
     *
     * @covers \mod_verbalfeedback_external::get_responses
     */
    public function test_get_responses_invalid_submission_id(): void {
        $this->resetAfterTest();

        // A second verbal feedback instance in the same course.
        $othervf = $this->getDataGenerator()->create_module('verbalfeedback', ['course' => $this->course->id]);

        $student = $this->students[0];
        // The submission belongs to the other instance.
        $submission = $this->create_submission($othervf->id, $this->teacher->id, $student->id);

        $this->setUser($this->teacher);

        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('invalididprovided', 'mod_verbalfeedback'));

        // But we query it with the first instance id, so the sanity check must fail.
        \mod_verbalfeedback_external::get_responses(
            (int)$this->verbalfeedback->id,
            (int)$this->teacher->id,
            (int)$student->id,
            $submission->get_id()
        );
    }
}
