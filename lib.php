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
 * Library of interface functions and constants.
 *
 * @package     mod_verbalfeedback
 * @copyright   2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\event\course_module_updated;
use core_calendar\action_factory;
use core_calendar\local\event\entities\action_interface;
use mod_verbalfeedback\api;
use mod_verbalfeedback\model\instance;
use mod_verbalfeedback\repository\instance_repository;
use mod_verbalfeedback\repository\submission_repository;
use mod_verbalfeedback\repository\tables;
use mod_verbalfeedback\repository\template_repository;

defined('MOODLE_INTERNAL') || die();

global $CFG;

// Include grade lib.
require_once($CFG->libdir . '/gradelib.php');
// Include forms lib.
require_once($CFG->libdir . '/formslib.php');

/**
 * A helper model to match a model to its verbal feedback instance.
 *
 * @param stdClass $object
 * @return instance The verbal feedback instance
 */
function mod_verbalfeedback_view_model_to_instance(stdClass $object): instance {
    $templaterepository = new template_repository();

    if (!isset($object->course) || !is_numeric($object->course)) {
        throw new InvalidArgumentException("stdClass object must have a course property of type int.");
    }

    if (empty($object->template)) {
        // If we enter here, it's mostly $object->template == ''.
        $instance = new instance((int) $object->course);
    } else {
        $template = $templaterepository->get_by_id((int)$object->template);
        $instance = instance::from_template((int) $object->course, $template);
    }

    if (isset($object->id)) {
        $instance->set_id($object->id);
    }
    if (isset($object->name)) {
        $instance->set_name($object->name);
    }
    if (isset($object->intro)) {
        $instance->set_intro($object->intro);
    }
    if (isset($object->introformat)) {
        $instance->set_introformat((int) $object->introformat);
    }
    if (isset($object->grade)) {
        $instance->set_grade((int) $object->grade);
    }
    if (isset($object->gradecat)) {
        $instance->set_gradecat((int) $object->gradecat);
    }
    if (isset($object->gradepass)) {
        $instance->set_gradepass($object->gradepass);
    }
    if (isset($object->gradescale)) {
        $instance->set_gradescale((int)$object->gradescale);
    }
    if (isset($object->status)) {
        $instance->set_status((int) $object->status);
    }
    if (isset($object->timeopen)) {
        $instance->set_timeopen((int) $object->timeopen);
    }
    if (isset($object->timeclose)) {
        $instance->set_timeclose((int) $object->timeclose);
    }
    if (isset($object->timemodified)) {
        $instance->set_timemodified((int) $object->timemodified);
    }
    if (isset($object->releasetype)) {
        $instance->set_release_type((int) $object->releasetype);
    }
    if (isset($object->released)) {
        $instance->set_released((int) $object->released);
    }

    return $instance;
}

/**
 * Adds a new verbal feedback instance.
 *
 * @param stdClass $modformviewmodel A verbal feedback class
 * @return bool|int The ID of the created verbal feedback or false if the insert failed.
 * @throws coding_exception
 * @throws dml_exception
 */
function verbalfeedback_add_instance(stdClass $modformviewmodel) {
    $instancerepository = new instance_repository();

    $instance = mod_verbalfeedback_view_model_to_instance($modformviewmodel);

    $instanceid = $instancerepository->save($instance);

    // Update grade item definition.
    verbalfeedback_grade_item_update($instance);

    return $instanceid;
}

/**
 * Updates the given verbalfeedback.
 *
 * @param stdClass $verbalfeedback
 * @return bool
 * @throws dml_exception
 */
function verbalfeedback_update_instance($verbalfeedback) {
    global $DB;

    $verbalfeedback->timemodified = time();
    $verbalfeedback->id = $verbalfeedback->instance;

    // Update grade item definition.
    verbalfeedback_grade_item_update($verbalfeedback);

    // Update grades.
    verbalfeedback_update_grades($verbalfeedback, 0, false);

    // Save the feedback into the db.
    return $DB->update_record("verbalfeedback", $verbalfeedback);
}

/**
 * Deletes the verbal feedback.
 *
 * @param int $id The ID of the verbal feedback to be deleted.
 * @return bool
 * @throws dml_exception
 */
function verbalfeedback_delete_instance($id) {
    global $DB;

    $submissionrepository = new submission_repository();
    $submissionrepository->delete_by_instance($id);

    // Delete events.
    $DB->delete_records('event', ['modulename' => 'verbalfeedback', 'instance' => $id]);

    $instancerepository = new instance_repository();
    return $instancerepository->delete($id);
}

