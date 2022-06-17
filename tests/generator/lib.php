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
 * mod_verbalfeedback data generator.
 *
 * @package mod_verbalfeedback
 * @category test
 * @copyright 2021 Luca Bösch <luca.boesch@bfh.ch>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * mod_verbalfeedback data generator class.
 *
 * @package mod_verbalfeedback
 * @category test
 * @copyright 2021 Luca Bösch <luca.boesch@bfh.ch>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_verbalfeedback_generator extends testing_module_generator {

    /**
     * Create a verbal feedback instance for testing purposes
     * @param null $record A verbal feedback record
     * @param array|null $options An array of options
     * @return stdClass
     * @throws coding_exception
     */
    public function create_instance($record = null, array $options = null) {
        $record = (object)(array)$record;

        if (!isset($record->timemodified)) {
            $record->timemodified = time();
        }
        return parent::create_instance($record, (array)$options);
    }
}
