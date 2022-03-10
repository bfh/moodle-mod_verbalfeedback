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
    'mod_verbalfeedback_get_questions' => [
        'classname'     => 'mod_verbalfeedback\external',
        'methodname'    => 'get_questions',
        'classpath'     => '',
        'description'   => 'Get the questions from the question bank.',
        'type'          => 'read',
        'capabilities'  => '',
        'ajax'          => true,
        'loginrequired' => false,
    ],
    'mod_verbalfeedback_add_question' => [
        'classname'     => 'mod_verbalfeedback\external',
        'methodname'    => 'add_question',
        'classpath'     => '',
        'description'   => 'Add a question to the question bank.',
        'type'          => 'write',
        'capabilities'  => 'mod/verbalfeedback:editquestions',
        'ajax'          => true,
        'loginrequired' => false,
    ],
    'mod_verbalfeedback_update_question' => [
        'classname'     => 'mod_verbalfeedback\external',
        'methodname'    => 'update_question',
        'classpath'     => '',
        'description'   => 'Update a question in the question bank.',
        'type'          => 'write',
        'capabilities'  => 'mod/verbalfeedback:editquestions',
        'ajax'          => true,
        'loginrequired' => false,
    ],
    'mod_verbalfeedback_delete_question' => [
        'classname'     => 'mod_verbalfeedback\external',
        'methodname'    => 'delete_question',
        'classpath'     => '',
        'description'   => 'Delete a question in the question bank.',
        'type'          => 'write',
        'capabilities'  => 'mod/verbalfeedback:editquestions',
        'ajax'          => true,
        'loginrequired' => false,
    ],
    'mod_verbalfeedback_get_items' => [
        'classname'     => 'mod_verbalfeedback\external',
        'methodname'    => 'get_items',
        'description'   => 'Get items for a specific verbal feedback instance.',
        'type'          => 'read',
        'ajax'          => true,
        'loginrequired' => false,
    ],
    'mod_verbalfeedback_set_items' => [
        'classname'     => 'mod_verbalfeedback\external',
        'methodname'    => 'set_items',
        'description'   => 'Set the items for a specific verbal feedback instance.',
        'type'          => 'read',
        'ajax'          => true,
        'loginrequired' => false,
    ],
    'mod_verbalfeedback_delete_item' => [
        'classname'     => 'mod_verbalfeedback\external',
        'methodname'    => 'delete_item',
        'description'   => 'Delete item.',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => false,
    ],
    'mod_verbalfeedback_get_question_types' => [
        'classname'     => 'mod_verbalfeedback\external',
        'methodname'    => 'get_question_types',
        'description'   => 'Get verbal feedback question types.',
        'type'          => 'read',
        'ajax'          => true,
        'loginrequired' => false,
    ],
    'mod_verbalfeedback_get_question_categories' => [
        'classname'     => 'mod_verbalfeedback\external',
        'methodname'    => 'get_question_categories',
        'description'   => 'Get verbal feedback question types.',
        'type'          => 'read',
        'ajax'          => true,
        'loginrequired' => false,
    ],
    'mod_verbalfeedback_move_item_up' => [
        'classname'     => 'mod_verbalfeedback\external',
        'methodname'    => 'move_item_up',
        'description'   => 'Move item up.',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => false,
    ],
    'mod_verbalfeedback_move_item_down' => [
        'classname'     => 'mod_verbalfeedback\external',
        'methodname'    => 'move_item_down',
        'description'   => 'Move item down.',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => false,
    ],
    'mod_verbalfeedback_decline_feedback' => [
        'classname'     => 'mod_verbalfeedback\external',
        'methodname'    => 'decline_feedback',
        'description'   => 'Decline feedback request.',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => false,
    ],
    'mod_verbalfeedback_undo_decline' => [
        'classname'     => 'mod_verbalfeedback\external',
        'methodname'    => 'undo_decline',
        'description'   => 'Undo declined feedback submission',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => true,
    ],
    'mod_verbalfeedback_data_for_participant_list' => [
        'classname'     => 'mod_verbalfeedback\external',
        'methodname'    => 'data_for_participant_list',
        'description'   => 'Get data for the list of participants.',
        'type'          => 'read',
        'ajax'          => true,
        'loginrequired' => false,
    ],
    'mod_verbalfeedback_save_responses' => [
        'classname'     => 'mod_verbalfeedback\external',
        'methodname'    => 'save_responses',
        'description'   => 'Save responses for the verbal feedback activity.',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => false,
    ],
    'mod_verbalfeedback_get_responses' => [
        'classname'     => 'mod_verbalfeedback\external',
        'methodname'    => 'get_responses',
        'description'   => 'Loads the responses of a user for the verbal feedback activity questionnaire.',
        'type'          => 'read',
        'ajax'          => true,
        'loginrequired' => false,
    ],
    'mod_verbalfeedback_update_item_multiplier' => [
        'classname'     => 'mod_verbalfeedback\external',
        'methodname'    => 'update_item_multiplier',
        'description'   => 'Updates the multiplier of a given item.',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => false,
    ],
    'mod_verbalfeedback_update_category_percentage' => [
        'classname'     => 'mod_verbalfeedback\external',
        'methodname'    => 'update_category_percentage',
        'description'   => 'Updates the percentage value of a category.',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => false,
    ],
];
