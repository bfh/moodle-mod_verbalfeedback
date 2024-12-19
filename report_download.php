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
 * Generates the user's feedback report for download.
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_verbalfeedback\repository\instance_repository;
use mod_verbalfeedback\service\report_service;
use mod_verbalfeedback\utils\font;
use mod_verbalfeedback\utils\graph_utils;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/pdflib.php');
require_once($CFG->libdir . '/filestorage/stored_file.php');

global $DB;

$instanceid = required_param('instance', PARAM_INT);
$touserid = required_param('touser', PARAM_INT);

list($course, $cm) = get_course_and_cm_from_instance($instanceid, 'verbalfeedback');

require_login($course, true, $cm);

$context = context_module::instance($cm->id);

$instancerepo = new instance_repository();
$instance = $instancerepo->get_by_id($instanceid);

$viewownreport = $touserid == $USER->id;
$participants = [];

if (!$viewownreport) {
    require_capability('mod/verbalfeedback:view_all_reports', $context);
} else if (!$instance->reports_are_released($instance)) {
    throw new moodle_exception('errorreportnotavailable', 'mod_verbalfeedback');
}

$PAGE->set_context($context);
$PAGE->set_cm($cm, $course);
$PAGE->set_pagelayout('incourse');

$urlparams = [
    'instance' => $instanceid,
    'touser' => $touserid,
    'forcedownload' => 1,
];
$PAGE->set_url('/mod/verbalfeedback/report_download.php', $urlparams);
$PAGE->set_heading($course->fullname);
$instancename = format_string($instance->get_name());
$PAGE->set_title($instancename);

// Fetch the user.
$touser = core_user::get_user($touserid);
// Render user heading.
$userheading = [
  'heading' => fullname($touser),
  'user' => $touser,
  'usercontext' => context_user::instance($touserid),
];

// Get the responses to the user.
$reportservice = new report_service();
$report = $reportservice->create_report($instanceid, $touserid);

$fonthandler = new font($report, $touser);

$templatedata = new mod_verbalfeedback\output\report_download($report, $course->fullname, $course->startdate, $course->enddate,
    $instancename, $touser, $fonthandler);

$renderer = $PAGE->get_renderer('mod_verbalfeedback');
$html = $renderer->render($templatedata);
$radarimg = graph_utils::create_radar_graph($report);

$pdf = new pdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information.
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('BFH');
$pdf->SetTitle('Verbalfeedback example report');
$pdf->SetSubject('TCPDF Tutorial');
$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

// Set default header data.
// Note: path must be relative to K_PATH_IMAGES and do not use "." or "..".

$fs = get_file_storage();

$logofilepath = $DB->get_field('config_plugins', 'value', ['plugin' => 'mod_verbalfeedback', 'name' => 'reportimage']);

if (!$logofilepath) {
    $imagefile = $CFG->dirroot . '/mod/verbalfeedback/pix/reportlogo.png';
} else {
    $systemcontext = context_system::instance();
    if ($file = $fs->get_file($systemcontext->id, 'mod_verbalfeedback', 'reportbackgroundimage', 0,
        '/', $logofilepath)) {
        $imagefile = $file->copy_content_to_temp();
    }
}

// Print a header with the original width and height using the transparent placeholder image.
$logoplaceholder = "mod/verbalfeedback/pix/logoplaceholder.png";
$pdf->SetHeaderData($logoplaceholder, 25, '', '');

// Set header and footer fonts.
$pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
$pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);

// Set default monospaced font.
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Set margins.
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Set auto page breaks.
$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

// Set image scale factor.
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Add a page.
$pdf->AddPage();

// Set font.
$fonthandler->set_font_for_pdf($pdf);

// Image size from logo image.
$image = @getimagesize($imagefile);

if ($image) {
    $imagefilewidth = $image[0];
    $imagefileheight = $image[1];
    $imagescaledwidth = 18 * $imagefilewidth / $imagefileheight;
}

$pdf->Image('@' . file_get_contents($imagefile), 15, 5, $imagescaledwidth, 18);
$pdf->ImageSVG('@' . $radarimg, $x = 16, $y = 28, $w = '', $h = '', $link = '', $align = '', $palign = 'R',
    $border = 1, $fitonpage = false);
$pdf->writeHTML($html, true, false, true, false, '');

$pdf->lastPage();
$pdf->Output('example_report.pdf', 'I');
