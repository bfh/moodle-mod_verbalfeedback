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
 * Class containing data for a verbal feedback report criterion.
 *
 * @package    mod_verbalfeedback
 * @copyright  2021 Kevin Tippenhauer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_verbalfeedback\model;

use mod_verbalfeedback\repository\language_repository;

/**
 * The report criterion class
 */
class report_criterion {
    /** @var instance_criterion The instance criterion */
    private $instancecriterion;
    /** @var array The criterion responses */
    private $responses = [];
    /** @var float The average of the response values */
    private $avg;

    /**
     * The report criterion class constructor
     *
     * @param instance_criterion $instancecriterion The instance criterion
     */
    public function __construct(instance_criterion $instancecriterion) {
        $this->instancecriterion = $instancecriterion;
        $this->avg = 0.0;
    }

    /**
     * Gets the report criterion id
     *
     * @return int The report criterion id
     */
    public function get_criterion_id() : int {
        return $this->instancecriterion->get_id();
    }

    /**
     * Gets the report criterion position
     *
     * @return int The report criterion position
     */
    public function get_position() : int {
        return $this->instancecriterion->get_position();
    }

    /**
     * Gets the weight
     *
     * @return float
     */
    public function get_weight() : float {
        return $this->instancecriterion->get_weight();
    }

    /**
     * Gets the criteria descriptions.
     *
     * @return array<int, localized_string> The localized strings.
     */
    public function get_descriptions() : array {
        return $this->instancecriterion->get_descriptions();
    }

    /**
     * Gets a localized criterion description for the given language string.
     *
     * @param string $languagestr The given language string
     * @return \mod_verbalfeedback\model\localized_string|null
     */
    public function get_description(string $languagestr) : ?localized_string {
        $languagerepository = new language_repository();

        // Select the language string matching the current language.
        foreach ($this->get_descriptions() as $localizedstring) {
            $language = $languagerepository->get_by_id($localizedstring->get_language_id());
            if ($language->get_language() == $languagestr) {
                return $localizedstring;
            }
        }
        return null;
    }

    /**
     * Adds a response.
     * @param response $response The response.
     */
    public function add_response(response $response) {
        $this->responses[] = $response;
        $this->update_avg();
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
        $avg = $this->get_avg();
        if ($avg === null || $this->get_weight() == null) {
            return null;
        }
        return $this->get_avg() * $this->get_weight();
    }

    /**
     * Gets the students comments
     * @return array The students comments
     */
    public function get_student_comments() : array {
        $comments = [];
        foreach ($this->responses as $response) {
            $comments[] = $response->get_student_comment();
        }
        return $comments;
    }

    /**
     * Updates the average value
     */
    private function update_avg() {
        $values = [];
        foreach ($this->responses as $response) {
            $values[] = $response->get_value();
        }
        $values = array_filter($values, fn($n) => $n !== null && $n !== false && $n !== '');
        if (count($values) == 0) {
            $this->avg = null;
        } else {
            $this->avg = array_sum($values) / count($values);
        }
    }
}
