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
 * Plugin administration pages are defined here.
 *
 * @package     mod_verbalfeedback
 * @copyright   2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_verbalfeedback\repository\template_repository;

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $templaterepository = new template_repository();
    $templates = [];
    foreach ($templaterepository->get_all() as $t) {
        $templates[$t->get_id()] = $t->get_name();
    }
    $templates[0] = get_string('notemplate', 'mod_verbalfeedback');
    $setting = new admin_setting_configselect(
        'mod_verbalfeedback/defaulttemplate',
        get_string('defaulttemplate', 'mod_verbalfeedback'),
        get_string('defaulttemplate_desc', 'mod_verbalfeedback'),
        '1',
        $templates
    );
    $settings->add($setting);

    $setting = new admin_setting_configstoredfile(
        'mod_verbalfeedback/reportimage',
        get_string('reportimage', 'mod_verbalfeedback', null, true),
        get_string('reportimage_desc', 'mod_verbalfeedback', null, true),
        'reportbackgroundimage',
        0,
        [
            'maxfiles' => 1,
            'accepted_types' => ['.jpg', '.png'],
        ],
    );
    $settings->add($setting);
}

$ADMIN->add('modsettings', new admin_category(
    'modsettingsverbalfeedbackcat',
    get_string('pluginname', 'mod_verbalfeedback'),
    $module->is_enabled() === false
));

// Retitle the general page so it does not repeat the category name.
$settings->visiblename = get_string('generalsettings', 'admin');
$ADMIN->add('modsettingsverbalfeedbackcat', $settings);
// The entry link to manage templates, categories, criteria and subratings.
$ADMIN->add('modsettingsverbalfeedbackcat', new admin_externalpage(
    'mod_verbalfeedback/templates',
    get_string('templateplural', 'mod_verbalfeedback'),
    $CFG->wwwroot . '/mod/verbalfeedback/template_list.php',
    'mod/verbalfeedback:managetemplates'
));

$settings = null; // Prevent the loader from adding the standard settings link again.
