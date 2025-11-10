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
 * @copyright 2024 Stephan Robotta <stephan.robotta@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_verbalfeedback;

defined('MOODLE_INTERNAL') || die();

global $CFG;

use mod_verbalfeedback\repository\model\localized_string_type;

/**
 * Verbal feedback language repository test class
 * @covers \mod_verbalfeedback\repository\model\localized_string_type::INSTANCE_CRITERION
 * @covers \mod_verbalfeedback\repository\model\localized_string_type::INSTANCE_CATEGORY_HEADER
 * @covers \mod_verbalfeedback\repository\model\localized_string_type::INSTANCE_SUBRATING_TITLE
 * @covers \mod_verbalfeedback\repository\model\localized_string_type::INSTANCE_SUBRATING_DESCRIPTION
 * @covers \mod_verbalfeedback\repository\model\localized_string_type::INSTANCE_SUBRATING_VERY_NEGATIVE
 * @covers \mod_verbalfeedback\repository\model\localized_string_type::INSTANCE_SUBRATING_NEGATIVE
 * @covers \mod_verbalfeedback\repository\model\localized_string_type::INSTANCE_SUBRATING_POSITIVE
 * @covers \mod_verbalfeedback\repository\model\localized_string_type::INSTANCE_SUBRATING_VERY_POSITIVE
 * @covers \mod_verbalfeedback\repository\model\localized_string_type::TEMPLATE_CRITERION
 * @covers \mod_verbalfeedback\repository\model\localized_string_type::TEMPLATE_CATEGORY_HEADER
 * @covers \mod_verbalfeedback\repository\model\localized_string_type::TEMPLATE_SUBRATING_TITLE
 * @covers \mod_verbalfeedback\repository\model\localized_string_type::TEMPLATE_SUBRATING_DESCRIPTION
 * @covers \mod_verbalfeedback\repository\model\localized_string_type::TEMPLATE_SUBRATING_VERY_NEGATIVE
 * @covers \mod_verbalfeedback\repository\model\localized_string_type::TEMPLATE_SUBRATING_NEGATIVE
 * @covers \mod_verbalfeedback\repository\model\localized_string_type::TEMPLATE_SUBRATING_POSITIVE
 * @covers \mod_verbalfeedback\repository\model\localized_string_type::TEMPLATE_SUBRATING_VERY_POSITIVE
 */
final class localized_string_type_test extends \advanced_testcase {
    /**
     * The string constants that are used in the verbal feedback module but are stored as ids in the database.
     * @var string[]
     */
    private $stringconstants = [
        'instance_criterion',
        'instance_category_header',
        'instance_subrating_title',
        'instance_subrating_description',
        'instance_subrating_verynegative',
        'instance_subrating_negative',
        'instance_subrating_positive',
        'instance_subrating_verypositive',
        'template_criterion',
        'template_category_header',
        'template_subrating_title',
        'template_subrating_description',
        'template_subrating_verynegative',
        'template_subrating_negative',
        'template_subrating_positive',
        'template_subrating_verypositive',
    ];

    /**
     * Test that the contants are defined correctly.
     */
    public function test_constants(): void {
        $this->assertEquals('instance_criterion', localized_string_type::INSTANCE_CRITERION);
        $this->assertEquals('instance_category_header', localized_string_type::INSTANCE_CATEGORY_HEADER);
        $this->assertEquals('instance_subrating_title', localized_string_type::INSTANCE_SUBRATING_TITLE);
        $this->assertEquals('instance_subrating_description', localized_string_type::INSTANCE_SUBRATING_DESCRIPTION);
        $this->assertEquals('instance_subrating_verynegative', localized_string_type::INSTANCE_SUBRATING_VERY_NEGATIVE);
        $this->assertEquals('instance_subrating_negative', localized_string_type::INSTANCE_SUBRATING_NEGATIVE);
        $this->assertEquals('instance_subrating_positive', localized_string_type::INSTANCE_SUBRATING_POSITIVE);
        $this->assertEquals('instance_subrating_verypositive', localized_string_type::INSTANCE_SUBRATING_VERY_POSITIVE);
        $this->assertEquals('template_criterion', localized_string_type::TEMPLATE_CRITERION);
        $this->assertEquals('template_category_header', localized_string_type::TEMPLATE_CATEGORY_HEADER);
        $this->assertEquals('template_subrating_title', localized_string_type::TEMPLATE_SUBRATING_TITLE);
        $this->assertEquals('template_subrating_description', localized_string_type::TEMPLATE_SUBRATING_DESCRIPTION);
        $this->assertEquals('template_subrating_verynegative', localized_string_type::TEMPLATE_SUBRATING_VERY_NEGATIVE);
        $this->assertEquals('template_subrating_negative', localized_string_type::TEMPLATE_SUBRATING_NEGATIVE);
        $this->assertEquals('template_subrating_positive', localized_string_type::TEMPLATE_SUBRATING_POSITIVE);
        $this->assertEquals('template_subrating_verypositive', localized_string_type::TEMPLATE_SUBRATING_VERY_POSITIVE);
    }

