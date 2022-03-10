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
 * Renderer class for template library.
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_verbalfeedback\output;

defined('MOODLE_INTERNAL') || die;

use moodle_exception;
use plugin_renderer_base;

/**
 * Renderer class for the verbal feedback module.
 *
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    /**
     * Defer to template.
     *
     * @param list_participants $page
     * @return string html for the page
     * @throws moodle_exception
     */
    public function render_list_participants($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('mod_verbalfeedback/list_participants', $data);
    }

    /**
     * Renders the questionnaire page.
     *
     * @param questionnaire $page
     * @return bool|string
     * @throws moodle_exception
     */
    public function render_questionnaire($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('mod_verbalfeedback/questionnaire', $data);
    }

    /**
     * Renders the items list page for a verbal feedback instance.
     *
     * @param list_verbalfeedback_items $page
     * @return bool|string html for the page.
     * @throws moodle_exception
     */
    public function render_list_verbalfeedback_items($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('mod_verbalfeedback/list_verbalfeedback_items', $data);
    }

    /**
     * Renders the reports page for a feedback recipient.
     *
     * @param report $page
     * @return bool|string html for the page.
     * @throws moodle_exception
     */
    public function render_report($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('mod_verbalfeedback/report', $data);
    }

    /**
     * Renders the reports page for a feedback recipient.
     *
     * @param report_download $page
     * @return bool|string html for the page.
     * @throws moodle_exception
     */
    public function render_report_download($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('mod_verbalfeedback/report_download', $data);
    }
}
