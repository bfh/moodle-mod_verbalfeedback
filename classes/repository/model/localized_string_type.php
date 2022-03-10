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
 * Class for performing DB actions for the verbal feedback activity module.
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_verbalfeedback\repository\model;

/**
 * The localized string type class.
 */
class localized_string_type {
    /** The instance criterion string. */
    public const INSTANCE_CRITERION = 'instance_criterion';
    /** The instance category header string. */
    public const INSTANCE_CATEGORY_HEADER = 'instance_category_header';
    /** The instance subrating title string. */
    public const INSTANCE_SUBRATING_TITLE = 'instance_subrating_title';
    /** The instance subrating description string. */
    public const INSTANCE_SUBRATING_DESCRIPTION = 'instance_subrating_description';
    /** The very negative instance subrating string. */
    public const INSTANCE_SUBRATING_VERY_NEGATIVE = 'instance_subrating_verynegative';
    /** The negative instance subrating string. */
    public const INSTANCE_SUBRATING_NEGATIVE = 'instance_subrating_negative';
    /** The positive instance subrating string. */
    public const INSTANCE_SUBRATING_POSITIVE = 'instance_subrating_positive';
    /** The very positive instance subrating string. */
    public const INSTANCE_SUBRATING_VERY_POSITIVE = 'instance_subrating_verypositive';
    /** The template criterion string. */
    public const TEMPLATE_CRITERION = 'template_criterion';
    /** The template category header string. */
    public const TEMPLATE_CATEGORY_HEADER = 'template_category_header';
    /** The template subrating title string. */
    public const TEMPLATE_SUBRATING_TITLE = 'template_subrating_title';
    /** The template subrating description string. */
    public const TEMPLATE_SUBRATING_DESCRIPTION = 'template_subrating_description';
    /** The very negative template subrating string. */
    public const TEMPLATE_SUBRATING_VERY_NEGATIVE = 'template_subrating_verynegative';
    /** The negative template subrating string. */
    public const TEMPLATE_SUBRATING_NEGATIVE = 'template_subrating_negative';
    /** The positive template subrating string. */
    public const TEMPLATE_SUBRATING_POSITIVE = 'template_subrating_positive';
    /** The very positive template subrating string. */
    public const TEMPLATE_SUBRATING_VERY_POSITIVE = 'template_subrating_verypositive';

    /**
     * Return whether a type exists
     * @param string $type A string type
     * @return bool If a string type exists
     */
    public static function exists(string $type) {
        switch($type) {
            case self::INSTANCE_CRITERION:
                return true;
            case self::INSTANCE_CATEGORY_HEADER:
                return true;
            case self::INSTANCE_SUBRATING_TITLE:
                return true;
            case self::INSTANCE_SUBRATING_DESCRIPTION:
                return true;
            case self::INSTANCE_SUBRATING_VERY_NEGATIVE:
                return true;
            case self::INSTANCE_SUBRATING_NEGATIVE:
                return true;
            case self::INSTANCE_SUBRATING_POSITIVE:
                return true;
            case self::INSTANCE_SUBRATING_VERY_POSITIVE:
                return true;

            case self::TEMPLATE_CRITERION:
                return true;
            case self::TEMPLATE_CATEGORY_HEADER:
                return true;
            case self::TEMPLATE_SUBRATING_TITLE:
                return true;
            case self::TEMPLATE_SUBRATING_DESCRIPTION:
                return true;
            case self::TEMPLATE_SUBRATING_VERY_NEGATIVE:
                return true;
            case self::TEMPLATE_SUBRATING_NEGATIVE:
                return true;
            case self::TEMPLATE_SUBRATING_POSITIVE:
                return true;
            case self::TEMPLATE_SUBRATING_VERY_POSITIVE:
                return true;

            default:
                return false;
        }
    }
}
