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
 * Class for performing DB actions for the verbalfeedback activity module.
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_verbalfeedback\repository;

/**
 * The verbal feedback tables class
 */
class tables {
    /** The instance table name */
    const INSTANCE_TABLE = 'verbalfeedback';
    /** The instance category table name */
    const INSTANCE_CATEGORY_TABLE = 'verbalfeedback_i_category';
    /** The instance criterion table name */
    const INSTANCE_CRITERION_TABLE = 'verbalfeedback_i_criterion';
    /** The instance subrating table name */
    const INSTANCE_SUBRATING_TABLE = 'verbalfeedback_i_subrating';

    /** The language table name */
    const LANGUAGE_TABLE = 'verbalfeedback_language';
    /** The localized strings table name */
    const LOCALIZED_STRING_TABLE = 'verbalfeedback_local_string';

    /** The parametrized template category table name */
    const PARAMETRIZED_TEMPLATE_CATEGORY_TABLE = 'verbalfeedback_t_param_cat';
    /** The parametrized template criterion table name */
    const PARAMETRIZED_TEMPLATE_CRITERION_TABLE = 'verbalfeedback_t_param_crit';

    /** The responses table name */
    const RESPONSE_TABLE = 'verbalfeedback_response';
    /** The submissions table name */
    const SUBMISSION_TABLE = 'verbalfeedback_submission';

    /**
     *
     */
    const TEMPLATE_TABLE = 'verbalfeedback_template';
    /**
     *
     */
    const TEMPLATE_CRITERION_TABLE = 'verbalfeedback_t_criterion';
    /**
     *
     */
    const TEMPLATE_SUBRATINGS_TABLE = 'verbalfeedback_t_subrating';
}
