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
 * Class containing data for a verbal feedback report category.
 *
 * @package    mod_verbalfeedback
 * @copyright  2021 Kevin Tippenhauer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_verbalfeedback\model;

use mod_verbalfeedback\repository\language_repository;

/**
 * The report category class
 */
class report_category {
    /** @var instance_category The instance category */
    private $instancecategory;
    /** @var array The report criteria */
    private $reportcriteria;
    /** @var float The average of the response values */
    private $avg = null;
    /** @var float The weighted result */
    private $weightedresult = null;

    /**
     * The report category class constructor
     *
     * @param instance_category $instancecategory The instance category
     */
    public function __construct(instance_category $instancecategory) {
        $this->instancecategory = $instancecategory;
    }

    /**
     * Gets the report category position
     *
     * @return int The report category position
     */
    public function get_position() : int {
        return $this->instancecategory->get_position();
    }

    /**
     * Gets the weight
     *
     * @return float
     */
    public function get_weight() : float {
        return $this->instancecategory->get_weight();
    }

    /**
     * Gets the localized category headers.
     *
     * @return array<int, localized_string> The localized category headers.
     */
    public function get_headers() : array {
        return $this->instancecategory->get_headers();
    }

    /**
     * Gets the category header
     *
     * @param string $languagestr The given language string
     * @return localized_string
     */
    public function get_header(string $languagestr) : localized_string {
        $langrepo = new language_repository();

        // Select the language string matching the current language.
        foreach ($this->get_headers() as $header) {
            $language = $langrepo->get_by_id($header->get_language_id());
            if ($language->get_language() == $languagestr) {
                return $header;
            }
        }
        return new localized_string(0, 0);
    }

    /**
     * Gets the report criteria within the category.
     * @return array<int, report_criterion> The criteria.
     */
    public function get_criteria() : array {
        return $this->reportcriteria;
    }

    /**
     * Adds a criterion within the category
     *
     * @param report_criterion $criterion The criterion
     */
    public function add_criterion(report_criterion $criterion) {
        $this->reportcriteria[] = $criterion;
        $this->update_avg();
        $this->update_weighed_result();
    }

    /**
     * Returns the average of the response values or null, if no response is available
     *
     * @return float|null The response value average or null.
     */
    public function get_avg() : ?float {
        return $this->avg;
    }

    /**
     * Returns response value average x weight, or null, if no response value is available
     *
     * @return float|null The weighted result or null.
     */
    public function get_weighted_result() : ?float {
        return $this->weightedresult;
    }

    /**
     * Updates the weighted result
     */
    public function update_weighed_result() {
        $weightedratings = [];
        $multipliers = [];
        foreach ($this->reportcriteria as $criterion) {
            if ($criterion->get_weighted_result() !== null) {
                $weightedratings[] = $criterion->get_weighted_result();
                $multipliers[] = $criterion->get_weight();
            }
        }

        if (count($weightedratings) == 0) {
            $this->weightedresult = null;
        } else {
            $multipliersum = array_sum($multipliers);
            $weightedratingsum = array_sum($weightedratings);
            $this->weightedresult = $weightedratingsum / $multipliersum;
        }
    }

    /**
     * Updates the average value
     */
    private function update_avg() {
        $values = [];
        foreach ($this->reportcriteria as $criterion) {
            $values[] = $criterion->get_avg();
        }
        $values = array_filter($values, fn($n) => $n !== null && $n !== false && $n !== '');
        if (count($values) == 0) {
            $this->avg = null;
        } else {
            $this->avg = array_sum($values) / count($values);
        }
    }
}
