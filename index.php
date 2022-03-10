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
 * Display information about all the verbal feedback modules in the requested course.
 *
 * @package     mod_verbalfeedback
 * @copyright   2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

// Course ID.
$id = required_param('id', PARAM_INT);

// Ensure that the course specified is valid.
if (!$course = $DB->get_record('course', ['id' => $id])) {
    throw new moodle_exception('Course ID is incorrect');
}

require_course_login($course);

$strverbalfeedback = get_string('modulename', 'verbalfeedback');
$strverbalfeedbacks = get_string('modulenameplural', 'verbalfeedback');

$pageurl = new moodle_url('/mod/verbalfeedback/index.php', ['id' => $id]);
$PAGE->set_url($pageurl);
$PAGE->set_title($strverbalfeedbacks);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strverbalfeedback);
echo $OUTPUT->header();

$verbalfeedbacks = get_all_instances_in_course('verbalfeedback', $course);
if (empty($verbalfeedbacks)) {
    $returnurl = new moodle_url('/course/view.php', ['id' => $course->id]);
    throw new moodle_exception('thereareno', 'moodle', $returnurl->out(), $strverbalfeedbacks);
}

$instancedata = [];
foreach ($verbalfeedbacks as $instance) {
    $instanceurl = new moodle_url('/mod/verbalfeedback/view.php', ['id' => $instance->coursemodule]);
    $instancedata[] = (object)[
        'name' => format_string($instance->name),
        'url' => $instanceurl->out(),
    ];
}
echo $OUTPUT->render_from_template('mod_verbalfeedback/index', ['instances' => $instancedata]);

echo $OUTPUT->footer();
