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
 * Class containing data for a verbal feedback instance.
 *
 * @package    mod_verbalfeedback
 * @copyright  2021 Kevin Tippenhauer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_verbalfeedback\model;

use mod_verbalfeedback\model\template\parametrized_template_category;
use mod_verbalfeedback\model\template\template;

/**
 * The instance class
 */
class instance {
    /**
     * @var int The id.
     */
    public $id;
    /**
     * @var int The template id.
     */
    public $templateid;
    /**
     * @var int  The course id.
     */
    public $course;
    /**
     * @var string The instance name.
     */
    public $name;
    /**
     * @var string The intro.
     */
    private $intro;
    /**
     * @var int The intro format
     */
    private $introformat;
    /**
     * @var int The max grade.
     */
    public $grade;
    /**
     * @var int The grade scale.
     */
    public $gradescale;
    /**
     * @var int The grade category.
     */
    public $gradecat;
    /**
     * @var float The pass grade.
     */
    public $gradepass;
    /**
     * @var int The instance status.
     */
    public $status;
    /**
     * @var int The opening time.
     */
    private $timeopen;
    /**
     * @var int The closing time.
     */
    private $timeclose;
    /**
     * @var int The time of the last modification.
     */
    private $timemodified;
    /**
     * @var int The releasetype property value.
     */
    private $releasetype;
    /**
     * @var int The released property value.
     */
    private $released;
    /**
     * @var array The categories.
     */
    public $categories = array();

    /**
     * Class constructor
     *
     * @param int $course The course id.
     * @param int $id The id.
     * @param string $name The instance name.
     * @param string $intro The intro.
     * @param int $introformat The intro format.
     * @param int $grade The max grade.
     * @param int $gradecat The grade category.
     * @param float $gradepass The pass grade.
     * @param int $status The instance status.
     * @param int $timeopen The opening time.
     * @param int $timeclose The closing time.
     * @param int $timemodified The time of the last modification.
     * @param int $releasetype The releasetype property value.
     * @param int $released The released property value.
     */
    public function __construct(int $course, int $id = 0, string $name = "", string $intro = "", int $introformat = 0,
        int $grade = 100, int $gradecat = 0, float $gradepass = 0, int $status = instance_status::NOT_READY,
        int $timeopen = 0, int $timeclose = 0, int $timemodified = 0, int $releasetype = instance_release_type::NONE,
        int $released = 0) {
        $this->set_id($id);
        $this->set_template_id(null);
        $this->set_course($course);
        $this->set_name($name);
        $this->set_intro($intro);
        $this->set_introformat($introformat);
        $this->set_grade($grade);
        $this->set_gradecat($gradecat);
        $this->set_gradepass($gradepass);
        $this->set_status($status);
        $this->set_timeopen($timeopen);
        $this->set_timeclose($timeclose);
        $this->set_timemodified($timemodified);
        $this->set_release_type($releasetype);
        $this->set_released($released);
    }

    /**
     * Build a verbal feedback from template
     *
     * @param int $course The course id.
     * @param template $template The template id.
     * @param int $id The id.
     * @param string $name The instance name.
     * @param string $intro The intro.
     * @param int $introformat The intro format.
     * @param int $gradecat The grade category.
     * @param int $grade The max grade.
     * @param float $gradepass The pass grade.
     * @param int $status The instance status.
     * @param int $timeopen The opening time.
     * @param int $timeclose The closing time.
     * @param int $timemodified The closing time.
     * @param int $releasetype The releasetype property value.
     * @param int $released The released property value.
     * @return instance
     */
    public static function from_template(int $course, template $template, int $id = 0, string $name = "", string $intro = "",
        int $introformat = 0, int $gradecat = 0, int $grade = 0, float $gradepass = 0,
        int $status = instance_status::NOT_READY, int $timeopen = 0, int $timeclose = 0, int $timemodified = 0,
        int $releasetype = instance_release_type::NONE, int $released = 0) : instance {

        $instance = new instance($course, $id, $name, $intro, $introformat, $grade, $gradecat, $gradepass,
            $status, $timeopen, $timeclose, $timemodified, $releasetype, $released);

        if ($template !== null) {
            foreach ($template->get_template_categories() as $templatecategory) {
                $instance->add_copy_of_template_category($templatecategory);
            }
        }
        $instance->set_template_id($template->get_id());
        return $instance;
    }

