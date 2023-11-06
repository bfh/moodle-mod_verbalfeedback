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
 * Class containing data for a verbal feedback subrating.
 *
 * @package    mod_verbalfeedback
 * @copyright  2021 Kevin Tippenhauer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_verbalfeedback\model;

use Exception;
use mod_verbalfeedback\repository\language_repository;

/**
 * The subrating class
 */
class subrating {
    /** @var int The id */
    public $id = 0;
    /** @var array The titles */
    public $titles = [];
    /** @var array The descriptions */
    public $descriptions = [];
    /** @var array The very negative subratings */
    public $verynegatives = [];
    /** @var array The negative subratings */
    public $negatives = [];
    /** @var array The positive subratings */
    public $positives = [];
    /** @var array The very positive subratings */
    public $verypositives = [];

    /**
     * The subrating class constructor
     *
     * @param int $id The id
     * @param array $titles The titles
     * @param array $descriptions The descriptions
     * @param array $verynegatives The very negative subratings
     * @param array $negatives The negative subratings
     * @param array $positives The positive subratings
     * @param array $verypositives The very positive subratings
     */
    public function __construct(int $id = 0, array $titles = [], array $descriptions = [], array $verynegatives = [],
    array $negatives = [], array $positives = [], array $verypositives = []) {
        $this->id = $id;
        $this->titles = $titles;
        $this->descriptions = $descriptions;
        $this->verynegatives = $verynegatives;
        $this->negatives = $negatives;
        $this->positives = $positives;
        $this->verypositives = $verypositives;
    }

    /**
     * Sets the id.
     *
     * @param int $id The id.
     */
    public function set_id(int $id) {
        $this->id = $id;
    }

    /**
     * Gets the id.
     *
     * @return int The id.
     */
    public function get_id() : int {
        return $this->id;
    }

    /**
     * Gets the localized titles.
     * @return array<int, localized_string> The localized strings.
     */
    public function get_titles() : array {
        return $this->titles;
    }

    /**
     * Gets the localized title for the given language string.
     *
     * @param string $languagestr The given language string
     * @return localized_string|null The localized title.
     */
    public function get_title(string $languagestr) : ?localized_string {
        $languagerepository = new language_repository();

        foreach ($this->get_titles() as $title) {
            $language = $languagerepository->get_by_id($title->get_language_id());
            if ($language->get_language() == $languagestr) {
                return $title;
            }
        }
        return null;
    }

    /**
     * Adds a localized title to the subrating instance.
     * @param localized_string $title The localized title.
     */
    public function add_title(localized_string $title) {
        $this->titles[] = $title;
    }

    /**
     * Gets the localized subrating descriptions.
     *
     * @return array<int, localized_string> The localized subrating descriptions.
     */
    public function get_descriptions() : array {
        return $this->descriptions;
    }

    /**
     * Gets a localized subrating description for the given language string.
     *
     * @param string $languagestr The given language string
     * @return localized_string|null The localized description.
     */
    public function get_description(string $languagestr) : ?localized_string {
        $languagerepository = new language_repository();

        foreach ($this->get_descriptions() as $description) {
            $language = $languagerepository->get_by_id($description->get_language_id());
            if ($language->get_language() == $languagestr) {
                return $description;
            }
        }
        return null;
    }

    /**
     * Adds a localized description to the subrating instance.
     *
     * @param localized_string $description The localized description.
     */
    public function add_description(localized_string $description) {
        $this->descriptions[] = $description;
    }

    /**
     * Gets the localized verynegatives.
     * @return array<int, localized_string> The localized verynegative.
     */
    public function get_verynegatives() : array {
        return $this->verynegatives;
    }

    /**
     * Gets the localized verynegative for the given language string.
     *
     * @param string $languagestr The given language string
     * @return localized_string|null The localized verynegative.
     */
    public function get_verynegative(string $languagestr) : ?localized_string {
        $languagerepository = new language_repository();

        foreach ($this->get_verynegatives() as $verynegative) {
            $language = $languagerepository->get_by_id($verynegative->get_language_id());
            if ($language->get_language() == $languagestr) {
                return $verynegative;
            }
        }
        return null;
    }

    /**
     * Adds a localized verynegative to the subrating instance.
     * @param localized_string $verynegative The localized verynegative.
     */
    public function add_verynegative(localized_string $verynegative) {
        $this->verynegatives[] = $verynegative;
    }

    /**
     * Gets the localized negatives.
     * @return array<int, localized_string> The localized negative.
     */
    public function get_negatives() : array {
        return $this->negatives;
    }

    /**
     * Gets the localized negative for the given language string.
     *
     * @param string $languagestr The given language string
     * @return localized_string|null The localized negative.
     */
    public function get_negative(string $languagestr) : ?localized_string {
        $languagerepository = new language_repository();

        foreach ($this->get_negatives() as $negative) {
            $language = $languagerepository->get_by_id($negative->get_language_id());
            if ($language->get_language() == $languagestr) {
                return $negative;
            }
        }
        return null;
    }

    /**
     * Adds a localized negative to the subrating instance.
     *
     * @param localized_string $negative The localized negative.
     */
    public function add_negative(localized_string $negative) {
        $this->negatives[] = $negative;
    }

    /**
     * Gets the localized positives.
     * @return array<int, localized_string> The localized positive.
     */
    public function get_positives() : array {
        return $this->positives;
    }

    /**
     * Gets the localized positive for the given language string.
     *
     * @param string $languagestr The given language string
     * @return localized_string|null The localized positive.
     */
    public function get_positive(string $languagestr) : ?localized_string {
        $languagerepository = new language_repository();

        foreach ($this->get_positives() as $positive) {
            $language = $languagerepository->get_by_id($positive->get_language_id());
            if ($language->get_language() == $languagestr) {
                return $positive;
            }
        }
        return null;
    }

    /**
     * Adds a localized positive to the subrating instance.
     * @param localized_string $positive The localized positive.
     */
    public function add_positive(localized_string $positive) {
        $this->positives[] = $positive;
    }

    /**
     * Gets the localized verypositives.
     * @return array<int, localized_string> The localized verypositive.
     */
    public function get_verypositives() : array {
        return $this->verypositives;
    }

    /**
     * Gets the localized verypositive for the given language string.
     *
     * @param string $languagestr The given language string
     * @return localized_string|null The localized verypositive.
     */
    public function get_verypositive(string $languagestr) : ?localized_string {
        $languagerepository = new language_repository();

        foreach ($this->get_verypositives() as $verypositive) {
            $language = $languagerepository->get_by_id($verypositive->get_language_id());
            if ($language->get_language() == $languagestr) {
                return $verypositive;
            }
        }
        return null;
    }

    /**
     * Adds a localized verypositive to the subrating instance.
     * @param localized_string $verypositive The localized verypositive.
     */
    public function add_verypositive(localized_string $verypositive) {
        $this->verypositives[] = $verypositive;
    }
}
