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
 * Class for performing DB actions for the verbal feedback activity module.
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_verbalfeedback;

defined('MOODLE_INTERNAL') || die();

global $CFG;

use mod_verbalfeedback\model\language;
use mod_verbalfeedback\repository\language_repository;
use mod_verbalfeedback\repository\mapper;
use mod_verbalfeedback\repository\tables;

/**
 * Verbal feedback language repository test class
 */
final class language_repository_test extends \advanced_testcase {

    /** @var language_repository A language repository */
    protected $repo;

    /**
     * Setup the test class.
     */
    public function setUp(): void {
        $this->repo = new language_repository();
    }

    /**
     * Test saving the language repository
     *
     * @covers \mod_verbalfeedback\repository\language_repository::save
     * @throws dml_exception
     */
    public function test_save(): void {
        $this->resetAfterTest();

        $language = new language(1, 'en');

        $id = $this->repo->save($language);
        $this->assertFalse($id === null, 'save method returned null or 0');
        if (empty($language->get_id())) {
            // Set the id to $id if the language was stored without a given id.
            $language->set_id($id);
        }
        $this->assertEquals($language->get_id(), $id, 'id does not match given id.');

        // Test updating a language.
        $language->set_language('de');
        $this->repo->save($language);

        $this->assertEquals($language->get_language(), 'de', 'language does not match given language.');
    }

    /**
     * Save the provider
     *
     * @return array[]
     */
    public function save_provider(): array {
        // Array fields => id, language.
        // Updating entries is also tested with these two tests.
        return [
            'with id = null' => [null, 'en'],
            'with id = 0' => [0, 'en'],
        ];
    }

    /**
     * Test getting all providers for languages
     *
     * @covers \mod_verbalfeedback\repository\language_repository::get_all
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_get_all(): void {
        global $DB;
        $this->resetAfterTest();

        $languages = [
            ['id' => 1, 'language' => 'en'],
            ['id' => 2, 'language' => 'de'],
            ['id' => 3, 'language' => 'fr'],
        ];
        $DB->insert_records('verbalfeedback_language', $languages);

        $results = $this->repo->get_all();

        $this->assertEquals(count($languages), count($results));
    }

    /**
     * Getting all providers
     *
     * @return array
     */
    public function get_all_provider(): array {
        // Array fields => id, language.
        return [
            'with empty table' => [[]],
            'with 1 entry' => [[
                ['id' => 1, 'language' => 'en'],
            ], ],
            'with 3 entries' => [[
                ['id' => 1, 'language' => 'en'],
                ['id' => 2, 'language' => 'de'],
                ['id' => 3, 'language' => 'fr'],
            ], ],
        ];
    }

    /**
     * Test getting by id
     *
     * @covers \mod_verbalfeedback\repository\language_repository::get_by_id
     */
    public function test_get_by_id(): void {
        $this->resetAfterTest();

        $language = new language(null, 'en');

        $id = $this->repo->save($language);

        $language = $this->repo->get_by_id($id);
        $this->assertInstanceOf(language::class, $language);
        $this->assertEquals('en', $language->get_language());
    }

    /**
     * Test deleting by id
     *
     * @covers \mod_verbalfeedback\repository\language_repository::delete_by_id
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_delete_by_id(): void {
        global $DB;
        $this->resetAfterTest();
        $languages = [
            ['id' => 1, 'language' => 'en'],
            ['id' => 2, 'language' => 'de'],
            ['id' => 3, 'language' => 'fr'],
        ];
        $DB->insert_records('verbalfeedback_language', $languages);

        $this->repo->delete_by_id(1);

        $this->assertFalse($DB->get_record('verbalfeedback_language', ['id' => 1]));
    }

    /**
     * Delete by id of provider
     *
     * @return array[]
     */
    public function delete_by_id_provider(): array {
        // Array fields => id, language.
        return [
            'with empty table' => ['languages' => [], 'delete_id' => 1],
            'with existing entries' => ['languages' => [
                ['id' => 1, 'language' => 'en'],
                ['id' => 2, 'language' => 'de'],
                ['id' => 3, 'language' => 'fr'],
            ], 'delete_id' => 1, ],
        ];
    }
}