    /**
     * Gets the id.
     *
     * @return int The id.
     */
    public function get_id() : int {
        return $this->id;
    }

    /**
     * Sets the id.
     *
     * @param int $id The id.
     */
    public function set_id(int $id) {
        $this->id = $id;
    }

    /**
     * Gets the template id.
     *
     * @return int|null The template id.
     */
    public function get_template_id() : ?int {
        return $this->templateid;
    }

    /**
     * Sets the template id. Does not apply the template.
     *
     * @param int|null $templateid The template id.
     */
    public function set_template_id(?int $templateid) {
        $this->templateid = $templateid;
    }

    /**
     * Gets the course id.
     *
     * @return int The course id.
     */
    public function get_course() : int {
        return $this->course;
    }

    /**
     * Sets the course id.
     *
     * @param int $course The course id.
     */
    public function set_course(int $course) {
        $this->course = $course;
    }

    /**
     * Gets the instance name.
     *
     * @return string The instance name.
     */
    public function get_name() : string {
        return $this->name;
    }

    /**
     * Sets the instance name.
     *
     * @param string $name The instance name.
     */
    public function set_name(string $name) {
        $this->name = $name;
    }

    /**
     * Gets the intro.
     *
     * @return string The intro.
     */
    public function get_intro() : string {
        return $this->intro;
    }

    /**
     * Sets the intro.
     *
     * @param string $intro The intro.
     */
    public function set_intro(string $intro) {
        $this->intro = $intro;
    }

    /**
     * Gets the introformat.
     *
     * @return int The introformat.
     */
    public function get_introformat() : int {
        return $this->introformat;
    }

    /**
     * Sets the introformat.
     *
     * @param int $introformat The introformat.
     */
    public function set_introformat(int $introformat) {
        $this->introformat = $introformat;
    }

    /**
     * Gets the max grade.
     *
     * @return int The max grade.
     */
    public function get_grade() : int {
        global $DB;
        return $DB->get_field('verbalfeedback', 'grade', array('id' => $this->id));
    }

    /**
     * Sets the max grade.
     *
     * @param int $grade The max grade.
     */
    public function set_grade(int $grade) {
        $this->grade = $grade;
    }

    /**
     * Gets the grade category.
     *
     * @return int The grade category.
     */
    public function get_gradecat() : int {
        return $this->gradecat;
    }

    /**
     * Sets the grade category.
     *
     * @param int $gradecat The grade category.
     */
    public function set_gradecat(int $gradecat) {
        $this->gradecat = $gradecat;
    }

    /**
     * Gets the pass grade.
     *
     * @return float The pass grade.
     */
    public function get_gradepass() : float {
        return $this->gradepass;
    }

    /**
     * Sets the pass grade.
     *
     * @param float $gradepass The pass grade.
     */
    public function set_gradepass(float $gradepass) {
        $this->gradepass = $gradepass;
    }

    /**
     * Gets the grade scale.
     *
     * @return int The grade scale.
     */
    public function get_gradescale() : int {
        return $this->gradescale;
    }

    /**
     * Sets the max grade.
     *
     * @param int $gradescale The grade scale.
     */
    public function set_gradescale(int $gradescale) {
        $this->gradescale = $gradescale;
    }

    /**
     * Gets the instance status.
     *
     * @return int The instance status.
     */
    public function get_status() : int {
        return $this->status;
    }

    /**
     * Sets the instance status.
     *
     * @param int $status The instance status.
     */
    public function set_status(int $status) {
        $this->status = $status;
    }

    /**
     * Gets the opening time.
     *
     * @return int The opening time.
     */
    public function get_timeopen() : int {
        return $this->timeopen;
    }

    /**
     * Sets the opening time.
     *
     * @param int $timeopen The opening time.
     */
    public function set_timeopen(int $timeopen) {
        $this->timeopen = $timeopen;
    }

    /**
     * Gets the closing time.
     *
     * @return int The closing time.
     */
    public function get_timeclose() : int {
        return $this->timeclose;
    }

    /**
     * Sets the closing time.
     *
     * @param int $timeclose The closing time.
     */
    public function set_timeclose(int $timeclose) {
        $this->timeclose = $timeclose;
    }

    /**
     * Gets the time of the last modification.
     *
     * @return int The time of the last modification.
     */
    public function get_timemodified() : int {
        return $this->timemodified;
    }

