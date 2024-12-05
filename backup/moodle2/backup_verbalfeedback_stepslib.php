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
 * Define all the backup steps that will be used by the backup_verbalfeedback_activity_task
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_verbalfeedback\repository\model\localized_string_type;
use mod_verbalfeedback\repository\tables;

/**
 * Define the complete verbalfeedback structure for backup, with file and id annotations
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_verbalfeedback_activity_structure_step extends backup_activity_structure_step {

    /**
     * Defines the verbal feedback instance structure.
     *
     * @return backup_nested_element
     * @throws base_element_struct_exception
     * @throws base_step_exception
     */
    protected function define_structure() {
        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $instance = new backup_nested_element('instance', ['id'], [
            'templateid', 'course', 'name', 'intro',
            'introformat', 'grade', 'status', 'timeopen',
            'timeclose', 'timemodified', 'releasetype', 'released', ]);

        $languages = new backup_nested_element('languages');
        $language = new backup_nested_element('language', ['id'], ['language']);

        $categories = new backup_nested_element('categories');
        $category = new backup_nested_element('category', ['id'], ['instanceid', 'paramtemplatecategoryid', 'position', 'weight']);

        $categoryheaders = new backup_nested_element('categoryheaders');
        $categoryheader = new backup_nested_element('categoryheader', ['id'], ['foreignkey', 'typeid', 'languageid', 'string']);

        $criteria = new backup_nested_element('criteria');
        $criterion = new backup_nested_element('criterion', ['id'],
            ['paramtemplatecriterionid', 'categoryid', 'position', 'weight']);

        $criteriontexts = new backup_nested_element('criteriontexts');
        $criteriontext = new backup_nested_element('criteriontext', ['id'], ['foreignkey', 'typeid', 'languageid', 'string']);

        $subratings = new backup_nested_element('subratings');
        $subrating = new backup_nested_element('subrating', ['id'], ['criterionid']);

        $subratingtitles = new backup_nested_element('titles');
        $subratingtitle = new backup_nested_element('title', ['id'], ['foreignkey', 'typeid', 'languageid', 'string']);

        $subratingdescriptions = new backup_nested_element('descriptions');
        $subratingdescription = new backup_nested_element('description', ['id'], ['foreignkey', 'typeid', 'languageid', 'string']);

        $subratingverynegatives = new backup_nested_element('verynegatives');
        $subratingverynegative = new backup_nested_element('verynegative', ['id'], ['foreignkey', 'typeid', 'languageid', 'string']);

        $subratingnegatives = new backup_nested_element('negatives');
        $subratingnegative = new backup_nested_element('negative', ['id'], ['foreignkey', 'typeid', 'languageid', 'string']);

        $subratingpositives = new backup_nested_element('positives');
        $subratingpositive = new backup_nested_element('positive', ['id'], ['foreignkey', 'typeid', 'languageid', 'string']);

        $subratingverypositives = new backup_nested_element('verypositives');
        $subratingverypositive = new backup_nested_element('verypositive', ['id'], ['foreignkey', 'typeid', 'languageid', 'string']);

        $submissions = new backup_nested_element('submissions');
        $submission = new backup_nested_element('submission', ['id'], ['instanceid', 'fromuserid', 'touserid', 'status',
            'remarks', ]);

        $responses = new backup_nested_element('responses');
        $response = new backup_nested_element('response', ['id'], ['instanceid', 'submissionid', 'criterionid',
            'fromuserid', 'touserid', 'value', 'studentcomment', 'privatecomment', ]);

        // Build the tree.

        $instance->add_child($languages);
        $languages->add_child($language);

        $instance->add_child($categories);
        $categories->add_child($category);
        $category->add_child($categoryheaders);
        $categoryheaders->add_child($categoryheader);

        $category->add_child($criteria);
        $criteria->add_child($criterion);
        $criterion->add_child($criteriontexts);
        $criteriontexts->add_child($criteriontext);

        $criterion->add_child($subratings);
        $subratings->add_child($subrating);

        $subrating->add_child($subratingtitles);
        $subratingtitles->add_child($subratingtitle);

        $subrating->add_child($subratingdescriptions);
        $subratingdescriptions->add_child($subratingdescription);

        $subrating->add_child($subratingverynegatives);
        $subratingverynegatives->add_child($subratingverynegative);

        $subrating->add_child($subratingnegatives);
        $subratingnegatives->add_child($subratingnegative);

        $subrating->add_child($subratingpositives);
        $subratingpositives->add_child($subratingpositive);

        $subrating->add_child($subratingverypositives);
        $subratingverypositives->add_child($subratingverypositive);

        $instance->add_child($submissions);
        $submissions->add_child($submission);

        $instance->add_child($responses);
        $responses->add_child($response);

        // Define sources.
        $instance->set_source_table(tables::INSTANCE_TABLE, ['id' => backup::VAR_ACTIVITYID]);

        $language->set_source_table(tables::LANGUAGE_TABLE, []);

        $category->set_source_table(tables::INSTANCE_CATEGORY_TABLE, ['instanceid' => backup::VAR_PARENTID], 'id ASC');
        $categoryheader->set_source_table(tables::LOCALIZED_STRING_TABLE, [
            'foreignkey' => backup::VAR_PARENTID,
            'typeid' => backup_helper::is_sqlparam(
                localized_string_type::str2id(localized_string_type::INSTANCE_CATEGORY_HEADER)
            ), ]);

        $criterion->set_source_table(tables::INSTANCE_CRITERION_TABLE, ['categoryid' => backup::VAR_PARENTID], 'id ASC');
        $criteriontext->set_source_table(tables::LOCALIZED_STRING_TABLE, [
            'foreignkey' => backup::VAR_PARENTID,
            'typeid' => backup_helper::is_sqlparam(
                localized_string_type::str2id(localized_string_type::INSTANCE_CRITERION)
            ), ]);

        $subrating->set_source_table(tables::INSTANCE_SUBRATING_TABLE, ['criterionid' => backup::VAR_PARENTID], 'id ASC');
        $subratingtitle->set_source_table(tables::LOCALIZED_STRING_TABLE, [
            'foreignkey' => backup::VAR_PARENTID,
            'typeid' => backup_helper::is_sqlparam(
                localized_string_type::str2id(localized_string_type::INSTANCE_SUBRATING_TITLE)
            ), ]);
        $subratingdescription->set_source_table(tables::LOCALIZED_STRING_TABLE, [
            'foreignkey' => backup::VAR_PARENTID,
            'typeid' => backup_helper::is_sqlparam(
                localized_string_type::str2id(localized_string_type::INSTANCE_SUBRATING_DESCRIPTION)
            ), ]);
        $subratingverynegative->set_source_table(tables::LOCALIZED_STRING_TABLE, [
            'foreignkey' => backup::VAR_PARENTID,
            'typeid' => backup_helper::is_sqlparam(
                localized_string_type::str2id(localized_string_type::INSTANCE_SUBRATING_VERY_NEGATIVE)
            ), ]);
        $subratingnegative->set_source_table(tables::LOCALIZED_STRING_TABLE, [
            'foreignkey' => backup::VAR_PARENTID,
            'typeid' => backup_helper::is_sqlparam(
                localized_string_type::str2id(localized_string_type::INSTANCE_SUBRATING_NEGATIVE)
            ), ]);
        $subratingpositive->set_source_table(tables::LOCALIZED_STRING_TABLE, [
            'foreignkey' => backup::VAR_PARENTID,
            'typeid' => backup_helper::is_sqlparam(
                localized_string_type::str2id(localized_string_type::INSTANCE_SUBRATING_POSITIVE)
            ), ]);
        $subratingverypositive->set_source_table(tables::LOCALIZED_STRING_TABLE, [
            'foreignkey' => backup::VAR_PARENTID,
            'typeid' => backup_helper::is_sqlparam(
                localized_string_type::str2id(localized_string_type::INSTANCE_SUBRATING_VERY_POSITIVE)
            ), ]);

        // All the rest of elements only happen if we are including user info.
        if ($userinfo) {
            $submission->set_source_table(tables::SUBMISSION_TABLE, ['instanceid' => '../../id']);
            $response->set_source_table(tables::RESPONSE_TABLE, ['instanceid' => '../../id']);
        }

        // Define id annotations.
        $submission->annotate_ids('user', 'fromuserid');
        $submission->annotate_ids('user', 'touserid');
        $response->annotate_ids('user', 'fromuserid');
        $response->annotate_ids('user', 'touserid');

        // Define file annotations.
        $instance->annotate_files('mod_verbalfeedback', 'intro', null); // This file area has no itemid.

        // Return the root element (verbalfeedback), wrapped into standard activity structure.
        return $this->prepare_activity_structure($instance);
    }
}
