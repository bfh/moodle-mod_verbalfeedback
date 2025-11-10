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
 * Class containing data for a verbal feedback report.
 *
 * @package    mod_verbalfeedback
 * @copyright  2021 Kevin Tippenhauer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_verbalfeedback\model;

/**
 * The report class
 */
class report {
    /** The maximum points, equal to the most right hand site entry which in our case is the fifth */
    const MAX_POINTS = 5;
    /** @var int The recipient of the feedback responses. */
    private $touserid;
    /** @var array The ids of the rating users */
    private $fromuserids = [];
    /** @var array The categories this report has */
    private $reportcategories = [];
    /** @var null The result */
    private $result = null;
    /** @var int The instance id */
    public $instanceid = 0;

    /**
     * The report class constructor
     */
    public function __construct() {
    }

    /**
     * Sets the instance id.
     *
     * @param int $instanceid The instance id.
     */
    public function set_instance_id(int $instanceid) {
        $this->instanceid = $instanceid;
    }

    /**
     * Sets the user id of the rated user.
     *
     * @param int $touserid The id of the rated user.
     */
    public function set_to_user_id(int $touserid) {
        $this->touserid = $touserid;
    }

    /**
     * Gets the user id of the rated user.
     *
     * @return int The id of the rated user.
     */
    public function get_to_user_id(): int {
        return $this->touserid;
    }

    /**
     * Gets the fromuserids
     *
     * @return array<int> The ids of the rating users.
     */
    public function get_from_user_ids(): array {
        return $this->fromuserids;
    }

    /**
     * Adds a id of a rating user
     *
     * @param int $fromuserid The id of the rating user.
     */
    public function add_from_user_id(int $fromuserid) {
        $this->fromuserids[] = $fromuserid;
    }

    /**
     * Gets the report's categories
     *
     * @return array
     */
    public function get_categories(): array {
        return $this->reportcategories;
    }

    /**
     * Adds a category to this report
     *
     * @param report_category $category
     */
    public function add_category(report_category $category) {
        $this->reportcategories[] = $category;
        $this->update_result();
    }

    /**
     * Gets this report's result
     *
     * @return float|null
     */
    public function get_result(): ?float {
        return $this->result;
    }

    /**
     * Gets this report's result percentage
     *
     * @return float
     */
    public function get_result_percentage(): float {
        return 100 * ($this->result / self::MAX_POINTS);
    }

    /**
     * Gets this report's ratio earned/available
     *
     * @return float
     */
    public function get_result_part(): float {
        return $this->result / self::MAX_POINTS;
    }

    /**
     * Gets this report's maximum grade
     *
     * @return int
     * @throws \dml_exception
     */
    public function get_max_points(): int {
        global $DB;
        return $DB->get_field('verbalfeedback', 'grade', ['id' => $this->instanceid]);
    }

    /**
     * Updates this report's results
     */
    private function update_result() {
        $categoryresults = [];
        foreach ($this->reportcategories as $category) {
            if ($category->get_weighted_result() === null || $category->get_weight() == null) { // Operator == null is intended.
                continue;
            }
            $categoryweights[] = $category->get_weight();
            $categoryresults[] = $category->get_weighted_result() * $category->get_weight();
        }
        if (count($categoryresults) == 0 || count($categoryweights) == 0) {
            $this->result = null;
        } else {
            $resultsum = array_sum($categoryresults);
            $weightsum = array_sum($categoryweights);
            $this->result = $resultsum / $weightsum;
        }
    }
}
