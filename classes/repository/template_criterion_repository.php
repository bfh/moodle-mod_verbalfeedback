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
 * Class for performing DB actions for the verbalfeedback activity module.
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_verbalfeedback\repository;

use Exception;
use mod_verbalfeedback\model\localized_string;
use mod_verbalfeedback\model\subrating;
use mod_verbalfeedback\model\template\parametrized_template_criterion;
use mod_verbalfeedback\model\template\template_criterion;
use mod_verbalfeedback\repository\model\db_localized_string;
use mod_verbalfeedback\repository\model\db_parametrized_criterion;
use mod_verbalfeedback\repository\model\db_subrating;
use mod_verbalfeedback\repository\model\db_template_criterion;
use mod_verbalfeedback\repository\model\localized_string_type;

/**
 * The template criterion repository class
 */
class template_criterion_repository {

    /**
     * Gets the category templates
     * @return array<int, template_criterion>.
     */
    public function get_all() : array {
        global $DB;
        $results = [];

        $rs = $DB->get_recordset(tables::TEMPLATE_CRITERION_TABLE);

        foreach ($rs as $dbocriterion) {
            $templatecriterion = self::dbo_to_template_criterion($dbocriterion);

            $criteriondescriptions = $this->get_localized_strings($templatecriterion->get_id(),
                localized_string_type::TEMPLATE_CRITERION);
            $templatecriterion->set_descriptions($criteriondescriptions);

            $subratings = $this->get_subratings_by_criterion_id($templatecriterion->get_id());
            $templatecriterion->set_subratings($subratings);

            $results[] = $templatecriterion;
        }

        $rs->close();
        return $results;
    }

    /**
     * Get all template criterion hashed by id.
     * @return array
     * @throws \dml_exception
     */
    private function get_all_template_criterion(): array {
        global $DB;
        $result = [];
        $rs = $DB->get_recordset(tables::TEMPLATE_CRITERION_TABLE);
        foreach ($rs as $row) {
            $templatecriterion = db_template_criterion::to_template_criterion($row);
            $criteriondescriptions = $this->get_localized_strings($templatecriterion->get_id(),
                localized_string_type::TEMPLATE_CRITERION);
            $templatecriterion->set_descriptions($criteriondescriptions);
            $subratings = $this->get_subratings_by_criterion_id($templatecriterion->get_id());
            $templatecriterion->set_subratings($subratings);
            $result[$row->id] = $templatecriterion;
        }
        $rs->close();
        return $result;
    }

    /**
     * Gets the criteria template for the given id
     * @param int $id The criteria template id.
     * @return template_criterion|null The template criterion.
     */
    public function get_by_id(int $id) : ?template_criterion {
        static $all = null;

        if ($all === null || PHPUNIT_TEST) {
            $all = $this->get_all_template_criterion();
        }

        return $all[$id] ?? null;
    }

    /**
     * Gets the criteria templates with the given template category id.
     * @param int $id the template category id.
     * @return array<int, parametrized_template_criterion>.
     */
    public function get_by_template_category_id(int $id) : array {
        global $DB;
        $results = [];
        $dboparamcriteria = $DB->get_records(tables::PARAMETRIZED_TEMPLATE_CRITERION_TABLE, ['categoryid' => $id]);
        foreach ($dboparamcriteria as $dboparamcriterion) {
            $results[] = db_parametrized_criterion::to_parametrized_criterion($dboparamcriterion);
        }
        return $results;
    }

