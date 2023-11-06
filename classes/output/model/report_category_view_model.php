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

use mod_verbalfeedback\model\report_category;

/**
 * The report category view model class
 */
class report_category_view_model {
    /** @var string The name */
    public $name;
    /** @var \lang_string|string The average */
    public $average;
    /** @var \lang_string|string The percentage */
    public $percentage;
    /** @var array The criteria */
    public $criteria = [];

    /**
     * The report category view model class constructor
     *
     * @param report_category $category
     * @throws \coding_exception
     */
    public function __construct(report_category $category) {
        $this->name = $category->get_header(current_language())->get_string();
        $this->weight = $category->get_weight();
        if ($category->get_weighted_result() === null) {
            $this->percentage = get_string('notapplicableabbr', 'mod_verbalfeedback');
            $this->average = get_string('notapplicableabbr', 'mod_verbalfeedback');
        } else {
            $this->percentage = (number_format($category->get_weight(), 2) * 100) . '%';
            $this->average = number_format($category->get_weighted_result(), 2);
        }
        foreach ($category->get_criteria() as $criteria) {
            $this->criteria[] = new report_criterion_view_model($criteria);
        }
    }
}
