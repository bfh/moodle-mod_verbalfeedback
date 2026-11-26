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
use mod_verbalfeedback\output\report;
use mod_verbalfeedback\service\report_service;

require_once(__DIR__ . '/../../config.php');

$instanceid = required_param('instance', PARAM_INT);
$touserid = required_param('touser', PARAM_INT);
$fromuserid = optional_param('fromuser', 0, PARAM_INT);
[$course, $cm] = get_course_and_cm_from_instance($instanceid, 'verbalfeedback');
require_login($course, true, $cm);
$context = \core\context\module::instance($cm->id);

$reportservice = new report_service($touserid, $instanceid);
$reportservice->set_fromuserid($fromuserid)->validate($context);

$PAGE->set_context($context);
$PAGE->set_cm($cm, $course);
$PAGE->set_pagelayout('incourse');

$pageurlparams = ['instance' => $instanceid, 'touser' => $touserid];
if ($reportservice->get_fromuserid() != 0) {
    $pageurlparams['fromuser'] = $reportservice->get_fromuserid();
}
$PAGE->set_url('/mod/verbalfeedback/report.php', $pageurlparams);
$PAGE->set_heading($course->fullname);
$title = format_string($reportservice->get_instance()->get_name());
$PAGE->set_title($title);

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($title));
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
echo html_writer::div($contextheader, 'card card-block border-0 p-1');

// Download format options.
$downloadformats = [];
$formats = core_plugin_manager::instance()->get_plugins_of_type('dataformat');
foreach ($formats as $format) {
    if (!$format->is_enabled()) {
        continue;
    }
    $downloadformats[$format->name] = $format->displayname;
}

$report = $reportservice->create_report();
$participants = api::get_participants($instanceid, $USER->id);

$templatedata = new report(
    $cm->id,
    $instanceid,
    $report,
    $participants,
    $touser,
    $downloadformats,
    $reportservice->get_reviewers(),
    $fromuserid
);

$renderer = $PAGE->get_renderer('mod_verbalfeedback');

echo $renderer->render($templatedata);

echo $OUTPUT->footer();
