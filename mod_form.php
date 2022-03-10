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
 * The main verbalfeedback configuration form.
 *
 * @package     mod_verbalfeedback
 * @copyright   2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    // It must be included from a Moodle page.
}

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/verbalfeedback/lib.php');

use mod_verbalfeedback\model\instance_release_type;
use mod_verbalfeedback\repository\tables;
use mod_verbalfeedback\repository\template_repository;

/**
 * Class mod_verbalfeedback_mod_form.
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_verbalfeedback_mod_form extends moodleform_mod {

    /**
     * Form definition.
     *
     * @throws HTML_QuickForm_Error
     * @throws coding_exception
     */
    public function definition() {
        global $DB;

        $templaterepository = new template_repository();

        $mform =& $this->_form;

        // General.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Name.
        $mform->addElement('text', 'name', get_string('name'), array('size' => '64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        // Description.
        $this->standard_intro_elements();

        // Anonymous.
        // $mform->addElement('advcheckbox', 'anonymous', get_string('anonymous', 'mod_verbalfeedback'));

        // Self-review.
        // $mform->addElement('advcheckbox', 'with_self_review', get_string('enableselfreview', 'mod_verbalfeedback'));
        // $mform->disabledIf('with_self_review', 'anonymous', 'checked');

        // Verbalfeedback participants.
        // $context = $this->get_context();
        // $roles = get_profile_roles($context);
        // $roleoptions = role_fix_names($roles, $context, ROLENAME_ALIAS, true);
        // $roleoptions[0] = get_string('allparticipants', 'mod_verbalfeedback');
        // ksort($roleoptions);
        // $mform->addElement('select', 'participantrole', get_string('participants'), $roleoptions);

        // Releasing options.
        $releasetypeoptions = [
            instance_release_type::NONE => get_string('rel_closed', 'mod_verbalfeedback'),
            instance_release_type::OPEN => get_string('rel_open', 'mod_verbalfeedback'),
            // instance_release_type::MANUAL => get_string('rel_manual', 'mod_verbalfeedback'),
            // instance_release_type::AFTER => get_string('rel_after', 'mod_verbalfeedback'),
        ];
        $mform->addElement('select', 'releasetype', get_string('releasetype', 'mod_verbalfeedback'), $releasetypeoptions);
        $mform->addHelpButton('releasetype', 'releasetype', 'mod_verbalfeedback');

        if ($this->_instance) {
            if ($DB->count_records(tables::SUBMISSION_TABLE, array('instanceid' => $this->_instance)) > 0) {
                // Prevent user from toggeling the template once there are submissions.
                $mform->addElement('hidden', 'allowchangetemplate', 0);
                $mform->setType('allowchangetemplate', PARAM_INT);
            }
        }

        $templates = [
          null => get_string('notemplate', 'mod_verbalfeedback')
        ];
        foreach ($templaterepository->get_all() as $t) {
            $templates[$t->get_id()] = format_text($t->get_name());
        }

        // Pop and remove 'No template' to add it later at the end.
        $notemplateoption = $templates[null];
        unset($templates[null]);

        // Sort alphabetically.
        asort($templates);

        // Add 'No template' at the end.
        $templates[null] = $notemplateoption;

        $mform->addElement('select', 'template', get_string('template', 'mod_verbalfeedback'), $templates);
        if ($this->_instance) {
            $defaulttemplate = $DB->get_field(tables::INSTANCE_TABLE, 'templateid', array('id' => $this->_instance));
            $mform->setDefault('template', $defaulttemplate);
        }

        $mform->disabledIf('template', 'allowchangetemplate', '0');

        // Allow participants to undo declined feedback submissions.
        // $mform->addElement('advcheckbox', 'undodecline', get_string('allowundodecline', 'mod_verbalfeedback'));

        // Availability.
        $mform->addElement('header', 'timinghdr', get_string('availability'));
        $mform->addElement('date_time_selector', 'timeopen', get_string('feedbackopen', 'feedback'),
            array('optional' => true));
        $mform->addElement('date_time_selector', 'timeclose', get_string('feedbackclose', 'feedback'),
            array('optional' => true));

        // Grade.
        $this->standard_grading_coursemodule_elements();

        // Remove "Grade" element like in the quiz activity.
        $mform->removeElement('grade');
        if (property_exists($this->current, 'grade')) {
            $currentgrade = $this->current->grade;
        } else {
            $currentgrade = 5;
        }
        $mform->addElement('hidden', 'grade', $currentgrade);
        $mform->setType('grade', PARAM_FLOAT);

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }
}
