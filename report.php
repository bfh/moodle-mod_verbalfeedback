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
 * The page containing the feedback to a certain user.
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_verbalfeedback\api;
use mod_verbalfeedback\model\report;
use mod_verbalfeedback\repository\instance_repository;
use mod_verbalfeedback\service\report_service;

require_once(__DIR__ . '/../../config.php');

$instanceid = required_param('instance', PARAM_INT);
$touserid = required_param('touser', PARAM_INT);

list ($course, $cm) = get_course_and_cm_from_instance($instanceid, 'verbalfeedback');

require_login($course, true, $cm);

// Quick hack for issue #43.
ini_set('memory_limit', '256M');

$context = context_module::instance($cm->id);

$instancerepo = new instance_repository();
$instance = $instancerepo->get_by_id($instanceid);

$viewownreport = $touserid == $USER->id;
$participants = [];

if (!$viewownreport) {
    require_capability('mod/verbalfeedback:view_all_reports', $context);
    $participants = api::get_participants($instanceid, $USER->id);
} else if (!$instance->reports_are_released($instance) && (!has_capability('mod/verbalfeedback:view_all_reports', $context))) {
    throw new moodle_exception('errorreportnotavailable', 'mod_verbalfeedback');
}

$PAGE->set_context($context);
$PAGE->set_cm($cm, $course);
$PAGE->set_pagelayout('incourse');

$PAGE->set_url('/mod/verbalfeedback/report.php', ['instance' => $instanceid, 'touser' => $touserid]);
$PAGE->set_heading($course->fullname);
$title = format_string($instance->get_name());
$PAGE->set_title($title);

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($title));
if ($touserid != 0) {
    // Result of a given user.
    echo $OUTPUT->heading(get_string('viewfeedbackforuser', 'mod_verbalfeedback'), 3);

    $touser = core_user::get_user($touserid);
    // Render user heading.
    $userheading = [
        'heading' => fullname($touser),
        'user' => $touser,
        'usercontext' => context_user::instance($touserid),
    ];

    $contextheader = $OUTPUT->context_header($userheading, 3);
    echo html_writer::div($contextheader, 'card card-block p-1');

    // Download format options.
    $downloadformats = [];
    $formats = core_plugin_manager::instance()->get_plugins_of_type('dataformat');
    foreach ($formats as $format) {
        if (!$format->is_enabled()) {
            continue;
        }
        $downloadformats[$format->name] = $format->displayname;
    }

    $reportservice = new report_service();
    $report = $reportservice->create_report($instanceid, $touserid);

    $templatedata = new mod_verbalfeedback\output\report($cm->id, $instanceid, $report, $participants, $touser,
        $downloadformats);

    $renderer = $PAGE->get_renderer('mod_verbalfeedback');

    echo $renderer->render($templatedata);
}
echo $OUTPUT->footer();
