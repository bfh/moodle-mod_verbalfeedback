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

defined('MOODLE_INTERNAL') || die();

global $CFG;

use mod_verbalfeedback\model\language;
use mod_verbalfeedback\repository\language_repository;
use mod_verbalfeedback\repository\mapper;

/**
 * Verbal feedback language repository test class
 */
class mod_verbalfeedback_language_repository_test extends advanced_testcase {

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
     * @param int|null $testid The id to test
     * @param string $testlanguage The language to test
     * @throws dml_exception
     */
    public function test_save(?int $testid, string $testlanguage): void {
        global $DB;
        $this->resetAfterTest(true);

        $language = new language($testid, $testlanguage);

        $id = $this->repo->save($language);
        $this->assertFalse($id === null, 'save method returned null or 0');
        if ($language->get_id() === null || $language->get_id() == 0) {
            // Set the id to $id if the language was stored without a given id.
            $language->set_id($id);
        }
        $this->assertEquals($language->get_id(), $id, 'id does not match given id.');

        $dbo = $DB->get_record('verbalfeedback_language', ["id" => $language->get_id()]);
        $this->assertEquals($language->get_id(), $dbo->id);
        // The stored language matches with the test string.
        $this->assertEquals($language->get_language(), $dbo->language);

        // Test updating a language.
        $language->set_language('de');
        $this->repo->save($language);

        $dbo = $DB->get_record('verbalfeedback_language', ["id" => $language->get_id()]);
        $this->assertEquals($language->get_id(), $dbo->id, 'id does not match anymore after update.');
        $this->assertEquals($language->get_language(), $dbo->language, 'language does not match anymore after update.');
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
            'with id = 0' => [0, 'en']
        ];
    }

    /**
     * Test getting all providers for languages
     *
     * @param array $languages
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_get_all(array $languages) {
        global $DB;
        $this->resetAfterTest(true);

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
                ['id' => 1, 'language' => 'en']
            ]],
            'with 3 entries' => [[
                ['id' => 1, 'language' => 'en'],
                ['id' => 2, 'language' => 'de'],
                ['id' => 3, 'language' => 'fr']
            ]]
        ];
    }

    /**
     * Test getting by id
     */
    public function test_get_by_id() {
        global $DB;
        $this->resetAfterTest(true);

        $language = new language($id = null, $language = 'en');

        $id = $this->repo->save($language);

        $language = $this->repo->get_by_id($id);
        $this->assertInstanceOf(language::class, $language);
        $this->assertEquals($language->get_language(), $language->get_language());
    }

    /**
     * Test deleting by id
     *
     * @param array $languages The languages
     * @param int $deleteid The id
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_delete_by_id(array $languages, int $deleteid) {
        global $DB;
        $this->resetAfterTest(true);
        $DB->insert_records('verbalfeedback_language', $languages);

        $this->repo->delete_by_id($deleteid);

        $this->assertFalse($DB->get_record('verbalfeedback_language', ['id' => $deleteid]));
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
                ['id' => 3, 'language' => 'fr']
            ], 'delete_id' => 1]
        ];
    }
}
