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
 * Chat external functions and service definitions.
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$functions = [
    'mod_verbalfeedback_data_for_participant_list' => [
        'classname'     => 'mod_verbalfeedback_external',
        'methodname'    => 'data_for_participant_list',
        'classpath'     => 'mod/verbalfeedback/classes/external.php',
        'description'   => 'Get data for the list of participants.',
        'type'          => 'read',
        'ajax'          => true,
        'loginrequired' => true,
    ],
    'mod_verbalfeedback_userselector' => [
        'classname'     => 'mod_verbalfeedback_external',
        'methodname'    => 'data_for_userselector',
        'classpath'     => 'mod/verbalfeedback/classes/external.php',
        'description'   => 'Get data for the list of participants for user selector.',
        'type'          => 'read',
        'ajax'          => true,
        'loginrequired' => true,
    ],
    'mod_verbalfeedback_save_responses' => [
        'classname'     => 'mod_verbalfeedback_external',
        'methodname'    => 'save_responses',
        'classpath'     => 'mod/verbalfeedback/classes/external.php',
        'description'   => 'Save responses for the verbal feedback activity.',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => true,
    ],
    'mod_verbalfeedback_get_responses' => [
        'classname'     => 'mod_verbalfeedback_external',
        'methodname'    => 'get_responses',
        'classpath'     => 'mod/verbalfeedback/classes/external.php',
        'description'   => 'Loads the responses of a user for the verbal feedback activity questionnaire.',
        'type'          => 'read',
        'ajax'          => true,
        'loginrequired' => true,
    ],
    'mod_verbalfeedback_update_item_multiplier' => [
        'classname'     => 'mod_verbalfeedback_external',
        'methodname'    => 'update_item_multiplier',
        'classpath'     => 'mod/verbalfeedback/classes/external.php',
        'description'   => 'Updates the multiplier of a given item.',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => true,
    ],
    'mod_verbalfeedback_update_category_percentage' => [
        'classname'     => 'mod_verbalfeedback_external',
        'methodname'    => 'update_category_percentage',
        'classpath'     => 'mod/verbalfeedback/classes/external.php',
        'description'   => 'Updates the percentage value of a category.',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => true,
    ],
];
