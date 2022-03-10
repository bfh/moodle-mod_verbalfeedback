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
 * @package    mod_verbalfeedback
 * @copyright  2021 Kevin Tippenhauer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_verbalfeedback\output\model;

use mod_verbalfeedback\model\report;
use mod_verbalfeedback\utils\graph_utils;

/**
 * The report view model class
 */
class report_view_model {
    /** @var \lang_string|string The result percentage */
    public $resultpercentage;
    /** @var string The radar code */
    public $radar;
    /** @var array The categories */
    public $categories = array();

    /**
     * The report view model class constructor
     *
     * @param report $report The verbalfeedback report
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function __construct(report $report) {
        if ($report->get_result() === null) {
            $this->resultpercentage = get_string('notapplicableabbr', 'mod_verbalfeedback');
            $this->resultvsmax = get_string('notapplicableabbr', 'mod_verbalfeedback');
        } else {
            $this->resultpercentage = number_format($report->get_result_percentage(), 2) . '%';
            $this->resultvsmax = number_format($report->get_max_points() * $report->get_result_part(), 2) . '/' .
                $report->get_max_points();
        }

        foreach ($report->get_categories() as $category) {
            $this->categories[] = new report_category_view_model($category);
        }
        $this->radar = graph_utils::create_radar_graph($report);
    }

}
