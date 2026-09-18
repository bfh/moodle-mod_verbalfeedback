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
 * Class containing the external API functions functions for the verbal feedback module.
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use mod_verbalfeedback\api;
use mod_verbalfeedback\model\submission;
use mod_verbalfeedback\model\submission_status;
use mod_verbalfeedback\output\list_participants;
use mod_verbalfeedback\repository\submission_repository;

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . "/user/externallib.php");

/**
 * Class external.
 *
 * The external API for the verbal feedback module.
 *
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_verbalfeedback_external extends external_api {
    /**
     * Parameter description for update_item_multiplier().
     *
     * @return external_function_parameters
     */
    public static function update_item_multiplier_parameters() {
        return new external_function_parameters([
            'itemid' => new external_value(PARAM_INT, 'The id of the item.'),
            'multiplier' => new external_value(PARAM_FLOAT, 'The new multiplier value.'),
        ]);
    }

    /**
     * Updates the multiplier of an item.
     * @param int $itemid The item.
     * @param float $multiplier The new value of the multiplier.
     * @return array
     * @throws coding_exception
     */
    public static function update_item_multiplier($itemid, $multiplier) {
        $warnings = [];
        $params = external_api::validate_parameters(self::update_item_multiplier_parameters(), [
            'itemid' => $itemid,
            'multiplier' => $multiplier,
        ]);

        // Validate context and capability.
        $verbalfeedback = api::get_instance_by_itemid($itemid);
        $cm = get_coursemodule_from_instance('verbalfeedback', $verbalfeedback->id);
        $cmid = $cm->id;
        $context = context_module::instance($cmid);
        self::validate_context($context);

        require_capability('mod/verbalfeedback:edititems', $context);
        $result = api::update_item_multiplier($params['itemid'], $params['multiplier']);

        return [
            'success' => $result,
            'warnings' => $warnings,
        ];
    }

    /**
     * Method results description for update_item_multiplier().
     *
     * @return external_description
     */
    public static function update_item_multiplier_returns() {
        return new external_single_structure(
            [
                'success' => new external_value(PARAM_BOOL, 'The success of the operation.'),
                'warnings' => new external_warnings(),
            ]
        );
    }

    /**
     * Parameter description for update_item_multiplier().
     *
     * @return external_function_parameters
     */
    public static function update_category_percentage_parameters() {
        return new external_function_parameters([
            'categoryid' => new external_value(PARAM_INT, 'The id of the item.'),
            'percentage' => new external_value(PARAM_FLOAT, 'The new percentage value.'),
        ]);
    }

    /**
     * Updates the percentage value of a category.
     *
     * @param int $categoryid The category id
     * @param float $percentage The new percentage value.
     * @return array
     * @throws coding_exception
     */
    public static function update_category_percentage($categoryid, $percentage) {
        $warnings = [];
        $params = external_api::validate_parameters(self::update_category_percentage_parameters(), [
            'categoryid' => $categoryid,
            'percentage' => $percentage,
        ]);

        // Validate context and capability.
        $verbalfeedback = api::get_instance_by_categoryid($categoryid);
        $cm = get_coursemodule_from_instance('verbalfeedback', $verbalfeedback->id);
        $cmid = $cm->id;
        $context = context_module::instance($cmid);
        self::validate_context($context);

        require_capability('mod/verbalfeedback:edititems', $context);

        $result = api::update_category_percentage($params['categoryid'], $params['percentage']);
        return [
            'success' => $result,
            'warnings' => $warnings,
        ];
    }

    /**
     * Method results description for update_item_multiplier().
     *
     * @return external_description
     */
    public static function update_category_percentage_returns() {
        return new external_single_structure(
            [
                'success' => new external_value(PARAM_BOOL, 'The success of the operation.'),
                'warnings' => new external_warnings(),
            ]
        );
    }

    /**
     * Fetches the plain data for the user selector.
     *
     * @param int $verbalfeedbackid The verbal feedback instance ID.
     * @param int $groupid The group ID if group mode is enabled.
     * @param int $status The submission status filter.
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws restricted_context_exception
     */
    public static function data_for_userselector($verbalfeedbackid, $groupid = 0, $status = -1) {
        global $PAGE, $USER;
        $params = external_api::validate_parameters(self::data_for_userselector_parameters(), [
            'verbalfeedbackid' => $verbalfeedbackid,
            'groupid' => $groupid,
            'status' => $status,
        ]);

        $verbalfeedbackid = $params['verbalfeedbackid'];
        $coursecm = get_course_and_cm_from_instance($verbalfeedbackid, 'verbalfeedback');
        $context = context_module::instance($coursecm[1]->id);
        self::validate_context($context);
        require_capability('mod/verbalfeedback:can_respond', $context);

        $verbalfeedback = api::get_instance($verbalfeedbackid);

        // Use here the filters only that are set via the action bar above the result set. Even though users
        // are filtered already by some letters, the ajax user search must operate on all possible users
        // to be able to create a new filtered list on the next page request.
        $filter = [];
        if ($params['groupid']) {
            $filter['groupid'] = $params['groupid'];
        }
        if ($params['status'] !== -1) {
            $filter['status'] = $params['status'];
        }
        $participants = api::get_participants($verbalfeedback->id, $USER->id, $filter);
        foreach (\array_keys($participants) as $id) {
            $participants[$id]->fullname = fullname($participants[$id]);
            $userpicture = new user_picture($participants[$id]);
            $userpicture->size = 1;
            $participants[$id]->profileimageurl = $userpicture->get_url($PAGE)->out(false);
        }
        return $participants;
    }

    /**
     * Fetches template data for the list participants the user will provide feedback to.
     *
     * @param int $verbalfeedbackid The verbal feedback instance ID.
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws restricted_context_exception
     */
    public static function data_for_participant_list($verbalfeedbackid) {
        global $PAGE, $USER;
        $warnings = [];
        $params = external_api::validate_parameters(self::data_for_participant_list_parameters(), [
            'verbalfeedbackid' => $verbalfeedbackid,
        ]);

        $verbalfeedbackid = $params['verbalfeedbackid'];
        $coursecm = get_course_and_cm_from_instance($verbalfeedbackid, 'verbalfeedback');
        $context = context_module::instance($coursecm[1]->id);
        self::validate_context($context);
        require_capability('mod/verbalfeedback:can_respond', $context);

        $renderer = $PAGE->get_renderer('mod_verbalfeedback');
        $verbalfeedback = api::get_instance($verbalfeedbackid);
        $participants = api::get_participants($verbalfeedback->id, $USER->id);
        $listparticipants = new list_participants($verbalfeedback, $USER->id, $participants);
        $data = $listparticipants->export_for_template($renderer);
        return [
            'verbalfeedbackid' => $data->verbalfeedbackid,
            'participants' => $data->participants,
            'warnings' => $warnings,
        ];
    }

    /**
     * Parameter description for data_for_participant_list().
     *
     * @return external_function_parameters
     */
    public static function data_for_participant_list_parameters() {
        return new external_function_parameters(
            [
                'verbalfeedbackid' => new external_value(PARAM_INT, 'The verbal feedback ID.'),
            ]
        );
    }

    /**
     * Method results description for data_for_participant_list().
     *
     * @return external_description
     */
    public static function data_for_participant_list_returns() {
        return new external_single_structure(
            [
                'verbalfeedbackid' => new external_value(PARAM_INT, 'The verbal feedback ID.'),
                'participants' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'name' => new external_value(PARAM_TEXT, 'The target participant name.'),
                            'statusid' => new external_value(PARAM_INT, 'The submission ID', VALUE_OPTIONAL),
                            'statuspending' => new external_value(PARAM_BOOL, 'Pending status', VALUE_DEFAULT, false),
                            'statusinprogress' => new external_value(PARAM_BOOL, 'In progress status', VALUE_DEFAULT, false),
                            'statusdeclined' => new external_value(PARAM_BOOL, 'Declined status', VALUE_DEFAULT, false),
                            'statuscompleted' => new external_value(PARAM_BOOL, 'Completed status', VALUE_DEFAULT, false),
                            'statusviewonly' => new external_value(PARAM_BOOL, 'View only status', VALUE_DEFAULT, false),
                            'viewlink' => new external_value(PARAM_RAW, 'Flag for view button.', VALUE_OPTIONAL, false),
                            'respondlink' => new external_value(PARAM_URL, 'Questionnaire URL.', VALUE_OPTIONAL),
                            'declinelink' => new external_value(PARAM_BOOL, 'Flag for decline button.', VALUE_OPTIONAL, false),
                            'undodeclinelink' => new external_value(
                                PARAM_BOOL,
                                'Flag for the undo decline button.',
                                VALUE_OPTIONAL,
                                false
                            ),
                        ]
                    )
                ),
                'warnings' => new external_warnings(),
            ]
        );
    }

    /**
     * Parameter description for data_for_participant_list().
     *
     * @return external_function_parameters
     */
    public static function data_for_userselector_parameters() {
        return new external_function_parameters(
            [
                'verbalfeedbackid' => new external_value(PARAM_INT, 'The verbal feedback ID.'),
                'groupid' => new external_value(PARAM_INT, 'The group ID if group mode is enabled.', VALUE_OPTIONAL, 0),
                'status' => new external_value(PARAM_INT, 'The submission status filter.', VALUE_OPTIONAL, 0),
            ]
        );
    }

    /**
     * Method results description for data_for_participant_list().
     *
     * @return external_description
     */
    public static function data_for_userselector_returns() {
        // Get user description.
        $userdesc = \core_user_external::user_description();
        // Unset all the keys that are not returned by the verbal feedback api class.
        foreach (\array_keys($userdesc->keys) as $prop) {
            if (!in_array($prop, api::get_fields_for_participants())) {
                unset($userdesc->keys[$prop]);
            }
        }
        // Add submission fields from the verbal feedback submission record related to the user.
        $userdesc->keys['submissionid'] = new external_value(PARAM_INT, 'The submission ID if exists.', VALUE_OPTIONAL);
        $userdesc->keys['submissionstatus'] = new external_value(PARAM_INT, 'The submission status if exists.', VALUE_OPTIONAL);
        // Add the fullname field and the profile image URL.
        $userdesc->keys['fullname'] = new external_value(PARAM_TEXT, 'The user full name.');
        $userdesc->keys['profileimageurl'] = new external_value(PARAM_URL, 'The user profile image URL.', VALUE_OPTIONAL);
        return new external_multiple_structure($userdesc);
    }

    /**
     * Parameter description for save_responses().
     *
     * @return external_function_parameters
     */
    public static function save_responses_parameters() {
        return new external_function_parameters(
            [
                'verbalfeedbackid' => new external_value(PARAM_INT, 'The verbal feedback identifier.'),
                'submissionid' => new external_value(PARAM_INT, 'The submission identifier.'),
                'touserid' => new external_value(PARAM_INT, 'The user identifier for the feedback subject.'),
                'responses' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'criterionid' => new external_value(PARAM_INT, 'The criterion ID.'),
                            'value' => new external_value(PARAM_INT, 'The response value.', VALUE_OPTIONAL, null),
                            'studentcomment' => new external_value(PARAM_RAW, 'The response public comment.', VALUE_OPTIONAL, ''),
                            'privatecomment' => new external_value(PARAM_RAW, 'The response private comment.', VALUE_OPTIONAL, ''),
                        ],
                        'item to save',
                        VALUE_OPTIONAL
                    ),
                    'item collection to save',
                    VALUE_OPTIONAL,
                    null
                ),
                'complete' => new external_value(PARAM_BOOL, 'Whether to mark the submission as complete.'),
            ]
        );
    }

    /**
     * Save a user's responses to the feedback questions for another user.
     *
     * @param int $verbalfeedbackid The verbal feedback instance ID.
     * @param int $submissionid The submission ID.
     * @param int $touserid The recipient of the feedback responses.
     * @param array $responses The responses data.
     * @param bool $complete Whether to mark the submission as complete.
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws restricted_context_exception
     */
    public static function save_responses($verbalfeedbackid, $submissionid, $touserid, $responses, $complete) {
        $warnings = [];
        $params = external_api::validate_parameters(self::save_responses_parameters(), [
            'verbalfeedbackid' => $verbalfeedbackid,
            'submissionid' => $submissionid,
            'touserid' => $touserid,
            'responses' => $responses,
            'complete' => $complete,
        ]);

        $verbalfeedbackid = $params['verbalfeedbackid'];
        $submissionid = $params['submissionid'];
        $touserid = $params['touserid'];
        $responses = $params['responses'];
        $complete = $params['complete'];

        $cm = get_coursemodule_from_instance('verbalfeedback', $verbalfeedbackid);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/verbalfeedback:can_respond', $context);

        $redirecturl = new \moodle_url('/mod/verbalfeedback/view.php');
        $redirecturl->param('id', $cm->id);

        $submission = self::get_verified_submission_by_id($submissionid, $verbalfeedbackid, 'fromuserid');
        $result = api::save_responses($verbalfeedbackid, $submissionid, $touserid, $responses);

        if ($complete && $result) {
            $submissionrepo = new submission_repository();
            $submission = $submissionrepo->get_by_id($submissionid);
            $submission->set_status(submission_status::COMPLETE);
            $submissionrepo->save($submission);
        }

        return [
            'result' => $result,
            'redirurl' => $redirecturl->out(),
            'warnings' => $warnings,
        ];
    }

    /**
     * Method results description for save_responses().
     *
     * @return external_description
     */
    public static function save_responses_returns() {
        return new external_single_structure(
            [
                'result' => new external_value(PARAM_BOOL, 'The item deletion processing result.'),
                'redirurl' => new external_value(PARAM_URL, 'The redirect URL.'),
                'warnings' => new external_warnings(),
            ]
        );
    }

    /**
     * Parameter description for get_responses().
     *
     * @return external_function_parameters
     */
    public static function get_responses_parameters() {
        return new external_function_parameters(
            [
                'verbalfeedbackid' => new external_value(PARAM_INT, 'The verbal feedback identifier.'),
                'fromuserid' => new external_value(PARAM_INT, 'The user identifier of the respondent.'),
                'touserid' => new external_value(PARAM_INT, 'The user identifier for the feedback subject.'),
                'submissionid' => new external_value(PARAM_INT, 'The submission identifier.'),
            ]
        );
    }

    /**
     * Fetches the user's responses to a feedback for a specific user.
     *
     * @param int $verbalfeedbackid The verbal feedback ID.
     * @param int $fromuserid The ID of the user who is responding to the feedback.
     * @param int $touserid The user ID of the recipient of the feedback.
     * @param int $submissionid The submission id.
     * @return array
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws restricted_context_exception
     */
    public static function get_responses($verbalfeedbackid, $fromuserid, $touserid, $submissionid) {
        global $USER;
        $warnings = [];

        $params = external_api::validate_parameters(self::get_responses_parameters(), [
            'verbalfeedbackid' => $verbalfeedbackid,
            'fromuserid' => $fromuserid,
            'touserid' => $touserid,
            'submissionid' => $submissionid,
        ]);
        $verbalfeedbackid = $params['verbalfeedbackid'];
        $fromuserid = $params['fromuserid'];
        $touserid = $params['touserid'];
        $submissionid = $params['submissionid'];

        $cm = get_coursemodule_from_instance('verbalfeedback', $verbalfeedbackid);
        $cmid = $cm->id;
        $context = context_module::instance($cmid);
        self::validate_context($context);
        $submission = self::get_verified_submission_by_id($submissionid, $verbalfeedbackid);
        $canviewall = has_capability('mod/verbalfeedback:view_all_reports', $context);
        $isrecipient = has_capability('mod/verbalfeedback:receive_rating', $context)
            && (int)$submission->touserid === (int)$USER->id;
        if (!$canviewall && !$isrecipient) {
            throw new moodle_exception('nopermissions', 'error');
        }
        $redirecturl = new \moodle_url('/mod/verbalfeedback/view.php');
        $redirecturl->param('id', $cmid);

        $responses = [];
        foreach ($submission->get_responses() as $response) {
            $viewmodel = [];
            $viewmodel['id'] = $response->get_id();
            $viewmodel['criterionid'] = $response->get_criterion_id();
            $viewmodel['value'] = $response->get_value();
            $viewmodel['studentcomment'] = $response->get_student_comment();
            $viewmodel['privatecomment'] = ($canviewall) ? $response->get_private_comment() : null;
            $responses[] = $viewmodel;
        }

        return [
            'responses' => $responses,
            'redirurl' => $redirecturl->out(),
            'warnings' => $warnings,
        ];
    }

    /**
     * Method results description for get_responses().
     *
     * @return external_description
     */
    public static function get_responses_returns() {
        return new external_single_structure(
            [
                'responses' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'id' => new external_value(PARAM_INT, 'The response ID.'),
                            'criterionid' => new external_value(PARAM_INT, 'The item ID for the response.'),
                            'value' => new external_value(PARAM_INT, 'The the value for the response.', VALUE_OPTIONAL, null),
                            'studentcomment' => new external_value(PARAM_RAW, 'The response public comment.'),
                            'privatecomment' => new external_value(PARAM_RAW, 'The response private comment.'),
                        ]
                    )
                ),
                'warnings' => new external_warnings(),
            ]
        );
    }

    /**
     * Get a verified submission by its ID and check whether it belongs to the given verbal feedback ID.
     * If not, throw an exception.
     *
     * @param int $submissionid The submission ID.
     * @param int $verbalfeedbackid The verbal feedback ID.
     * @param string $mode The mode to check the user against ('fromuserid' or 'touserid').
     * @return submission The verified submission.
     * @throws moodle_exception If the submission is not found or does not belong to the given verbal feedback.
     */
    private static function get_verified_submission_by_id($submissionid, $verbalfeedbackid, string $mode = ''): submission {
        global $USER;
        $submissionrepo = new submission_repository();
        $submission = $submissionrepo->get_by_id($submissionid);
        // Sanity check: if the submission id does not belong to the given verbal feedback id, throw an exception.
        if ($submission === null || $submission->instanceid !== $verbalfeedbackid) {
            throw new moodle_exception('invalididprovided', 'mod_verbalfeedback');
        }
        // Check if the current user is either the recipient or the respondent of the submission.
        if ($mode === 'touserid' && $submission->get_to_user_id() !== $USER->id) {
            throw new moodle_exception('nopermissions', 'error');
        }
        if ($mode === 'fromuserid' && $submission->get_from_user_id() !== $USER->id) {
            throw new moodle_exception('nopermissions', 'error');
        }
        return $submission;
    }
}
