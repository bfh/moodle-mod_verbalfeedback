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
 * Class for managing user reports.
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_verbalfeedback\service;

use mod_verbalfeedback\model\report;
use mod_verbalfeedback\model\report_category;
use mod_verbalfeedback\model\report_criterion;
use mod_verbalfeedback\repository\instance_repository;
use mod_verbalfeedback\repository\submission_repository;

/**
 * The report service class for managing user reports
 */
class report_service {

    /** @var instance_repository The instance repository */
    private $instancerepo;
    /** @var submission_repository The submission repository */
    private $submissionrepo;

    /**
     * The class constructor
     */
    public function __construct() {
        $this->instancerepo = new instance_repository();
        $this->submissionrepo = new submission_repository();
    }

    /**
     * Creates a report for a specific participants by instance id and user id.
     *
     * @param int $instanceid The instance id.
     * @param int $touserid The user id of the participant for which the report shall be created.
     * @return report The report model of the submission.
     * @throws dml_exception A DML specific exception is thrown for any errors.
     */
    public function create_report(int $instanceid, int $touserid): report {
        $instance = $this->instancerepo->get_by_id($instanceid);
        $submissions = $this->submissionrepo->get_by_instance_and_touser($instanceid, $touserid);

        $report = new report();
        $report->set_to_user_id($touserid);
        $report->set_instance_id($instanceid);

        // Group responses by criteria id.
        $responses = [];
        foreach ($submissions as $submission) {
            $report->add_from_user_id($submission->get_from_user_id());
            foreach ($submission->get_responses() as $response) {
                $responses[$response->get_criterion_id()][] = $response;
            }
        }

        // Building report structure.
        foreach ($instance->get_categories() as $category) {
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