/**
 * Features supported by this plugin.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, null if doesn't know
 */
function verbalfeedback_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
        case FEATURE_SHOW_DESCRIPTION:
        case FEATURE_COMPLETION_TRACKS_VIEWS:
        case FEATURE_GRADE_HAS_GRADE:
        case FEATURE_BACKUP_MOODLE2:
        case FEATURE_GROUPINGS:
        case FEATURE_GROUPS:
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_MOD_PURPOSE:
            return MOD_PURPOSE_ASSESSMENT;
        default:
            return null;
    }
}

/**
 * This function receives a calendar event and returns the action associated with it, or null if there is none.
 *
 * This is used by block_timeline in order to display the event appropriately. If null is returned then the event
 * is not displayed on the block.
 *
 * @param calendar_event $event
 * @param action_factory $factory
 * @param int $userid User id to use for all capability checks, etc. Set to 0 for current user (default).
 * @return action_interface|null
 */
function verbalfeedback_core_calendar_provide_event_action(calendar_event $event, action_factory $factory, $userid = 0) {
    global $USER;

    if (!$userid) {
        $userid = $USER->id;
    }

    $cm = get_fast_modinfo($event->courseid, $userid)->instances['verbalfeedback'][$event->instance];

    if (!$cm->uservisible) {
        // The module is not visible to the user for any reason.
        return null;
    }

    $now = time();

    if (!empty($cm->customdata['timeclose']) && $cm->customdata['timeclose'] < $now) {
        // The verbal feedback has closed so the user can no longer submit anything.
        return null;
    }

    // The verbal feedback is actionable if we don't have a start time or the start time is in the past, if the instance is ready,
    // and the user can provide feedback to other users.
    $actionable = (empty($cm->customdata['timeopen']) || $cm->customdata['timeopen'] <= $now) && api::is_ready($event->instance);
    $pendingcount = 0;
    if ($actionable) {
        $pendingcount = api::count_users_awaiting_feedback($event->instance, $userid);
        if (empty($pendingcount)) {
            // There is no action if the instance is not yet ready, or the user can't provide feedback to the participants, or the
            // user has already finished providing feedback to all of the participants..
            return null;
        }
    }

    return $factory->create_instance(
        get_string('providefeedback', 'verbalfeedback'),
        new moodle_url('/mod/verbalfeedback/view.php', ['id' => $cm->id]),
        $pendingcount,
        $actionable
    );
}

/**
 * This function calculates the minimum and maximum cutoff values for the timestart of
 * the given event.
 *
 * It will return an array with two values, the first being the minimum cutoff value and
 * the second being the maximum cutoff value. Either or both values can be null, which
 * indicates there is no minimum or maximum, respectively.
 *
 * If a cutoff is required then the function must return an array containing the cutoff
 * timestamp and error string to display to the user if the cutoff value is violated.
 *
 * A minimum and maximum cutoff return value will look like:
 * [
 *     [1505704373, 'The date must be after this date'],
 *     [1506741172, 'The date must be before this date']
 * ]
 *
 * @param calendar_event $event The calendar event to get the time range for
 * @param stdClass $verbalfeedback The module instance to get the range from
 * @return array
 */
function verbalfeedback_core_calendar_get_valid_event_timestart_range(calendar_event $event, stdClass $verbalfeedback) {
    $mindate = null;
    $maxdate = null;

    if ($event->eventtype == api::VERBALFEEDBACK_EVENT_TYPE_OPEN) {
        if (!empty($verbalfeedback->timeclose)) {
            $maxdate = [
                $verbalfeedback->timeclose,
                get_string('openafterclose', 'verbalfeedback'),
            ];
        }
    } else if ($event->eventtype == api::VERBALFEEDBACK_EVENT_TYPE_CLOSE) {
        if (!empty($verbalfeedback->timeopen)) {
            $mindate = [
                $verbalfeedback->timeopen,
                get_string('closebeforeopen', 'verbalfeedback'),
            ];
        }
    }

    return [$mindate, $maxdate];
}

/**
 * This function will update the verbal feedback module according to the
 * event that has been modified.
 *
 * It will set the timeopen or timeclose value of the verbalfeedback instance
 * according to the type of event provided.
 *
 * @param calendar_event $event
 * @param stdClass $verbalfeedback The module instance to get the range from
 */
