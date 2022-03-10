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

use mod_verbalfeedback\repository\template_category_repository;

require_once(__DIR__ . '/../../config.php');

require_login();

$strverbalfeedback = get_string('modulename', 'verbalfeedback');
$strverbalfeedbacks = get_string('modulenameplural', 'verbalfeedback');

$pageurl = new moodle_url('/mod/verbalfeedback/template_category_list.php');
$PAGE->set_url($pageurl);
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('listcategories', 'verbalfeedback'));
$PAGE->set_heading(get_string('listcategories', 'verbalfeedback'));
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();

$templatecategoryrepository = new template_category_repository();
$categories = $templatecategoryrepository->get_all();

$templatecategorydata = new \mod_verbalfeedback\output\template_category_list($categories);

$renderer = $PAGE->get_renderer('mod_verbalfeedback');

echo $renderer->render($templatecategorydata);
echo $OUTPUT->footer();
