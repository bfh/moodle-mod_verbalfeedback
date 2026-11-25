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

namespace mod_verbalfeedback;

use mod_verbalfeedback\model\instance_category;
use mod_verbalfeedback\model\instance_criterion;
use mod_verbalfeedback\model\language;
use mod_verbalfeedback\model\localized_string;
use mod_verbalfeedback\model\subrating;
use mod_verbalfeedback\repository\language_repository;
use Override;

/**
 * Unit tests for classes/helper.php.
 *
 * @package    mod_verbalfeedback
 * @copyright  2026 Stephan Robotta <stephan.robotta@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_verbalfeedback\helper
 */
final class helper_test extends \advanced_testcase {
    /**
     * Add languages to the vb setup which is missing otherwise but needed
     * for these tests.
     */
    #[Override]
    public function setUp(): void {
        parent::setUp();
        // The setUp() writes to the language to the database, so flag the reset here.
        $this->resetAfterTest();
        // Ensure an English verbal feedback language exists for the localised lookups.
        // Passing id 0 forces an insert (a non-zero id would trigger an update that
        // silently matches no rows on an empty table).
        $repo = new language_repository();
        if ($repo->get_by_iso('en')->get_id() === null) {
            $repo->save(new language(null, 'en'));
        }
    }

    /**
     * Test get_status_string() returns the localised string for each known status.
     *
     * @covers \mod_verbalfeedback\helper::get_status_string
     */
    public function test_get_status_string(): void {
        $this->assertEquals(
            get_string('statuspending', 'mod_verbalfeedback'),
            helper::get_status_string(api::STATUS_PENDING)
        );
        $this->assertEquals(
            get_string('statusinprogress', 'mod_verbalfeedback'),
            helper::get_status_string(api::STATUS_IN_PROGRESS)
        );
        $this->assertEquals(
            get_string('statuscompleted', 'mod_verbalfeedback'),
            helper::get_status_string(api::STATUS_COMPLETE)
        );
        $this->assertEquals(
            get_string('statusdeclined', 'mod_verbalfeedback'),
            helper::get_status_string(api::STATUS_DECLINED)
        );
    }

