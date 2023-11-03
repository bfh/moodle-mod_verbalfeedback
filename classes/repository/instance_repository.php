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

defined('MOODLE_INTERNAL') || die();

use Exception;
use mod_verbalfeedback\model\instance;
use mod_verbalfeedback\model\instance_category;
use mod_verbalfeedback\model\instance_status;
use mod_verbalfeedback\repository\model\db_instance;
use mod_verbalfeedback\repository\model\db_instance_category;
use mod_verbalfeedback\repository\model\db_instance_criterion;
use mod_verbalfeedback\repository\model\db_instance_subrating;
use mod_verbalfeedback\repository\model\db_localized_string;
use mod_verbalfeedback\repository\model\localized_string_type;

require_once(__DIR__ . '/../../lib.php');

/**
 * The instance repository class.
 */
class instance_repository {

    /**
     * Gets the instance with the given id
     * @param int $id The language id.
     * @return instance|null The language.
     */
    public static function get_by_id(int $id) : instance {
        global $DB;

        // Return cached $byid instance if available and if we are not running a php unit test.
        static $byid = [];
        if (isset($byid[$id]) && !PHPUNIT_TEST) {
            return $byid[$id];
        }

        $dboinstance = $DB->get_record(tables::INSTANCE_TABLE, ["id" => $id]);
        $instance = db_instance::to_instance($dboinstance);

        $dbocategories = $DB->get_records(tables::INSTANCE_CATEGORY_TABLE, ["instanceid" => $id]);

        $criteriabycatid = self::get_criteria_by_category_for_instance_id($id);
        $subratingsbycritid = self::get_subratings_by_criterion_for_instance($id);

        foreach ($dbocategories as $dbocategory) {
            $category = db_instance_category::to_instance_category($dbocategory);

            // Load category headers.
            $dbolocalizedstrings = self::get_strings(localized_string_type::INSTANCE_CATEGORY_HEADER, $category->get_id());
            foreach ($dbolocalizedstrings as $dbo) {
                $header = db_localized_string::to_localized_string($dbo);
                $category->add_header($header);
            }

            // Load category criteria.
            $criteria = $criteriabycatid[$category->get_id()];
            foreach ($criteria as $criterion) {

                // Load criterion description.
                $dbodescriptions = self::get_strings(localized_string_type::INSTANCE_CRITERION, $criterion->get_id());
                foreach ($dbodescriptions as $dbodescription) {
                    $description = db_localized_string::to_localized_string($dbodescription);
                    $criterion->add_description($description);
                }

                // Load criterion subratings.
                $subratings = $subratingsbycritid[$criterion->get_id()];
                foreach ($subratings as $subrating) {
                    foreach ([
                        // Load subrating titles (1 title per language).
                        localized_string_type::INSTANCE_SUBRATING_TITLE => 'titles',
                        // Load subrating descriptions.
                        localized_string_type::INSTANCE_SUBRATING_DESCRIPTION => 'descriptions',
                        // Load subrating very negative texts.
                        localized_string_type::INSTANCE_SUBRATING_VERY_NEGATIVE => 'verynegatives',
                        // Load subrating negative texts.
                        localized_string_type::INSTANCE_SUBRATING_NEGATIVE => 'negatives',
                        // Load subrating positive texts.
                        localized_string_type::INSTANCE_SUBRATING_POSITIVE => 'positives',
                        // Load subrating very positive texts.
                        localized_string_type::INSTANCE_SUBRATING_VERY_POSITIVE => 'verypositives',
                    ] as $type => $attribute) {
                        $dbotitles = self::get_strings($type, $subrating->get_id());
                        foreach ($dbotitles as $dbotitle) {
                            $item = db_localized_string::to_localized_string($dbotitle);
                            $subrating->{$attribute}[] = $item;
                        }
                    }

                    // Load subrating descriptions.
                    $dbosubratingdescriptions = self::get_strings(localized_string_type::INSTANCE_SUBRATING_DESCRIPTION,
                        $subrating->get_id());
                    foreach ($dbosubratingdescriptions as $dbosubratingdescription) {
                        $subratingdescription = db_localized_string::to_localized_string($dbosubratingdescription);
                        $subrating->add_description($subratingdescription);
                    }

                    // Load subrating very negative texts.
                    $dboverynegatives = self::get_strings(localized_string_type::INSTANCE_SUBRATING_VERY_NEGATIVE,
                        $subrating->get_id());
                    foreach ($dboverynegatives as $dboverynegative) {
                        $verynegative = db_localized_string::to_localized_string($dboverynegative);
                        $subrating->add_verynegative($verynegative);
                    }

                    // Load subrating negative texts.
                    $dbonegatives = self::get_strings(localized_string_type::INSTANCE_SUBRATING_NEGATIVE,
                        $subrating->get_id());
                    foreach ($dbonegatives as $dbonegative) {
                        $negative = db_localized_string::to_localized_string($dbonegative);
                        $subrating->add_negative($negative);
                    }

                    // Load subrating positive texts.
                    $dbopositives = self::get_strings(localized_string_type::INSTANCE_SUBRATING_POSITIVE,
                        $subrating->get_id());
                    foreach ($dbopositives as $dbopositive) {
                        $positive = db_localized_string::to_localized_string($dbopositive);
                        $subrating->add_positive($positive);
                    }

                    // Load subrating very positive texts.
                    $dboverypositives = self::get_strings(localized_string_type::INSTANCE_SUBRATING_VERY_POSITIVE,
                        $subrating->get_id());
                    foreach ($dboverypositives as $dboverypositive) {
                        $verypositive = db_localized_string::to_localized_string($dboverypositive);
                        $subrating->add_verypositive($verypositive);
                    }
                    $criterion->add_subrating($subrating);
                }
                $category->add_criterion($criterion);
            }
            $instance->add_category($category);
        }

        // Add to cache.
        $byid[$id] = $instance;
        return $instance;
    }

