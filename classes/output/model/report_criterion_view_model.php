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

use mod_verbalfeedback\api;
use mod_verbalfeedback\model\report_criterion;

/**
 * The report criterion view model class
 */
class report_criterion_view_model {
    /** @var string The text */
    public $text;
    /** @var false|\lang_string|string The scale text */
    public $scaletext;
    /** @var \lang_string|string The average rating */
    public $averagerating;
    /** @var string The multiplier */
    public $multiplier;

    /**
     * The report criterion view model class constructor
     *
     * @param report_criterion $criterion
     * @throws \coding_exception
     */
    public function __construct(report_criterion $criterion) {
        $this->text = $criterion->get_description(current_language())->get_string();
        if ($criterion->get_avg() === null) {
            $this->averagerating = get_string('notapplicableabbr', 'mod_verbalfeedback');
            $this->scaletext = get_string('notapplicableabbr', 'mod_verbalfeedback');
        } else {
            $this->averagerating = number_format($criterion->get_avg(), 2);
            $this->scaletext = $this->get_scale_text($this->averagerating);
        }

        $this->multiplier = number_format($criterion->get_weight(), 2);
        $addfirstelement = true;
        foreach ($criterion->get_student_comments() as $comment) {
            if ($comment == "") {
                continue;
            }
            if ($addfirstelement) {
                // This is a nasty hack to allow mustache to decide whether to display the "comments" label.
                $addfirstelement = false;
                $this->comments = [];
                $this->comments[0] = new \stdClass();
                $this->comments[0]->texts = [];
            }
            $this->comments[0]->texts[] = $comment;
        }
    }

    /**
     * Gets the localised string value of a status code.
     *
     * @param int $value The scale value.
     * @return string|false The scale description. False if there's no scale matching the given value.
     */
    private function get_scale_text($value) {
        $scales = api::get_scales();
        foreach ($scales as $scale) {
            if ($scale->scale == round($value)) {
                return $scale->description;
            }
        }
        return false;
    }
}
