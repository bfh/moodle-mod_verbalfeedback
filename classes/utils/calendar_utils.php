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

use stdClass;
use calendar_event;
use mod_verbalfeedback\api;
use mod_verbalfeedback\model\instance;

/**
 * Class for performing calendar related actions for the verbal feedback activity module.
 *
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class calendar_utils {

    /**
     * This creates new calendar events given as timeopen and timeclose by $instance.
     *
     * @param instance $instance The verbal feedback instance.
     * @return void
     */
    public static function register_events(instance $instance) {
        global $CFG;

        require_once($CFG->dirroot.'/calendar/lib.php');

        $cm = get_coursemodule_from_instance('verbalfeedback', $instance->get_id(), $courseid = $instance->get_course(),
            $sectionnum = false, $strictness = MUST_EXIST);

        // Get CMID if not sent as part of $verbalfeedback.
        if (!isset($instance->coursemodule)) {
            $instance->coursemodule = $cm->id;
        }

        // Common event parameters.
        $instanceid = $instance->get_id();
        $courseid = $instance->get_course();
        $eventdescription = format_module_intro('verbalfeedback', $instance, $cm);
        $visible = instance_is_visible('verbalfeedback', $instance);

        // Calendar event for when the verbal feedback opens.
        $eventname = get_string('calendarstart', 'verbalfeedback', $instance->get_name());
        $eventtype = api::VERBALFEEDBACK_EVENT_TYPE_OPEN;
        // Calendar event type is set to action event when there's no timeclose.
        $calendareventtype = empty($instance->get_timeclose()) ? CALENDAR_EVENT_TYPE_ACTION : CALENDAR_EVENT_TYPE_STANDARD;
        self::set_event($instanceid, $eventname, $eventdescription, $eventtype, $calendareventtype, $instance->get_timeopen(),
          $visible, $courseid);

        // Calendar event for when the verbal feedback closes.
        $eventname = get_string('calendarend', 'verbalfeedback', $instance->get_name());
        $eventtype = api::VERBALFEEDBACK_EVENT_TYPE_CLOSE;
        $calendareventtype = CALENDAR_EVENT_TYPE_ACTION;
        self::set_event($instanceid, $eventname, $eventdescription, $eventtype, $calendareventtype,
            $verbalfeedback->get_timeclose(),  $visible, $courseid);
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
    protected static function set_event($id, $eventname, $description, $eventtype, $calendareventtype, $timestamp, $visible,
                                        $courseid) {
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
        $event->id = $DB->get_field('event', 'id', ['modulename' => 'verbalfeedback', 'instance' => $id,
            'eventtype' => $eventtype]);
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
}
