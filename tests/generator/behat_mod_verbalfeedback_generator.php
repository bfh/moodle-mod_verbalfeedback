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
 * Behat data generator for mod_verbalfeedback.
 *
 * @package    mod_verbalfeedback
 * @category   test
 * @copyright  2026 Stephan Robotta <stephan.robotta@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_mod_verbalfeedback_generator extends behat_generator_base {
    /**
     * Get a list of the entities that can be created for this component.
     *
     * @return array The list of creatable entities.
     */
    protected function get_creatable_entities(): array {
        return [
            'submissions' => [
                'singular' => 'submission',
                'datagenerator' => 'submission',
                'required' => ['verbalfeedback', 'userfrom', 'userto'],
                'switchids' => [
                    'verbalfeedback' => 'instanceid',
                    'userfrom' => 'fromuserid',
                    'userto' => 'touserid',
                ],
            ],
        ];
    }

    /**
     * Look up the id of a verbal feedback instance from its name.
     *
     * @param string $name The verbal feedback instance name, for example 'Test verbal feedback'.
     * @return int The corresponding instance id.
     */
    protected function get_verbalfeedback_id(string $name): int {
        global $DB;

        // The verbalfeedback "name" column is a TEXT field, which cannot be compared directly
        // in a WHERE clause (e.g. on PostgreSQL), so use sql_compare_text().
        $select = $DB->sql_compare_text('name') . ' = ' . $DB->sql_compare_text(':name');
        if (!$id = $DB->get_field_select('verbalfeedback', 'id', $select, ['name' => $name])) {
            throw new Exception('There is no verbal feedback instance with name "' . $name . '"');
        }
        return (int)$id;
    }
}