    /**
     * Test get_status_string() throws for an unknown status code.
     *
     * @covers \mod_verbalfeedback\helper::get_status_string
     */
    public function test_get_status_string_invalid(): void {
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('errorinvalidstatus', 'mod_verbalfeedback'));
        helper::get_status_string(-1);
    }

    /**
     * Test prepare_items_view() with an empty category list.
     *
     * @covers \mod_verbalfeedback\helper::prepare_items_view
     */
    public function test_prepare_items_view_empty(): void {
        $this->assertSame([], helper::prepare_items_view([]));
    }

    /**
     * Test prepare_items_view() flattens categories, criteria and subratings into view models.
     *
     * @covers \mod_verbalfeedback\helper::prepare_items_view
     */
    public function test_prepare_items_view(): void {
        // The English language is set up in setUp(), so its id can be looked up here.
        $langid = (new language_repository())->get_by_iso('en')->get_id();

        // Build a subrating with a title and the five localised rating labels.
        $subrating = new subrating(71);
        $subrating->add_title(new localized_string($langid, 0, 'Subrating title'));
        $subrating->add_description(new localized_string($langid, 0, 'Subrating description'));
        $subrating->add_verynegative(new localized_string($langid, 0, 'Very negative'));
        $subrating->add_negative(new localized_string($langid, 0, 'Negative'));
        $subrating->add_positive(new localized_string($langid, 0, 'Positive'));
        $subrating->add_verypositive(new localized_string($langid, 0, 'Very positive'));

        // Build a criterion holding the subrating.
        $criterion = new instance_criterion(42, null, 2, 1.5);
        $criterion->add_description(new localized_string($langid, 0, 'Criterion text'));
        $criterion->add_subrating($subrating);

        // Build the category holding the criterion.
        $category = new instance_category(7, null, 1, 3.25);
        $category->add_header(new localized_string($langid, 0, 'Category header'));
        $category->add_criterion($criterion);

        $result = helper::prepare_items_view([$category]);

        $this->assertCount(1, $result);
        $categoryview = $result[0];
        $this->assertEquals('Category header', $categoryview->header);
        $this->assertEquals(7, $categoryview->id);
        $this->assertEquals(1, $categoryview->position);
        $this->assertEquals('3.25', $categoryview->weight);
        $this->assertCount(1, $categoryview->criteria);

        $criterionview = $categoryview->criteria[0];
        $this->assertEquals(7, $criterionview->categoryid);
        $this->assertEquals(42, $criterionview->id);
        $this->assertEquals(2, $criterionview->position);
        $this->assertEquals('1.50', $criterionview->weight);
        $this->assertEquals('Criterion text', $criterionview->text);
        $this->assertCount(1, $criterionview->subratings);

        $subratingview = $criterionview->subratings[0];
        $this->assertEquals(71, $subratingview->id);
        $this->assertEquals('Subrating title', $subratingview->title);
        $this->assertEquals('Subrating description', $subratingview->description);
        $this->assertEquals('Very negative', $subratingview->verynegative);
        $this->assertEquals('Negative', $subratingview->negative);
        $this->assertEquals('Positive', $subratingview->positive);
        $this->assertEquals('Very positive', $subratingview->verypositive);
    }

    /**
     * Test prepare_items_view() falls back to empty strings when no localised text matches.
     *
     * @covers \mod_verbalfeedback\helper::prepare_items_view
     */
    public function test_prepare_items_view_missing_localisation(): void {
        $criterion = new instance_criterion(42, null, 0, 0.0);
        $category = new instance_category(7, null, 0, 0.0);
        $category->add_criterion($criterion);

        $result = helper::prepare_items_view([$category]);

        $this->assertCount(1, $result);
        $this->assertSame('', $result[0]->header);
        $this->assertSame('', $result[0]->criteria[0]->text);
    }

    /**
     * Test parseyamlfile() parses a YAML file into an object structure.
     *
     * @covers \mod_verbalfeedback\helper::parseyamlfile
     */
    public function test_parseyamlfile(): void {
        if (version_compare(PHP_VERSION, '8.1.14') < 0) {
            $this->expectException(\Exception::class);
            helper::parseyamlfile('dummy.yaml');
            return;
        }

        $path = make_request_directory() . '/sample.yaml';
        file_put_contents($path, "greeting: hello\nitems:\n  - one\n  - two\n");

        $parsed = helper::parseyamlfile($path);

        $this->assertEquals('hello', $parsed->greeting);
        $this->assertCount(2, $parsed->items);
        $this->assertEquals('one', $parsed->items[0]);
        $this->assertEquals('two', $parsed->items[1]);
    }

    /**
     * Data provider for sanitize_comment().
     *
     * @return array[] Array of [input html, expected output html].
     */
    public static function sanitize_comment_provider(): array {
        return [
            'plain text is preserved' => [
                'Just some text.',
                'Just some text.',
            ],
            'allowed tags are kept' => [
                '<p>A <strong>bold</strong> and <em>italic</em> paragraph.</p>',
                '<p>A <strong>bold</strong> and <em>italic</em> paragraph.</p>',
            ],
            'disallowed tag is removed but its text is kept' => [
                '<p>Hello <script>alert(1)</script>world</p>',
                '<p>Hello alert(1)world</p>',
            ],
            'disallowed attribute is stripped' => [
                '<p onclick="evil()">Click</p>',
                '<p>Click</p>',
            ],
            'allowed class attribute is kept' => [
                '<p class="intro">Text</p>',
                '<p class="intro">Text</p>',
            ],
            'style with url() is removed' => [
                '<div style="background-image: url(http://example.com/x.png)">Text</div>',
                '<div>Text</div>',
            ],
            'harmless style is kept' => [
                '<div style="color: red">Text</div>',
                '<div style="color: red">Text</div>',
            ],
        ];
    }

    /**
     * Test sanitize_comment() filters HTML down to the allowed tags and attributes.
     *
     * @dataProvider sanitize_comment_provider
     * @param string $input The raw HTML.
     * @param string $expected The expected sanitised HTML.
     * @covers \mod_verbalfeedback\helper::sanitize_comment
     */
    public function test_sanitize_comment(string $input, string $expected): void {
        $this->assertEquals($expected, helper::sanitize_comment($input));
    }

    /**
     * Test sanitize_comment() removes a disallowed inline event handler but keeps allowed markup.
     *
     * @covers \mod_verbalfeedback\helper::sanitize_comment
     */
    public function test_sanitize_comment_mixed(): void {
        $input = '<ul><li class="a" data-x="1">Item</li></ul>';
        $output = helper::sanitize_comment($input);

        // The list structure and the allowed class survive, the custom data attribute does not.
        $this->assertStringContainsString('<ul>', $output);
        $this->assertStringContainsString('<li class="a">', $output);
        $this->assertStringNotContainsString('data-x', $output);
    }
}