function verbalfeedback_core_calendar_event_timestart_updated(calendar_event $event, stdClass $verbalfeedback) {
    global $DB;

    if (!in_array($event->eventtype, [api::VERBALFEEDBACK_EVENT_TYPE_OPEN, api::VERBALFEEDBACK_EVENT_TYPE_CLOSE])) {
        return;
    }

    $courseid = $event->courseid;
    $modulename = $event->modulename;
    $instanceid = $event->instance;
    $modified = false;

    // Something weird going on. The event is for a different module so we should ignore it.
    if ($modulename != 'verbalfeedback') {
        return;
    }

    if ($verbalfeedback->id != $instanceid) {
        return;
    }

    $coursemodule = get_fast_modinfo($courseid)->instances[$modulename][$instanceid];
    $context = context_module::instance($coursemodule->id);

    // The user does not have the capability to modify this activity.
    if (!has_capability('moodle/course:manageactivities', $context)) {
        return;
    }

    if ($event->eventtype == api::VERBALFEEDBACK_EVENT_TYPE_OPEN) {
        // If the event is for the verbalfeedback activity opening then we should set the start time of the verbalfeedback activity
        // to be the new start time of the event.
        if ($verbalfeedback->timeopen != $event->timestart) {
            $verbalfeedback->timeopen = $event->timestart;
            $modified = true;
        }
    } else if ($event->eventtype == api::VERBALFEEDBACK_EVENT_TYPE_CLOSE) {
        // If the event is for the verbalfeedback activity closing then we should set the end time of the verbalfeedback activity
        // to be the new start time of the event.
        if ($verbalfeedback->timeclose != $event->timestart) {
            $verbalfeedback->timeclose = $event->timestart;
            $modified = true;
        }
    }

    if ($modified) {
        $verbalfeedback->timemodified = time();
        // Persist the instance changes.
        $DB->update_record('verbalfeedback', $verbalfeedback);
        $event = course_module_updated::create_from_cm($coursemodule, $context);
        $event->trigger();
    }
}

/**
 * Callback function that determines whether an action event should be showing its item count
 * based on the event type and the item count.
 *
 * @param calendar_event $event The calendar event.
 * @param int $itemcount The item count associated with the action event.
 * @return bool
 */
function verbalfeedback_core_calendar_event_action_shows_item_count(calendar_event $event, $itemcount = 0) {
    // Make sure that this event is for the verbalfeedback module (shouldn't happen though).
    if ($event->modulename !== 'verbalfeedback') {
        return false;
    }

    // Item count should be shown if there is one or more item count.
    return $itemcount > 0;
}

// Gradebook callbacks.

/**
 * In case of a course reset, find all verbal feedback instances then calls verbalfeedback_grade_item_update()
 * with the special 'reset' parameter.
 *
 * @param int $courseid course id
 * @param string $type optional type (default '')
 * @throws dml_exception
 */
function verbalfeedback_reset_gradebook($courseid, $type = '') {
    global $DB;

    $sql = "SELECT v.*, cm.idnumber as cmidnumber, v.course as courseid
              FROM {verbalfeedback} v, {course_modules} cm, {modules} m
             WHERE m.name='verbalfeedback' AND m.id=cm.module AND cm.instance=v.id AND v.course=?";

    if ($verbalfeedbacks = $DB->get_records_sql($sql, [$courseid])) {
        foreach ($verbalfeedbacks as $verbalfeedback) {
            verbalfeedback_grade_item_update($verbalfeedback, 'reset');
        }
    }
}

/**
 * Actual implementation of the reset course functionality, delete all the
 * verbalfeedback grades and comments for course $data->courseid.
 *
 * @param object $data The data submitted from the reset course.
 * @return array The status array
 */
