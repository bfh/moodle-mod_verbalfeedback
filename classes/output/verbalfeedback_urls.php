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
 * Class for performing DB actions for the verbal feedback activity module.
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_verbalfeedback\output;

/**
 * The verbal feedback URLs class
 */
class verbalfeedback_urls {
    /** @var string The template list url */
    private static $templatelisturl;
    /** @var string The template category list url */
    private static $templatecategorylisturl;
    /** @var string The template criteria list url */
    private static $templatecriterialisturl;
    /** @var string The language list url */
    private static $languagelisturl;
    /** @var string The report download url */
    private static $reportdownloadurl;

    /**
     * Construct static urls
     */
    public static function construct_static() {
        static::$templatelisturl = new \moodle_url('/mod/verbalfeedback/template_list.php');
        static::$templatecategorylisturl = new \moodle_url('/mod/verbalfeedback/template_category_list.php');
        static::$templatecriterialisturl = new \moodle_url('/mod/verbalfeedback/template_criterion_list.php');
        static::$languagelisturl = new \moodle_url('/mod/verbalfeedback/language_list.php');
        static::$reportdownloadurl = new \moodle_url('/mod/verbalfeedback/report.php');
    }

    /**
     * Get the template list url
     *
     * @return mixed
     */
    public static function get_template_list_url() {
        return static::$templatelisturl;
    }

    /**
     * Get the template category list url
     *
     * @return mixed
     */
    public static function get_template_category_list_url() {
        return static::$templatecategorylisturl;
    }

    /**
     * Get the template criteria list url
     *
     * @return mixed
     */
    public static function get_template_criterion_list_url() {
        return static::$templatecriterialisturl;
    }

    /**
     * Get the language list url
     *
     * @return mixed
     */
    public static function get_language_list_url() {
        return static::$languagelisturl;
    }

    /**
     * Get the report download url
     *
     * @return mixed
     */
    public static function get_report_download_url() {
        return static::$reportdownloadurl;
    }
}
