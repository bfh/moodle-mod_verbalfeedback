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
                   Cannot be used with the --hierarchy parameter.
--filter=<string>[,<string>]
                   Show only the selected data. The filter can be language, template,
                   category, criterion. If no filter is set, all data is displayed.
--hierarchy=category|criterion|subrating
                   Show the hierarchy of e.g. a criterion. Need to be combined with
                   the id of the entity in question, given by --id.
--id=<             The id of the hierarchy entity.
-h, --help         Print out this help message.

Examples:
\$ sudo -u www-data /usr/bin/php public/mod/verbalfeedback/cli/check-template.php --filter=criterion,language
\$ sudo -u www-data /usr/bin/php public/mod/verbalfeedback/cli/check-template.php --hierarchy=criterion --id=23
EOF;

// Now get cli options.
[$options, $unrecognized] = cli_get_params(
    [
        'help' => false,
        'fix' => false,
        'filter' => '',
        'hierarchy' => '',
        'id' => '',
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
$hierarchy = $options['hierarchy'] ?: '';
$entityid = $options['id'] ?: '';

if (!empty($filter) && !empty($hierarchy)) {
    cli_error(get_string('clierrorhierarchyandfilter', 'mod_verbalfeedback'));
}
if (!empty($hierarchy)) {
    // Put a dummy filter so that the filters display nothing.
    $filter['dummy'] = true;
    if (!in_array($hierarchy, ['subrating', 'category', 'criterion'])) {
        cli_error(get_string('cliinvalidhierarchyentity', 'mod_verbalfeedback'));
    }
    if (empty($entityid)) {
        cli_error(get_string('clinoid', 'mod_verbalfeedback'));
    }
}

global $DB;

// Get all languages.
$languages = (new language_repository())->get_all();
// Filter: list all languages.
if (empty($filter) || array_key_exists('language', $filter)) {
    echo "Languages\n=========\n";
    foreach ($languages as $lang) {
        echo "{$lang->get_id()}\t{$lang->get_language()}\n";
    }
}

// Filter: list templates.
if (empty($filter) || array_key_exists('template', $filter)) {
    echo "Templates\n=========\n";
    $templates = (new template_repository())->get_all();
    foreach ($templates as $template) {
        echo "{$template->id}\t{$template->name}\n";
    }
}

// Filter: list all categories.
if (empty($filter) || array_key_exists('category', $filter)) {
    echo "Categories\n==========\n";
    $categories = (new template_category_repository())->get_all();
    foreach ($categories as $category) {
        echo "{$category->get_id()}\t{$category->get_unique_name()}\n";
    }
}

// Filter: list all criteria.
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

// List hierarchy of entity.
if ($hierarchy === 'criterion') {
    $category = [];
    $res = $DB->get_records(tables::PARAMETRIZED_TEMPLATE_CRITERION_TABLE, ['criterionid' => $entityid]);
    if (empty($res)) {
        cli_error("No criterion found with id {$entityid}");
    }
    foreach ($res as $row) {
        if (!array_key_exists((int)$row->categoryid, $category)) {
            $category[(int)$row->categoryid] = [];
        }
        $category[(int)$row->categoryid] = [];
    }
    [$insql, $inparams] = $DB->get_in_or_equal(array_keys($category));
    $res = $DB->get_records_select(tables::PARAMETRIZED_TEMPLATE_CATEGORY_TABLE, 'categoryid ' . $insql, $inparams);
    foreach ($res as $row) {
        foreach ($category as $i => $cat) {
            if ((int)$row->categoryid === $i) {
                $category[$i][] = $row->templateid;
            }
        }
    }
    echo "Criterion: {$entityid}\n";
    foreach (array_keys($category) as $i) {
        echo "|- Category: " . $i . "\n";
        foreach ($category[$i] as $template) {
            echo "  |- Template: {$template}\n";
        }
    }
} else if ($hierarchy === 'category') {
    $templates = [];
    $res = $DB->get_records(tables::PARAMETRIZED_TEMPLATE_CATEGORY_TABLE, ['categoryid' => $entityid]);
    foreach ($res as $row) {
        $templates[] = $row->templateid;
    }
    echo "Category: {$entityid}\n";
    foreach ($templates as $template) {
        echo "|- Template: {$template}\n";
    }
} else if ($hierarchy === 'subrating') {
    $criteria = [];
    $res = $DB->get_records(tables::TEMPLATE_SUBRATINGS_TABLE, ['id' => $entityid]);
    if (empty($res)) {
        cli_error("No subrating found with id {$entityid}");
    }
    foreach ($res as $row) {
        $criteria[(int)$row->criterionid] = [];
    }
    [$insql, $inparams] = $DB->get_in_or_equal(array_keys($criteria));
    $res = $DB->get_records_select(tables::PARAMETRIZED_TEMPLATE_CRITERION_TABLE, 'criterionid ' . $insql, $inparams);
    foreach ($res as $row) {
        $criteria[(int)$row->criterionid][] = (int)$row->categoryid;
    }
    $categoryids = array_values(array_merge([], ...array_map(fn($a) => array_values($a), $criteria)));
    [$insql, $inparams] = $DB->get_in_or_equal($categoryids);
    $res = $DB->get_records_select(tables::PARAMETRIZED_TEMPLATE_CATEGORY_TABLE, 'categoryid ' . $insql, $inparams);
    $cat2temp = [];
    foreach ($res as $row) {
        if (!array_key_exists((int)$row->categoryid, $cat2temp)) {
            $cat2temp[(int)$row->categoryid] = [];
        }
        $cat2temp[(int)$row->categoryid][] = (int)$row->templateid;
    }

    echo "Subrating: {$entityid}\n";
    foreach (array_keys($criteria) as $criterion) {
        echo "|- Criterion: {$criterion}\n";
        foreach ($criteria[$criterion] as $category) {
            echo "  |- Category: {$category}\n";
            foreach ($cat2temp[$category] ?? [] as $template) {
                echo "    |- Template: {$template}\n";
            }
        }
    }
}