    private static function get_strings(string $type, int $subratingid, bool $throwonerror = false): array {
        global $DB;

        static $sortedstrings = null;

        if ($sortedstrings === null || PHPUNIT_TEST) {
            $sortedstrings = [];
            $rs = $DB->get_recordset(tables::LOCALIZED_STRING_TABLE);
            foreach ($rs as $dboheader) {
                $dbobj = new db_localized_string;
                $dbobj->id = $dboheader->id;
                $dbobj->languageid = $dboheader->languageid;
                $dbobj->string = $dboheader->string;
                $dbobj->type = $dboheader->type;
                $dbobj->foreignkey = $dboheader->foreignkey;

                $sortedstrings[$dbobj->type][$dbobj->foreignkey][$dbobj->languageid] = $dbobj;
            }
            $rs->close();
        }

        if (!isset($sortedstrings[$type])) {
            if ($throwonerror) {
                throw new \coding_exception("Invalid type .$type");
            }
            return [];
        }
        if (!isset($sortedstrings[$type][$subratingid])) {
            if ($throwonerror) {
                throw new \coding_exception("Invalid subratingid $subratingid for type $type");
            }
            return [];
        }

        return $sortedstrings[$type][$subratingid];
    }

    /**
     * Gets all criteria hashed by category id for a specific instance.
     * @param int $id - instance id
     * @return array
     * @throws \dml_exception
     */
    private static function get_criteria_by_category_for_instance_id(int $id): array {
        global $DB;

        $crittab = tables::INSTANCE_CRITERION_TABLE;
        $cattab = tables::INSTANCE_CATEGORY_TABLE;

        $sql = "SELECT crit.*
                  FROM {{$crittab}} crit
                  JOIN {{$cattab}} cat
                    ON crit.categoryid = cat.id
                 WHERE cat.instanceid = ?";

        $bycat = [];
        $rs = $DB->get_recordset_sql($sql, [$id]);
        foreach ($rs as $dbocriterion) {
            if (!isset($bycat[$dbocriterion->categoryid])) {
                $bycat[$dbocriterion->categoryid] = [];
            }
            $bycat[$dbocriterion->categoryid][$dbocriterion->id] = db_instance_criterion::to_instance_criterion($dbocriterion);
        }
        $rs->close();
        return $bycat;
    }

