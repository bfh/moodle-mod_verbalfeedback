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
 * Class for the verbal feedback template category edit form.
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_verbalfeedback\forms;

use mod_verbalfeedback\repository\language_repository;
use mod_verbalfeedback\repository\template_criterion_repository;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * The template category edit form
 */
class template_category_edit_form extends \moodleform {
    /**
     * Add elements to form.
     *
     * @throws \coding_exception
     */
    public function definition() {
        global $CFG, $PAGE;
        $templatecriteriarepo = new template_criterion_repository();
        $languagerepo = new language_repository();

        $mform = $this->_form; // Don't forget the underscore!
        $mform->addElement('hidden', 'id', 0); // Add elements to your form.
        $mform->setType('id', PARAM_INT); // Set type of element.

        $mform->addElement('text', 'unique_name', get_string('name')); // Add elements to your form.
        $mform->setType('unique_name', PARAM_TEXT); // Set type of element.

        $languages = $languagerepo->get_all();

        foreach ($languages as $l) {
            $headers = [];
            $headers[] =& $mform->createElement('hidden', 'id', 0);
            $headers[] =& $mform->createElement('hidden', 'language_id', $l->get_id());
            $categoryheader = get_string('categoryheader', 'verbalfeedback') . ' - ' . $l->get_language();
            $headers[] =& $mform->createElement('text', 'string', $categoryheader);
            $mform->addGroup($headers, 'headers[' . $l->get_id() . ']', $categoryheader, [''], true);
            $mform->setType('headers[' . $l->get_id() . '][id]', PARAM_INT);
            $mform->setType('headers[' . $l->get_id() . '][language_id]', PARAM_INT);
            $mform->setType('headers[' . $l->get_id() . '][string]', PARAM_TEXT);
        }

        $mform->addElement('header', 'criteriaheader', 'Select criteria');
        $mform->addElement('static', 'text', null, 'Checkbox: include criterion, First textbox: Position within this category, ' .
            'Second textbox: weight within this category.');
        $criteria = $templatecriteriarepo->get_all();
        foreach ($criteria as $criterion) {
            $criteriongroup = [];
            $currentlanguage = current_language();
            $localizedtext = '';

            // Select the language string matching the current language.
            foreach ($criterion->get_descriptions() as $s) {
                $lang = $languagerepo->get_by_id($s->get_language_id());
                if ($lang->get_language() == $currentlanguage) {
                    $localizedtext = $s->get_string();
                    break;
                }
            }

            // Parametrized criteria.
            $criteriongroup[] = $mform->createElement('hidden', 'param_criterion_id', 0);
            $criteriongroup[] = $mform->createElement('hidden', 'criterion_id', $criterion->get_id());
            $criteriongroup[] = $mform->createElement('checkbox', 'selected', null);
            $criteriongroup[] =& $mform->createElement('float', 'position', 'position', 'maxlength="5" size="5" ');
            $criteriongroup[] =& $mform->createElement('float', 'weight', 'weight', 'maxlength="5" size="5" value="1.00"');
            $elementname = 'criterion' . $criterion->get_id();
            $mform->setType($elementname . '[selected]', PARAM_INT);
            $mform->setType($elementname . '[param_criterion_id]', PARAM_INT);
            $mform->setType($elementname . '[criterion_id]', PARAM_INT);
            $mform->setType($elementname . '[position]', PARAM_INT);
            $mform->setType($elementname . '[weight]', PARAM_FLOAT);
            $mform->disabledIf($elementname . '[position]', $elementname . '[selected]', 'notchecked');
            $mform->disabledIf($elementname . '[weight]', $elementname . '[selected]', 'notchecked');
            $mform->addGroup($criteriongroup, $elementname, $localizedtext, [''], true);
        }

        $this->add_action_buttons($cancel = true);
    }

    /**
     * Custom validation should be added here.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        return [];
    }
}
