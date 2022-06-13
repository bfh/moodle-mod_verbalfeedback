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
 * Class for the verbal feedback template criterion delete form.
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_verbalfeedback\forms;

use mod_verbalfeedback\repository\language_repository;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * The template criterion delete form
 */
class template_criterion_delete_form extends \moodleform {

    /**
     * Add elements to form.
     *
     * @throws \coding_exception
     */
    public function definition() {
        global $CFG;
        $languagerepo = new language_repository();

        $mform = $this->_form; // Don't forget the underscore!

        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

        $languages = $languagerepo->get_all();

        foreach ($languages as $l) {
            $localizedstring = [];
            $localizedstring[] =& $mform->createElement('hidden', 'id', 0);
            $localizedstring[] =& $mform->createElement('hidden', 'language_id', $l->get_id());
            $textfieldname = get_string('text', 'verbalfeedback') . ' - ' . $l->get_language();

            $style = 'disabled="disabled" wrap="virtual" rows="5" cols="50"';
            $localizedstring[] =& $mform->createElement('textarea', 'string', $textfieldname, $style);

            $groupname = 'localized_strings[' . $l->get_id() .']';
            $mform->addGroup($localizedstring, $groupname, $textfieldname, array(''), true, 'disabled="disabled"');

            $mform->setType('localized_strings[' . $l->get_id() .'][id]', PARAM_INT);
            $mform->setType('localized_strings[' . $l->get_id() .'][language_id]', PARAM_INT);
            $mform->setType('localized_strings[' . $l->get_id() .'][string]', PARAM_TEXT);
        }

        $this->add_action_buttons($cancel = true, $submitlabel = get_string('delete'));
    }

    /**
     * Custom validation should be added here.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        return array();
    }
}
