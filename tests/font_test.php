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

use mod_verbalfeedback\api;
use mod_verbalfeedback\repository\submission_repository;
use mod_verbalfeedback\service\report_service;
use mod_verbalfeedback\utils\font;
use pdf;

/**
 * Unit tests for classes/font.php
 *
 * @package mod_verbalfeedback
 * @copyright 2024 Stephan Robotta <stephan.robotta@bfh.ch>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class font_test extends \advanced_testcase {
    /** @var array */
    protected $students;
    /** @var stdClass the verval feedback course module. */
    protected $cm;
    /** @var stdClass the current course with the verbal feedback activity. */
    protected $course;

    /**
     * Setup verbalfeedback.
     */
    public function setUp(): void {
        parent::setUp();
        $category = $this->getDataGenerator()->create_category();
        $this->course = $this->getDataGenerator()->create_course(['category' => $category->id]);
        $this->cm = $this->getDataGenerator()->create_module('verbalfeedback', ['course' => $this->course->id]);

        $this->students = [];
        foreach ($this->get_students() as $name) {
            $this->students[] = $this->getDataGenerator()->create_and_enrol($this->course, 'student', $name);
        }
    }

    /**
     * Get teachers with different names.
     *
     * @return array
     */
    protected function get_teachers(): array {
        return [
            ['firstname' => 'John', 'lastname' => 'White'],
            ['firstname' => 'Eliška', 'lastname' => 'Němcová'],
            ['firstname' => 'Даниил', 'lastname' => 'Кузнецов'],
            ['firstname' => 'פרס', 'lastname' => 'שמעון'],
            ['firstname' => 'اللطيف رشيد', 'lastname' => 'عبد'],
            ['firstname' => '拓海', 'lastname' => '田中'],
            ['firstname' => 'さくら', 'lastname' => '伊藤'],
        ];
    }

    /**
     * Get students with different names.
     *
     * @return array
     */
    protected function get_students(): array {
        return [
            ['firstname' => 'John', 'lastname' => 'Doe'],
            ['firstname' => 'Matěj', 'lastname' => 'Černý'],
            ['firstname' => 'Albrecht', 'lastname' => 'Dürer'],
            ['firstname' => 'محمد', 'lastname' => 'علي'],
            ['firstname' => 'מאיר', 'lastname' => 'גולדה'],
            ['firstname' => 'Артем', 'lastname' => 'Иванов'],
            ['firstname' => '羽', 'lastname' => '周'],
            ['firstname' => '美玲', 'lastname' => '王'],
            ['firstname' => 'さくら', 'lastname' => '小林'],
        ];
    }

    /**
     * Test get_font_base().
     *
     * @covers \mod_verbalfeedback\font::get_font_base
     */
    public function test_get_font_base(): void {
        global $SESSION;

        $this->resetAfterTest();
        $reportservice = new report_service();

        $tests = [
            ['lang' => 'en', 'expected' => font::FONT_BASE],
            ['lang' => 'ar', 'expected' => font::FONT_ARABIC],
            ['lang' => 'he', 'expected' => font::FONT_HEBREW],
            ['lang' => 'ja', 'expected' => font::FONT_JAPANESE],
            ['lang' => 'zh', 'expected' => font::FONT_CHINESE],
            ['lang' => 'fr_ca', 'expected' => font::FONT_BASE],
            ['lang' => 'zh_tw_wp', 'expected' => font::FONT_CHINESE],
        ];
        foreach ($tests as $test) {
            $SESSION->forcelang = $test['lang'];
            $report = $reportservice->create_report($this->cm->id, $this->students[0]->id);
            $font = new font($report);
            $this->assertEquals($test['expected'], $font->get_font_base(), "Font for {$test['lang']} is not {$test['expected']}.");
        }
    }

    /**
     * Test get_font_student().
     *
     * @covers \mod_verbalfeedback\font::get_font_student
     */
    public function test_get_font_student(): void {
        $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher', $this->get_teachers()[0]);
        $this->resetAfterTest();
        $expected = [
            font::FONT_BASE,
            font::FONT_BASE,
            font::FONT_BASE,
            font::FONT_ARABIC,
            font::FONT_HEBREW,
            font::FONT_BASE,
            font::FONT_CHINESE,
            font::FONT_CHINESE,
            font::FONT_JAPANESE,
        ];
        foreach ($this->students as $key => $student) {
            $reportservice = new report_service();
            $report = $reportservice->create_report($this->cm->id, $student->id);
            $font = new font($report);
            $this->assertEquals($expected[$key], $font->get_font_student(), "Font for student {$key} is not {$expected[$key]}.");
        }
    }

    /**
     * Test get_font_teacher.
     *
     * @covers \mod_verbalfeedback\font::get_font_teacher
     */
    public function test_get_font_teacher(): void {
        $this->resetAfterTest();
        $expected = [
            font::FONT_BASE,
            font::FONT_BASE,
            font::FONT_BASE,
            font::FONT_HEBREW,
            font::FONT_ARABIC,
            font::FONT_CHINESE,
            font::FONT_JAPANESE,
        ];
        $enroledteachers = [];
        foreach ($this->get_teachers() as $key => $teacher) {
            $enroledteachers[] = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher', $teacher);
            api::generate_verbalfeedback_feedback_states($this->cm->id, $enroledteachers[$key]->id);
            $reportservice = new report_service();
            $report = $reportservice->create_report($this->cm->id, $this->students[0]->id);
            $font = new font($report);
            // Because looking at the first teacher only, the result here is always the same.
            $this->assertEquals(font::FONT_BASE, $font->get_font_teacher(), "Font for teacher {$key} is not Noto_Sans.");
        }
        // Now delete all submissions skeletons from teachers.
        (new submission_repository())->delete_by_instance($this->cm->id);

        foreach ($this->get_teachers() as $key => $teacher) {
            api::generate_verbalfeedback_feedback_states($this->cm->id, $enroledteachers[$key]->id);
            $reportservice = new report_service();
            $report = $reportservice->create_report($this->cm->id, $this->students[3]->id);
            $font = new font($report);
            $this->assertEquals($expected[$key], $font->get_font_teacher(), "Wrong font {$expected[$key]} for teacher {$key}.");
            // Remove submission skeletons by current teacher.
            (new submission_repository())->delete_by_instance($this->cm->id);
        }
    }

    /**
     * Test set_font_for_pdf.
     *
     * @covers \mod_verbalfeedback\font::set_font_for_pdf
     */
    public function test_set_font_for_pdf(): void {
        global $CFG;

        require_once($CFG->libdir . '/pdflib.php');

        $this->resetAfterTest();

        foreach ($this->students as $student) {
            $pdf = new pdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $font = new font((new report_service())->create_report($this->cm->id, $student->id));
            $font->set_font_for_pdf($pdf);
            $charfist = mb_substr($student->firstname, 0, 1);
            $charlast = mb_substr($student->lastname, 0, 1);
            $this->assertTrue($pdf->isCharDefined($charfist), "char $charfist not defined");
            $this->assertTrue($pdf->isCharDefined($charlast), "char $charlast not defined");
        }
    }
}
