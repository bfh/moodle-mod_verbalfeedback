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
use mod_verbalfeedback\model\submission_status;
use mod_verbalfeedback\output\list_participants;
use mod_verbalfeedback\repository\submission_repository;

require_once($CFG->libdir . "/externallib.php");

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
     * Fetches the questions assigned to a verbal feedback instance.
     *
     * @param int $verbalfeedbackid The verbalfeedback ID.
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     */
    public static function get_questions($verbalfeedbackid) {
        $warnings = [];
        $params = external_api::validate_parameters(self::get_items_parameters(), ['verbalfeedbackid' => $verbalfeedbackid]);

        $cats = api::get_categories_with_items($params['verbalfeedbackid']);
        $preparedcats = helper::prepare_items_view($cats);

        // Validate context and capability.
        $coursecm = get_course_and_cm_from_instance($verbalfeedbackid, 'verbalfeedback');
        $context = context_module::instance($coursecm[1]->id);
        self::validate_context($context);

        require_capability('mod/verbalfeedback:can_respond', $context);

        throw new moodle_exception(json_encode($preparedcats));
        return [
            'items' => $preparedcats,
            'warnings' => $warnings
        ];
    }

    /**
     * Parameter description for get_questions().
     *
     * @return external_function_parameters
     */
    public static function get_questions_parameters() {
        return new external_function_parameters([
            'verbalfeedbackid' => new external_value(PARAM_INT, 'The verbal feedback instance ID. For capability checking.'),
        ]);
    }

    /**
     * Method results description for get_questions().
     *
     * @return external_description
     */
    public static function get_questions_returns() {
        return new external_single_structure(
            [
                'questions' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'id' => new external_value(PARAM_INT, 'The question ID.'),
                            'question' => new external_value(PARAM_TEXT, 'The question text.'),
                            'type' => new external_value(PARAM_INT, 'The question type.'),
                            'typeName' => new external_value(PARAM_TEXT, 'The question type text value.'),
                            'category' => new external_value(PARAM_INT, 'The question category.'),
                            'categoryName' => new external_value(PARAM_TEXT, 'The question category text value.')
                        ]
                    )
                ),
                'warnings' => new external_warnings()
            ]
        );
    }

    /**
     * Adds a question into the verbal feedback question bank.
     *
     * @param string $question The question text.
     * @param int $type The question type.
     * @param int $category The question category.
     * @param int $verbalfeedbackid The verbal feedback instance ID, for capability checking.
     * @return array
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     * @throws restricted_context_exception
     * @throws dml_exception
     */
    public static function add_question($question, $type, $category, $verbalfeedbackid) {
        $warnings = [];

        $params = external_api::validate_parameters(self::add_question_parameters(), [
            'question' => $question,
            'type' => $type,
            'category' => $category,
            'verbalfeedbackid' => $verbalfeedbackid,
        ]);

        // Validate context and capability.
        $verbalfeedbackid = $params['verbalfeedbackid'];
        $coursecm = get_course_and_cm_from_instance($verbalfeedbackid, 'verbalfeedback');
        $context = context_module::instance($coursecm[1]->id);
        self::validate_context($context);

        require_capability('mod/verbalfeedback:editquestions', $context);

        $dataobj = new stdClass();
        $dataobj->question = $params['question'];
        $dataobj->type = $params['type'];
        $dataobj->category = $params['category'];
        $questionid = api::add_question($dataobj);

        return [
            'questionid' => $questionid,
            'warnings' => $warnings
        ];
    }

    /**
     * Parameter description for add_question().
     *
     * @return external_function_parameters
     */
    public static function add_question_parameters() {
        return new external_function_parameters([
            'question' => new external_value(PARAM_TEXT, 'The question text.'),
            'type' => new external_value(PARAM_INT, 'The question type.'),
            'category' => new external_value(PARAM_INT, 'The question category.'),
            'verbalfeedbackid' => new external_value(PARAM_INT, 'The verbal feedback instance ID. For capability checking.'),
        ]);
    }

    /**
     * Method results description for add_question().
     *
     * @return external_description
     */
    public static function add_question_returns() {
        return new external_single_structure(
            [
                'questionid' => new external_value(PARAM_INT, 'The question ID of the added question.'),
                'warnings' => new external_warnings()
            ]
        );
    }

    /**
     * Updates a question in the verbal feedback question bank.
     *
     * @param int $id The question ID.
     * @param string $question The question text.
     * @param int $type The question type.
     * @param int $category The question category.
     * @param int $verbalfeedbackid The verbal feedback instance ID, for capability checking.
     * @return array
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     * @throws restricted_context_exception
     */
    public static function update_question($id, $question, $type, $category, $verbalfeedbackid) {
        $warnings = [];

        $params = external_api::validate_parameters(self::update_question_parameters(), [
                'id' => $id,
                'question' => $question,
                'type' => $type,
                'category' => $category,
                'verbalfeedbackid' => $verbalfeedbackid,
            ]
        );

        // Validate context and capability.
        $verbalfeedbackid = $params['verbalfeedbackid'];
        $coursecm = get_course_and_cm_from_instance($verbalfeedbackid, 'verbalfeedback');
        $context = context_module::instance($coursecm[1]->id);
        self::validate_context($context);

        require_capability('mod/verbalfeedback:editquestions', $context);

        $dataobj = new stdClass();
        $dataobj->id = $params['id'];
        $dataobj->question = $params['question'];
        $dataobj->type = $params['type'];
        $dataobj->category = $params['category'];

        $result = api::update_question($dataobj);

        return [
            'result' => $result,
            'warnings' => $warnings
        ];
    }

    /**
     * Parameter description for update_question().
     *
     * @return external_function_parameters
     */
    public static function update_question_parameters() {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'The question ID.'),
            'question' => new external_value(PARAM_TEXT, 'The question text.'),
            'type' => new external_value(PARAM_INT, 'The question type.'),
            'category' => new external_value(PARAM_INT, 'The question category.'),
            'verbalfeedbackid' => new external_value(PARAM_INT, 'The verbal feedback instance ID. For capability checking.'),
        ]);
    }

    /**
     * Method results description for update_question().
     *
     * @return external_description
     */
    public static function update_question_returns() {
        return new external_single_structure(
            [
                'result' => new external_value(PARAM_BOOL, 'The question update processing result.'),
                'warnings' => new external_warnings()
            ]
        );
    }

    /**
     * Deletes a question in the verbal feedback question bank.
     *
     * @param int $id The question ID.
     * @param int $verbalfeedbackid The verbal feedback instance ID, for capability checking.
     * @return array
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     * @throws restricted_context_exception
     */
    public static function delete_question($id, $verbalfeedbackid) {
        require_capability('mod/verbalfeedback:editquestions', $context);
    }

    /**
     * Parameter description for delete_question().
     *
     * @return external_function_parameters
     */
    public static function delete_question_parameters() {
        return new external_function_parameters([
            'id' => new external_value(PARAM_INT, 'The question ID.'),
            'verbalfeedbackid' => new external_value(PARAM_INT, 'The verbal feedback instance ID. For capability checking.'),
        ]);
    }

    /**
     * Method results description for delete_question().
     *
     * @return external_description
     */
    public static function delete_question_returns() {
        return new external_single_structure(
            [
                'result' => new external_value(PARAM_BOOL, 'The question update processing result.'),
                'warnings' => new external_warnings()
            ]
        );
    }

    /**
<<<<<<< HEAD
=======
     * Fetches the questions assigned to a verbal feedback instance.
     *
     * @param int $verbalfeedbackid The verbalf eedback ID.
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     */
    public static function get_items($verbalfeedbackid) {
        $warnings = [];
        $params = external_api::validate_parameters(self::get_items_parameters(), ['verbalfeedbackid' => $verbalfeedbackid]);

        $cats = api::get_categories_with_items($params['verbalfeedbackid']);
        $preparedcats = helper::prepare_items_view($cats);
        throw new moodle_exception(json_encode($preparedcats));

        // Validate context and capability.
        $coursecm = get_course_and_cm_from_instance($verbalfeedbackid, 'verbalfeedback');
        $context = context_module::instance($coursecm[1]->id);
        self::validate_context($context);

        require_capability('mod/verbalfeedback:can_respond', $context);
        return [
            'items' => $preparedcats,
            'warnings' => $warnings
        ];
    }

    /**
     * Parameter description for get_items().
     *
     * @return external_function_parameters
     */
    public static function get_items_parameters() {
        return new external_function_parameters(
            [
                'verbalfeedbackid' => new external_value(PARAM_INT, 'The verbal feedback ID.')
            ]
        );
    }

    /**
     * Method results description for get_items().
     *
     * @return external_description
     */
    public static function get_items_returns() {
        return new external_single_structure(
            [
                'items' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'category' => new external_value(PARAM_TEXT, 'The question category text value.'),
                            'categoryposition' => new external_value(PARAM_INT, 'The item ID.'),
                            'questions' => new external_multiple_structure(
                                new external_single_structure(
                                    [
                                        'id' => new external_value(PARAM_INT, 'The item ID.'),
                                        'verbalfeedbackid' => new external_value(PARAM_INT, 'The verbal feedback ID.'),
                                        'questionid' => new external_value(PARAM_INT, 'The question ID.'),
                                        'position' => new external_value(PARAM_INT, 'The item position'),
                                        'question' => new external_value(PARAM_TEXT, 'The question text.'),
                                        'type' => new external_value(PARAM_INT, 'The question type.'),
                                        'typetext' => new external_value(PARAM_TEXT, 'The question type text value.'),
                                        'category' => new external_value(PARAM_INT, 'The question category.'),
                                        'categorytext' => new external_value(PARAM_TEXT, 'The question category text value.')
                                    ]
                                )
                            )
                        ]
                    )
                ),
                'warnings' => new external_warnings()
            ]
        );
    }

    /**
     * Sets the questions for the verbal feedback activity.
     *
     * @param int $verbalfeedbackid The verbal feedback instance.
     * @param int[] $questionids The list of question IDs from the question bank being assigned to the verbal feedback instance.
     * @return array
     * @throws moodle_exception
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     * @throws restricted_context_exception
     */
    public static function set_items($verbalfeedbackid, $questionids) {
        $warnings = [];
        $params = external_api::validate_parameters(self::set_items_parameters(), [
            'verbalfeedbackid' => $verbalfeedbackid,
            'questionids' => $questionids
        ]);

        // Validate context and capability.
        $cm = get_coursemodule_from_instance('verbalfeedback', $verbalfeedbackid);
        $cmid = $cm->id;
        $context = context_module::instance($cmid);
        self::validate_context($context);

        require_capability('mod/verbalfeedback:edititems', $context);

        $result = api::set_items($params['verbalfeedbackid'], $params['questionids']);

        return [
            'result' => $result,
            'warnings' => $warnings
        ];
    }

    /**
     * Parameter description for set_items().
     *
     * @return external_function_parameters
     */
    public static function set_items_parameters() {
        return new external_function_parameters(
            [
                'verbalfeedbackid' => new external_value(PARAM_INT, 'The verbal feedback ID.'),
                'questionids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'The question ID.')
                )
            ]
        );
    }

    /**
     * Method results description for set_items().
     *
     * @return external_description
     */
    public static function set_items_returns() {
        return new external_single_structure(
            [
                'result' => new external_value(PARAM_BOOL, 'The processing result.'),
                'warnings' => new external_warnings()
            ]
        );
    }

    /**
>>>>>>> 6b7e2bb (Adding capabilities check in external.php fixes #4.)
     * Parameter description for update_item_multiplier().
     *
     * @return external_function_parameters
     */
    public static function update_item_multiplier_parameters() {
        return new external_function_parameters([
            'itemid' => new external_value(PARAM_INT, 'The id of the item.'),
            'multiplier' => new external_value(PARAM_FLOAT, 'The new multiplier value.')
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
            'multiplier' => $multiplier
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
            'warnings' => $warnings
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
                'warnings' => new external_warnings()
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
            'percentage' => new external_value(PARAM_FLOAT, 'The new percentage value.')
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
            'percentage' => $percentage
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
            'warnings' => $warnings
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
                'warnings' => new external_warnings()
            ]
        );
    }

    /**
     * Function get_question_types().
     *
     */
    public static function get_question_types() {
    }

    /**
     * Parameter description for get_question_types().
     *
     * @return external_function_parameters
     */
    public static function get_question_types_parameters() {
        return new external_function_parameters([]);
    }

    /**
     * Method results description for get_question_types().
     *
     * @return external_description
     */
    public static function get_question_types_returns() {
        return new external_single_structure(
            [
                'questiontypes' => new external_multiple_structure(
                    new external_value(PARAM_TEXT, 'Question type.'),
                    'List of question types.'
                ),
                'warnings' => new external_warnings()
            ]
        );
    }

    /**
     * Function get_question_categories().
     *
     * @param int $verbalfeedbackid The verbal feedback ID
     */
    public static function get_question_categories($verbalfeedbackid) {
    }

    /**
     * Parameter description for get_question_categories().
     *
     * @return external_function_parameters
     */
    public static function get_question_categories_parameters() {
        return new external_function_parameters([
            'verbalfeedbackid' => new external_value(PARAM_INT, 'The verbal feedback ID.')
        ]);
    }

    /**
     * Method results description for get_question_categories().
     *
     * @return external_description
     */
    public static function get_question_categories_returns() {
        return new external_single_structure(
            [
                'questioncategories' => new external_multiple_structure(
                    new external_value(PARAM_TEXT, 'Question category.'),
                    'List of question categories.'
                ),
                'warnings' => new external_warnings()
            ]
        );
    }

    /**
     * Deletes a question item from the verbal feedback activity.
     *
     * @param int $id The item ID.
     * @return array
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws required_capability_exception
     * @throws restricted_context_exception
     */
    public static function delete_item($id) {
        $warnings = [];

        $params = external_api::validate_parameters(self::delete_item_parameters(), ['itemid' => $id]);

        $id = $params['itemid'];

        // Validate context and capability.
        $item = api::get_item_by_id($id);
        $cm = get_coursemodule_from_instance('verbalfeedback', $item->verbalfeedback);
        $cmid = $cm->id;
        $context = context_module::instance($cmid);
        self::validate_context($context);

        require_capability('mod/verbalfeedback:edititems', $context);

        $result = api::delete_item($id);

        return [
            'result' => $result,
            'warnings' => $warnings
        ];
    }

    /**
     * Parameter description for delete_item().
     *
     * @return external_function_parameters
     */
    public static function delete_item_parameters() {
        return new external_function_parameters(
            [
                'itemid' => new external_value(PARAM_INT, 'The item ID.')
            ]
        );
    }

    /**
     * Method results description for delete_item().
     *
     * @return external_description
     */
    public static function delete_item_returns() {
        return new external_single_structure(
            [
                'result' => new external_value(PARAM_BOOL, 'The item deletion processing result.'),
                'warnings' => new external_warnings()
            ]
        );
    }

    /**
     * Move an item up.
     *
     * @param int $id The item ID.
     * @return array
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws required_capability_exception
     * @throws restricted_context_exception
     */
    public static function move_item_up($id) {
        $warnings = [];

        $params = external_api::validate_parameters(self::move_item_up_parameters(), ['itemid' => $id]);

        $id = $params['itemid'];

        // Validate context and capability.
        $item = api::get_item_by_id($id);
        $cm = get_coursemodule_from_instance('verbalfeedback', $item->verbalfeedback);
        $cmid = $cm->id;
        $context = context_module::instance($cmid);
        self::validate_context($context);

        require_capability('mod/verbalfeedback:edititems', $context);

        $result = api::move_item_up($id);
        if (!$result) {
            $warnings[] = 'An error was encountered while trying to move the item up.';
        }

        return [
            'result' => $result,
            'warnings' => $warnings
        ];
    }

    /**
     * Parameter description for move_item_up().
     *
     * @return external_function_parameters
     */
    public static function move_item_up_parameters() {
        return new external_function_parameters(
            [
                'itemid' => new external_value(PARAM_INT, 'The item ID.')
            ]
        );
    }

    /**
     * Method results description for move_item_up().
     *
     * @return external_description
     */
    public static function move_item_up_returns() {
        return new external_single_structure(
            [
                'result' => new external_value(PARAM_BOOL, 'The item deletion processing result.'),
                'warnings' => new external_warnings()
            ]
        );
    }

    /**
     * Move an item down.
     *
     * @param int $id The item ID.
     * @return array
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @throws required_capability_exception
     * @throws restricted_context_exception
     */
    public static function move_item_down($id) {
        $warnings = [];

        $params = external_api::validate_parameters(self::move_item_down_parameters(), ['itemid' => $id]);

        $id = $params['itemid'];

        // Validate context and capability.
        $item = api::get_item_by_id($id);
        $cm = get_coursemodule_from_instance('verbalfeedback', $item->verbalfeedback);
        $cmid = $cm->id;
        $context = context_module::instance($cmid);
        self::validate_context($context);

        require_capability('mod/verbalfeedback:edititems', $context);

        $result = api::move_item_down($id);
        if (!$result) {
            $warnings[] = 'An error was encountered while trying to move the item down.';
        }

        return [
            'result' => $result,
            'warnings' => $warnings
        ];
    }

    /**
     * Parameter description for move_item_down().
     *
     * @return external_function_parameters
     */
    public static function move_item_down_parameters() {
        return new external_function_parameters(
            [
                'itemid' => new external_value(PARAM_INT, 'The item ID.')
            ]
        );
    }

    /**
     * Method results description for move_item_down().
     *
     * @return external_description
     */
    public static function move_item_down_returns() {
        return new external_single_structure(
            [
                'result' => new external_value(PARAM_BOOL, 'The item deletion processing result.'),
                'warnings' => new external_warnings()
            ]
        );
    }

    /**
     * Function decline_feedback().
     *
     * @param int $statusid The submission ID
     * @param string $declinereason The reason for declining the feedback request
     */
    public static function decline_feedback($statusid, $declinereason) {
    }

    /**
     * Parameter description for decline_feedback().
     *
     * @return external_function_parameters
     */
    public static function decline_feedback_parameters() {
        return new external_function_parameters(
            [
                'statusid' => new external_value(PARAM_INT, 'The submission ID.'),
                'declinereason' => new external_value(PARAM_TEXT, 'The reason for declining the feedback request.', VALUE_DEFAULT)
            ]
        );
    }

    /**
     * Method results description for decline_feedback().
     *
     * @return external_description
     */
    public static function decline_feedback_returns() {
        return new external_single_structure(
            [
                'result' => new external_value(PARAM_BOOL, 'The item deletion processing result.'),
                'warnings' => new external_warnings()
            ]
        );
    }

    /**
     * Function undo_decline().
     *
     * @param int $statusid The submission ID
     */
    public static function undo_decline($statusid) {
    }

    /**
     * Parameter description for undo_decline().
     *
     * @return external_function_parameters
     */
    public static function undo_decline_parameters() {
        return new external_function_parameters(
            [
                'statusid' => new external_value(PARAM_INT, 'The submission ID.'),
            ]
        );
    }

    /**
     * Method results description for undo_decline().
     *
     * @return external_description
     */
    public static function undo_decline_returns() {
        return new external_single_structure(
            [
                'result' => new external_value(PARAM_BOOL, 'The processing result.'),
                'warnings' => new external_warnings()
            ]
        );
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
            'verbalfeedbackid' => $verbalfeedbackid
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
            'warnings' => $warnings
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
                            'undodeclinelink' => new external_value(PARAM_BOOL, 'Flag for the undo decline button.', VALUE_OPTIONAL,
                                false),
                        ]
                    )
                ),
                'warnings' => new external_warnings()
            ]
        );
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
                            'privatecomment' => new external_value(PARAM_RAW, 'The response private comment.', VALUE_OPTIONAL, '')
                        ], 'item to save', VALUE_OPTIONAL
                    ), 'item collection to save', VALUE_OPTIONAL, null
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
        global $USER;
        $warnings = [];
        $cm = get_coursemodule_from_instance('verbalfeedback', $verbalfeedbackid);
        $cmid = $cm->id;
        $context = context_module::instance($cmid);
        self::validate_context($context);

        require_capability('mod/verbalfeedback:can_respond', $context);

        $redirecturl = new \moodle_url('/mod/verbalfeedback/view.php');
        $redirecturl->param('id', $cmid);

        $params = external_api::validate_parameters(self::save_responses_parameters(), [
            'verbalfeedbackid' => $verbalfeedbackid,
            'submissionid' => $submissionid,
            'touserid' => $touserid,
            'responses' => $responses,
            'complete' => $complete
        ]);

        $verbalfeedbackid = $params['verbalfeedbackid'];
        $submissionid = $params['submissionid'];
        $touserid = $params['touserid'];
        $responses = $params['responses'];
        $complete = $params['complete'];

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
            'warnings' => $warnings
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
                'warnings' => new external_warnings()
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
        $warnings = [];

        $cm = get_coursemodule_from_instance('verbalfeedback', $verbalfeedbackid);
        $cmid = $cm->id;
        $context = context_module::instance($cmid);
        self::validate_context($context);

        if (has_capability('mod/verbalfeedback:view_all_reports', $context) ||
            has_capability('mod/verbalfeedback:receive_rating', $context)) {
            $redirecturl = new \moodle_url('/mod/verbalfeedback/view.php');
            $redirecturl->param('id', $cmid);

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

            $submissionrepo = new submission_repository();
            $submission = $submissionrepo->get_by_id($submissionid);

            $responses = [];
            foreach ($submission->get_responses() as $response) {
                $viewmodel = [];
                $viewmodel['id'] = $response->get_id();
                $viewmodel['criterionid'] = $response->get_criterion_id();
                $viewmodel['value'] = $response->get_value();
                $viewmodel['studentcomment'] = $response->get_student_comment();
                $viewmodel['privatecomment'] = $response->get_private_comment();
                $responses[] = $viewmodel;
            }

            return [
                'responses' => $responses,
                'redirurl' => $redirecturl->out(),
                'warnings' => $warnings
            ];
        }
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
                            'privatecomment' => new external_value(PARAM_RAW, 'The response private comment.')
                        ]
                    )
                ),
                'warnings' => new external_warnings()
            ]
        );
    }
}
