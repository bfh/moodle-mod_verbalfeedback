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
 * Check templates, categories and criteria with subratings.
 *
 * @package    mod_verbalfeedback
 * @copyright  2026 Stephan Robotta <stephan.robotta@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../../config.php');
require_once("$CFG->libdir/clilib.php");

use mod_verbalfeedback\repository\language_repository;
use mod_verbalfeedback\repository\template_category_repository;
use mod_verbalfeedback\repository\template_criterion_repository;
use mod_verbalfeedback\repository\template_repository;
use mod_verbalfeedback\repository\model\localized_string_type;
use mod_verbalfeedback\repository\tables;

$usage = <<<EOF
"Show template data from the verbal feedback.

Options:
-f, --fix          Fix problems with missing localized strings, add an empty string.
--filter=<string>[,<string>]
                   Show only the selected data. The filter can be language, template,
                   category, criterion. If no filter is set, all data is displayed.
-h, --help         Print out this help message.

Examples:
\$ sudo -u www-data /usr/bin/php public/mod/verbalfeedback/cli/check-template.php --filter=criterion
EOF;

// Now get cli options.
[$options, $unrecognized] = cli_get_params(
    [
        'help' => false,
        'fix' => false,
        'filter' => '',
    ],
    [
        'h' => 'help',
        'f' => 'fix',
    ]
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'mod_verbalfeedback', $unrecognized));
}

if ($options['help']) {
    cli_error($usage, 0);
}
$filter = array_flip(array_filter(array_map('trim', explode(',', $options['filter']))));

// Get all languages.
$languages = (new language_repository())->get_all();
if (empty($filter) || array_key_exists('language', $filter)) {
    echo "Languages\n=========\n";
    foreach ($languages as $lang) {
        echo "{$lang->get_id()}\t{$lang->get_language()}\n";
    }
}


// List templates.
if (empty($filter) || array_key_exists('template', $filter)) {
    echo "Templates\n=========\n";
    $templates = (new template_repository())->get_all();
    foreach ($templates as $template) {
        echo "{$template->id}\t{$template->name}\n";
    }
}

// List all categories.
if (empty($filter) || array_key_exists('category', $filter)) {
    echo "Categories\n==========\n";
    $categories = (new template_category_repository())->get_all();
    foreach ($categories as $category) {
        echo "{$category->get_id()}\t{$category->get_unique_name()}\n";
    }
}

// List all criteria.
if (empty($filter) || array_key_exists('criterion', $filter)) {
    echo "Criteria\n=========\n";
    while (true) {
        try {
            $criteria = (new template_criterion_repository())->get_all();
            foreach ($criteria as $criterion) {
                echo $criterion->get_id();
                foreach ($criterion->get_descriptions() as $text) {
                    echo "\t$text->string";
                }
                echo "\n";
            }
            break;
        } catch (\coding_exception $e) {
            global $DB;
            if ($options['fix'] && preg_match("/'([a-z_]+)'.*?'(\d+)'/", $e->getMessage(), $m)) {
                if (!defined('PHPUNIT_TEST')) {
                    define('PHPUNIT_TEST', true);
                }
                foreach ($languages as $lang) {
                    $params = [
                        'foreignkey' => $m[2],
                        'typeid' => localized_string_type::str2id($m[1]),
                        'instanceid' => 0,
                        'languageid' => $lang->get_id(),
                    ];
                    if (empty($DB->get_record(tables::LOCALIZED_STRING_TABLE, $params))) {
                        $DB->insert_record(tables::LOCALIZED_STRING_TABLE, (object)array_merge($params, ['string' => '']));
                        echo 'Add record in ' . tables::LOCALIZED_STRING_TABLE . ' with values: ' . json_encode($params) . PHP_EOL;
                    }
                }
            } else {
                throw $e;
            }
        }
    }
}
