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
 * Class containing data for a verbal feedback response.
 *
 * @package    mod_verbalfeedback
 * @copyright  2021 Kevin Tippenhauer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_verbalfeedback\model;

/**
 * The response class
 */
class response {
    /** @var int The id */
    public $id;
    /** @var int The instance id */
    public $instanceid;
    /** @var int The criterion id */
    public $criterionid;
    /** @var int The rating user */
    public $fromuserid;
    /** @var int The id of the rated user */
    public $touserid;
    /** @var int|null The response value */
    public $value;
    /** @var string The student comment */
    public $studentcomment;
    /** @var string The private comment */
    public $privatecomment;

    /**
     * The response class constructor
     *
     * @param int $id
     * @param int $instanceid
     * @param int $criterionid
     * @param int $fromuserid
     * @param int $touserid
     * @param int|null $value
     * @param string $studentcomment
     * @param string $privatecomment
     */
    public function __construct(
        int $id = 0,
        int $instanceid = 0,
        int $criterionid = 0,
        int $fromuserid = 0,
        int $touserid = 0,
        ?int $value = null,
        string $studentcomment = "",
        string $privatecomment = ""
    ) {
        $this->id = $id;
        $this->instanceid = $instanceid;
        $this->criterionid = $criterionid;
        $this->fromuserid = $fromuserid;
        $this->touserid = $touserid;
        $this->value = $value;
        $this->studentcomment = $studentcomment;
        $this->privatecomment = $privatecomment;
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
     * Gets the id.
     *
     * @return int The id.
     */
    public function get_id(): int {
        return $this->id;
    }

    /**
     * Sets the instance id.
     *
     * @param int $instanceid The instance id.
     */
    public function set_instance_id(int $instanceid) {
        $this->instanceid = $instanceid;
    }

    /**
     * Gets the instance id.
     *
     * @return int The instance id.
     */
    public function get_instance_id(): int {
        return $this->instanceid;
    }

    /**
     * Sets the criterion id.
     *
     * @param int $criterionid The criterion id.
     */
    public function set_criterion_id(int $criterionid) {
        $this->criterionid = $criterionid;
    }

    /**
     * Gets the criterion id.
     *
     * @return int The criterion id.
     */
    public function get_criterion_id(): int {
        return $this->criterionid;
    }

    /**
     * Sets the user id of the responding user.
     *
     * @param int $fromuserid The id of the responding user.
     */
    public function set_from_user_id(int $fromuserid) {
        $this->fromuserid = $fromuserid;
    }

    /**
     * Gets the user id of the responding user.
     *
     * @return int The id of the responding user.
     */
    public function get_from_user_id(): int {
        return $this->fromuserid;
    }

    /**
     * Sets the user id of the rated user.
     *
     * @param int $touserid The id of the rated user.
     */
    public function set_to_user_id(int $touserid) {
        $this->touserid = $touserid;
    }

    /**
     * Gets the user id of the rated user.
     *
     * @return int The id of the rated user.
     */
    public function get_to_user_id(): int {
        return $this->touserid;
    }

    /**
     * Sets the response value.
     *
     * @param ?int $value The response value.
     */
    public function set_value(?int $value) {
        $this->value = $value;
    }

    /**
     * Gets the response value.
     *
     * @return ?int The response value.
     */
    public function get_value(): ?int {
        return $this->value;
    }

    /**
     * Sets the comment for the student.
     *
     * @param string $comment The comment.
     */
    public function set_student_comment(string $comment) {
        $this->studentcomment = $comment;
    }

    /**
     * Gets the comment for the student.
     *
     * @return string The comment.
     */
    public function get_student_comment(): string {
        return $this->studentcomment;
    }

    /**
     * Sets the private user comment.
     *
     * @param string $comment The comment.
     */
    public function set_private_comment(string $comment) {
        $this->privatecomment = $comment;
    }

    /**
     * Gets the private user comment.
     *
     * @return string The comment.
     */
    public function get_private_comment(): string {
        return $this->privatecomment;
    }
}
