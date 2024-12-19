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
 * Class containing data for rendering the verbal feedback report page for a participant.
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_verbalfeedback\output;

use mod_verbalfeedback\api;
use mod_verbalfeedback\model\report as ModelReport;
use mod_verbalfeedback\output\model\report_view_model;
use mod_verbalfeedback\utils\font;
use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 * Class containing data for rendering the verbal feedback report page for a participant.
 *
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_download implements renderable, templatable {

    /** @var ModelReport The model class with the data. */
    protected $report;

    /** @var string The course title. */
    protected $coursename;

    /** @var int The course start date. */
    protected $coursestart;

    /** @var int The course end date. */
    protected $courseend;

    /** @var int The verbal feedback instance name. */
    protected $instancename;

    /** @var array List of items with the average rating/comments given to the user. */
    protected $categories;

    /** @var object Moodle user object of the assessed user. */
    protected $touser;

    /** @var font The font object. */
    protected $font;

    /**
     * The report constructor.
     *
     * @param ModelReport $report Report object for the user.
     * @param string $coursename The course name
     * @param int $coursestart The course start date
     * @param int $courseend The course end date
     * @param string $instancename The verbal feedback instance name.
     * @param font $font The font object.
     * @param int $touser The user this report is being generated for.
     */
    public function __construct(ModelReport $report, $coursename, $coursestart, $courseend, $instancename, $touser, font $font) {
        $this->report = $report;
        $this->coursename = $coursename;
        $this->coursestart = $coursestart;
        $this->courseend = $courseend;
        $this->instancename = $instancename;
        $this->touser = $touser;
        $this->font = $font;
    }

    /**
     * Function to export the renderer data in a format that is suitable for a
     * mustache template. This means:
     * 1. No complex types - only stdClass, array, int, string, float, bool
     * 2. Any additional info that is required for the template is pre-calculated (e.g. capability checks).
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return stdClass|array
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();

        $data->scales = api::get_scales();
        $data->report = new report_view_model($this->report);
        $data->lang = current_language();
        $data->font_base = $this->font->get_font_base();
        $data->font_student = $this->font->get_font_student();
        $data->font_teacher = $this->font->get_font_teacher();

        // Iterate and drop criteria with weight 0.
        // First, let's filter our set of criteria inside the categories.

        foreach ($data->report->categories as $category) {
            $filteredcriteria = array_filter($category->criteria, function($criterion) {
                // Adding our criteria for a valid category.
                return
                    property_exists($criterion, 'multiplier') // The property weight exists.
                    && $criterion->multiplier != "0.00"; // And it's not '0.00'.
            });
            // If you want to keep the index of the entry just remove the next line.
            $filteredcriteria = array_values($filteredcriteria);
            // Overwrite the original criteria with the filtered set.
            $category->criteria = $filteredcriteria;
        }

        // Iterate and drop categories with weight 0.
        // Then, let's filter our set of categories.

        $filteredcategories = array_filter($data->report->categories, function($category) {
            // Adding our criteria for a valid category.
            return
                property_exists($category, 'weight') // The property weight exists.
                && $category->weight > 0; // And it's not 0.
        });

        // If you want to keep the index of the entry just remove the next line.
        $filteredcategories = array_values($filteredcategories);
        // Overwrite the original categories with the filtered set.
        $data->report->categories = $filteredcategories;

        $data->coursestart = userdate($this->coursestart);
        $data->courseend = userdate($this->courseend);

        $data->instancename = $this->instancename;

        $data->student = fullname($this->touser);

        $teacherfullnames = [];
        foreach ($this->report->get_from_user_ids() as $fromuserid) {
            $fromuser = \core_user::get_user($fromuserid);
            $teacherfullnames[] = fullname($fromuser);
        }
        $data->teachers = implode(', ', $teacherfullnames);

        return $data;
    }
}