    /**
     * Get subratings hashed by criterion id for an instance.
     * @param int $id - instance id
     * @return array
     * @throws \dml_exception
     */
    private static function get_subratings_by_criterion_for_instance(int $id): array {
        global $DB;
        $crittab = tables::INSTANCE_CRITERION_TABLE;
        $cattab = tables::INSTANCE_CATEGORY_TABLE;
        $srattab = tables::INSTANCE_SUBRATING_TABLE;
        $sql = "SELECT srat.*
                  FROM {{$srattab}} srat
                  JOIN {{$crittab}} crit
                    ON srat.criterionid = crit.id
                  JOIN {{$cattab}} mvic
                    ON crit.categoryid = mvic.id
                 WHERE mvic.instanceid = ?";
        $rs = $DB->get_recordset_sql($sql, [$id]);
        $bycrit = [];
        foreach ($rs as $dbosubrating) {
            if (!isset($bycrit[$dbosubrating->criterionid])) {
                $bycrit[$dbosubrating->criterionid] = [];
            }
            $bycrit[$dbosubrating->criterionid][$dbosubrating->id] = db_instance_subrating::to_subrating($dbosubrating);
        }
        $rs->close();
        return $bycrit;
    }

    /**
     * Save the given instance
     * @param instance $instance The instance object.
     */
    public function save(instance $instance) {
        global $DB;
        try {
            $transaction = $DB->start_delegated_transaction();
            $dboinstance = db_instance::from_instance($instance);
            if ($instance->get_id() == 0) {
                $id = $DB->insert_record(tables::INSTANCE_TABLE, $dboinstance);
                $instance->set_id($id);
                // Set the grade.
                $DB->set_field('verbalfeedback', 'grade', $instance->grade, array('id' => $id));
            } else {
                $DB->update_record(tables::INSTANCE_TABLE, $dboinstance);
            }

            foreach ($instance->get_categories() as $category) {
                $dbocategory = db_instance_category::from_instance_category($category, $instance->get_id());
                if ($category->get_id() == 0) {
                    $id = $DB->insert_record(tables::INSTANCE_CATEGORY_TABLE, $dbocategory);
                    $category->set_id($id);
                } else {
                    $DB->update_record(tables::INSTANCE_CATEGORY_TABLE, $dbocategory);
                }

                foreach ($category->get_headers() as $header) {
                    $dbolocalizedstring = db_localized_string::from_localized_string($header,
                        localized_string_type::INSTANCE_CATEGORY_HEADER, $category->get_id());
                    if ($header->get_id() == 0) {
                        $DB->insert_record(tables::LOCALIZED_STRING_TABLE, $dbolocalizedstring);
                    } else {
                        $DB->update_record(tables::LOCALIZED_STRING_TABLE, $dbolocalizedstring);
                    }
                }

                foreach ($category->get_criteria() as $criterion) {
                    $dbocriterion = db_instance_criterion::from_instance_criterion($criterion, $category->get_id());
                    if ($criterion->get_id() == 0) {
                        $id = $DB->insert_record(tables::INSTANCE_CRITERION_TABLE, $dbocriterion);
                        $criterion->set_id($id);
                    } else {
                        $DB->update_record(tables::INSTANCE_CRITERION_TABLE, $dbocriterion);
                    }

                    foreach ($criterion->get_descriptions() as $localizedstring) {
                        $dbolocalizedstring = db_localized_string::from_localized_string($localizedstring,
                            localized_string_type::INSTANCE_CRITERION, $criterion->get_id());
                        if ($localizedstring->get_id() == 0) {
                            $DB->insert_record(tables::LOCALIZED_STRING_TABLE, $dbolocalizedstring);
                        } else {
                            $DB->update_record(tables::LOCALIZED_STRING_TABLE, $dbolocalizedstring);
                        }
                    }

                    foreach ($criterion->get_subratings() as $subrating) {
                        $dbosubrating = db_instance_subrating::from_subrating($subrating, $criterion->get_id());
                        if ($subrating->get_id() === 0) {
                            $id = $DB->insert_record(tables::INSTANCE_SUBRATING_TABLE, $dbosubrating);
                            $subrating->set_id($id);
                        } else {
                            $DB->update_record(tables::INSTANCE_SUBRATING_TABLE, $dbosubrating);
                        }

                        foreach ($subrating->get_titles() as $title) {
                            $dbotitle = db_localized_string::from_localized_string($title,
                                localized_string_type::INSTANCE_SUBRATING_TITLE, $subrating->get_id());
                            if ($title->get_id() == 0) {
                                $DB->insert_record(tables::LOCALIZED_STRING_TABLE, $dbotitle);
                            } else {
                                $DB->update_record(tables::LOCALIZED_STRING_TABLE, $dbotitle);
                            }
                        }

                        // Subrating description.
                        foreach ($subrating->get_descriptions() as $description) {
                            $dbodescription = db_localized_string::from_localized_string($description,
                                localized_string_type::INSTANCE_SUBRATING_DESCRIPTION, $subrating->get_id());
                            if ($description->get_id() == 0) {
                                $DB->insert_record(tables::LOCALIZED_STRING_TABLE, $dbodescription);
                            } else {
                                $DB->update_record(tables::LOCALIZED_STRING_TABLE, $dbodescription);
                            }
                        }

                        foreach ($subrating->get_verynegatives() as $verynegative) {
                            $dboverynegative = db_localized_string::from_localized_string($verynegative,
                                localized_string_type::INSTANCE_SUBRATING_VERY_NEGATIVE, $subrating->get_id());
                            if ($verynegative->get_id() == 0) {
                                $DB->insert_record(tables::LOCALIZED_STRING_TABLE, $dboverynegative);
                            } else {
                                $DB->update_record(tables::LOCALIZED_STRING_TABLE, $dboverynegative);
                            }
                        }
                        foreach ($subrating->get_negatives() as $negative) {
                            $dbonegative = db_localized_string::from_localized_string($negative,
                                localized_string_type::INSTANCE_SUBRATING_NEGATIVE, $subrating->get_id());
                            if ($negative->get_id() == 0) {
                                $DB->insert_record(tables::LOCALIZED_STRING_TABLE, $dbonegative);
                            } else {
                                $DB->update_record(tables::LOCALIZED_STRING_TABLE, $dbonegative);
                            }
                        }
                        foreach ($subrating->get_positives() as $positive) {
                            $dbopositive = db_localized_string::from_localized_string($positive,
                                localized_string_type::INSTANCE_SUBRATING_POSITIVE, $subrating->get_id());
                            if ($positive->get_id() == 0) {
                                $DB->insert_record(tables::LOCALIZED_STRING_TABLE, $dbopositive);
                            } else {
                                $DB->update_record(tables::LOCALIZED_STRING_TABLE, $dbopositive);
                            }
                        }
                        foreach ($subrating->get_verypositives() as $verypositive) {
                            $dboverypositive = db_localized_string::from_localized_string($verypositive,
                                localized_string_type::INSTANCE_SUBRATING_VERY_POSITIVE, $subrating->get_id());
                            if ($verypositive->get_id() == 0) {
                                $DB->insert_record(tables::LOCALIZED_STRING_TABLE, $dboverypositive);
                            } else {
                                $DB->update_record(tables::LOCALIZED_STRING_TABLE, $dboverypositive);
                            }
                        }
                    }
                }
            }

            $transaction->allow_commit();
        } catch (Exception $e) {
            $transaction->rollback($e);
        }
        return $instance->get_id();
    }

