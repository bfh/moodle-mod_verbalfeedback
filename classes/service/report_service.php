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

namespace mod_verbalfeedback\service;

use mod_verbalfeedback\model\instance;
use mod_verbalfeedback\model\report;
use mod_verbalfeedback\model\report_category;
use mod_verbalfeedback\model\report_criterion;
use mod_verbalfeedback\repository\instance_repository;
use mod_verbalfeedback\repository\submission_repository;

/**
 * The report service class for managing user reports
 *
 * @package    mod_verbalfeedback
 * @copyright  2020 Kevin Tippenhauer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_service {
    /** @var instance_repository The instance repository */
    private $instancerepo;
    /** @var submission_repository The submission repository */
    private $submissionrepo;
    /** @var instance The verbal feedback instance */
    private $instance;
    /** @var int user id of the user that the report is created for (e.g. the student). */
    private $touserid;
    /** @var int user id of the user that created the report (e.g. the teacher). */
    private $fromuserid;
    /** @var array users that act as the reviewers and have completed the review. */
    private $reviewers;

    /**
     * The class constructor
     * @param int $touserid the user id for which the report is generated.
     * @param int $instanceid the id of the verbal feedback instance.
     */
    public function __construct(int $touserid, int $instanceid) {
        if ($touserid <= 0) {
            throw new \moodle_exception('No userid set for report');
        }
        $this->touserid = (int)$touserid;
        $this->instancerepo = new instance_repository();
        $this->submissionrepo = new submission_repository();
        $this->instance = $this->instancerepo->get_by_id($instanceid);
        $this->reviewers = [];
    }

    /**
     * Set a from user id, when the report of a certain reviewer is requested.
     * @param int $fromuserid
     * @return self
     */
    public function set_fromuserid(int $fromuserid): self {
        $this->fromuserid = $fromuserid;
        return $this;
    }

    /**
     * Return the userid of the reviewer when the report of a single reviewer is requested.
     * @return int
     */
    public function get_fromuserid(): int {
        return (int)$this->fromuserid;
    }

    /**
     * Return the verbal feedback instance.
     * @return instance
     */
    public function get_instance(): instance {
        return $this->instance;
    }

    /**
     * Get reviewers when needed outside the report, after validation.
     * @return array
     */
    public function get_reviewers(): array {
        return $this->reviewers ?? [];
    }

    /**
     * Validate that the requested report can actually be viewd. This function may reset
     * the fromuserid to 0 when the user is not allowed to filter the reviewers, e.g. a
     * student or non editing teacher.
     * @param \core\context $context
     * @return self
     * @throws \moodle_exception
     */
    public function validate(\core\context $context): self {
        global $USER;
        // Am I the student?
        $viewownreport = $this->touserid === (int)$USER->id;
        // Can I see all reports, e.g. teacher?
        $canviewall = has_capability('mod/verbalfeedback:view_all_reports', $context);

        if (!$viewownreport) {
            // I can not see all reports, then I must have at least the "reviewer" capability.
            require_capability('mod/verbalfeedback:can_respond', $context);
        } else if (!$this->instance->reports_are_released() && !$canviewall) {
            throw new \moodle_exception('errorreportnotavailable', 'mod_verbalfeedback');
        }

        // Only users who are allowed to view all reports may break down the feedback by a single reviewer.
        // For everyone else (e.g. the rated user looking at their own report) the feedback stays combined.
        // and listing all reviewers.
        $this->reviewers = [];
        if ($canviewall) {
            $reviewerids = $this->submissionrepo->get_completed_fromuserids($this->instance->get_id(), $this->touserid);
            foreach ($reviewerids as $reviewerid) {
                if ($reviewer = \core\user::get_user($reviewerid)) {
                    $this->reviewers[$reviewerid] = $reviewer;
                }
            }
            // Ignore an invalid reviewer selection and fall back to the combined report.
            if (!\array_key_exists($this->fromuserid, $this->reviewers)) {
                $this->fromuserid = 0;
            }
        } else {
            $this->fromuserid = 0;
        }
        return $this;
    }

    /**
     * Creates a report for a specific participants by instance id and user id (the participant
     * for which the report shall be created). If a fromuserid is set, the report is limited
     * to the feedback given by this single reviewer. Otherwise the combined feedback of all
     * reviewers is used.
     *
     * @return report The report model of the submission.
     * @throws \dml_exception A DML specific exception is thrown for any errors.
     */
    public function create_report(): report {
        $submissions = $this->submissionrepo->get_by_instance_and_touser($this->instance->get_id(), $this->touserid);

        $report = new report();
        $report->set_to_user_id($this->touserid);
        $report->set_instance_id($this->instance->get_id());

        // Group responses by criteria id.
        $responses = [];
        foreach ($submissions as $submission) {
            // When a single reviewer is requested, skip every other reviewer's submission.
            if ($this->fromuserid > 0 && $submission->get_from_user_id() != $this->fromuserid) {
                continue;
            }
            $report->add_from_user_id($submission->get_from_user_id());
            foreach ($submission->get_responses() as $response) {
                $responses[$response->get_criterion_id()][] = $response;
            }
        }

        // Building report structure.
        foreach ($this->instance->get_categories() as $category) {
            $reportcategory = new report_category($category);
            foreach ($category->get_criteria() as $criterion) {
                $reportcriterion = new report_criterion($criterion);
                if (isset($responses[$criterion->get_id()])) {
                    foreach ($responses[$criterion->get_id()] as $response) {
                        $reportcriterion->add_response($response);
                    }
                }
                $reportcategory->add_criterion($reportcriterion);
            }
            $report->add_category($reportcategory);
        }
        return $report;
    }
}
