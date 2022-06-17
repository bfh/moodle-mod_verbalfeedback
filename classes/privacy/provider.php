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
 * Privacy Subsystem implementation for mod_verbalfeedback.
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_verbalfeedback\privacy;

use context_module;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use mod_verbalfeedback\api;
use mod_verbalfeedback\helper;

/**
 * Implementation of the privacy subsystem plugin provider for the 36o-degree feedback activity module.
 *
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
        // This plugin stores personal data.
        \core_privacy\local\metadata\provider,

        // This plugin is a core_user_data_provider.
        \core_privacy\local\request\plugin\provider,

        // This plugin is capable of determining which users have data within it.
        \core_privacy\local\request\core_userlist_provider {
    /**
     * Return the fields which contain personal data.
     *
     * @param collection $items a reference to the collection to use to store the metadata.
     * @return collection the updated collection of metadata items.
     */
    public static function get_metadata(collection $items) : collection {
        $items->add_database_table(
            'verbalfeedback_submission',
            [
                'verbalfeedback' => 'privacy:metadata:verbalfeedback',
                'fromuser' => 'privacy:metadata:verbalfeedback_submission:fromuser',
                'touser' => 'privacy:metadata:verbalfeedback_submission:touser',
                'status' => 'privacy:metadata:verbalfeedback_submission:status',
                'remarks' => 'privacy:metadata:verbalfeedback_submission:remarks',
            ],
            'privacy:metadata:verbalfeedback_submission'
        );
        $items->add_database_table(
            'verbalfeedback_response',
            [
                'verbalfeedback' => 'privacy:metadata:verbalfeedback',
                'item' => 'privacy:metadata:verbalfeedback_item',
                'fromuser' => 'privacy:metadata:verbalfeedback_submission:fromuser',
                'touser' => 'privacy:metadata:verbalfeedback_submission:touser',
                'value' => 'privacy:metadata:verbalfeedback_response:value',
            ],
            'privacy:metadata:verbalfeedback_response'
        );

        return $items;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid the userid.
     * @return contextlist the list of contexts containing user info for the user.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        // Fetch all verbalfeedback activity contexts where the user is participating.
        $sql = "SELECT ctx.id
                  FROM {context} ctx
            INNER JOIN {course_modules} cm
                    ON cm.id = ctx.instanceid AND ctx.contextlevel = :contextlevel
            INNER JOIN {modules} m
                    ON m.id = cm.module AND m.name = :modname
            INNER JOIN {verbalfeedback} t
                    ON t.id = cm.instance
            INNER JOIN {verbalfeedback_submission} ts
                    ON ts.verbalfeedback = t.id
                 WHERE ts.fromuser = :fromuser OR ts.touser = :touser";

        $params = [
            'modname'       => 'verbalfeedback',
            'contextlevel'  => CONTEXT_MODULE,
            'fromuser'      => $userid,
            'touser'        => $userid,
        ];
        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Export personal data for the given approved_contextlist.
     * User and context information is contained within the contextlist.
     *
     * @param approved_contextlist $contextlist a list of contexts approved for export.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        $contextids = $contextlist->get_contextids();

        // Export the user's feedback submissions for other users.
        self::export_submission_data($contextids, $userid);
        // Export the feedback submissions for the user from other users.
        self::export_submission_data($contextids, $userid, false);

        // Export the user's responses to the feedback questions for other users.
        self::export_responses_data($contextids, $userid);
        // Export the responses received by the user from other users.
        self::export_responses_data($contextids, $userid, false);
    }

    /**
     * Export the submission data related to the user.
     *
     * @param int[] $contextids The list of context IDs.
     * @param int $user The user's ID.
     * @param bool $respondent Whether we're exporting the data where the use is the respondent (true)
     *                         or the recipient (false) of the feedback.
     */
    protected static function export_submission_data($contextids, $user, $respondent = true) {
        global $DB;

        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextids, SQL_PARAMS_NAMED);
        $sql = "
                SELECT ts.id,
                       cm.id as cmid,
                       t.id as verbalfeedback,
                       t.name as verbalfeedbackname,
                       ts.status,
                       ts.remarks,
                       ts.fromuser,
                       ts.touser
                  FROM {context} ctx
                  JOIN {course_modules} cm
                    ON cm.id = ctx.instanceid AND ctx.contextlevel = :contextlevel
                  JOIN {modules} m
                    ON m.id = cm.module AND m.name = :modname
                  JOIN {verbalfeedback} t
                    ON t.id = cm.instance
                  JOIN {verbalfeedback_submission} ts
                    ON ts.verbalfeedback = t.id
                 WHERE ctx.id {$contextsql} %s
              ORDER BY cmid ASC";

        if ($respondent) {
            $sqluser = 'fromuser';
            $userkey = 'recipient';
            $parent = get_string('feedbackgiven', 'mod_verbalfeedback');
        } else {
            $sqluser = 'touser';
            $userkey = 'respondent';
            $parent = get_string('feedbackreceived', 'mod_verbalfeedback');
        }

        $submissionssql = sprintf($sql, "AND ts.{$sqluser} = :userid");
        $params = ['modname' => 'verbalfeedback', 'contextlevel' => CONTEXT_MODULE, 'userid' => $user] + $contextparams;
        $submissions = $DB->get_recordset_sql($submissionssql, $params);
        $submissionsdata = [];
        foreach ($submissions as $submission) {
            $context = context_module::instance($submission->cmid);
            $options = ['context' => $context];
            if (!isset($submissionsdata[$submission->cmid])) {
                $submissionsdata[$submission->cmid] = [
                    'name' => $submission->verbalfeedbackname
                ];
            }
            if ($respondent) {
                $relateduser = transform::user($submission->touser);
            } else {
                $relateduser = transform::user($submission->fromuser);
            }
            $submissionsdata[$submission->cmid]['submissions'][$submission->id] = [
                $userkey => $relateduser,
                'status' => helper::get_status_string($submission->status),
                'remarks' => format_string($submission->remarks, true, $options),
            ];
        }
        $submissions->close();

        foreach ($submissionsdata as $cmid => $data) {
            $context = context_module::instance($cmid);
            $subcontext = [
                $parent,
                get_string('submissions', 'mod_verbalfeedback')
            ];
            writer::with_context($context)->export_data($subcontext, (object)$data);
        }
    }

    /**
     * Exports the feedback responses relating to the user.
     *
     * @param int[] $contextids Array of context IDs.
     * @param int $user The user's ID.
     * @param bool $respondent Whether we're exporting the data where the use is the respondent (true)
     *                         or the recipient (false) of the feedback.
     */
    protected static function export_responses_data($contextids, $user, $respondent = true) {
        global $DB;

        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextids, SQL_PARAMS_NAMED);
        $sql = "
                SELECT tr.id,
                       cm.id as cmid,
                       t.id as verbalfeedback,
                       t.name as verbalfeedbackname,
                       ti.position,
                       tq.id as questionid,
                       tq.question,
                       tq.type,
                       tq.category,
                       tr.value,
                       tr.fromuser,
                       tr.touser
                  FROM {context} ctx
                  JOIN {course_modules} cm
                    ON cm.id = ctx.instanceid AND ctx.contextlevel = :contextlevel
                  JOIN {modules} m
                    ON m.id = cm.module AND m.name = :modname
                  JOIN {verbalfeedback} t
                    ON t.id = cm.instance
                  JOIN {verbalfeedback_item} ti
                    ON ti.verbalfeedback = t.id
                  JOIN {verbalfeedback_question} tq
                    ON tq.id = ti.question
                  JOIN {verbalfeedback_response} tr
                    ON tr.verbalfeedback = t.id AND tr.item = ti.id
                 WHERE ctx.id {$contextsql} %s
              ORDER BY cmid ASC,
                       ti.position ASC,
                       tq.type ASC";

        $params = ['modname' => 'verbalfeedback', 'contextlevel' => CONTEXT_MODULE, 'userid' => $user] + $contextparams;

        if ($respondent) {
            $sqluser = 'fromuser';
            $userkey = 'recipient';
            $parent = get_string('feedbackgiven', 'mod_verbalfeedback');
        } else {
            $sqluser = 'touser';
            $userkey = 'respondent';
            $parent = get_string('feedbackreceived', 'mod_verbalfeedback');
        }
        $responsesdata = [];
        $responsessql = sprintf($sql, "AND tr.{$sqluser} = :userid");
        $responses = $DB->get_recordset_sql($responsessql, $params);
        foreach ($responses as $response) {
            $context = context_module::instance($response->cmid);
            $options = ['context' => $context];
            if (!isset($responsesdata[$response->cmid])) {
                $responsesdata[$response->cmid] = [
                    'name' => $response->verbalfeedbackname
                ];
            }
            $question = format_string($response->question, true, $options);
            if ($respondent) {
                $relateduser = transform::user($response->touser);
            } else {
                if ($response->touser) {
                    $relateduser = transform::user($response->fromuser);
                } else {
                    $relateduser = get_string('anonymous', 'mod_verbalfeedback');
                }
            }

            if ($response->type == api::QTYPE_RATED) {
                $valuetext = helper::get_scale_values($response->value);
            } else {
                $valuetext = format_string($response->value, true, $options);
            }

            if (!isset($responsesdata[$response->cmid]['questions'][$response->questionid])) {
                $responsesdata[$response->cmid]['questions'][$response->questionid]['question'] = $question;
            }
            $responsesdata[$response->cmid]['questions'][$response->questionid]['responses'][] = [
                $userkey => $relateduser,
                'value' => $valuetext,
            ];
        }
        $responses->close();

        foreach ($responsesdata as $cmid => $data) {
            $context = context_module::instance($cmid);
            $subcontext = [
                $parent,
                get_string('responses', 'mod_verbalfeedback')
            ];
            writer::with_context($context)->export_data($subcontext, (object)$data);
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context the context to delete in.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if (!$context instanceof context_module) {
            return;
        }

        if ($cm = get_coursemodule_from_id('verbalfeedback', $context->instanceid)) {
            $DB->delete_records('verbalfeedback_response', ['verbalfeedback' => $cm->instance]);
            $DB->delete_records('verbalfeedback_submission', ['verbalfeedback' => $cm->instance]);
        }
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist a list of contexts approved for deletion.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {

            if (!$context instanceof context_module) {
                continue;
            }
            $instanceid = $DB->get_field('course_modules', 'instance', ['id' => $context->instanceid], MUST_EXIST);
            $select = 'verbalfeedback = :verbalfeedback AND (fromuser = :fromuser OR touser = :touser)';
            $params = ['verbalfeedback' => $instanceid, 'fromuser' => $userid, 'touser' => $userid];
            $DB->delete_records_select('verbalfeedback_response', $select, $params);
            $DB->delete_records_select('verbalfeedback_submission', $select, $params);
        }
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param   userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!$context instanceof context_module) {
            return;
        }

        $params = [
            'cmid'      => $context->instanceid,
            'modname'   => 'verbalfeedback',
        ];

        // Fetch all users who gave non-anonymous feedback to other users.
        $fromsql = "
            SELECT DISTINCT ts.fromuser
                       FROM {course_modules} cm
                       JOIN {modules} m
                         ON m.id = cm.module AND m.name = :modname
                       JOIN {verbalfeedback} t
                         ON t.id = cm.instance
                       JOIN {verbalfeedback_submission} ts
                         ON ts.verbalfeedback = t.id
                      WHERE cm.id = :cmid";
        $userlist->add_from_sql('fromuser', $fromsql, $params);

        $fromsql = "
            SELECT DISTINCT tr.fromuser
                       FROM {course_modules} cm
                       JOIN {modules} m
                         ON m.id = cm.module AND m.name = :modname
                       JOIN {verbalfeedback} t
                         ON t.id = cm.instance
                       JOIN {verbalfeedback_response} tr
                         ON tr.verbalfeedback = t.id
                      WHERE cm.id = :cmid AND tr.fromuser <> 0";
        $userlist->add_from_sql('fromuser', $fromsql, $params);

        // Fetch all users who received feedback from other users.
        $tosql = "
           SELECT DISTINCT ts.touser
                      FROM {course_modules} cm
                      JOIN {modules} m
                        ON m.id = cm.module AND m.name = :modname
                      JOIN {verbalfeedback} t
                        ON t.id = cm.instance
                      JOIN {verbalfeedback_submission} ts
                        ON ts.verbalfeedback = t.id
                     WHERE cm.id = :cmid";
        $userlist->add_from_sql('touser', $tosql, $params);

        $tosql = "
           SELECT DISTINCT tr.touser
                      FROM {course_modules} cm
                      JOIN {modules} m
                        ON m.id = cm.module AND m.name = :modname
                      JOIN {verbalfeedback} t
                        ON t.id = cm.instance
                      JOIN {verbalfeedback_response} tr
                        ON tr.verbalfeedback = t.id
                     WHERE cm.id = :cmid";
        $userlist->add_from_sql('touser', $tosql, $params);
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param   approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        if (!$context instanceof context_module) {
            return;
        }

        $cm = get_coursemodule_from_id('verbalfeedback', $context->instanceid);

        if (!$cm) {
            // Only verbalfeedback module will be handled.
            return;
        }

        $userids = $userlist->get_userids();
        list($usersql, $userparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);

        $fromselect = "verbalfeedback = :verbalfeedback AND fromuser $usersql";
        $toselect = "verbalfeedback = :verbalfeedback AND touser $usersql";
        $params = ['verbalfeedback' => $cm->instance] + $userparams;
        $DB->delete_records_select('verbalfeedback_submission', $fromselect, $params);
        $DB->delete_records_select('verbalfeedback_submission', $toselect, $params);
        $DB->delete_records_select('verbalfeedback_response', $fromselect, $params);
        $DB->delete_records_select('verbalfeedback_response', $toselect, $params);
    }
}