function verbalfeedback_reset_userdata($data) {
    global $CFG, $DB;

    $status = [];

    if (!empty($data->reset_verbalfeedback)) {
        $verbalfeedbackssql = "SELECT vf.id " .
                       "FROM " . tables::INSTANCE_TABLE . " vf " .
                       "WHERE vf.course=?";

        $DB->delete_records_select(tables::RESPONSE_TABLE, "instanceid IN ($verbalfeedbackssql)", [$data->courseid]);
        $status[] = ['component' => $componentstr, 'item' => get_string('removeresponses', 'verbalfeedback'),
            'error' => false, ];

        $DB->delete_records_select(tables::SUBMISSION_TABLE, "instanceid IN ($verbalfeedbackssql)", [$data->courseid]);
        $status[] = ['component' => $componentstr, 'item' => get_string('removesubmissions', 'verbalfeedback'),
            'error' => false, ];

        $DB->delete_records_select(tables::INSTANCE_CATEGORY_TABLE, "instanceid IN ($verbalfeedbackssql)", [$data->courseid]);
        $status[] = ['component' => $componentstr, 'item' => get_string('removecategories', 'verbalfeedback'),
            'error' => false, ];

        $allverbalfeedbackcriteriasql       = "SELECT vfcri.id " .
                                                "FROM " . tables::INSTANCE_CRITERION_TABLE . " vfcri " .
                                                "JOIN " . tables::INSTANCE_CATEGORY_TABLE .
                                                      " vfcat ON vfcri.categoryid = vfcat.id " .
                                                "JOIN " . tables::INSTANCE_TABLE . " vf ON vfcat.instanceid = vf.id" .
                                               "WHERE vf.course = ?";
        $DB->delete_records_select(
            tables::INSTANCE_CRITERION_TABLE,
            "instanceid IN ($allverbalfeedbackcriteriasql)",
            [$data->courseid]
        );
        $status[] = [
            'component' => $componentstr,
            'item' => get_string('removecriteria', 'verbalfeedback'),
            'error' => false,
        ];

        $allverbalfeedbacksubratingssql = "SELECT vfsub.id " .
            "FROM " . tables::INSTANCE_SUBRATING_TABLE . " vfsub " .
            "JOIN " . tables::INSTANCE_CRITERION_TABLE .
            " vfcri ON vfcri.id = vfsub.criterionid " .
            "JOIN " . tables::INSTANCE_CATEGORY_TABLE .
            " vfcat ON vfcri.categoryid = vfcat.id " .
            "JOIN " . tables::INSTANCE_TABLE . " vf ON vfcat.instanceid = vf.id" .
            "WHERE vf.course = ?";
        $DB->delete_records_select(
            tables::INSTANCE_SUBRATING_TABLE,
            "instanceid IN ($allverbalfeedbacksubratingssql)",
            [$data->courseid]
        );
        $status[] = [
            'component' => $componentstr,
            'item' => get_string('removesubratings', 'verbalfeedback'),
            'error' => false,
        ];
    }

    return $status;
}

/**
 * Creates or updates grade item for given verbalfeedback instance
 *
 * Needed by {@see grade_update_mod_grades()}.
 *
 * @category grade
 * @uses GRADE_TYPE_VALUE
 * @uses GRADE_TYPE_NONE
 * @param object $verbalfeedback object with extra cmidnumber
 * @param mixed $grades optional array/object of grades; 'reset' means reset ratings in gradebook
 * @return int 0 if ok, error code otherwise
 */
function verbalfeedback_grade_item_update($verbalfeedback, $grades = null) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');
    // Update the grade.
    $params = [
        'itemname' => $verbalfeedback->name,
        'idnumber' => $verbalfeedback->id,
    ];

    if ($verbalfeedback->grade > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax'] = $verbalfeedback->grade;
        $params['grademin'] = 0;
        $params['gradepass'] = $verbalfeedback->gradepass;
    } else if ($verbalfeedback->grade < 0) {
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid'] = -$verbalfeedback->grade;
        $params['gradepass'] = $verbalfeedback->gradepass;
    } else {
        $params['gradetype'] = GRADE_TYPE_TEXT;
    }

    if ($grades === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }

    // Itemnumber 0 is the grade.
    return grade_update(
        'mod/verbalfeedback',
        $verbalfeedback->course,
        'mod',
        'verbalfeedback',
        $verbalfeedback->id,
        0,
        $grades,
        $params
    );
}

/**
 * Update verbal feedback grades in the gradebook
 *
 * Needed by {@see grade_update_mod_grades()}.
 *
 * @param object $verbalfeedback The verbalfeedback instance object.
 * @param int $userid specific user only, 0 means all users.
 * @param bool $nullifnone If a single user is specified and $nullifnone is true a grade item with a null rawgrade will be inserted
 */
function verbalfeedback_update_grades($verbalfeedback, $userid = 0, $nullifnone = true) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    // Populate array of grade objects indexed by userid.
    $grades = verbalfeedback_get_user_grades($verbalfeedback, $userid);

    if ($grades) {
        verbalfeedback_grade_item_update($verbalfeedback, $grades);
    } else if ($userid && $nullifnone) {
        $grade = new stdClass();
        $grade->userid = $userid;
        $grade->rawgrade = null;
        verbalfeedback_grade_item_update($verbalfeedback, $grade);
    } else {
        verbalfeedback_grade_item_update($verbalfeedback);
    }
}

