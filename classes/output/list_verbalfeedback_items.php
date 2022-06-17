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
 * Class containing data for users that need to be given with verbalfeedback.
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_verbalfeedback\output;

use dml_exception;
use mod_verbalfeedback\helper;
use mod_verbalfeedback\repository\instance_repository;
use moodle_url;
use renderer_base;
use stdClass;

/**
 * Class containing data for users that need to be given with verbalfeedback.
 *
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class list_verbalfeedback_items implements \renderable, \templatable {

    /** @var int The context module ID. */
    private $cmid;

    /** @var int The course ID. */
    private $courseid;

    /** @var int The verbal feedback instance ID. */
    private $verbalfeedbackid;

    /** @var int The user ID of the user giving the feedback. */
    private $userid;

    /** @var moodle_url The URL to the view.php page. */
    protected $viewurl;

    /** @var moodle_url The URL to the view.php page with the make available parameter set to true. */
    protected $makeavailableurl;

    /** @var moodle_url The URL to questionnaire.php with instance ID and the parameter preview set to true. */
    protected $previewurl;

    /** @var int The max grade */
    protected $maxgrade;

    /**
     * list_verbalfeedback_items constructor.
     *
     * @param int $cmid The context module ID.
     * @param int $courseid The course ID.
     * @param int $verbalfeedbackid The verbal feedback instance ID.
     * @param moodle_url $viewurl The URL to the view.php page.
     * @param moodle_url $previewurl The URL to questionaire.php with instance ID and the parameter preview set to true.
     * @param moodle_url $makeavailableurl The URL to the view.php page with the make available parameter set to true.
     * @param int $maxgrade The maximum grade.
     */
    public function __construct($cmid, $courseid, $verbalfeedbackid, $viewurl, $previewurl, $makeavailableurl = null,
        $maxgrade = 100) {
        global $USER;

        $this->cmid = $cmid;
        $this->courseid = $courseid;
        $this->verbalfeedbackid = $verbalfeedbackid;
        $this->userid = $USER->id;
        $this->viewurl = $viewurl;
        $this->makeavailableurl = $makeavailableurl;
        $this->previewurl = $previewurl;
        $this->maxgrade = $maxgrade;
    }

    /**
     * Function to export the renderer data in a format that is suitable for a
     * mustache template. This means:
     * 1. No complex types - only stdClass, array, int, string, float, bool
     * 2. Any additional info that is required for the template is pre-calculated (e.g. capability checks).
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return stdClass|array
     * @throws \coding_exception
     * @throws dml_exception
     */
    public function export_for_template(renderer_base $output) {
        global $CFG;

        $data = new stdClass();
        $data->categories = array();
        $data->verbalfeedbackid = $this->verbalfeedbackid;
        $data->viewurl = $this->viewurl;
        $data->makeavailableurl = $this->makeavailableurl;
        $data->previewurl = $this->previewurl;

        $data->cmid = $this->cmid;
        $data->sesskey = sesskey();
        $data->maxgrade = format_float($this->maxgrade, 2);

        $instancerepository = new instance_repository();
        $instance = $instancerepository->get_by_id($this->verbalfeedbackid);

        $data->categories = helper::prepare_items_view($instance->get_categories());
        return $data;
    }
}
