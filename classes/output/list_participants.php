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
 * Class containing data for users that need to be given with verbalfeedback.
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_verbalfeedback\output;

use coding_exception;
use mod_verbalfeedback\api;
use moodle_url;
use renderable;
use renderer_base;
use stdClass;
use templatable;
use core_course\output\actionbar\user_selector;
use core_course\output\actionbar\group_selector;

/**
 * Class containing data for users that need to be given with verbalfeedback.
 *
 * @copyright  2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class list_participants implements renderable, templatable {
    /** @var stdClass The verbalfeedback instance. */
    protected $verbalfeedback;

    /** @var int The user ID of the respondent. */
    protected $userid;

    protected $groupid;

    protected $course;

    protected $cm;

    /** @var array The array of participants for the verbalfeedback, excluding the respondent. */
    protected $participants = [];

    /** @var bool Whether the user has the capability to view reports. */
    protected $canviewreports = false;

    /** @var bool Whether the instance is open for participants to interact with. */
    protected $isopen = false;

    /** @var array The filter parameters. */
    protected $filter = [];

    /**
     * list_participants constructor.
     * @param stdClass $verbalfeedback The verbalfeedback instance.
     * @param int $userid The respondent's user ID.
     * @param array $participants The array of participants for the verbalfeedback, excluding the respondent.
     * @param bool $canviewreports Whether the user has the capability to view reports.
     * @param bool $isopen Whether the instance is open for participants to interact with.
     */
    public function __construct($verbalfeedback, $userid, $participants, $canviewreports = false, $isopen = false) {
        $this->userid = $userid;
        $this->verbalfeedback = $verbalfeedback;
        $this->participants = $participants;
        $this->canviewreports = $canviewreports;
        $this->isopen = $isopen;
        $this->cm = get_coursemodule_from_instance('verbalfeedback', $this->verbalfeedback->id);
        $this->course = get_course($this->cm->course);
        $this->groupid = groups_get_course_group($this->course, true);
    }

    public function set_filter(array $filter) {
        $this->filter = $filter;
        return $this;
    }

    protected function get_action_menu(renderer_base $output): array {
        global $PAGE;
        $data = [];
        $userid = optional_param('userid', null, PARAM_INT);
        $usersearch = $userid ? fullname(\core_user::get_user($userid)) : optional_param('search', '', PARAM_NOTAGS);
        $resetlink = new moodle_url('/mod/verbalfeedback/view.php', ['id' => $this->verbalfeedback->id]);
        $groupid = groups_get_course_group($this->course, true);
        $userselector = new user_selector(
            course: $this->course,
            resetlink: $resetlink,
            userid: $userid,
            groupid: $groupid,
            usersearch: $usersearch,
            instanceid: $this->verbalfeedback->id
        );
        $data['userselector'] = $userselector->export_for_template($output);

        if (groups_get_activity_groupmode($this->cm, $this->course)) {
            $gs = new group_selector($PAGE->context, false);
            $data['groupselector'] = $gs->export_for_template($output);
        }
        return $data;
    }

    /**
     * Function to export the renderer data in a format that is suitable for a
     * mustache template. This means:
     * 1. No complex types - only stdClass, array, int, string, float, bool
     * 2. Any additional info that is required for the template is pre-calculated (e.g. capability checks).
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return stdClass|array
     * @throws coding_exception
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $data->verbalfeedbackid = $this->verbalfeedback->id;
        $data->courseid = $this->course->id;
        $data->participants = [];
        $data->canperformactions = $this->isopen;
        foreach ($this->get_action_menu($output) as $key => $value) {
            $data->$key = $value;
        }

        $viewfullnames = has_capability('moodle/site:viewfullnames', \context_module::instance($this->cm->id));

        foreach ($this->participants as $user) {
            $member = new stdClass();

            // User ID, email and name column.
            $member->userid = $user->userid;
            $member->email = $user->email;
            $member->name = fullname($user, $viewfullnames);
            $member->link = (new \moodle_url('/user/view.php', ['id' => $member->userid, 'course' => $this->course->id]))->out(false);
            //$member->picture = $output->user_picture($user, ['size' => 35, 'courseid' => $this->course->id]);

            // Status column.
            // By default the user viewing the participants page can respond if there's a submission record.
            $canrespond = !empty($user->submissionid);
            if ($canrespond) {
                switch ($user->submissionstatus) {
                    case api::STATUS_IN_PROGRESS: // In Progress.
                        $member->statusinprogress = true;
                        break;
                    case api::STATUS_COMPLETE: // Completed.
                        $member->statuscompleted = true;
                        break;
                    case api::STATUS_DECLINED: // Declined.
                        $member->statusdeclined = true;
                        // If declined, user won't be able to respond anymore.
                        $canrespond = false;
                        if ($this->verbalfeedback->undodecline == api::UNDO_DECLINE_ALLOW) {
                            $member->undodeclinelink = true;
                        }
                        break;
                    default:
                        $member->statuspending = true;
                        break;
                }

                $member->submissionid = $user->submissionid;
            }

            // Action buttons column.
            // View action.
            $member->reportslink = false;
            if ($this->canviewreports) {
                // When the user can't provide feedback to the participants but can view reports.
                if (empty($user->submissionid)) {
                    $member->statusviewonly = true;
                }
                $reportslink = new moodle_url('/mod/verbalfeedback/report.php');
                $reportslink->params([
                    'instance' => $this->verbalfeedback->id,
                    'touser' => $user->userid,
                ]);
                $member->reportslink = $reportslink->out();
            }

            // Show action buttons depending on status.
            if ($canrespond) {
                $respondurl = new moodle_url('/mod/verbalfeedback/questionnaire.php');
                $respondurl->params([
                    'instance' => $this->verbalfeedback->id,
                    'submission' => $user->submissionid,
                ]);
                $member->respondlink = $respondurl->out();
            }

            $data->participants[] = $member;
        }

        return $data;
    }
}
