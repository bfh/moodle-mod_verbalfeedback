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
 * Class for the verbal feedback template edit form.
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_verbalfeedback\forms;

use mod_verbalfeedback\repository\template_category_repository;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * The template edit form
 */
class template_edit_form extends \moodleform {

    /**
     * Add elements to form.
     *
     * @throws \coding_exception
     */
    public function definition() {
        $templatecategoryrepo = new template_category_repository();

        $mform = $this->_form; // Don't forget the underscore!

        $mform->addElement('hidden', 'id', 0); // Add elements to your form.
        $mform->setType('id', PARAM_INT); // Set type of element.

        $mform->addElement('text', 'name', get_string('name')); // Add elements to your form.
        $mform->setType('name', PARAM_TEXT); // Set type of element.

        $mform->addElement('textarea', 'description', get_string('description'), 'wrap="virtual" rows="5" cols="50"');
        $mform->setType('description', PARAM_TEXT); // Set type of element.

        $mform->addElement('header', 'categoriesheader', 'Select categories');
        $statictext = 'Checkbox: include category, First textbox: Position within this template' .
        ', Second textbox: weight within this template.';
        $mform->addElement('static', 'text', null, $statictext);
        $templatecategories = $templatecategoryrepo->get_all();
        foreach ($templatecategories as $templatecategory) {
            $categoryformgroup = array();

            $categoryformgroup[] = $mform->createElement('hidden', 'param_category_id', 0); // Parametrized category id.
            $categoryformgroup[] = $mform->createElement('hidden', 'template_category_id', $templatecategory->get_id());
            $categoryformgroup[] = $mform->createElement('checkbox', 'selected', null);
            $categoryformgroup[] =& $mform->createElement('float', 'position', 'position', 'maxlength="5" size="5" ');
            $categoryformgroup[] =& $mform->createElement('float', 'weight', 'weight', 'maxlength="5" size="5" value="1.00"');
            $elementname = 'category' . $templatecategory->get_id();
            $mform->setType($elementname . '[selected]', PARAM_INT);
            $mform->setType($elementname . '[param_category_id]', PARAM_INT);
            $mform->setType($elementname . '[template_category_id]', PARAM_INT);
            $mform->setType($elementname . '[position]', PARAM_INT);
            $mform->setType($elementname . '[weight]', PARAM_FLOAT);
            $mform->disabledIf($elementname . '[position]', $elementname . '[selected]', 'notchecked');
            $mform->disabledIf($elementname . '[weight]', $elementname . '[selected]', 'notchecked');
            $mform->addGroup($categoryformgroup, $elementname, $templatecategory->get_unique_name(), array(''), true);
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
        return array();
    }
}
