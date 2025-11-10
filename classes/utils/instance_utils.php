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

require_once($CFG->libdir . '/accesslib.php');

use context_module;
use moodle_exception;
use moodle_url;
use stdClass;
use calendar_event;
use mod_verbalfeedback\api;
use mod_verbalfeedback\model\instance;
use mod_verbalfeedback\model\instance_status;
use mod_verbalfeedback\repository\instance_repository;

/**
 * Class for performing user related actions for the verbal feedback activity module.
 *
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class instance_utils {
    /**
     * Make the verbal feedback instance ready for use by the participants.
     *
     * @param instance $instance The verbal feedback instance.
     * @return bool
     * @throws moodle_exception
     */
    public static function make_ready(instance $instance) {
        $cm = get_coursemodule_from_instance('verbalfeedback', $instance->get_id());
        $context = context_module::instance($cm->id);
        $url = new moodle_url('/mod/verbalfeedback/view.php', ['id' => $cm->id]);
        if (!user_utils::can_edit_items($instance->get_id(), $context)) {
            throw new moodle_exception('nocaptoedititems', 'mod_verbalfeedback', $url);
        }
        if (!$instance->has_items()) {
            throw new moodle_exception('noitemsyet', 'mod_verbalfeedback', $url);
        }
        $instancerepo = new instance_repository();
        $instance->set_status(instance_status::READY);
        return $instancerepo->save($instance);
    }

    /**
     * Returns the context_module of the given activity.
     *
     * @param instance $instance The verbal feedback activity instance.
     * @return context_module $context
     */
    public static function get_context_by_instance(instance $instance) {
        $cm = get_coursemodule_from_instance('verbalfeedback', $instance->get_id());
        return context_module::instance($cm->id);
    }


    /**
     * This creates new calendar events given as timeopen and timeclose by $verbalfeedback.
     *
     * @param instance $verbalfeedback The verbal feedback instance.
     * @return void
     */
    public static function register_calendar_events(instance $verbalfeedback) {
        global $CFG;

        require_once($CFG->dirroot . '/calendar/lib.php');

        $cm = get_coursemodule_from_instance('verbalfeedback', $verbalfeedback->get_id(), $verbalfeedback->get_course());

        // Get CMID if not sent as part of $verbalfeedback.
        if (!isset($instance->coursemodule)) {
            $verbalfeedback->coursemodule = $cm->id;
        }

        // Common event parameters.
        $instanceid = $verbalfeedback->get_id();
        $courseid = $verbalfeedback->get_course();
        $eventdescription = format_module_intro('verbalfeedback', $verbalfeedback, $cm);
        $visible = instance_is_visible('verbalfeedback', $verbalfeedback);

        // Calendar event for when the verbal feedback opens.
        $eventname = get_string('calendarstart', 'verbalfeedback', $verbalfeedback->get_name());
        $eventtype = api::VERBALFEEDBACK_EVENT_TYPE_OPEN;
        // Calendar event type is set to action event when there's no timeclose.
        $calendareventtype = empty($verbalfeedback->get_timeclose()) ? CALENDAR_EVENT_TYPE_ACTION : CALENDAR_EVENT_TYPE_STANDARD;
        self::set_event(
            $instanceid,
            $eventname,
            $eventdescription,
            $eventtype,
            $calendareventtype,
            $verbalfeedback->get_timeopen(),
            $visible,
            $courseid
        );

        // Calendar event for when the verbal feedback closes.
        $eventname = get_string('calendarend', 'verbalfeedback', $verbalfeedback->get_name());
        $eventtype = api::VERBALFEEDBACK_EVENT_TYPE_CLOSE;
        $calendareventtype = CALENDAR_EVENT_TYPE_ACTION;
        self::set_event(
            $instanceid,
            $eventname,
            $eventdescription,
            $eventtype,
            $calendareventtype,
            $verbalfeedback->get_timeclose(),
            $visible,
            $courseid
        );
    }

    /**
     * Sets the calendar event for the verbal feedback instance.
     *
     * For existing events, if timestamp is not empty, the event will be updated. Otherwise, it will be deleted.
     * If the event is not yet existing and the timestamp is empty, the event will be created.
     *
     * @param int $id The verbal feedback instance ID.
     * @param string $eventname The event name.
     * @param string $description The event description.
     * @param string $eventtype The type of the module event.
     * @param int $calendareventtype The calendar event type, whether a standard or an action event.
     * @param int $timestamp The event's timestamp.
     * @param bool $visible Whether this event is visible.
     * @param int $courseid The course ID of this event.
     */
    protected static function set_event(
        $id,
        $eventname,
        $description,
        $eventtype,
        $calendareventtype,
        $timestamp,
        $visible,
        $courseid
    ) {
        global $DB;

        // Build the calendar event object.
        $event = new stdClass();
        $event->name         = $eventname;
        $event->description  = $description;
        $event->eventtype    = $eventtype;
        $event->timestart    = $timestamp;
        $event->timesort     = $timestamp;
        $event->visible      = $visible;
        $event->timeduration = 0;
        $event->type         = $calendareventtype;

        // Check if event exists.
        $event->id = $DB->get_field(
            'event',
            'id',
            [
                'modulename' => 'verbalfeedback',
                'instance' => $id,
                'eventtype' => $eventtype,
            ]
        );
        if ($event->id) {
            $calendarevent = calendar_event::load($event->id);
            if ($timestamp) {
                // Calendar event exists so update it.
                $calendarevent->update($event, false);
            } else {
                // Calendar event is no longer needed.
                $calendarevent->delete();
            }
        } else if ($timestamp) {
            // Event doesn't exist so create one.
            $event->courseid     = $courseid;
            $event->groupid      = 0;
            $event->userid       = 0;
            $event->modulename   = 'verbalfeedback';
            $event->instance     = $id;
            calendar_event::create($event, false);
        }
    }

    /**
     * Set verbal feedback maximum grade.
     *
     * @param float $newgrade the new maximum grade for the quiz.
     * @param object $verbalfeedback the quiz we are updating. Passed by reference so its
     *      grade field can be updated too.
     * @return bool indicating success or failure.
     */
    public static function verbalfeedback_set_grade(float $newgrade, object $verbalfeedback) {
        global $DB;
        // This is potentially expensive, so only do it if necessary.
        if (abs($verbalfeedback->grade - $newgrade) < 1e-7) {
            // Nothing to do.
            return true;
        }
        $verbalfeedback->grade = $newgrade;

        // Use a transaction, so that on those databases that support it, this is safer.
        $transaction = $DB->start_delegated_transaction();

        // Update the verbal feedback table.
        $DB->set_field('verbalfeedback', 'grade', $newgrade, ['id' => $verbalfeedback->id]);

        $transaction->allow_commit();
        return true;
    }
}