/**
 * Return grade for given user or all users.
 *
 * @param object $verbalfeedback object with extra cmidnumber
 * @param int $userid optional user id, 0 means all users
 * @return array array of grades, false if none. These are raw grades. They should
 * be processed with verbalfeedback_format_grade for display.
 * @throws dml_exception
 */
function verbalfeedback_get_user_grades($verbalfeedback, $userid = 0) {
    global $DB;

    $params = [$verbalfeedback->id, $verbalfeedback->id, $verbalfeedback->id, $verbalfeedback->id];
    $usertest = '';
    if ($userid > 0) {
        $params = [$verbalfeedback->id, $verbalfeedback->id, $userid, $verbalfeedback->id, $verbalfeedback->id];
        $usertest = 'AND vfs.touserid = ?';
    }
    return $DB->get_records_sql(
        "SELECT subquery1.userid id,
            subquery1.userid userid,
            SUM(subquery1.grade) rawgrade
        FROM
        (
            SELECT subquery.userid id,
                subquery.userid,
                SUM(subquery.grade),
                subquery.catid,
                subquery.catweight,
                SUM(subquery.grade) * subquery.catweight catsum,
                (SUM(subquery.grade) * subquery.catweight) / 5 * vf.grade grade
            FROM
            (
                SELECT u.id id,
                    u.id userid,
                    vfcri.id criid,
                    vfcat.id catid,
                    vfcat.weight catweight,
                    vfcri.weight criweight,
                    ctw.criteriatotalweight,
                    vfcri.weight / ctw.criteriatotalweight criteriaeffweight,
                    --SUM(vfres.value),
                    (SUM(vfres.value)/ COUNT(vfres.fromuserid)) * (vfcri.weight / ctw.criteriatotalweight) grade
                FROM
                (
                    SELECT vfcat.id,
                        SUM(vfcri.weight) criteriatotalweight
                    FROM {verbalfeedback_i_criterion} vfcri
                        JOIN {verbalfeedback_i_category} vfcat
                            ON vfcat.id = vfcri.categoryid
                        JOIN {verbalfeedback} vf
                            ON vfcat.instanceid = vf.id
                    WHERE vf.id = ?
                    GROUP BY vfcat.id
                ) AS ctw
                    JOIN {verbalfeedback_i_category} vfcat
                        ON vfcat.id = ctw.id
                    JOIN {verbalfeedback_i_criterion} vfcri
                        ON vfcri.categoryid = vfcat.id
                    JOIN {verbalfeedback} vf
                        ON vf.id = vfcat.instanceid
                    JOIN {verbalfeedback_submission} vfs
                        ON vfs.instanceid = vf.id
                    JOIN {user} u
                        ON u.id = vfs.touserid
                    JOIN {verbalfeedback_response} vfres
                        ON vfres.criterionid = vfcri.id
                        AND vfres.instanceid = vfs.instanceid
                        AND vfres.fromuserid = vfs.fromuserid
                        AND vfres.touserid = vfs.touserid
                WHERE vfcat.instanceid = ? $usertest
                  AND vfs.status <> 0 -- pending status excluded, not sure if needed
                  AND vfres.value is not null -- N/A values should not be taken into account
                GROUP BY u.id,
                        vfcat.id,
                        vfcri.id,
                        vfcat.weight,
                        vfcri.weight,
                        ctw.criteriatotalweight
            ) subquery
                JOIN {verbalfeedback} vf
                    ON vf.id = ?
            GROUP BY subquery.id,
                    subquery.userid,
                    subquery.catid,
                    vf.grade,
                    subquery.catweight
        ) AS subquery1
            JOIN {verbalfeedback} vf
                ON vf.id = ?
        GROUP BY subquery1.userid,
                subquery1.userid
        ",
        $params
    );
}

/**
 * Adds verbal feedback specific settings to the settings block
 *
 * @param settings_navigation $settings The settings navigation object
 * @param navigation_node $navigationnode The node to add module settings to
 */
function verbalfeedback_extend_settings_navigation(settings_navigation $settings, navigation_node $navigationnode) {
    global $PAGE;

    if (!$context = context_module::instance($PAGE->cm->id, IGNORE_MISSING)) {
        throw new moodle_exception('badcontext');
    }

    if (has_capability('mod/verbalfeedback:managetemplates', $context)) {
        $navigationnode->add(
            get_string('verbalfeedbacktemplates', 'verbalfeedback'),
            new moodle_url('/mod/verbalfeedback/template_list.php')
        );
    }
}