    /**
     * Deletes the instance with the given id.
     * @param int $instanceid The instance id.
     */
    public function delete(int $instanceid) {
        global $DB;

        $dbocategories = $DB->get_records(tables::INSTANCE_CATEGORY_TABLE, ["instanceid" => $instanceid]);
        foreach ($dbocategories as $dbocategory) {
            // Delete category headers.
            $DB->delete_records(tables::LOCALIZED_STRING_TABLE,
            ["type" => localized_string_type::INSTANCE_CATEGORY_HEADER, "foreignkey" => $dbocategory->id]);

            // Delete category criteria.
            $dbocriteria = $DB->get_records(tables::INSTANCE_CRITERION_TABLE, ["categoryid" => $dbocategory->id]);
            foreach ($dbocriteria as $dbocriterion) {

                // Delete criterion description.
                $dbodescriptions = $DB->delete_records(tables::LOCALIZED_STRING_TABLE,
                    ["type" => localized_string_type::INSTANCE_CRITERION, "foreignkey" => $dbocriterion->id]);

                // Delete criterion subratings.
                $dbosubratings = $DB->get_records(tables::INSTANCE_SUBRATING_TABLE, ["criterionid" => $dbocriterion->id]);
                foreach ($dbosubratings as $dbosubrating) {

                    // Delete subrating titles.
                    $DB->delete_records(tables::LOCALIZED_STRING_TABLE,
                        ["type" => localized_string_type::INSTANCE_SUBRATING_TITLE, "foreignkey" => $dbosubrating->id]);

                    // Delete subrating descriptions.
                    $DB->delete_records(tables::LOCALIZED_STRING_TABLE,
                        ["type" => localized_string_type::INSTANCE_SUBRATING_DESCRIPTION, "foreignkey" => $dbosubrating->id]);

                    // Delete subrating very negative texts.
                    $DB->delete_records(tables::LOCALIZED_STRING_TABLE,
                        ["type" => localized_string_type::INSTANCE_SUBRATING_VERY_NEGATIVE, "foreignkey" => $dbosubrating->id]);

                    // Delete subrating negative texts.
                    $DB->delete_records(tables::LOCALIZED_STRING_TABLE,
                        ["type" => localized_string_type::INSTANCE_SUBRATING_NEGATIVE, "foreignkey" => $dbosubrating->id]);

                    // Delete subrating positive texts.
                    $DB->delete_records(tables::LOCALIZED_STRING_TABLE,
                        ["type" => localized_string_type::INSTANCE_SUBRATING_POSITIVE, "foreignkey" => $dbosubrating->id]);

                    // Delete subrating very positive texts.
                    $DB->delete_records(tables::LOCALIZED_STRING_TABLE,
                        ["type" => localized_string_type::INSTANCE_SUBRATING_VERY_POSITIVE, "foreignkey" => $dbosubrating->id]);
                }

                // Delete subratings.
                $dbosubratings = $DB->delete_records(tables::INSTANCE_SUBRATING_TABLE, ["criterionid" => $dbocriterion->id]);
            }
            $DB->delete_records(tables::INSTANCE_CRITERION_TABLE, ["categoryid" => $dbocategory->id]);
        }

        $DB->delete_records(tables::INSTANCE_CATEGORY_TABLE, ["instanceid" => $instanceid]);
        return $DB->delete_records(tables::INSTANCE_TABLE, ["id" => $instanceid]);
    }

