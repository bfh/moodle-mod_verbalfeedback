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

use coding_exception;
use context_module;
use dml_exception;
use Exception;
use moodle_exception;
use stdClass;
use core_date;
use mod_verbalfeedback\api\gradebook;
use mod_verbalfeedback\model\response;
use mod_verbalfeedback\model\submission;
use mod_verbalfeedback\model\submission_status;
use mod_verbalfeedback\repository\instance_repository;
use mod_verbalfeedback\repository\submission_repository;
use mod_verbalfeedback\repository\tables;
use mod_verbalfeedback\utils\user;
use mod_verbalfeedback\utils\instance;
use mod_verbalfeedback\utils\user_utils;


/**
 * Class for performing DB actions for the verbal feedback activity module.
 *
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api {

    /** The instance database table. */
    const DB_INSTANCE = "verbalfeedback";
    /** The response database table. */
    const DB_RESPONSE = "verbalfeedback_response";
    /** The submission database table. */
    const DB_SUBMISSION = "verbalfeedback_submission";

    /** Status when a user has not yet provided feedback to another user. */
    const STATUS_PENDING = 0;
    /** Status when a user has begun providing feedback to another user. */
    const STATUS_IN_PROGRESS = 1;
    /** Status when a user has completed providing feedback to another user. */
    const STATUS_COMPLETE = 2;
    /** Status when a user has declined to provide feedback to another user. */
    const STATUS_DECLINED = 3;

    /** Move a question item up. */
    const MOVE_UP = 1;
    /** Move a question item down. */
    const MOVE_DOWN = 2;

    /** Indicates all course participants regardless of role are the participants of the verbal feedback activity. */
    const PARTICIPANT_ROLE_ALL = 0;

    /** Do not allow participants to undo their declined feedback submissions. */
    const UNDO_DECLINE_DISALLOW = 0;
    /** Allow participants to undo their declined feedback submissions. */
    const UNDO_DECLINE_ALLOW = 1;

    /** Activity open event type. */
    const VERBALFEEDBACK_EVENT_TYPE_OPEN = 'open';
    /** Activity close event type. */
    const VERBALFEEDBACK_EVENT_TYPE_CLOSE = 'close';

    /**
     * Fetches the verbal feedback instance.
     *
     * @param int $verbalfeedbackid The verbal feedback ID.
     * @return mixed
     * @throws dml_exception
     */
    public static function get_instance($verbalfeedbackid) {
        global $DB;

        return $DB->get_record('verbalfeedback', ['id' => $verbalfeedbackid], '*', MUST_EXIST);
    }

    /**
     * Fetches the verbal feedback instance with given itemid.
     *
     * @param int $itemid The verbal feedback item ID.
     * @return mixed
     * @throws dml_exception
     */
    public static function get_instance_by_itemid($itemid) {
        global $DB;
        return $DB->get_record_sql("SELECT *
                                          FROM {" . tables::INSTANCE_TABLE .
                                      "} WHERE id = (SELECT instanceid
                                                       FROM {" . tables::INSTANCE_CATEGORY_TABLE .
                                                   "} WHERE id = (SELECT categoryid
                                                                            FROM {" . tables::INSTANCE_CRITERION_TABLE .
                                                                        "} WHERE id = ?))",
            array($itemid), IGNORE_MISSING);
    }

    /**
     * Fetches the verbal feedback instance with given categoryid.
     *
     * @param int $categoryid The verbal feedback category ID.
     * @return mixed
     * @throws dml_exception
     */
    public static function get_instance_by_categoryid($categoryid) {
        global $DB;
        return $DB->get_record_sql("SELECT *
                                          FROM {" . tables::INSTANCE_TABLE .
            "} WHERE id = (SELECT instanceid
                                                       FROM {" . tables::INSTANCE_CATEGORY_TABLE .
            "} WHERE id = ?)",
            array($categoryid), IGNORE_MISSING);
    }

    /**
     * Check whether only active users in course should be shown.
     *
     * @param context_module $context context the verbal feedback belongs to.
     * @return bool true if only active users should be shown.
     */
    public static function show_only_active_users(context_module $context = null) {
        global $CFG;

        $defaultgradeshowactiveenrol = !empty($CFG->grade_report_showonlyactiveenrol);
        $showonlyactiveenrol = get_user_preferences('grade_report_showonlyactiveenrol', $defaultgradeshowactiveenrol);

        if (!is_null($context)) {
            $showonlyactiveenrol = $showonlyactiveenrol || !has_capability('moodle/course:viewsuspendedusers', $context);
        }
        return $showonlyactiveenrol;
    }

    /**
     * Function that retrieves the participants for the verbal feedback activity.
     *
     * @param int $verbalfeedbackid The verbal feedback instance ID.
     * @param int $currentuserid The current user ID.
     * @param bool $includeself Whether to include the respondent in the list.
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function get_participants($verbalfeedbackid, $currentuserid, $includeself = false) {
        global $DB;

        $cm = get_coursemodule_from_instance('verbalfeedback', $verbalfeedbackid);
        $context = context_module::instance($cm->id);

        $submissioncandidates =
            get_enrolled_users($context, $withcapability = 'mod/verbalfeedback:receive_rating', $groupid = 0,
                $userfields = 'u.id AS userid,
                u.firstname,
                u.lastname,
                u.firstnamephonetic,
                u.lastnamephonetic,
                u.middlename,
                u.alternatename', $orderby = 'u.lastname, u.firstname', $limitfrom = 0, $limitnum = 0);

        $userssql = "SELECT DISTINCT s.touserid AS touserid, s.id AS submissionid, s.status AS submissionstatus
                     FROM {verbalfeedback_submission} s
                     WHERE s.instanceid = :instanceid AND s.fromuserid = :currentuserid";

        $statusrecords = $DB->get_records_sql($userssql, ['instanceid' => $verbalfeedbackid, 'currentuserid' => $currentuserid]);

        // Combine sql results and drop current user ($includeself) if necessary.
        $filtermap = function($v) use ($currentuserid, $includeself, $statusrecords) {
            if ($v->userid == $currentuserid && !$includeself) {
                return false;
            }
            if (isset($statusrecords[$v->userid]->submissionid)) {
                $v->submissionid = $statusrecords[$v->userid]->submissionid;
            }
            if (isset($statusrecords[$v->userid]->submissionstatus)) {
                $v->submissionstatus = $statusrecords[$v->userid]->submissionstatus;
            }
            return true;
        };

        $submissioncandidates = array_filter($submissioncandidates, $filtermap);

        return $submissioncandidates;
    }

    /**
     * Generate default records for the table verbalfeedback_submission.
     *
     * @param int $verbalfeedbackid The verbal feedback instance ID.
     * @param int $fromuserid The user ID of the respondent.
     * @param bool $includeself Whether to include self.
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function generate_verbalfeedback_feedback_states($verbalfeedbackid, $fromuserid, $includeself = false) {
        global $DB;
        $submissionrepo = new submission_repository();

        $verbalfeedback = $DB->get_record(self::DB_INSTANCE, ['id' => $verbalfeedbackid], '*', MUST_EXIST);
        $wheres = [
            'u.id NOT IN (
                SELECT fs.touserid
                  FROM {verbalfeedback_submission} fs
                 WHERE fs.instanceid = f.id
                       AND fs.fromuserid = :fromuser2
            )'
        ];
        $params = [
            'verbalfeedbackid' => $verbalfeedbackid,
            'fromuser2' => $fromuserid
        ];

        if (!$includeself) {
            $wheres[] = 'u.id <> :fromuser';
            $params['fromuser'] = $fromuserid;
        }

        list($course, $cm) = get_course_and_cm_from_instance($verbalfeedbackid, 'verbalfeedback', $verbalfeedback->course,
            $fromuserid);

        $groupmode = groups_get_activity_groupmode($cm);
        if ($groupmode != NOGROUPS) {
            $currentgroup = groups_get_activity_group($cm, true);
            $context = $cm->context;
            $userids = get_enrolled_users($context, '', $currentgroup, 'u.id', null, 0, 0, self::show_only_active_users($context));

            if ($userids) {
                $userids = array_map(
                    function($user) {
                        return $user->id;
                    },
                    $userids
                );
                list($sql, $inparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
                $params = array_merge($params, $inparams);
                $wheres[] = "u.id $sql";
            }
        }

        $whereclause = implode(' AND ', $wheres);
        $usersql = "SELECT DISTINCT u.id
                               FROM {user} u
                         INNER JOIN {user_enrolments} ue
                                 ON u.id = ue.userid
                         INNER JOIN {enrol} e
                                 ON e.id = ue.enrolid
                         INNER JOIN {verbalfeedback} f
                                 ON f.course = e.courseid AND f.id = :verbalfeedbackid
                              WHERE {$whereclause}";

        if ($users = $DB->get_records_sql($usersql, $params)) {
            foreach ($users as $user) {

                $submission = new submission();
                $submission->set_instance_id($verbalfeedbackid);
                $submission->set_from_user_id($fromuserid);
                $submission->set_to_user_id($user->id);
                $submissionrepo->save($submission);
            }
        }
    }

    /**
     * Get scales for rated questions.
     *
     * @return array
     * @throws coding_exception
     */
    public static function get_scales() {

        $s0 = new stdClass();
        $s0->scale = null;
        $s0->scalelabel = get_string('notapplicableabbr', 'verbalfeedback');
        $s0->description = get_string('scalenotapplicable', 'mod_verbalfeedback');

        $s1 = new stdClass();
        $s1->scale = 0;
        $s1->scalelabel = '0';
        $s1->description = get_string('scalestronglydisagree', 'mod_verbalfeedback');

        $s2 = new stdClass();
        $s2->scale = 1;
        $s2->scalelabel = '1';
        $s2->description = get_string('scaledisagree', 'mod_verbalfeedback');

        $s3 = new stdClass();
        $s3->scale = 2;
        $s3->scalelabel = '2';
        $s3->description = get_string('scalesomewhatdisagree', 'mod_verbalfeedback');

        $s4 = new stdClass();
        $s4->scale = 3;
        $s4->scalelabel = '3';
        $s4->description = get_string('scalesomewhatagree', 'mod_verbalfeedback');

        $s5 = new stdClass();
        $s5->scale = 4;
        $s5->scalelabel = '4';
        $s5->description = get_string('scaleagree', 'mod_verbalfeedback');

        $s6 = new stdClass();
        $s6->scale = 5;
        $s6->scalelabel = '5';
        $s6->description = get_string('scalestronglyagree', 'mod_verbalfeedback');

        return [$s1, $s2, $s3, $s4, $s5, $s6, $s0];
    }

    /**
     * Save a user's responses to the verbal feedback questions for another user.
     *
     * @param int $instanceid The verbal feedback instance ID.
     * @param int $submissionid The submission ID.
     * @param int $touser The recipient of the feedback responses.
     * @param array $responses The responses data.
     * @return bool|int
     * @throws dml_exception
     */
    public static function save_responses($instanceid, $submissionid, $touser, $responses) {
        global $USER;
        $submissionrepo = new submission_repository();
        $fromuser = $USER->id;

        $inprogress = false;

        $submission = $submissionrepo->get_by_id($submissionid);
        foreach ($responses as $resp) {
            $response = new response();
            foreach ($submission->get_responses() as $savedresponse) {
                if ($savedresponse->get_criterion_id() == $resp['criterionid']) {
                    $response = &$savedresponse;
                    break;
                }
            }

            if ($response->get_id() == 0) {
                $response->set_instance_id($instanceid);
                $response->set_criterion_id($resp['criterionid']);
                $response->set_to_user_id($touser);
                $response->set_from_user_id($fromuser);
                $response->set_value($resp['value']);

                // Setting an empty string as default value in external.php does not work.
                if (isset($resp['studentcomment'])) {
                    $response->set_student_comment($resp['studentcomment']);
                }
                if (isset($resp['privatecomment'])) {
                    $response->set_private_comment($resp['privatecomment']);
                }

                $submission->add_response($response);

                if ($response->get_value() != null || $response->get_student_comment() != ""
                || $response->get_private_comment() != "") {
                    $inprogress = true;
                }

            } else {
                // There is already a response object on the submission.
                // $response is here a pointer to this object. Therefore, we dont need to add a response to submission.
                $response->set_value($resp['value']);
                // Setting an empty string as default value in external.php does not work.
                if (isset($resp['studentcomment'])) {
                    $response->set_student_comment($resp['studentcomment']);
                }
                if (isset($resp['privatecomment'])) {
                    $response->set_private_comment($resp['privatecomment']);
                }
                if ($response->get_value() != null || $response->get_student_comment() != "" ||
                $response->get_private_comment() != "") {
                    $inprogress = true;
                }
            }
        }

        if ($inprogress) {
            $submission->set_status(submission_status::IN_PROGRESS);
        }
        try {
            $submissionrepo->save($submission);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Updates the multiplier of a given item.
     *
     * @param int $itemid The item ID.
     * @param float $multiplier The new value for the multiplier.
     * @return bool
     */
    public static function update_item_multiplier($itemid, $multiplier) {
        $instancerepository = new instance_repository();
        return $instancerepository->update_criterion_weight($itemid, $multiplier);
    }

    /**
     * Updates the percentage value of a category.
     *
     * @param int $categoryid The category id
     * @param float $percentage The new value for the multiplier.
     * @return bool
     */
    public static function update_category_percentage($categoryid, $percentage) {
        $instancerepository = new instance_repository();
        return $instancerepository->update_category_weight($categoryid, $percentage);
    }

    /**
     * Counts the number of users awaiting feedback from the given user ID.
     *
     * @param int $verbalfeedbackid The verbal feedback instance ID.
     * @param int $user The user ID.
     * @return int
     */
    public static function count_users_awaiting_feedback($verbalfeedbackid, $user) {
        global $DB;

        $verbalfeedback = self::get_instance($verbalfeedbackid);

        // Check first if the user can write feedback to other participants.
        if (user_utils::can_respond($verbalfeedback, $user) === true) {
            if (!$DB->record_exists(self::DB_SUBMISSION, ['instance' => $verbalfeedback->id, 'fromuser' => $user])) {
                // Generate submission records if there are no submission records yet.
                self::generate_verbalfeedback_feedback_states($verbalfeedback->id, $user, $verbalfeedback->with_self_review);
            }

            // Count participants awaiting feedback from this user.
            list($insql, $params) = $DB->get_in_or_equal([self::STATUS_PENDING, self::STATUS_IN_PROGRESS], SQL_PARAMS_NAMED);
            $select = "instance = :verbalfeedback AND fromuser = :fromuser AND status $insql";
            $params['verbalfeedback'] = $verbalfeedbackid;
            $params['fromuser'] = $user;
            return $DB->count_records_select(self::DB_SUBMISSION, $select, $params);
        }

        return 0;
    }
}