    /**
     * Sets the time of the last modification.
     *
     * @param int $timemodified The time of the last modification.
     */
    public function set_timemodified(int $timemodified) {
        $this->timemodified = $timemodified;
    }

    /**
     * Gets the releasetype property value, which defines whether to release this feedback to the participants.
     * 0 - Closed to participants. Participants cannot view the feedback given to them.
     * 1 - Open to participants. Participants can view the feedback given to them any time.
     * 2 - Manual release. Participants can view the feedback given to them when released by users
     * with the capability to manage the verbal feedback activity instance (e.g. teacher, manager, admin).
     * 3 - Release after the activity has closed.
     *
     * @return int The releasetype property value.
     */
    public function get_release_type() : int {
        return $this->releasetype;
    }

    /**
     * Sets the releasetype property value, which defines whether to release this feedback to the participants
     * 0 - Closed to participants. Participants cannot view the feedback given to them.
     * 1 - Open to participants. Participants can view the feedback given to them any time.
     * 2 - Manual release. Participants can view the feedback given to them when released by users
     * with the capability to manage the verbal feedback activity instance (e.g. teacher, manager, admin).
     * 3 - Release after the activity has closed.
     *
     * @param int $releasetype The releasetype property value.
     */
    public function set_release_type(int $releasetype) {
        $this->releasetype = $releasetype;
    }

    /**
     * Gets the released property value. For instances that are manually released. 0 = Not yet released. 1 = Released.
     *
     * @return int The released property value.
     */
    public function get_released() : int {
        return $this->released;
    }

    /**
     * Sets the released property value. For instances that are manually released. 0 = Not yet released. 1 = Released.
     *
     * @param int $released The released property value.
     */
    public function set_released(int $released) {
        $this->released = $released;
    }

    /**
     * Gets the categories.
     *
     * @return array<int, instance_category> The categories.
     */
    public function get_categories() : array {
        return $this->categories;
    }

    /**
     * Adds a category.
     *
     * @param instance_category $category
     */
    public function add_category(instance_category $category) {
        $this->categories[] = $category;
    }

    /**
     * Adds a copy of a template category.
     *
     * @param parametrized_template_category $category
     */
    public function add_copy_of_template_category(parametrized_template_category $category) {
        $instancecategory = instance_category::from_template($category);
        $this->add_category($instancecategory);
    }

    /**
     * Returns if a category has items.
     *
     * @return bool
     */
    public function has_items() {
        if (count($this->categories) > 0) {
            return true;
        }
        return false;
    }

    /**
     * Returns if the verbal feedback is ready.
     *
     * @return bool
     */
    public function is_ready() {
        // Check if this instance already has items.
        if (!$this->has_items()) {
            // An instance is not yet ready if doesn't have any item yet.
            return false;
        }
        return $this->get_status() == instance_status::READY;
    }

    /**
     * Checks the availability of the instance based on the open and close times of the activity.
     *
     * @param bool $messagewhenclosed Whether to return a message when the instance is not yet open.
     * @return bool|string
     */
    public function is_open($messagewhenclosed = false) {
        // If there's open and close times are not defined, instance is open.
        if (empty($this->get_timeopen()) && empty($this->get_timeclose())) {
            return true;
        }

        $now = time();
        // If there's open time is before the current time, instance is not yet open.
        if (!empty($this->get_timeopen()) && $this->get_timeopen() > $now) {
            if ($messagewhenclosed) {
                return get_string('instancenotyetopen', 'verbalfeedback', userdate($this->get_timeopen()));
            } else {
                return false;
            }
        }

        // If there's close time is after the current time, instance is not yet open.
        if (!empty($this->get_timeclose()) && $this->get_timeclose() <= $now) {
            if ($messagewhenclosed) {
                return get_string('instancealreadyclosed', 'verbalfeedback');
            } else {
                return false;
            }
        }
        // All good, instance is open.
        return true;
    }

    /**
     * Whether the release mechanism of the instance was triggered.
     *
     * @return bool
     */
    public function reports_are_released() {
        switch ($this->releasetype) {
            case instance_release_type::OPEN:
                return true;
            case instance_release_type::MANUAL:
                return $this->released;
            case instance_release_type::AFTER:
                return $this->timeclose < time();
            default:
                return false;
        }
    }
}
