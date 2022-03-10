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

defined('MOODLE_INTERNAL') || die();

use action_link;
use mod_verbalfeedback\api;
use mod_verbalfeedback\helper;
use mod_verbalfeedback\model\report as ModelReport;
use mod_verbalfeedback\output\model\report_view_model;
use moodle_url;
use renderable;
use renderer_base;
use single_select;
use stdClass;
use templatable;
use url_select;

/**
 * Class containing data for rendering the verbal feedback report page for a participant.
 *
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report implements renderable, templatable {

    /** @var int The verbal feedback instance ID. */
    protected $instanceid;

    /** @var array List of items with the average rating/comments given to the user. */
    protected $categories;

    /** @var url_select The user selector control. */
    protected $userselect;

    /** @var action_link The action link pointing to the verbal feedback view page. */
    protected $activitylink;

    /** @var single_select $downloadselect The single element containing the download format options. */
    protected $downloadselect;

    /** @var string $reportdownloadurl The report download url. */
    protected $reportdownloadurl;

    /**
     * report constructor.
     *
     * @param int $cmid The course module ID of the verbal feedback instance.
     * @param int $instanceid The verbal feedback instance ID.
     * @param ModelReport $report report object for the user.
     * @param array $participants List of participants for the verbal feedback activity.
     * @param int $touser The user this report is being generated for.
     * @param array $downloadformats List of download format options for the report.
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function __construct($cmid, $instanceid, ModelReport $report, $participants, $touser, $downloadformats = null) {
        $this->instanceid = $instanceid;
        $this->report = $report;
        $this->touser = $touser;

        $urlparams = ['instance' => $this->instanceid, 'touser' => $this->touser->id];
        $this->reportdownloadurl = new moodle_url('/mod/verbalfeedback/report_download.php', $urlparams);

        // Generate data for the user selector widget.
        $participantslist = [];
        foreach ($participants as $participant) {
            // Module URL.
            $urlparams = ['instance' => $this->instanceid, 'touser' => $participant->userid];
            $linkurl = new moodle_url('/mod/verbalfeedback/report.php', $urlparams);
            // Add module URL (as key) and name (as value) to the activity list array.
            $participantslist[$linkurl->out(false)] = fullname($participant);
        }

        if (!empty($participantslist)) {
            $select = new url_select($participantslist, '', ['' => get_string('switchtouser', 'mod_verbalfeedback')]);
            $select->set_label(get_string('jumpto'), ['class' => 'sr-only']);
            $select->attributes = ['id' => 'jump-to-user-report'];
            $select->class = 'd-inline-block';
            $this->userselect = $select;
        }

        if (!empty($downloadformats)) {
            $downloadlabel = get_string('downloadreportas', 'mod_verbalfeedback');
            $downloadurlparams = ['instance' => $this->instanceid, 'touser' => $this->touser->id];
            $downloadurl = new moodle_url('/mod/verbalfeedback/report_download.php', $downloadurlparams);
            $downloadselect = new single_select($downloadurl, 'format', $downloadformats, '', ['' => $downloadlabel]);
            $downloadselect->set_label($downloadlabel, ['class' => 'sr-only']);
            $downloadselect->attributes = ['id' => 'download-user-report'];
            $this->downloadselect = $downloadselect;
        }

        // Activity link.
        $linkname = get_string('backtoverbalfeedbackdashboard', 'mod_verbalfeedback');
        $attributes = [
            'class' => 'btn btn-link',
            'id' => 'back-to-dashboard',
            'title' => $linkname,
        ];
        $activitylinkurl = new moodle_url('/mod/verbalfeedback/view.php', ['id' => $cmid]);
        $this->activitylink = new action_link($activitylinkurl, $linkname, null, $attributes);
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
        if ($this->userselect) {
            $data->userselect = $this->userselect->export_for_template($output);
        }
        if ($this->downloadselect) {
            $data->downloadselect = $this->downloadselect->export_for_template($output);
        }
        $data->reportdownloadurl = $this->reportdownloadurl;
        $data->activitylink = $this->activitylink->export_for_template($output);
        $data->studentfullname = fullname($this->touser);
        $data->scales = api::get_scales();
        $data->report = new report_view_model($this->report);

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

        return $data;
    }
}
