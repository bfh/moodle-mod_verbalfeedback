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
 * @package    mod_verbalfeedback
 * @copyright  2021 Kevin Tippenhauer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_verbalfeedback\repository\model;

use mod_verbalfeedback\model\instance;

/**
 * The database instance class
 */
class db_instance {
    /**
     * @var int
     */
    public $id = 0;
    /**
     * @var
     */
    public $templateid;
    /**
     * @var
     */
    public $name;
    /**
     * @var
     */
    public $course;
    /**
     * @var
     */
    public $intro;
    /**
     * @var
     */
    public $introformat;
    /**
     * @var
     */
    public $status;
    /**
     * @var
     */
    public $timeopen;
    /**
     * @var
     */
    public $timeclose;
    /**
     * @var
     */
    public $timemodified;
    /**
     * @var
     */
    public $releasetype;
    /**
     * @var
     */
    public $released;

    /**
     * Return a instance database object
     *
     * @param instance $instance
     * @return db_instance
     */
    public static function from_instance(instance $instance) {
        $dbo = new db_instance();
        $dbo->id = $instance->get_id();
        $dbo->templateid = $instance->get_template_id();
        $dbo->name = $instance->get_name();
        $dbo->course = $instance->get_course();
        $dbo->intro = $instance->get_intro();
        $dbo->introformat = $instance->get_introformat();
        $dbo->status = $instance->get_status();
        $dbo->timeopen = $instance->get_timeopen();
        $dbo->timeclose = $instance->get_timeclose();
        $dbo->timemodified = $instance->get_timemodified();
        $dbo->releasetype = $instance->get_release_type();
        $dbo->released = $instance->get_released();
        return $dbo;
    }

    /**
     * Returns a instance when a database object given
     *
     * @param object $dbo The database object
     * @return instance
     * @throws \Exception
     */
    public static function to_instance($dbo) : instance {
        if (!isset($dbo->course) || $dbo->course == null) {
            throw new \Exception('Missing $dbo->course.');
        }

        $instance = new instance($dbo->course);

        if (isset($dbo->id)) {
            $instance->set_id($dbo->id);
        }
        if (isset($dbo->templateid)) {
            $instance->set_template_id($dbo->templateid);
        }
        if (isset($dbo->name)) {
            $instance->set_name($dbo->name);
        }
        if (isset($dbo->intro)) {
            $instance->set_intro($dbo->intro);
        }
        if (isset($dbo->introformat)) {
            $instance->set_introformat($dbo->introformat);
        }
        if (isset($dbo->status)) {
            $instance->set_status($dbo->status);
        }
        if (isset($dbo->timeopen)) {
            $instance->set_timeopen($dbo->timeopen);
        }
        if (isset($dbo->timeclose)) {
            $instance->set_timeclose($dbo->timeclose);
        }
        if (isset($dbo->timemodified)) {
            $instance->set_timemodified($dbo->timemodified);
        }
        if (isset($dbo->releasetype)) {
            $instance->set_release_type($dbo->releasetype);
        }
        if (isset($dbo->released)) {
            $instance->set_released($dbo->released);
        }
        return $instance;
    }
}
