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
 * Class for creating graphs. For example, to display on the report.
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_verbalfeedback\utils;

use mod_verbalfeedback\model\report;


/**
 * Class for handling the font in the PDF. That may vary depending on the teacher,
 * student name and the content of the course.
 *
 * @package   mod_verbalfeedback
 * @copyright 2024 Stephan Robotta <stephan.robotta@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class font {

    /** @var string Base font name. */
    public const FONT_BASE = 'Noto_Sans';
    /** @var string Font name for Arabic. */
    public const FONT_ARABIC = 'Noto_Naskh_Arabic';
    /** @var string Font name for Hebrew. */
    public const FONT_HEBREW = 'Noto_Sans_Hebrew';
    /** @var string Font name for Japanese. */
    public const FONT_JAPANESE = 'Noto_Sans_JP';
    /** @var string Font name for Chinese. */
    public const FONT_CHINESE = 'Noto_Sans_TC';

    /** @var object The report object. */
    protected $report;

    /** @var string The selected font for the entire document. */
    protected $font_base;
    /** @var string The selected font for the students name. */
    protected $font_student;
    /** @var string The selected font for the teachers name. */
    protected $font_teacher;

    /**
     * Constructor.
     *
     * @param report $course The report object.
     */
    public function __construct(report $report) {
        $this->report = $report;
    }

    /**
     * Get the base font for the PDF.
     */
    public function get_font_base() {
        if (!$this->font_base) {
            $lang = substr(current_language(), 0, 2);
            if ($lang === 'ar') {
                $this->font_base = static::FONT_ARABIC;
            } else if ($lang === 'he') {
                $this->font_base = static::FONT_HEBREW;
            } else if ($lang === 'ja') {
                $this->font_base = static::FONT_JAPANESE;
            } else if ($lang === 'zh') {
                $this->font_base = static::FONT_CHINESE;
            } else {
                $this->font_base = static::FONT_BASE;  
            }
        }
        return $this->font_base;
    }

    /**
     * Get the font for the student name.
     *
     * @return string The font name (one of the class constants)
     */
    public function get_font_student() {
        if (!$this->font_student) {
            $touser = \core_user::get_user($this->report->get_to_user_id());
            $font = $this->eval_string(fullname($touser));
            $this->font_student = $font === $this->get_font_base() ? 'inherit' : $font;
        }
        return $this->font_student;
    }

    /**
     * Get the font for the teachers name. Check the first teacher only.
     *
     * @return string The font name (one of the class constants)
     */
    public function get_font_teacher() {
        if (!$this->font_teacher) {
            $this->font_teacher = 'inherit';
            foreach ($this->report->get_from_user_ids() as $fromuserid) {
                $fromuser = \core_user::get_user($fromuserid);
                $font = $this->eval_string(fullname($fromuser));
                $this->font_teacher = $font === $this->get_font_base() ? 'inherit' : $font;
                break;
            }
        }
        return $this->font_teacher;
    }

    /**
     * Evaluate the font based on the input string.
     *
     * @param string $input The input string.
     * @return string The font name (one of the class constants)
     */
    protected function eval_string(string $input): string {
        
        $n = mb_ord(mb_substr($input, 0, 1));
        if ($n >= 0x600 && $n <= 0x6ff) {
            return static::FONT_ARABIC;
        }
        if ($n >= 0x0590 && $n <= 0x05ff) {
            return static::FONT_HEBREW;
        }
        if ($n >= 0x3040 && $n <= 0x30ff) {
            return static::FONT_JAPANESE;
        }
        if ($n >= 0x4e00 && $n <= 0x9fff) {
            return static::FONT_CHINESE;
        }
        return static::FONT_BASE;
    }

    /**
     * Set the required fonts for the PDF.
     *
     * @param \pdf $pdf The pdf object.
     */
    public function set_font_for_pdf(\pdf $pdf) {
        global $CFG;

        $toload = [$this->get_font_base()];
        if ($this->get_font_student() !== 'inherit') {
            $toload[] = $this->get_font_student();
        }
        if ($this->get_font_teacher() !== 'inherit') {
            $toload[] = $this->get_font_teacher();
        }
        foreach ($toload as $font) {
            if ($font === static::FONT_BASE) {
                $file = $CFG->dirroot . '/mod/verbalfeedback/fonts/Noto_Sans/notosans';
                $pdf->AddFont($font, '', $file . '.php');
                $pdf->AddFont($font, 'B', $file . 'b.php');
                $pdf->AddFont($font, 'I', $file . 'i.php');
                $pdf->AddFont($font, 'BI', $file . 'bi.php');
                $pdf->SetFont($font, '', 12);
            } elseif ($font === static::FONT_ARABIC) {
                $this->set_font_two($pdf, $font, 'notonaskharabic');
            } else if ($font === static::FONT_HEBREW) {
                $this->set_font_two($pdf, $font, 'notosanshebrew');
            } else if ($font === static::FONT_JAPANESE) {
                $this->set_font_two($pdf, $font, 'notosansjp');
            } else if ($font === static::FONT_CHINESE) {
                $this->set_font_two($pdf, $font, 'notosanstc');
            }
        }
    }

    /**
     * Helper function to set the font for the PDF that only supports regular and bold style.
     *
     * @param \pdf $pdf The pdf object.
     * @param string $name The name of the font.
     * @param string $file The file name prefix of the font.
     */
    protected function set_font_two(\pdf $pdf, $name, $file) {
        global $CFG;
        $fontdir = "{$CFG->dirroot}/mod/verbalfeedback/fonts/{$name}/{$file}";
        $pdf->AddFont($name, '', $fontdir . '.php');
        $pdf->AddFont($name, 'B', $fontdir . 'b.php');
        $pdf->AddFont($name, 'I', $fontdir . '.php');
        $pdf->AddFont($name, 'BI', $fontdir . 'b.php');
        $pdf->SetFont($name, '', 12);
    }
}