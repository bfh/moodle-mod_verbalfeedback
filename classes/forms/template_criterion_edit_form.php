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
 * Class for the verbal feedback template criterion edit form.
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
 * The template criterion edit form
 */
class template_criterion_edit_form extends \moodleform {
    /**
     * The class constructor
     *
     * @param int $subratingcount
     */
    public function __construct($subratingcount = 0) {
        $this->subratingcount = $subratingcount;
        parent::__construct();
    }


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

        $mform->addElement('static', 'header', '<h4>' . get_string('text', 'verbalfeedback') . '</h4>', '');
        foreach ($languages as $l) {
            // Localized strings textareas.
            $localizedstring = [];
            $localizedstring[] = &$mform->createElement('hidden', 'id', 0);
            $localizedstring[] = &$mform->createElement('hidden', 'language_id', $l->get_id());
            $localizedstring[] = &$mform->createElement('textarea', 'string', '&emsp;' . $l->get_language(),
                'wrap="virtual" rows="3" cols="50"');
            $mform->addGroup($localizedstring, 'localized_strings[' . $l->get_id() . ']', '&emsp;' . $l->get_language(), null,
                true);

            $mform->setType('localized_strings[' . $l->get_id() . '][id]', PARAM_INT);
            $mform->setType('localized_strings[' . $l->get_id() . '][language_id]', PARAM_INT);
            $mform->setType('localized_strings[' . $l->get_id() . '][string]', PARAM_TEXT);
        }

        $mform->addElement('header', 'ratingsheader', 'Edit ratings');

        $repeateloptions = [];

        $repeatarray = [];
        $repeatarray[] = $mform->createElement('html', '<hr>');
        $repeatarray[] = $mform->createElement('static', 'header', '<h4>' . get_string('subrating', 'verbalfeedback') .
            ' - {no}</h4>', '');
        $repeatarray[] = $mform->createElement('hidden', 'subrating_id', 0);
        $repeateloptions['subrating_id']['type'] = PARAM_INT;