    /**
     * Saves a template criterion
     *
     * @param template_criterion $templatecriterion The template criterion
     * @return int The id of the saved template criterion
     * @throws \dml_transaction_exception
     */
    public function save(template_criterion $templatecriterion) {
        global $DB;
        try {
            $transaction = $DB->start_delegated_transaction();
            $dbocriterion = db_template_criterion::from_template_criterion($templatecriterion);
            if ($templatecriterion->get_id() == 0) {
                $id = $DB->insert_record(tables::TEMPLATE_CRITERION_TABLE, $dbocriterion);
                $templatecriterion->set_id($id);
            } else {
                $DB->update_record(tables::TEMPLATE_CRITERION_TABLE, $dbocriterion);
            }

            // Criteria description.
            foreach ($templatecriterion->get_descriptions() as $localizedstring) {
                $dbolocalizedstring = db_localized_string::from_localized_string($localizedstring,
                    localized_string_type::TEMPLATE_CRITERION, $templatecriterion->get_id());
                if ($localizedstring->get_id() == 0) {
                    $DB->insert_record(tables::LOCALIZED_STRING_TABLE, $dbolocalizedstring);
                } else {
                    $DB->update_record(tables::LOCALIZED_STRING_TABLE, $dbolocalizedstring);
                }
            }

            // Save/update subratings.
            foreach ($templatecriterion->get_subratings() as $subrating) {
                $dbosubrating = db_subrating::from_subrating($subrating, $templatecriterion->get_id());
                if ($subrating->get_id() == 0) {
                    $id = $DB->insert_record(tables::TEMPLATE_SUBRATINGS_TABLE, $dbosubrating);
                    $subrating->set_id($id);
                } else {
                    $DB->update_record(tables::TEMPLATE_SUBRATINGS_TABLE, $dbosubrating);
                }

                foreach ($subrating->get_titles() as $title) {
                    $dbotitle = db_localized_string::from_localized_string($title,
                        localized_string_type::TEMPLATE_SUBRATING_TITLE, $subrating->get_id());
                    if ($title->get_id() == 0) {
                        $DB->insert_record(tables::LOCALIZED_STRING_TABLE, $dbotitle);
                    } else {
                        $DB->update_record(tables::LOCALIZED_STRING_TABLE, $dbotitle);
                    }
                }

                // Subrating description.
                foreach ($subrating->get_descriptions() as $description) {
                    $dbodescription = db_localized_string::from_localized_string($description,
                        localized_string_type::TEMPLATE_SUBRATING_DESCRIPTION, $subrating->get_id());
                    if ($description->get_id() == 0) {
                        $DB->insert_record(tables::LOCALIZED_STRING_TABLE, $dbodescription);
                    } else {
                        $DB->update_record(tables::LOCALIZED_STRING_TABLE, $dbodescription);
                    }
                }

                foreach ($subrating->get_verynegatives() as $verynegative) {
                    $dboverynegative = db_localized_string::from_localized_string($verynegative,
                        localized_string_type::TEMPLATE_SUBRATING_VERY_NEGATIVE, $subrating->get_id());
                    if ($verynegative->get_id() == 0) {
                        $DB->insert_record(tables::LOCALIZED_STRING_TABLE, $dboverynegative);
                    } else {
                        $DB->update_record(tables::LOCALIZED_STRING_TABLE, $dboverynegative);
                    }
                }
                foreach ($subrating->get_negatives() as $negative) {
                    $dbonegative = db_localized_string::from_localized_string($negative,
                        localized_string_type::TEMPLATE_SUBRATING_NEGATIVE, $subrating->get_id());
                    if ($negative->get_id() == 0) {
                        $DB->insert_record(tables::LOCALIZED_STRING_TABLE, $dbonegative);
                    } else {
                        $DB->update_record(tables::LOCALIZED_STRING_TABLE, $dbonegative);
                    }
                }
                foreach ($subrating->get_positives() as $positive) {
                    $dbopositive = db_localized_string::from_localized_string($positive,
                        localized_string_type::TEMPLATE_SUBRATING_POSITIVE, $subrating->get_id());
                    if ($positive->get_id() == 0) {
                        $DB->insert_record(tables::LOCALIZED_STRING_TABLE, $dbopositive);
                    } else {
                        $DB->update_record(tables::LOCALIZED_STRING_TABLE, $dbopositive);
                    }
                }
                foreach ($subrating->get_verypositives() as $verypositive) {
                    $dboverypositive = db_localized_string::from_localized_string($verypositive,
                        localized_string_type::TEMPLATE_SUBRATING_VERY_POSITIVE, $subrating->get_id());
                    if ($verypositive->get_id() == 0) {
                        $DB->insert_record(tables::LOCALIZED_STRING_TABLE, $dboverypositive);
                    } else {
                        $DB->update_record(tables::LOCALIZED_STRING_TABLE, $dboverypositive);
                    }
                }
            }

            $transaction->allow_commit();
        } catch (Exception $e) {
            $transaction->rollback($e);
        }
        return $templatecriterion->get_id();
    }

