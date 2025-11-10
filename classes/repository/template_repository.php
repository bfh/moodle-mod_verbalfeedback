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
use mod_verbalfeedback\model\template\template;
use mod_verbalfeedback\repository\model\db_parametrized_category;
use mod_verbalfeedback\repository\model\db_template;

/**
 * The template repository class.
 */
class template_repository {
    /**
     * Gets all the templates within the database.
     * @return array<int, template> The resulting templates.
     */
    public function get_all(): array {
        global $DB;
        $results = [];
        $dbotemplates = $DB->get_records(tables::TEMPLATE_TABLE);

        foreach ($dbotemplates as $dbotemplate) {
            $template = db_template::to_template($dbotemplate);

            $dboparametrizedcategories =
                $DB->get_records(tables::PARAMETRIZED_TEMPLATE_CATEGORY_TABLE, ['templateid' => $template->get_id()]);
            $parametrizedcategories = [];
            foreach ($dboparametrizedcategories as $o) {
                $parametrizedcategories[] = db_parametrized_category::to_parametrized_category($o);
            }
            $template->set_template_categories($parametrizedcategories);

            $results[] = $template;
        }
        return $results;
    }

    /**
     * Gets the template for the given id
     * @param int $id The template id.
     * @return template|null The template.
     */
    public function get_by_id(int $id): template {
        global $DB;
        $dbo = $DB->get_record(tables::TEMPLATE_TABLE, ['id' => $id]);
        $template = db_template::to_template($dbo);

        $dboparametrizedcategories =
            $DB->get_records(tables::PARAMETRIZED_TEMPLATE_CATEGORY_TABLE, ['templateid' => $template->get_id()]);

        $parametrizedcategories = [];
        foreach ($dboparametrizedcategories as $o) {
            $parametrizedcategories[] = db_parametrized_category::to_parametrized_category($o);
        }
        $template->set_template_categories($parametrizedcategories);

        return $template;
    }

    /**
     * Saves the template
     *
     * @param template $template
     * @return int
     * @throws \dml_transaction_exception
     */
    public function save(template $template) {
        global $DB;
        try {
            $transaction = $DB->start_delegated_transaction();
            $dbotemplate = db_template::from_template($template);
            if ($template->get_id() === 0) {
                $id = $DB->insert_record(tables::TEMPLATE_TABLE, $dbotemplate);
                $template->set_id($id);
            } else {
                $DB->update_record(tables::TEMPLATE_TABLE, $dbotemplate);
            }

            $DB->delete_records(tables::PARAMETRIZED_TEMPLATE_CATEGORY_TABLE, ['templateid' => $template->get_id()]);
            foreach ($template->get_template_categories() as $category) {
                $dboparametrizedcategory = db_parametrized_category::from_parametrized_category($category, $template->get_id());
                $DB->insert_record(tables::PARAMETRIZED_TEMPLATE_CATEGORY_TABLE, $dboparametrizedcategory);
            }
            $transaction->allow_commit();
        } catch (Exception $e) {
            $transaction->rollback($e);
        }
        return $template->get_id();
    }

    /**
     * Deletes the template with the given id in the database
     *
     * @param int $id The id of the template.
     * @return bool true
     */
    public function delete_by_id(int $id): bool {
        global $DB;
        try {
            $transaction = $DB->start_delegated_transaction();
            $DB->delete_records(tables::PARAMETRIZED_TEMPLATE_CATEGORY_TABLE, ['templateid' => $id]);
            $DB->delete_records(tables::TEMPLATE_TABLE, ['id' => $id]);

            $transaction->allow_commit();
        } catch (Exception $e) {
            $transaction->rollback($e);
        }
        return true;
    }
}
