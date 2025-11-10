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
 * Class containing data for a verbal feedback submission.
 *
 * @package    mod_verbalfeedback
 * @copyright  2021 Kevin Tippenhauer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_verbalfeedback\model;

/**
 * The submission class
 */
class submission {
    /** @var int The submission id */
    public $id;
    /** @var int The instance id */
    public $instanceid;
    /** @var int The rating user */
    public $fromuserid;
    /** @var int The id of the rated user */
    public $touserid;
    /** @var int The submission status */
    public $status;
    /** @var string The submission remarks */
    public $remarks;
    /** @var array The submission responses */
    public $responses = [];

    /**
     * The submission class constructor
     *
     * @param int $id The submission id
     * @param int $instanceid The instance id
     * @param int $fromuserid The rating user
     * @param int $touserid The id of the rated user
     * @param int $status The submission status
     * @param string $remarks The submission remarks
     * @param array $responses The submission responses
     */
    public function __construct(
        int $id = 0,
        int $instanceid = 0,
        int $fromuserid = 0,
        int $touserid = 0,
        int $status = submission_status::PENDING,
        string $remarks = "",
        array $responses = []
    ) {
        $this->id = $id;
        $this->instanceid = $instanceid;
        $this->fromuserid = $fromuserid;
        $this->touserid = $touserid;
        $this->status = $status;
        $this->remarks = $remarks;
        $this->responses = $responses;
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
     * Sets the status.
     *
     * @param int $status The status.
     */
    public function set_status(int $status) {
        $this->status = $status;
    }

    /**
     * Gets the status.
     *
     * @return int The status.
     */
    public function get_status(): int {
        return $this->status;
    }

    /**
     * Sets the remarks.
     *
     * @param string $remarks The remarks.
     */
    public function set_remarks(string $remarks) {
        $this->remarks = $remarks;
    }

    /**
     * Gets the remarks.
     *
     * @return string The remarks.
     */
    public function get_remarks(): string {
        return $this->remarks;
    }

    /**
     * Gets the responses.
     *
     * @return array<int, response> The responses.
     */
    public function get_responses(): array {
        return $this->responses;
    }

    /**
     * Adds a response.
     * @param response $response The response.
     */
    public function add_response(response $response) {
        $this->responses[] = $response;
    }
}
