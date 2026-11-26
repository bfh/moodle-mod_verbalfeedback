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

namespace mod_verbalfeedback;

use mod_verbalfeedback\model\instance;
use mod_verbalfeedback\model\instance_release_type;
use mod_verbalfeedback\output\report_download;
use mod_verbalfeedback\repository\instance_repository;
use mod_verbalfeedback\service\report_service;
use mod_verbalfeedback\utils\font;

/**
 * Unit tests for the downloadable report renderable.
 *
 * The report_download.php endpoint renders this renderable to HTML and then hands that HTML to
 * TCPDF to produce the PDF. Asserting on the rendered HTML here is the reliable way to check what
 * the generated PDF contains: the PDF itself embeds subsetted fonts and compresses its content
 * streams, so its text cannot be pattern matched directly in a Behat step (see the
 * report_access.feature "combined report" scenario, which only checks access, not content).
 *
 * @package    mod_verbalfeedback
 * @copyright  2026 Stephan Robotta <stephan.robotta@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class report_download_test extends \advanced_testcase {
    /** @var \stdClass The course holding the verbal feedback activity. */
    protected $course;

    /** @var \stdClass The verbal feedback activity instance. */
    protected $instance;

    /** @var \stdClass The rated participant. */
    protected $student;

    /** @var \stdClass[] The three reviewers (editing teachers), keyed by a readable label. */
    protected $teachers = [];

    /**
     * Create a course with a verbal feedback activity, one student and three reviewers, each of
     * which submits a completed feedback for the student. This mirrors the Behat background of
     * report_access.feature.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $this->course = $generator->create_course();
        $this->instance = $generator->create_module('verbalfeedback', [
            'course' => $this->course->id,
        ]);

        $this->student = $generator->create_and_enrol($this->course, 'student', [
            'firstname' => 'Stella', 'lastname' => 'Student',
        ]);

        $names = [
            'teacher1' => ['firstname' => 'Tamara', 'lastname' => 'Teacherone'],
            'teacher2' => ['firstname' => 'Timo', 'lastname' => 'Teachertwo'],
            'teacher3' => ['firstname' => 'Thea', 'lastname' => 'Teacherthree'],
        ];
        $plugingenerator = $generator->get_plugin_generator('mod_verbalfeedback');
        foreach ($names as $key => $name) {
            $role = $key === 'teacher3' ? 'teacher' : 'editingteacher';
            $teacher = $generator->create_and_enrol($this->course, $role, $name);
            $this->teachers[$key] = $teacher;
            $plugingenerator->create_submission([
                'instanceid' => $this->instance->id,
                'fromuserid' => $teacher->id,
                'touserid' => $this->student->id,
                'rating' => (int)str_replace('teacher', '', $key) + 2,
                // Teacher1 rates 3, teacher2 rates 4, teacher3 rates 5.
            ]);
        }
    }

    /**
     * Renders the downloadable report (the HTML that is turned into the PDF) for the student,
     * optionally restricted to a single reviewer.
     *
     * @param int $fromuserid The reviewer to restrict to, or 0 for the combined report.
     * @return string The rendered report HTML.
     */
    protected function render_report($fromuserid): string {
        global $PAGE;

        $context = \core\context\module::instance($this->instance->cmid);

        $report = (new report_service($this->student->id, $this->instance->id))
            ->set_fromuserid($fromuserid)
            ->validate($context)
            ->create_report();

        $renderable = new report_download(
            $report,
            $this->course->fullname,
            $this->course->startdate,
            $this->course->enddate,
            format_string($this->instance->name),
            \core_user::get_user($this->student->id),
            new font($report)
        );

        return $PAGE->get_renderer('mod_verbalfeedback')->render($renderable);
    }

    /**
     * The combined report (no single reviewer selected) must mention every reviewer that gave
     * feedback. This is the content that the "non-editing teacher" download falls back to in
     * report_access.feature.
     *
     * @covers \mod_verbalfeedback\output\report_download::export_for_template
     */
    public function test_combined_report_mentions_all_reviewers(): void {
        // Before a student can vew the report, it must be open.
        $instancerepo = new instance_repository();
        $instance = instance::from_record($this->instance);
        $instance->set_release_type(instance_release_type::OPEN);
        $instancerepo->save($instance);
        // Show combined report for student and teachers.
        $users = array_merge($this->teachers, [$this->student]);
        foreach ($users as $user) {
            $this->setUser($user);
            $html = $this->render_report(0);

            foreach ($this->teachers as $key => $teacher) {
                $this->assertStringContainsString(
                    fullname($teacher),
                    $html,
                    "The combined report does not mention reviewer \"$key\"."
                );
            }
            $this->assertStringContainsString(fullname($this->student), $html);
        }
    }

    /**
     * A report restricted to a single reviewer must mention only that reviewer, not the others.
     *
     * @covers \mod_verbalfeedback\output\report_download::export_for_template
     */
    public function test_single_reviewer_report_mentions_only_that_reviewer(): void {
        $this->setUser($this->teachers['teacher1']);
        $html = $this->render_report($this->teachers['teacher2']->id);

        $this->assertStringContainsString(fullname($this->teachers['teacher2']), $html);
        $this->assertStringNotContainsString(fullname($this->teachers['teacher1']), $html);
        $this->assertStringNotContainsString(fullname($this->teachers['teacher3']), $html);
    }

    /**
     * Non editing teacher that requests a report for some other reviewer only gets the
     * combined report.
     *
     * @covers \mod_verbalfeedback\output\report_download::export_for_template
     */
    public function test_non_editing_teacher_gets_combined_report_only(): void {
        $this->setUser($this->teachers['teacher3']);
        $html = $this->render_report($this->teachers['teacher2']->id);

        $this->assertStringContainsString(fullname($this->teachers['teacher2']), $html);
        $this->assertStringContainsString(fullname($this->teachers['teacher1']), $html);
        $this->assertStringContainsString(fullname($this->teachers['teacher3']), $html);
    }
}
