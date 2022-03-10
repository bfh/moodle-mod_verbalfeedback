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
use mod_verbalfeedback\model\template\template_category;
use mod_verbalfeedback\repository\model\db_localized_string;
use mod_verbalfeedback\repository\model\db_parametrized_criterion;
use mod_verbalfeedback\repository\model\db_template_category;
use mod_verbalfeedback\repository\model\localized_string_type;

/**
 * The template category repository class
 */
class template_category_repository {

    /**
     * Gets all the template categories in the database.
     *
     * @return array<int, template_category> The resulting categories.
     */
    public function get_all() : array {
        global $DB;
        $results = [];
        $dbcategories = $DB->get_records("verbalfeedback_t_category");

        foreach ($dbcategories as $dbocategory) {
            $templatecategory = db_template_category::to_template_category($dbocategory);

            $headers = $this->get_headers($templatecategory->get_id());
            $templatecategory->set_headers($headers);

            $parametrizedcriteria = $this->get_parametrized_criteria($templatecategory->get_id());
            $templatecategory->set_template_criteria($parametrizedcriteria);

            $results[] = $templatecategory;
        }
        return $results;
    }

    /**
     * Gets the category template for the given id
     *
     * @param int $id The category template id.
     * @return template_category|null The template categories.
     */
    public function get_by_id(int $id) : template_category {
        global $DB;
        $dbo = $DB->get_record('verbalfeedback_t_category', ['id' => $id]);
        $templatecategory = db_template_category::to_template_category($dbo);

        $headers = $this->get_headers($templatecategory->get_id());
        $templatecategory->set_headers($headers);

        $parametrizedcriteria = $this->get_parametrized_criteria($templatecategory->get_id());
        $templatecategory->set_template_criteria($parametrizedcriteria);

        return $templatecategory;
    }

    /**
     * Saves the template category
     *
     * @param template_category $templatecategory
     * @return int
     * @throws \dml_transaction_exception
     */
    public function save(template_category $templatecategory) {
        global $DB;
        try {
            $transaction = $DB->start_delegated_transaction();
            $dbocategory = db_template_category::from_template_category($templatecategory);
            if ($templatecategory->get_id() === null || $templatecategory->get_id() == 0) {
                $id = $DB->insert_record('verbalfeedback_t_category', $dbocategory);
                $templatecategory->set_id($id);
            } else {
                $DB->update_record('verbalfeedback_t_category', $dbocategory);
            }

            // Insert or update category headers.
            foreach ($templatecategory->get_headers() as $header) {
                $dboheader = db_localized_string::from_localized_string($header,
                localized_string_type::TEMPLATE_CATEGORY_HEADER, $templatecategory->get_id());

                if ($header->get_id() == 0) {
                    $DB->insert_record('verbalfeedback_local_string', $dboheader);
                } else {
                    $DB->update_record('verbalfeedback_local_string', $dboheader);
                }
            }

            // Update linked criteria.
            $DB->delete_records('verbalfeedback_t_param_crit', ['categoryid' => $templatecategory->get_id()]);
            foreach ($templatecategory->get_template_criteria() as $criterion) {
                $dboparamcriterion = db_parametrized_criterion::from_parametrized_criterion($criterion,
                $templatecategory->get_id());
                $DB->insert_record('verbalfeedback_t_param_crit', $dboparamcriterion);
            }

            $transaction->allow_commit();
        } catch (Exception $e) {
            $transaction->rollback($e);
        }
        return $templatecategory->get_id();
    }

    /**
     * Deletes the category template with the given id in the database
     *
     * @param int $id The id of the category template.
     * @return bool True, if successful
     */
    public function delete_by_id(int $id) : bool {
        global $DB;
        try {
            $transaction = $DB->start_delegated_transaction();
            $DB->delete_records('verbalfeedback_local_string', ['foreignkey' => $id,
            'type' => localized_string_type::TEMPLATE_CATEGORY_HEADER]);
            $DB->delete_records('verbalfeedback_t_param_crit', ['categoryid' => $id]);
            $DB->delete_records('verbalfeedback_t_category', ['id' => $id]);

            $transaction->allow_commit();
        } catch (Exception $e) {
            $transaction->rollback($e);
        }
        return true;
    }

    /**
     * Gets the template category headers
     *
     * @param int $foreignkey The foreign key
     * @return array The category headers
     * @throws \dml_exception
     */
    private function get_headers($foreignkey) : array {
        global $DB;

        $dboheaders = $DB->get_records('verbalfeedback_local_string', ['foreignkey' => $foreignkey,
        'type' => localized_string_type::TEMPLATE_CATEGORY_HEADER]);
        $headers = [];
        foreach ($dboheaders as $dboheader) {
            $headers[] = db_localized_string::to_localized_string($dboheader);
        }

        return $headers;
    }

    /**
     * Gets the parametrized criteria of a category
     *
     * @param int $categoryid The category id
     * @return array The parametrized criteria
     * @throws \dml_exception
     */
    private function get_parametrized_criteria($categoryid) : array {
        global $DB;

        $dboparametrizedcriteria = $DB->get_records('verbalfeedback_t_param_crit', ['categoryid' => $categoryid]);
        $parametrizedcriteria = [];
        foreach ($dboparametrizedcriteria as $dboparametrizedcriterion) {
            $parametrizedcriteria[] = db_parametrized_criterion::to_parametrized_criterion($dboparametrizedcriterion);
        }
        return $parametrizedcriteria;
    }
}
