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
 * Class containing data to render the questionnaire page.
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_verbalfeedback\output;

use coding_exception;
use core_user;
use dml_exception;
use mod_verbalfeedback\api;
use mod_verbalfeedback\helper;
use mod_verbalfeedback\model\submission;
use mod_verbalfeedback\model\submission_status;
use mod_verbalfeedback\repository\instance_repository;
use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 * Class containing data to render the questionnaire page.
 *
 * @copyright  2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class questionnaire implements renderable, templatable {

    /** @var submission The feedback submission data. */
    protected $submission;

    /** @var bool True, if the questionnaire is rendered for preview. */
    private $preview;

    /**
     * questionnaire constructor.
     *
     * @param int $contextid The context ID.
     * @param submission $submission The feedback submission data.
     * @param bool $preview Set true, if the questionnaire is rendered for preview.
     */
    public function __construct(int $contextid, submission $submission, bool $preview = false) {
        $instancerepository = new instance_repository();

        $this->contextid = $contextid;
        $this->submission = $submission;
        $this->preview = $preview;
        $this->instance = $instancerepository->get_by_id($submission->get_instance_id());
        $this->categories = helper::prepare_items_view($this->instance->get_categories());

        if (!$preview) {
            $this->touserid = $submission->get_to_user_id();
            $this->fromuserid = $submission->get_from_user_id();
        }
    }

    /**
     * Function to export the renderer data in a format that is suitable for a
     * mustache template. This means:
     * 1. No complex types - only stdClass, array, int, string, float, bool
     * 2. Any additional info that is required for the template is pre-calculated (e.g. capability checks).
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return stdClass|array
     * @throws coding_exception
     * @throws dml_exception
     */
    public function export_for_template(renderer_base $output) {
        global $PAGE;

        $submission = $this->submission;
        $data = new stdClass();

        switch ($submission->get_status()) {
            case submission_status::IN_PROGRESS: // In Progress.
                $data->statusclass = 'label-info';
                $data->status = get_string('statusinprogress', 'verbalfeedback');
            break;
            case submission_status::COMPLETE: // Completed.
                $data->statusclass = 'label-success';
                $data->status = get_string('statuscompleted', 'verbalfeedback');
            break;
            case submission_status::DECLINED: // Declined.
                $data->statusclass = 'label-warning';
                $data->status = get_string('statusdeclined', 'verbalfeedback');
            break;
            default: // Pending.
                $data->statusclass = 'label';
                $data->status = get_string('statuspending', 'verbalfeedback');
            break;
        }
        $data->scales = api::get_scales();

        $instancerepository = new instance_repository();
        $instance = $instancerepository->get_by_id($submission->get_instance_id());

        $data->categories = $this->categories;

        // Iterate and drop criteria with weight 0.
        // First, let's filter our set of criteria inside the categories.

        foreach ($data->categories as $category) {
                $filteredcriteria = array_filter($category->criteria, function($criterion) {
                    // Adding our criteria for a valid category.
                    return
                        property_exists($criterion, 'weight') // The property weight exists.
                        && $criterion->weight != "0.00"; // And it's not '0.00'.
                });
            // If you want to keep the index of the entry just remove the next line.
            $filteredcriteria = array_values($filteredcriteria);
            // Overwrite the original criteria with the filtered set.
            $category->criteria = $filteredcriteria;
        }

        // Iterate and drop categories with weight 0.
        // Then, let's filter our set of categories.

        $filteredcategories = array_filter($data->categories, function($category) {
            // Adding our criteria for a valid category.
            return
                property_exists($category, 'weight') // The property weight exists.
                && $category->weight != "0.00"; // And it's not '0.00'.
        });

        // If you want to keep the index of the entry just remove the next line.
        $filteredcategories = array_values($filteredcategories);
        // Overwrite the original categories with the filtered set.
        $data->categories = $filteredcategories;

        if ($this->preview) {
            $data->preview = $this->preview;
            $data->tousername = 'Max Muster';
        } else {
            $data->touserid = $this->touserid;
            if (class_exists('core_user\fields')) {
                // Post Moodle 3.11 way.
                $userfieldsapi = \core_user\fields::for_name();
                $touser = core_user::get_user($submission->get_to_user_id(),
                    $userfieldsapi->get_sql('', false, '', '', false)->selects);
            } else {
                // Pre Moodle 3.11 way.
                $touser = core_user::get_user($submission->get_to_user_id(), get_all_user_name_fields(true));
            }
            $viewfullnames = has_capability('moodle/site:viewfullnames', \context::instance_by_id($this->contextid));
            $data->tousername = fullname($touser, $viewfullnames);
            $data->fromuserid = $this->fromuserid;
            $data->returnurl = $PAGE->url;
        }

        $data->verbalfeedbackid = $submission->get_instance_id();
        $data->submissionid = $submission->get_id();
        $data->contextid = $this->contextid;

        return $data;
    }
}
