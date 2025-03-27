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

use mod_verbalfeedback\model\language;
use mod_verbalfeedback\repository\model\db_language;

/**
 * The language repository class.
 */
class language_repository {

    /**
     * Inserts (language->id = null or 0) or updates a language record in the database.
     * Note: no error is thrown if no record for the given id exists.
     *
     * @param language $language The language object.
     * @return int The id of the language.
     * @throws dml_exception A DML specific exception is thrown for any errors.
     */
    public function save(language $language) {
        global $DB;
        $dbolanguage = db_language::from_language($language);
        if ((int)$language->get_id() === 0) {
            $id = $DB->insert_record(tables::LANGUAGE_TABLE, $dbolanguage);
            $language->set_id($id);
        } else {
            $DB->update_record(tables::LANGUAGE_TABLE, $dbolanguage);
        }
        return $language->get_id();
    }

    /**
     * Deletes language records in the database
     * @param int $id The language id
     * @return bool True, if successful
     */
    public function delete_by_id(int $id): bool {
        global $DB;
        return $DB->delete_records(tables::LANGUAGE_TABLE, ['id' => $id]);
    }

    /**
     * Selects all languages in the database
     * @return array<int, language> The resulting languages
     */
    public function get_all(): array {
        global $DB;
        $languages = [];

        $dbo = $DB->get_records(tables::LANGUAGE_TABLE);
        foreach ($dbo as $o) {
            $languages[] = db_language::to_language($o);
        }
        return $languages;
    }

    /**
     * Gets the language for the given id
     * @param int $id The language id.
     * @return language|null The language.
     */
    public function get_by_id(int $id): language {
        global $DB;
        $dbo = $DB->get_record(tables::LANGUAGE_TABLE, ["id" => $id]);
        return db_language::to_language($dbo);
    }

    /**
     * Gets the language for the given iso code.
     * @param string $iso The language iso code.
     * @return language|null The language.
     */
    public function get_by_iso(string $iso): language {
        global $DB;
        $dbo = $DB->get_record(tables::LANGUAGE_TABLE, ["language" => $iso]);
        return db_language::to_language($dbo);
    }
}
