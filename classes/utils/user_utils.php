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
 * Class for performing user related actions for the verbal feedback activity module.
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_verbalfeedback\utils;

defined('MOODLE_INTERNAL') || die();

global $CFG;
global $DB;

require_once($CFG->libdir.'/accesslib.php');

use coding_exception;
use context_module;
use Exception;
use mod_verbalfeedback\model\instance;
use moodle_exception;
use stdClass;

/**
 * Class for performing user related actions for the verbal feedback activity module.
 *
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_utils {

    /**
     * Returns true, if the user is enrolled in the given context.
     *
     * @param context_module $context the context to test
     * @return bool true, if the user is enrolled
     */
    public static function is_enrolled(context_module $context) {
        // User can't participate if not enrolled in the course.
        return is_enrolled($context);
    }

    /**
     * Returns true, if the user is a student in the given context.
     *
     * @param context_module $context to lookup the roles
     * @param int $userid the id of the user
     * @return bool|string True if the user can view other users reports. An error message if not.
     */
    public static function can_view_all_reports(context_module $context, $userid) {
        // User can't participate if not enrolled in the course.
        if (!self::is_enrolled($context, $userid)) {
            return get_string('errornotenrolled', 'mod_verbalfeedback');
        }
        if (has_capability('mod/verbalfeedback:view_all_reports', $context)) {
            return true;
        }
        return get_string('errorcannotviewallreports', 'mod_verbalfeedback');
    }

    /**
     * Whether the user can view their own report.
     *
     * @param instance $instance The verbal feedback instance data.
     * @param context_module|null $context The context the verbal feedback belongs to
     * @return bool
     * @throws coding_exception
     */
    public static function can_view_own_report(instance $instance, ?context_module $context = null): bool {
        $isreleased = $instance->reports_are_released();
        // Get context if not provided.
        if (empty($context)) {
            try {
                $cm = get_coursemodule_from_instance('verbalfeedback', $instance->get_id());
                $context = context_module::instance($cm->id);
            } catch (Exception $e) {
                trigger_error('Failed to load context, returning default value "false" for function "can_view_own_report".',
                    E_USER_WARNING);
                    return false;
            }
        }

        if ($isreleased && has_capability('mod/verbalfeedback:receive_rating', $context)) {
            return true;
        }
        return false;
    }

    /**
     * Checks if the given user ID can give feedback to other participants in the given verbal feedback activity.
     *
     * @param instance $instance The verbal feedback activity object.
     * @param int $userid The user ID.
     * @return bool|string True if the user can respond. An error message if not.
     */
    public static function can_respond(instance $instance, $userid) {
        try {
            $context = instance_utils::get_context_by_instance($instance);
        } catch (Exception $e) {
            trigger_error('Failed to load context, returning default value "false" for function "user:can_respond".',
              E_USER_WARNING);
            return false;
        }
        // User can't participate if not enrolled in the course.
        if (!self::is_enrolled($context, $userid)) {
            return get_string('errornotenrolled', 'mod_verbalfeedback');
        }
        if (has_capability('mod/verbalfeedback:can_respond', $context)) {
            return true;
        }
        return get_string('errorcannotrespond', 'mod_verbalfeedback');
    }

    /**
     * Checks if the given user ID can participate in the given verbal feedback activity.
     *
     * @param instance $instance The verbal feedback activity object.
     * @param int $userid The user ID.
     * @return bool|string True if the user can participate. An error message if not.
     * @throws moodle_exception
     */
    public static function can_participate(instance $instance, $userid) {
        try {
            $context = instance_utils::get_context_by_instance($instance);
        } catch (Exception $e) {
            trigger_error('Failed to load context, returning default value "false" for function "user:can_participate".',
                E_USER_WARNING);
            return false;
        }
        // User can't participate if not enrolled in the course.
        if ((!self::is_enrolled($context, $userid)) && (!has_capability('moodle/site:config', $context))) {
            return get_string('errornotenrolled', 'mod_verbalfeedback');
        }
        if (has_capability('mod/verbalfeedback:can_participate', $context)) {
            return true;
        }
        return get_string('errorcannotparticipate', 'mod_verbalfeedback');
    }

    /**
     * Whether the user has the capability to edit items.
     *
     * @param int $verbalfeedbackid The verbal feedback instance ID.
     * @param context_module $context
     * @return bool
     * @throws coding_exception
     * @throws moodle_exception
     */
    public static function can_edit_items($verbalfeedbackid, $context = null) {
        if (empty($context)) {
            $cm = get_coursemodule_from_instance('verbalfeedback', $verbalfeedbackid);
            $context = context_module::instance($cm->id);
        }
        return has_capability('mod/verbalfeedback:edititems', $context);
    }
}
