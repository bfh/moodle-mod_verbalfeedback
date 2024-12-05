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
     * Return all existing types in a order, so that the numeric value + 1 can
     * be used as a string.
     *
     * @return string[] 
     */
    public static function getStringTypes(): array {
        return [
            self::INSTANCE_CRITERION,
            self::INSTANCE_CATEGORY_HEADER,
            self::INSTANCE_SUBRATING_TITLE,
            self::INSTANCE_SUBRATING_DESCRIPTION,
            self::INSTANCE_SUBRATING_VERY_NEGATIVE,
            self::INSTANCE_SUBRATING_NEGATIVE,
            self::INSTANCE_SUBRATING_POSITIVE,
            self::INSTANCE_SUBRATING_VERY_POSITIVE,
            self::TEMPLATE_CRITERION,
            self::TEMPLATE_CATEGORY_HEADER,
            self::TEMPLATE_SUBRATING_TITLE,
            self::TEMPLATE_SUBRATING_DESCRIPTION,
            self::TEMPLATE_SUBRATING_VERY_NEGATIVE,
            self::TEMPLATE_SUBRATING_NEGATIVE,
            self::TEMPLATE_SUBRATING_POSITIVE,
            self::TEMPLATE_SUBRATING_VERY_POSITIVE,
        ];
    }
    /**
     * Return whether a type string exists.
     *
     * @param string $type A string type
     * @return bool If a string type exists
     */
    public static function exists(string $type) {
        return in_array($type, self::getStringTypes());
    }

    /**
     * Convert string constant to id.
     *
     * @param string $type
     * @return int
     * @throws \InvalidArgumentException
     */
    public static function str2id(string $type):int {
        $key = array_search($type, self::getStringTypes());
        if ($key === false) {
            throw new \InvalidArgumentException("Invalid str: $type");
        }
        return $key + 1;
    }

    /**
     * Convert id to string contant.
     *
     * @param int $id
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function id2str(int $id):string {
        $constants = self::getStringTypes();
        if ($id < 1 || $id > count($constants)) {
            throw new \InvalidArgumentException("Invalid id: $id");
        }
        return $constants[$id - 1];
    }

    /**
     * Return whether a type is a template type.
     *
     * @param string $type
     * @return bool
     */
    public static function is_template_type(string $type): bool {
        return in_array($type, [
            self::TEMPLATE_CRITERION,
            self::TEMPLATE_CATEGORY_HEADER,
            self::TEMPLATE_SUBRATING_TITLE,
            self::TEMPLATE_SUBRATING_DESCRIPTION,
            self::TEMPLATE_SUBRATING_VERY_NEGATIVE,
            self::TEMPLATE_SUBRATING_NEGATIVE,
            self::TEMPLATE_SUBRATING_POSITIVE,
            self::TEMPLATE_SUBRATING_VERY_POSITIVE,
        ]);
    }
}