    /**
     * Deletes criteria template records in the database
     *
     * @param int $id The id of the criteria template to delete.
     * @throws \dml_transaction_exception
     */
    public function delete(int $id) {
        global $DB;
        try {
            $transaction = $DB->start_delegated_transaction();

            // Delete parametrized template criteria based on this template criterion.
            $DB->delete_records(tables::PARAMETRIZED_TEMPLATE_CRITERION_TABLE, ['criterionid' => $id]);

            // Delete subratings.
            $dbosubratings = $DB->get_records(tables::TEMPLATE_SUBRATINGS_TABLE, ['criterionid' => $id]);
            foreach ($dbosubratings as $dbosubrating) {
                $DB->delete_records(tables::LOCALIZED_STRING_TABLE, ['foreignkey' => $dbosubrating->id,
                'type' => localized_string_type::TEMPLATE_SUBRATING_TITLE, ], );
                $DB->delete_records(tables::LOCALIZED_STRING_TABLE, ['foreignkey' => $dbosubrating->id,
                'type' => localized_string_type::TEMPLATE_SUBRATING_DESCRIPTION, ], );
                $DB->delete_records(tables::LOCALIZED_STRING_TABLE, ['foreignkey' => $dbosubrating->id,
                'type' => localized_string_type::TEMPLATE_SUBRATING_VERY_NEGATIVE, ], );
                $DB->delete_records(tables::LOCALIZED_STRING_TABLE, ['foreignkey' => $dbosubrating->id,
                'type' => localized_string_type::TEMPLATE_SUBRATING_NEGATIVE, ], );
                $DB->delete_records(tables::LOCALIZED_STRING_TABLE, ['foreignkey' => $dbosubrating->id,
                'type' => localized_string_type::TEMPLATE_SUBRATING_POSITIVE, ], );
                $DB->delete_records(tables::LOCALIZED_STRING_TABLE, ['foreignkey' => $dbosubrating->id,
                'type' => localized_string_type::TEMPLATE_SUBRATING_VERY_POSITIVE, ], );
            }
            $DB->delete_records(tables::TEMPLATE_SUBRATINGS_TABLE, ['criterionid' => $id]);

            // Delete localized strings.
            $DB->delete_records(tables::LOCALIZED_STRING_TABLE, ['foreignkey' => $id,
            'type' => localized_string_type::TEMPLATE_CRITERION, ], );

            // Delete criterion.
            $DB->delete_records(tables::TEMPLATE_CRITERION_TABLE, ['id' => $id]);
            $transaction->allow_commit();
        } catch (Exception $e) {
            $transaction->rollback($e);
        }
    }

    /**
     * Gets a template criterion when given a database object
     *
     * @param object $dbo The database object
     * @return template_criterion The template criterion
     */
    public static function dbo_to_template_criterion($dbo) {
        $templatecriteria = new template_criterion();
        if (isset($dbo->id)) {
            $templatecriteria->set_id($dbo->id);
        }
        return $templatecriteria;
    }

    /**
     * Get all localized strings for a type, hashed by foreignkey, cached in memory for speed.
     * @TODO - evaluate this for memory usage.
     * @param string $type
     * @return array<localized_string[]>
     * @throws \dml_exception
     */
    private function get_all_localized_strings_for_type(string $type) : array {
        global $DB;

        static $strings = [];

        if (isset($strings[$type]) && !PHPUNIT_TEST) {
            // We already have this cached, so return it.
            return $strings[$type];
        }

        $strings[$type] = [];

        $rs = $DB->get_recordset(tables::LOCALIZED_STRING_TABLE, ['type' => $type]);
        foreach ($rs as $row) {
            $type = $row->type;
            $key = $row->foreignkey;
            if (!isset($strings[$type][$key])) {
                $strings[$type][$key] = [];
            }
            $strings[$type][$key][$row->id] = db_localized_string::to_localized_string($row);
        }
        $rs->close();

        return $strings[$type];
    }

    /**
     * Get localized strings for a criterion
     *
     * @param int $foreignkey The foreign key
     * @param string $type The type
     * @return array Localized strings
     * @throws \dml_exception
     */
    private function get_localized_strings(int $foreignkey, string $type) : array {
        if (!localized_string_type::exists($type)) {
            throw new \Exception("Unknown localized string type.");
        }

        $allstrings = $this->get_all_localized_strings_for_type($type);
        $localizedstrings = $allstrings[$foreignkey] ?? null;
        if (!$localizedstrings) {
            throw new coding_exception("Couldn't find localized strings by type '$type' and foreignkey '$foreignkey'");
        }

        return $localizedstrings;
    }

    /**
     * Get subratings by a criterion id
     *
     * @param int $criterionid The criterion id
     * @return array The subratings
     * @throws \dml_exception
     */
    private function get_subratings_by_criterion_id(int $criterionid) {
        global $DB;
        $subratings = [];

        $dbosubratings = $DB->get_records(tables::TEMPLATE_SUBRATINGS_TABLE, ['criterionid' => $criterionid]);
        foreach ($dbosubratings as $dbosubrating) {
            $titles = $this->get_localized_strings($dbosubrating->id,
                localized_string_type::TEMPLATE_SUBRATING_TITLE);

            $descriptions = $this->get_localized_strings($dbosubrating->id,
                localized_string_type::TEMPLATE_SUBRATING_DESCRIPTION);

            $verynegatives = $this->get_localized_strings($dbosubrating->id,
                localized_string_type::TEMPLATE_SUBRATING_VERY_NEGATIVE);

            $negatives = $this->get_localized_strings($dbosubrating->id,
                localized_string_type::TEMPLATE_SUBRATING_NEGATIVE);

            $positives = $this->get_localized_strings($dbosubrating->id,
                localized_string_type::TEMPLATE_SUBRATING_POSITIVE);

            $verypositives = $this->get_localized_strings($dbosubrating->id,
                localized_string_type::TEMPLATE_SUBRATING_VERY_POSITIVE);

            $subrating = new subrating($dbosubrating->id, $titles, $descriptions, $verynegatives,
            $negatives, $positives, $verypositives);

            $subratings[] = $subrating;
        }
        return $subratings;
    }
}
