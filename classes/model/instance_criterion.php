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
 * Class containing data for a verbal feedback instance criterion.
 *
 * @package    mod_verbalfeedback
 * @copyright  2021 Kevin Tippenhauer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_verbalfeedback\model;

use mod_verbalfeedback\model\localized_string;
use mod_verbalfeedback\model\template\parametrized_template_criterion;
use mod_verbalfeedback\repository\language_repository;
use mod_verbalfeedback\repository\template_criterion_repository;

/**
 * The criterion instance class
 */
class instance_criterion {
    /** @var int The id */
    public $id;
    /** @var int|null The parametrized template criterion id */
    public $parametrizedtemplatecriterionid;
    /** @var int The criterion position */
    public $position;
    /** @var float The criterion weight */
    public $weight;
    /** @var array|mixed The criterion descriptions */
    public $descriptions = array();
    /** @var array|mixed The criterion subratings */
    public $subratings = array();

    /**
     * The criterion instance class constructor
     *
     * @param int $id The id
     * @param int|null $parametrizedtemplatecriterionid The parametrized template criterion id
     * @param int $position The criterion position
     * @param float $weight The criterion weight
     * @param array $descriptions The criterion descriptions
     * @param array $subratings The criterion subratings
     */
    public function __construct(int $id = 0, ?int $parametrizedtemplatecriterionid = null, int $position = 0,
    float $weight = 0.0, $descriptions = array(), $subratings = array()) {
        $this->id = $id;
        $this->parametrizedtemplatecriterionid = $parametrizedtemplatecriterionid;
        $this->position = $position;
        $this->weight = $weight;
        $this->descriptions = $descriptions;
        $this->subratings = $subratings;
    }

    /**
     * Build a parametrized template criterion from template
     *
     * @param parametrized_template_criterion $parametrizedtemplatecriterion The parametrized template criterion
     * @return instance_criterion
     */
    public static function from_template(parametrized_template_criterion $parametrizedtemplatecriterion) {
        $instancecriterion = new instance_criterion();
        $instancecriterion->set_id(0);
        $instancecriterion->set_parametrized_template_criterion_id($parametrizedtemplatecriterion->get_id());
        $instancecriterion->set_position($parametrizedtemplatecriterion->get_position());
        $instancecriterion->set_weight($parametrizedtemplatecriterion->get_weight());

        $templatecriterionrepository = new template_criterion_repository();
        $templatecriterion = $templatecriterionrepository->get_by_id($parametrizedtemplatecriterion->get_template_criterion_id());

        foreach ($templatecriterion->get_descriptions() as $templatelocalizedstring) {
            $languageid = $templatelocalizedstring->get_language_id();
            $localizedstring = new localized_string($languageid, 0, $templatelocalizedstring->get_string());
            $instancecriterion->add_description($localizedstring);
        }

        foreach ($templatecriterion->get_subratings() as $templatesubrating) {
            // Create a copy for the instance criteria.
            $subrating = new subrating();
            foreach ($templatesubrating->get_titles() as $templatetitle) {
                $localizedstring = new localized_string($templatetitle->get_language_id(), 0, $templatetitle->get_string());
                $subrating->add_title($localizedstring);
            }
            foreach ($templatesubrating->get_descriptions() as $templatedescription) {
                $languageid = $templatedescription->get_language_id();
                $localizedstring = new localized_string($languageid, 0, $templatedescription->get_string());
                $subrating->add_description($localizedstring);
            }
            foreach ($templatesubrating->get_verynegatives() as $templateverynegative) {
                $languageid = $templateverynegative->get_language_id();
                $localizedstring = new localized_string($languageid, 0, $templateverynegative->get_string());
                $subrating->add_verynegative($localizedstring);
            }
            foreach ($templatesubrating->get_negatives() as $templatenegative) {
                $languageid = $templatenegative->get_language_id();
                $localizedstring = new localized_string($languageid, 0, $templatenegative->get_string());
                $subrating->add_negative($localizedstring);
            }
            foreach ($templatesubrating->get_positives() as $templatepositive) {
                $languageid = $templatepositive->get_language_id();
                $localizedstring = new localized_string($languageid, 0, $templatepositive->get_string());
                $subrating->add_positive($localizedstring);
            }
            foreach ($templatesubrating->get_verypositives() as $templateverypositive) {
                $languageid = $templateverypositive->get_language_id();
                $localizedstring = new localized_string($languageid, 0, $templateverypositive->get_string());
                $subrating->add_verypositive($localizedstring);
            }

            $instancecriterion->add_subrating($subrating);
        }

        return $instancecriterion;
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
     * Gets the id
     *
     * @return int
     */
    public function get_id() : int {
        return $this->id;
    }

    /**
     * Sets the parametrized template criterion id
     *
     * @param int|null $parametrizedtemplatecriterionid
     */
    public function set_parametrized_template_criterion_id(?int $parametrizedtemplatecriterionid) {
        $this->parametrizedtemplatecriterionid = $parametrizedtemplatecriterionid;
    }

    /**
     * Gets the parametrized template criterion id
     *
     * @return int|null
     */
    public function get_parametrized_template_criterion_id() : ?int {
        return $this->parametrizedtemplatecriterionid;
    }

    /**
     * Sets the instance criterion position
     *
     * @param int $position The instance criterion position
     */
    public function set_position(int $position) {
        $this->position = $position;
    }

    /**
     * Gets the instance criterion position
     *
     * @return int The instance criterion position
     */
    public function get_position() : int {
        return $this->position;
    }

    /**
     * Sets the weight
     *
     * @param float $weight
     */
    public function set_weight(float $weight) {
        $this->weight = $weight;
    }

    /**
     * Gets the weight
     *
     * @return float
     */
    public function get_weight() : float {
        return $this->weight;
    }

    /**
     * Gets the criteria descriptions
     *
     * @return array<int, localized_string> The localized strings.
     */
    public function get_descriptions() : array {
        return $this->descriptions;
    }

    /**
     * Gets a localized criterion description for the given language string
     *
     * @param string $languagestr The given language string
     * @return \mod_verbalfeedback\model\localized_string
     */
    public function get_description(string $languagestr) : localized_string {
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
     * Adds a criteria description
     *
     * @param \mod_verbalfeedback\model\localized_string $string
     */
    public function add_description(localized_string $string) {
        $this->descriptions[] = $string;
    }

    /**
     * Sets criteria's descriptions
     *
     * @param array $descriptions The criteria's descriptions
     */
    public function set_descriptions(array $descriptions) {
        $this->descriptions = $descriptions;
    }

    /**
     * Adds a criteria subrating
     *
     * @param subrating $subrating
     */
    public function add_subrating(subrating $subrating) {
        $this->subratings[] = $subrating;
    }

    /**
     * Gets a criteria's subratings
     *
     * @return array
     */
    public function get_subratings() : array {
        return $this->subratings;
    }
}