    /**
     * Returns whether the instance repository is ready
     *
     * @param int $id The instance id to check
     * @return bool Whether the instance repository is ready
     * @throws \dml_exception
     */
    public function is_ready(int $id) {
        global $DB;
        // Check if this instance already has items.
        if (!$this->has_items($id)) {
            // An instance is not yet ready if doesn't have any item yet.
            return false;
        }
        $status = $DB->get_field(tables::INSTANCE_TABLE, 'status', ['id' => $id]);
        // An instance is ready if its status has been set to ready and it already has items.
        return $status == instance_status::READY;
    }

    /**
     * Returns whether the instance repository has items
     *
     * @param int $id The instance id to check
     * @return bool Whether the instance repository has items
     * @throws \dml_exception
     */
    public function has_items(int $id) {
        global $DB;
        $categories = $DB->get_records(tables::INSTANCE_CATEGORY_TABLE, ['instanceid' => $id]);
        foreach ($categories as $category) {
            if ($DB->record_exists(tables::INSTANCE_CRITERION_TABLE, ['categoryid' => $category->id])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Updates a category weight
     *
     * @param id $categoryid The category id
     * @param float $weight The category weight
     * @return bool
     * @throws \dml_exception
     */
    public function update_category_weight($categoryid, $weight) {
        global $DB;
        // Update all users grades.
        verbalfeedback_update_grades(self::get_by_id($DB->get_field('verbalfeedback_i_category', 'instanceid',
            ["id" => $categoryid])), 0);
        return $DB->set_field(tables::INSTANCE_CATEGORY_TABLE, 'weight', $weight, ['id' => $categoryid]);
    }

    /**
     * Updates a criterion weight
     *
     * @param int $criterionid The criterion id
     * @param float $weight The criterion weight
     * @return bool
     * @throws \dml_exception
     */
    public function update_criterion_weight($criterionid, $weight) {
        global $DB;
        // Update all users grades.
        $instanceid = $DB->get_field_sql('SELECT v.id FROM {verbalfeedback} v JOIN {verbalfeedback_i_category} vc ' .
            'ON vc.instanceid = v.id JOIN {verbalfeedback_i_criterion} vcr ' .
            'ON vc.id = vcr.categoryid WHERE vcr.id = ' . $criterionid);
        verbalfeedback_update_grades(self::get_by_id($instanceid), 0);
        return $DB->set_field(tables::INSTANCE_CRITERION_TABLE, 'weight', $weight, ['id' => $criterionid]);
    }
}