    /**
     * Test the string to id conversion.
     *
     * @covers \mod_verbalfeedback\repository\model\localized_string_type::str2id
     */
    public function test_str2id(): void {
        $i = 1;
        foreach ($this->stringconstants as $string) {
            $this->assertEquals($i, localized_string_type::str2id($string));
            $i++;
        }
    }

    /**
     * Test the id to string conversion.
     *
     * @covers \mod_verbalfeedback\repository\model\localized_string_type::id2str
     */
    public function test_id2str(): void {
        $i = 1;
        foreach ($this->stringconstants as $string) {
            $this->assertEquals($string, localized_string_type::id2str($i));
            $i++;
        }
        $this->expectException(\InvalidArgumentException::class);
        localized_string_type::id2str(17);
        $this->expectException(\InvalidArgumentException::class);
        localized_string_type::id2str(0);
    }

    /**
     * Test the getStringTypes method.
     *
     * @covers \mod_verbalfeedback\repository\model\localized_string_type::get_string_types
     */
    public function test_get_string_types(): void {
        $this->assertEquals($this->stringconstants, localized_string_type::get_string_types());
    }

    /**
     * Test the exists method.
     *
     * @covers \mod_verbalfeedback\repository\model\localized_string_type::exists
     */
    public function test_exists(): void {
        foreach ($this->stringconstants as $string) {
            $this->assertTrue(localized_string_type::exists($string));
            $this->assertFalse(localized_string_type::exists(strtoupper($string)));
            $this->assertFalse(localized_string_type::exists(ucfirst($string)));
        }
        $this->assertFalse(localized_string_type::exists('nonexisting'));
    }

    /**
     * Test the is_template_type method.
     *
     * @covers \mod_verbalfeedback\repository\model\localized_string_type::is_template_type
     */
    public function test_is_template_type(): void {
        $this->assertTrue(localized_string_type::is_template_type(localized_string_type::TEMPLATE_CRITERION));
        $this->assertTrue(localized_string_type::is_template_type(localized_string_type::TEMPLATE_CATEGORY_HEADER));
        $this->assertTrue(localized_string_type::is_template_type(localized_string_type::TEMPLATE_SUBRATING_TITLE));
        $this->assertTrue(localized_string_type::is_template_type(localized_string_type::TEMPLATE_SUBRATING_DESCRIPTION));
        $this->assertTrue(localized_string_type::is_template_type(localized_string_type::TEMPLATE_SUBRATING_VERY_NEGATIVE));
        $this->assertTrue(localized_string_type::is_template_type(localized_string_type::TEMPLATE_SUBRATING_NEGATIVE));
        $this->assertTrue(localized_string_type::is_template_type(localized_string_type::TEMPLATE_SUBRATING_POSITIVE));
        $this->assertTrue(localized_string_type::is_template_type(localized_string_type::TEMPLATE_SUBRATING_VERY_POSITIVE));
        $this->assertFalse(localized_string_type::is_template_type(localized_string_type::INSTANCE_CRITERION));
        $this->assertFalse(localized_string_type::is_template_type(localized_string_type::INSTANCE_CATEGORY_HEADER));
        $this->assertFalse(localized_string_type::is_template_type(localized_string_type::INSTANCE_SUBRATING_TITLE));
        $this->assertFalse(localized_string_type::is_template_type(localized_string_type::INSTANCE_SUBRATING_DESCRIPTION));
        $this->assertFalse(localized_string_type::is_template_type(localized_string_type::INSTANCE_SUBRATING_VERY_NEGATIVE));
        $this->assertFalse(localized_string_type::is_template_type(localized_string_type::INSTANCE_SUBRATING_NEGATIVE));
        $this->assertFalse(localized_string_type::is_template_type(localized_string_type::INSTANCE_SUBRATING_POSITIVE));
        $this->assertFalse(localized_string_type::is_template_type(localized_string_type::INSTANCE_SUBRATING_VERY_POSITIVE));
    }
}
