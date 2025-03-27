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
 * Class containing data for a verbal feedback instance category.
 *
 * @package    mod_verbalfeedback
 * @copyright  2021 Kevin Tippenhauer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_verbalfeedback\model;

use mod_verbalfeedback\model\localized_string;
use mod_verbalfeedback\model\template\parametrized_template_category;
use mod_verbalfeedback\repository\language_repository;
use mod_verbalfeedback\repository\template_category_repository;
use mod_verbalfeedback\repository\template_criterion_repository;
use stdClass;

/**
 * The category instance class
 */
class instance_category {
    /** @var int The instance category id */
    public $id;
    /** @var int|null The parametrized template category id */
    public $paramtemplatecategoryid;
    /** @var array The localized headers */
    public $localizedheaders = [];
    /** @var int The category position */
    private $position;
    /** @var float The category weight */
    private $weight;
    /** @var array The category instance criteria */
    public $instancecriteria = [];

    /**
     * The category instance class constructor
     *
     * @param int $id The instance category id
     * @param int|null $paramtemplatecategoryid The parametrized template category id
     * @param int $position The category position
     * @param float $weight The category weight
     */
    public function __construct(int $id = 0, ?int $paramtemplatecategoryid = null, int $position = 0, float $weight = 0.0) {
        $this->id = $id;
        $this->position = $position;
        $this->weight = $weight;
        $this->paramtemplatecategoryid = $paramtemplatecategoryid;
    }

    /**
     * Build a parametrized template category from template
     *
     * @param parametrized_template_category $paramtemplatecategory The parametrized template category id
     * @return instance_category
     */
    public static function from_template(parametrized_template_category $paramtemplatecategory): instance_category {
        $instancecategory = new instance_category();
        $instancecategory->set_parametrized_template_category_id($paramtemplatecategory->get_id());
        $instancecategory->set_position($paramtemplatecategory->get_position());
        $instancecategory->set_weight($paramtemplatecategory->get_weight());

        $templatecategoryrepo = new template_category_repository();
        $templatecategory = $templatecategoryrepo->get_by_id($paramtemplatecategory->get_template_category_id());
        foreach ($templatecategory->get_headers() as $localizedheader) {
            $instanceheader = new localized_string($localizedheader->get_language_id(), 0, $localizedheader->get_string());
            $instancecategory->add_header($instanceheader);
        }

        $templatecriterionrepo = new template_criterion_repository();
        $templatecategoryid = $paramtemplatecategory->get_template_category_id();
        $paramtemplatecriteria = $templatecriterionrepo->get_by_template_category_id($templatecategoryid);

        foreach ($paramtemplatecriteria as $templatecriterion) {
            $instancecriterion = instance_criterion::from_template($templatecriterion);
            $instancecategory->instancecriteria[] = $instancecriterion;
        }

        return $instancecategory;
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
    public function get_id(): int {
        return $this->id;
    }

    /**
     * Sets the parametrized template category id
     *
     * @param int|null $paramtemplatecategoryid The parametrized template category id
     */
    public function set_parametrized_template_category_id(?int $paramtemplatecategoryid) {
        $this->paramtemplatecategoryid = $paramtemplatecategoryid;
    }

    /**
     * Gets the parametrized template category id
     *
     * @return int|null The parametrized template category id
     */
    public function get_parametrized_template_category_id(): ?int {
        return $this->paramtemplatecategoryid;
    }

    /**
     * Sets the template category position
     *
     * @param int $position The template category position
     */
    public function set_position(int $position) {
        $this->position = $position;
    }

    /**
     * Gets the template category position
     *
     * @return int The template category position
     */
    public function get_position(): int {
        return $this->position;
    }

    /**
     * Sets the category weight
     *
     * @param float $weight The category weight
     */
    public function set_weight(float $weight) {
        $this->weight = $weight;
    }

    /**
     * Gets the category weight
     *
     * @return float The category weight
     */
    public function get_weight(): float {
        return $this->weight;
    }

    /**
     * Gets the localized instance category headers
     *
     * @return array<int, localized_string> The localized instance category headers.
     */
    public function get_headers(): array {
        return $this->localizedheaders;
    }

    /**
     * Gets the instance category header
     *
     * @param string $languagestr The given language string
     * @return \mod_verbalfeedback\model\localized_string
     */
    public function get_header(string $languagestr): localized_string {
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
     * Adds a header to the category
     *
     * @param localized_string $header The header
     */
    public function add_header(localized_string $header) {
        $this->localizedheaders[] = $header;
    }

    /**
     * Gets the instance criteria within the category
     *
     * @return array<int, instance_criterion> The criteria
     */
    public function get_criteria(): array {
        return $this->instancecriteria;
    }

    /**
     * Adds a criterion within the category
     *
     * @param instance_criterion $criterion The criterion
     */
    public function add_criterion(instance_criterion $criterion) {
        $this->instancecriteria[] = $criterion;
    }
}
