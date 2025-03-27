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
use mod_verbalfeedback\model\instance_category;
use mod_verbalfeedback\model\instance_criterion;
use mod_verbalfeedback\repository\language_repository;
use mod_verbalfeedback\repository\model\localized_string_type;

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
    public static function get_metadata(collection $items): collection {
        $items->add_database_table(
            'verbalfeedback_submission',
            [
                'instanceid' => 'privacy:metadata:instanceid',
                'fromuserid' => 'privacy:metadata:verbalfeedback_submission:fromuserid',
                'touserid' => 'privacy:metadata:verbalfeedback_submission:touserid',
                'status' => 'privacy:metadata:verbalfeedback_submission:status',
                'remarks' => 'privacy:metadata:verbalfeedback_submission:remarks',
            ],
            'privacy:metadata:verbalfeedback_submission'
        );
        $items->add_database_table(
            'verbalfeedback_response',
            [
                'instanceid' => 'privacy:metadata:verbalfeedback',
                'submissionid' => 'privacy:metadata:verbalfeedback_submissionid',
                'fromuserid' => 'privacy:metadata:verbalfeedback_submission:fromuserid',
                'touserid' => 'privacy:metadata:verbalfeedback_submission:touserid',
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
    public static function get_contexts_for_userid(int $userid): contextlist {
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
                    ON ts.instanceid = t.id
                 WHERE ts.fromuserid = :fromuserid OR ts.touserid = :touserid";

        $params = [
            'modname'       => 'verbalfeedback',
            'contextlevel'  => CONTEXT_MODULE,
            'fromuserid'      => $userid,
            'touserid'        => $userid,
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
                       ts.fromuserid,
                       ts.touserid
                  FROM {context} ctx
                  JOIN {course_modules} cm
                    ON cm.id = ctx.instanceid AND ctx.contextlevel = :contextlevel
                  JOIN {modules} m
                    ON m.id = cm.module AND m.name = :modname
                  JOIN {verbalfeedback} t
                    ON t.id = cm.instance
                  JOIN {verbalfeedback_submission} ts
                    ON ts.instanceid = t.id
                 WHERE ctx.id {$contextsql} %s
              ORDER BY cmid ASC";

        if ($respondent) {
            $sqluser = 'fromuserid';
            $userkey = 'recipient';
            $parent = get_string('feedbackgiven', 'mod_verbalfeedback');
        } else {
            $sqluser = 'touserid';
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
                    'name' => $submission->verbalfeedbackname,
                ];
            }
            if ($respondent) {
                $relateduser = transform::user($submission->touserid);
            } else {
                $relateduser = transform::user($submission->fromuserid);
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
                get_string('submissions', 'mod_verbalfeedback'),
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

        $categorystr = [];
        $criterionstr = [];

        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextids, SQL_PARAMS_NAMED);
        $sql = "
                SELECT tr.id,
                       cm.id as cmid,
                       t.id as verbalfeedback,
                       t.name as verbalfeedbackname,
                       ti.id as criterionid,
                       ti.categoryid,
                       tr.value,
                       tr.fromuserid,
                       tr.touserid
                  FROM {context} ctx
                  JOIN {course_modules} cm
                    ON cm.id = ctx.instanceid AND ctx.contextlevel = :contextlevel
                  JOIN {modules} m
                    ON m.id = cm.module AND m.name = :modname
                  JOIN {verbalfeedback} t
                    ON t.id = cm.instance
                  JOIN {verbalfeedback_submission} ts
                    ON ts.instanceid = t.id
                  JOIN {verbalfeedback_response} tr
                    ON tr.submissionid = ts.id
                  JOIN {verbalfeedback_i_criterion} ti
                    ON ti.id = tr.criterionid
                 WHERE ctx.id {$contextsql} %s
              ORDER BY cmid ASC,
                       ti.position ASC";

        $params = ['modname' => 'verbalfeedback', 'contextlevel' => CONTEXT_MODULE, 'userid' => $user] + $contextparams;

        if ($respondent) {
            $sqluser = 'fromuserid';
            $userkey = 'recipient';
            $parent = get_string('feedbackgiven', 'mod_verbalfeedback');
        } else {
            $sqluser = 'touserid';
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
                    'name' => $response->verbalfeedbackname,
                ];
            }
            if (!\array_key_exists($response->categoryid, $categorystr)) {
                $ids = static::get_strings($response->verbalfeedback, localized_string_type::INSTANCE_CRITERION);
                foreach ($ids as $id => $str) {
                    $categorystr[$id] = $str;
                }
            }
            if (!\array_key_exists($response->criterionid, $criterionstr)) {
                $ids = static::get_strings($response->verbalfeedback, localized_string_type::INSTANCE_CRITERION);
                foreach ($ids as $id => $str) {
                    $criterionstr[$id] = $str;
                }
            }

            if ($respondent) {
                $relateduser = transform::user($response->touserid);
            } else {
                if ($response->touserid) {
                    $relateduser = transform::user($response->fromuserid);
                } else {
                    $relateduser = get_string('anonymous', 'mod_verbalfeedback');
                }
            }

            if (!isset($responsesdata[$response->cmid]['criterion'][$response->criterionid])) {
                $responsesdata[$response->cmid]['criterion'][$response->criterionid]['criterion'] =
                    $criterionstr[$response->criterionid];
                $responsesdata[$response->cmid]['criterion'][$response->criterionid]['category'] =
                    $categorystr[$response->categoryid];
                $responsesdata[$response->cmid]['criterion'][$response->criterionid]['submissions'] = [];
            }
            $responsesdata[$response->cmid]['criterion'][$response->criterionid]['submissions'][] = [
                $userkey => $relateduser,
                'value' => format_string($response->value, true, $options),
            ];
        }
        $responses->close();

        foreach ($responsesdata as $cmid => $data) {
            $context = context_module::instance($cmid);
            $subcontext = [
                $parent,
                get_string('responses', 'mod_verbalfeedback'),
            ];
            writer::with_context($context)->export_data($subcontext, (object)$data);
        }
    }

    /**
     * Get the localized strings for the given instance and category/criterion.
     * @param int $id The instance ID.
     * @param string $type The type of localized string.
     * @return array With key = category/criterion id and value = string.
     */
    private static function get_strings(int $id, string $type) {
        global $DB;

        $lang = (new language_repository)->get_by_iso(current_language());
        if (!$lang) {
            return [$id => ''];
        }
        $sql = 'SELECT DISTINCT(c.id) as cid, string
                  FROM {verbalfeedback_local_string} s
                  JOIN {verbalfeedback_i_criterion} c
                    ON c.id = s.foreignkey AND s.typeid = :typeid AND s.languageid = :languageid
                  JOIN {verbalfeedback_response} r
                    ON r.criterionid = __c_id__
                 WHERE r.instanceid = :id';
        if ($type === localized_string_type::INSTANCE_CATEGORY_HEADER) {
            $sql = str_replace(
                'c.id', 'c.categoryid', $sql
            );
        }
        $sql = str_replace('__c_id__', 'c.id', $sql);
        $params = [
            'id' => $id,
            'languageid' => $lang->get_id(),
            'typeid' => localized_string_type::str2id($type),
        ];
        return $DB->get_records_sql_menu($sql, $params);
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
            $DB->delete_records('verbalfeedback_response', ['instanceid' => $cm->instance]);
            $DB->delete_records('verbalfeedback_submission', ['instanceid' => $cm->instance]);
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
            $select = 'instanceid = :instanceid AND (fromuserid = :fromuserid OR touserid = :touserid)';
            $params = ['instanceid' => $instanceid, 'fromuserid' => $userid, 'touserid' => $userid];
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
            SELECT DISTINCT ts.fromuserid
                       FROM {course_modules} cm
                       JOIN {modules} m
                         ON m.id = cm.module AND m.name = :modname
                       JOIN {verbalfeedback} t
                         ON t.id = cm.instance
                       JOIN {verbalfeedback_submission} ts
                         ON ts.instanceid = t.id
                      WHERE cm.id = :cmid";
        $userlist->add_from_sql('fromuserid', $fromsql, $params);

        $fromsql = "
            SELECT DISTINCT tr.fromuserid
                       FROM {course_modules} cm
                       JOIN {modules} m
                         ON m.id = cm.module AND m.name = :modname
                       JOIN {verbalfeedback} t
                         ON t.id = cm.instance
                       JOIN {verbalfeedback_response} tr
                         ON tr.instanceid = t.id
                      WHERE cm.id = :cmid AND tr.fromuserid <> 0";
        $userlist->add_from_sql('fromuserid', $fromsql, $params);

        // Fetch all users who received feedback from other users.
        $tosql = "
           SELECT DISTINCT ts.touserid
                      FROM {course_modules} cm
                      JOIN {modules} m
                        ON m.id = cm.module AND m.name = :modname
                      JOIN {verbalfeedback} t
                        ON t.id = cm.instance
                      JOIN {verbalfeedback_submission} ts
                        ON ts.instanceid = t.id
                     WHERE cm.id = :cmid";
        $userlist->add_from_sql('touserid', $tosql, $params);

        $tosql = "
           SELECT DISTINCT tr.touserid
                      FROM {course_modules} cm
                      JOIN {modules} m
                        ON m.id = cm.module AND m.name = :modname
                      JOIN {verbalfeedback} t
                        ON t.id = cm.instance
                      JOIN {verbalfeedback_response} tr
                        ON tr.instanceid = t.id
                     WHERE cm.id = :cmid";
        $userlist->add_from_sql('touserid', $tosql, $params);
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

        $fromselect = "verbalfeedback = :verbalfeedback AND fromuserid $usersql";
        $toselect = "verbalfeedback = :verbalfeedback AND touserid $usersql";
        $params = ['verbalfeedback' => $cm->instance] + $userparams;
        $DB->delete_records_select('verbalfeedback_submission', $fromselect, $params);
        $DB->delete_records_select('verbalfeedback_submission', $toselect, $params);
        $DB->delete_records_select('verbalfeedback_response', $fromselect, $params);
        $DB->delete_records_select('verbalfeedback_response', $toselect, $params);
    }
}
