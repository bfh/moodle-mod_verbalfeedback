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
 * Helper functions for the verbal feedback activity module.
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_verbalfeedback;
use Exception;
use mod_verbalfeedback\model\instance_category;
use moodle_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/weblib.php');

/**
 * Class containing helper functions for the verbal feedback activity module.
 *
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {

    /**
     * Gets the localised string value of a status code.
     *
     * @param int $status
     * @return string
     */
    public static function get_status_string($status) {
        switch ($status) {
            case api::STATUS_PENDING: // Pending.
                return get_string('statuspending', 'mod_verbalfeedback');
            case api::STATUS_IN_PROGRESS: // In Progress.
                return get_string('statusinprogress', 'mod_verbalfeedback');
            case api::STATUS_COMPLETE: // Completed.
                return get_string('statuscompleted', 'mod_verbalfeedback');
            case api::STATUS_DECLINED: // Declined.
                return get_string('statusdeclined', 'mod_verbalfeedback');
            default:
                throw new moodle_exception('errorinvalidstatus', 'mod_verbalfeedback');
        }
    }

    /**
     * Gets the localised string value of a question type code.
     *
     * @param int $type The question type numeric equivalent
     * @return string The string equivalent of the question type.
     * @throws \coding_exception
     */
    public static function get_question_type_text($type) {
        switch ($type) {
            case api::QTYPE_RATED:
                return get_string('qtyperated', 'verbalfeedback');
            case api::QTYPE_COMMENT:
                return get_string('qtypecomment', 'verbalfeedback');
            default:
                return '';
        }
    }

    /**
     * This populates items in categories.
     *
     * @param array $categories List of items with the average rating/comments given to the user.
     * @return array List of items with the average rating/comments given to the user.
     */
    public static function prepare_items_view($categories) {
        $currentlanguage = current_language();
        $viewmodel = array();
        foreach ($categories as $category) { /** @var instance_category $category */
            $categoryviewmodel = new stdClass();
            $categoryviewmodel->header = $category->get_header($currentlanguage)->get_string();
            $categoryviewmodel->id = $category->get_id();
            $categoryviewmodel->criteria = array();
            $categoryviewmodel->position = $category->get_position();
            $categoryviewmodel->weight = number_format($category->get_weight(), 2);

            foreach ($category->get_criteria() as $criterion) {
                $criterionviewmodel = new stdClass();
                $criterionviewmodel->categoryid = $category->get_id();
                $criterionviewmodel->id = $criterion->get_id();
                $criterionviewmodel->position = $criterion->get_position();
                $criterionviewmodel->weight = number_format($criterion->get_weight(), 2);
                $criterionviewmodel->text = $criterion->get_description($currentlanguage)->get_string();

                foreach ($criterion->get_subratings() as $subrating) {
                    $subratingviewmodel = new stdClass();

                    $subratingviewmodel->id = $subrating->get_id();
                    $subratingviewmodel->title = $subrating->get_title($currentlanguage)->get_string();
                    $subratingviewmodel->description = $subrating->get_description($currentlanguage)->get_string();
                    $subratingviewmodel->verynegative = $subrating->get_verynegative($currentlanguage)->get_string();
                    $subratingviewmodel->negative = $subrating->get_negative($currentlanguage)->get_string();
                    $subratingviewmodel->positive = $subrating->get_positive($currentlanguage)->get_string();
                    $subratingviewmodel->verypositive = $subrating->get_verypositive($currentlanguage)->get_string();

                    $criterionviewmodel->subratings[] = $subratingviewmodel;
                }

                $categoryviewmodel->criteria[] = $criterionviewmodel;
            }

            $viewmodel[] = $categoryviewmodel;
        }
        return $viewmodel;
    }
}