        foreach ($languages as $language) {
            $repeatarray[] = &$mform->createElement('html', '<b>' . $language->get_language() . '</b>');

            $titlelabel = '&emsp;' . get_string('titlelabel', 'verbalfeedback');
            $titleplaceholder = get_string('titlelabel', 'verbalfeedback');
            $repeatarray[] = &$mform->createElement('hidden', 'subrating_title_' . $language->get_language() . '_id', 0);
            $repeatarray[] = &$mform->createElement('hidden', 'subrating_title_' . $language->get_language() . '_language_id',
                $language->get_id());
            $repeatarray[] = &$mform->createElement('text', 'subrating_title_' . $language->get_language() . '_string',
                $titlelabel, 'placeholder="'. $titleplaceholder .'"');
            $repeateloptions['subrating_title_' . $language->get_language() . '_id']['type'] = PARAM_INT;
            $repeateloptions['subrating_title_' . $language->get_language() . '_language_id']['type'] = PARAM_INT;
            $repeateloptions['subrating_title_' . $language->get_language() . '_string']['type'] = PARAM_TEXT;

            $descriptionlabel = '&emsp;' . get_string('description');
            $descriptionplaceholder = get_string('description');
            $repeatarray[] = &$mform->createElement('hidden', 'subrating_description_' . $language->get_language() . '_id', 0);
            $repeatarray[] = &$mform->createElement('hidden', 'subrating_description_' . $language->get_language() .
                '_language_id', $language->get_id());
            $repeatarray[] = &$mform->createElement('textarea', 'subrating_description_' . $language->get_language() . '_string',
                $descriptionlabel, 'placeholder="'. $descriptionplaceholder .'" wrap="virtual" rows="2" cols="50"');
            $repeateloptions['subrating_description_' . $language->get_language() . '_id']['type'] = PARAM_INT;
            $repeateloptions['subrating_description_' . $language->get_language() . '_language_id']['type'] = PARAM_INT;
            $repeateloptions['subrating_description_' . $language->get_language() . '_string']['type'] = PARAM_TEXT;

            $verynegativelabel = '&emsp;' . get_string('verynegative', 'verbalfeedback');
            $verynegativeplaceholder = get_string('verynegative', 'verbalfeedback');
            $repeatarray[] = &$mform->createElement('hidden', 'subrating_verynegative_' . $language->get_language() . '_id', 0);
            $repeatarray[] = &$mform->createElement('hidden', 'subrating_verynegative_' . $language->get_language() .
                '_language_id', $language->get_id());
            $repeatarray[] = &$mform->createElement('textarea', 'subrating_verynegative_' . $language->get_language() . '_string',
                $verynegativelabel, 'placeholder="'. $verynegativeplaceholder .'" wrap="virtual" rows="2" cols="50"');
            $repeateloptions['subrating_verynegative_' . $language->get_language() . '_id']['type'] = PARAM_INT;
            $repeateloptions['subrating_verynegative_' . $language->get_language() . '_language_id']['type'] = PARAM_INT;
            $repeateloptions['subrating_verynegative_' . $language->get_language() . '_string']['type'] = PARAM_TEXT;

            $negativelabel = '&emsp;' . get_string('negative', 'verbalfeedback');
            $negativeplaceholder = get_string('negative', 'verbalfeedback');
            $repeatarray[] = &$mform->createElement('hidden', 'subrating_negative_' . $language->get_language() . '_id', 0);
            $repeatarray[] = &$mform->createElement('hidden', 'subrating_negative_' . $language->get_language() .
                '_language_id', $language->get_id());
            $repeatarray[] = &$mform->createElement('textarea', 'subrating_negative_' . $language->get_language() . '_string',
                $negativelabel, 'placeholder="'. $negativeplaceholder .'" wrap="virtual" rows="2" cols="50"');
            $repeateloptions['subrating_negative_' . $language->get_language() . '_id']['type'] = PARAM_INT;
            $repeateloptions['subrating_negative_' . $language->get_language() . '_language_id']['type'] = PARAM_INT;
            $repeateloptions['subrating_negative_' . $language->get_language() . '_string']['type'] = PARAM_TEXT;

            $positivelabel = '&emsp;' . get_string('positive', 'verbalfeedback');
            $positiveplaceholder = get_string('positive', 'verbalfeedback');
            $repeatarray[] = &$mform->createElement('hidden', 'subrating_positive_' . $language->get_language() . '_id', 0);
            $repeatarray[] = &$mform->createElement('hidden', 'subrating_positive_' . $language->get_language() .
                '_language_id', $language->get_id());
            $repeatarray[] = &$mform->createElement('textarea', 'subrating_positive_' . $language->get_language() . '_string',
                $positivelabel, 'placeholder="'. $positiveplaceholder .'" wrap="virtual" rows="2" cols="50"');
            $repeateloptions['subrating_positive_' . $language->get_language() . '_id']['type'] = PARAM_INT;
            $repeateloptions['subrating_positive_' . $language->get_language() . '_language_id']['type'] = PARAM_INT;
            $repeateloptions['subrating_positive_' . $language->get_language() . '_string']['type'] = PARAM_TEXT;

            $verypositivelabel = '&emsp;' . get_string('verypositive', 'verbalfeedback');
            $verypositiveplaceholder = get_string('verypositive', 'verbalfeedback');
            $repeatarray[] = &$mform->createElement('hidden', 'subrating_verypositive_' . $language->get_language() . '_id', 0);
            $repeatarray[] = &$mform->createElement('hidden', 'subrating_verypositive_' . $language->get_language() .
                '_language_id', $language->get_id());
            $repeatarray[] = &$mform->createElement('textarea', 'subrating_verypositive_' . $language->get_language() . '_string',
                $verypositivelabel, 'placeholder="'. $verypositiveplaceholder .'" wrap="virtual" rows="2" cols="50"');
            $repeateloptions['subrating_verypositive_' . $language->get_language() . '_id']['type'] = PARAM_INT;
            $repeateloptions['subrating_verypositive_' . $language->get_language() . '_language_id']['type'] = PARAM_INT;
            $repeateloptions['subrating_verypositive_' . $language->get_language() . '_string']['type'] = PARAM_TEXT;
        }

        $repeatno = $this->subratingcount;
        if ($repeatno == 0) {
            $repeatno = 1;
        }

        $this->repeat_elements($repeatarray, $repeatno,
            $repeateloptions, 'subrating_repeats', 'subrating_add_fields', 1, '+{no} ' . get_string('subrating', 'verbalfeedback'),
            true);

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
