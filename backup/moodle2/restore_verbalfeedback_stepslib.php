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
 * Defines all the restore steps that will be used by the restore_verbalfeedback_activity_task
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_verbalfeedback\repository\model\localized_string_type;
use mod_verbalfeedback\repository\tables;

/**
 * Structure step to restore one verbalfeedback activity instance
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_verbalfeedback_activity_structure_step extends restore_activity_structure_step {

    /**
     * The instance id when the item is newly inserted.
     *
     * @var int
     */
    private $instanceid;

    /**
     * Function that will return the structure to be processed by this restore_step.
     * Must return one array of @restore_path_element elements
     *
     * @return array
     */
    protected function define_structure() {

        $paths = [];
        $userinfo = $this->get_setting_value('userinfo');

        // Using 'instance' instead of 'verbalfeedback' does not work here.
        $paths[] = new restore_path_element('verbalfeedback', '/activity/instance');

        // Languages.
        $paths[] = new restore_path_element('language', '/activity/instance/languages/language');

        // Category.
        $paths[] = new restore_path_element('category', '/activity/instance/categories/category');
        $paths[] = new restore_path_element('categoryheader', '/activity/instance/categories/' .
            'category/categoryheaders/categoryheader');

        // Criterion.
        $paths[] = new restore_path_element('criterion',
            '/activity/instance/categories/category/criteria/criterion');
        $paths[] = new restore_path_element('criteriontext',
            '/activity/instance/categories/category/criteria/criterion/criteriontexts/criteriontext');

        // Subrating.
        $paths[] = new restore_path_element('subrating',
            '/activity/instance/categories/category/criteria/criterion/subratings/subrating');
        $paths[] = new restore_path_element('subratingtitle',
            '/activity/instance/categories/category/criteria/criterion/subratings/subrating/titles/title');
        $paths[] = new restore_path_element('subratingdescription',
            '/activity/instance/categories/category/criteria/criterion/subratings/subrating/descriptions/description');
        $paths[] = new restore_path_element('subratingverynegative',
            '/activity/instance/categories/category/criteria/criterion/subratings/subrating/verynegatives/verynegative');
        $paths[] = new restore_path_element('subratingnegative',
            '/activity/instance/categories/category/criteria/criterion/subratings/subrating/negatives/negative');
        $paths[] = new restore_path_element('subratingpositive',
            '/activity/instance/categories/category/criteria/criterion/subratings/subrating/positives/positive');
        $paths[] = new restore_path_element('subratingverypositive',
            '/activity/instance/categories/category/criteria/criterion/subratings/subrating/verypositives/verypositive');

        if ($userinfo) {
            $paths[] = new restore_path_element('submission', '/activity/instance/submissions/submission');
            $paths[] = new restore_path_element('response', '/activity/instance/responses/response');
        }

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Processes the instance.
     *
     * @param array $data The instance data from the backup file.
     */
    protected function process_verbalfeedback($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->get_courseid();

        // Any changes to the list of dates that needs to be rolled should be same during course restore and course reset.
        // See MDL-9367.
        $data->timeopen = $this->apply_date_offset($data->timeopen);
        $data->timeclose = $this->apply_date_offset($data->timeclose);

        // Remove the template relationship on restore but not on duplicate/copy.
        if ($this->get_task()->get_info()->mode != backup::MODE_IMPORT) {
            $data->templateid = null;
        }

        // Insert the verbal feedback record.
        $this->instanceid = $DB->insert_record(tables::INSTANCE_TABLE, $data);
        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($this->instanceid);
    }

    /**
     * Processes language data from the verbal feedback instance.
     *
     * @param array $data The item data from the backup file.
     */
    protected function process_language($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $newid = $DB->get_record(tables::LANGUAGE_TABLE, ['language' => $data->language], 'id');
        if (isset($newid->id)) {
            $this->set_mapping('language', $oldid, $newid->id);
        } else {
            $newid = $DB->insert_record(tables::LANGUAGE_TABLE, $data);
            $this->set_mapping('language', $oldid, $newid);
        }
    }

    /**
     * Processes category data from the verbal feedback instance.
     *
     * @param array $data The item data from the backup file.
     */
    protected function process_category($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        // Using 'instance' instead of 'verbalfeedback' does not work here.
        $data->instanceid = $this->get_new_parentid('verbalfeedback');

        // Remove the template relationship on restore but not on duplicate/copy.
        if ($this->get_task()->get_info()->mode != backup::MODE_IMPORT) {
            $data->paramtemplatecategoryid = null;
        }

        $newcategoryid = $DB->insert_record(tables::INSTANCE_CATEGORY_TABLE, $data);
        $this->set_mapping('category', $oldid, $newcategoryid);
    }

    /**
     * Processes category header data from the verbal feedback instance.
     *
     * @param array $data The item data from the backup file.
     */
    protected function process_categoryheader($data) {
        $this->process_localized_string('category', $data);
    }

    /**
     * Processes criterion data.
     *
     * @param array $data The criterion data from the backup file.
     */
    protected function process_criterion($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $data->categoryid = $this->get_mappingid('category', $data->categoryid);

        $newid = $DB->insert_record(tables::INSTANCE_CRITERION_TABLE, $data);

        // Remove the template relationship on restore but not on duplicate/copy.
        if ($this->get_task()->get_info()->mode != backup::MODE_IMPORT) {
            $data->paramtemplatecriterionid = null;
        }

        $this->set_mapping('criterion', $oldid, $newid);
    }

    /**
     * Processes criterion text data.
     *
     * @param array $data The criterion text data from the backup file.
     */
    protected function process_criteriontext($data) {
        $this->process_localized_string('criterion', $data);
    }


    /**
     * Processes subrating data.
     *
     * @param array $data The subrating data from the backup file.
     */
    protected function process_subrating($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $data->criterionid = $this->get_mappingid('criterion', $data->criterionid);

        $newid = $DB->insert_record(tables::INSTANCE_SUBRATING_TABLE, $data);

        $this->set_mapping('subrating', $oldid, $newid);
    }

    /**
     * Processes subrating title data.
     *
     * @param array $data The subrating title data from the backup file.
     */
    protected function process_subratingtitle($data) {
        $this->process_localized_string('subrating', $data);
    }

    /**
     * Processes subrating description data.
     *
     * @param array $data The subrating description data from the backup file.
     */
    protected function process_subratingdescription($data) {
        $this->process_localized_string('subrating', $data);
    }

    /**
     * Processes subrating verynegative data.
     *
     * @param array $data The subrating verynegative data from the backup file.
     */
    protected function process_subratingverynegative($data) {
        $this->process_localized_string('subrating', $data);
    }

    /**
     * Processes subrating negative data.
     *
     * @param array $data The subrating negative data from the backup file.
     */
    protected function process_subratingnegative($data) {
        $this->process_localized_string('subrating', $data);
    }

    /**
     * Processes subrating positive data.
     *
     * @param array $data The subrating positive data from the backup file.
     */
    protected function process_subratingpositive($data) {
        $this->process_localized_string('subrating', $data);
    }

    /**
     * Processes subrating verypositive data.
     *
     * @param array $data The subrating verypositive data from the backup file.
     */
    protected function process_subratingverypositive($data) {
        $this->process_localized_string('subrating', $data);
    }

    /**
     * Processes submission data from the verbal feedback instance.
     *
     * @param array $data The submission data from the backup file.
     */
    protected function process_submission($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->instanceid = $this->get_new_parentid('verbalfeedback');
        $data->fromuserid = $this->get_mappingid('user', $data->fromuserid);
        $data->touserid = $this->get_mappingid('user', $data->touserid);

        $newid = $DB->insert_record(tables::SUBMISSION_TABLE, $data);
        $this->set_mapping('submission', $oldid, $newid);
    }

    /**
     * Processes response data from the verbal feedback instance.
     *
     * @param array $data The response data from the backup file.
     */
    protected function process_response($data) {
        global $DB;

        $data = (object) $data;

        $data->instanceid = $this->get_new_parentid('verbalfeedback');
        $data->submissionid = $this->get_mappingid('submission', $data->submissionid);
        $data->criterionid = $this->get_mappingid('criterion', $data->criterionid);
        $data->fromuser = $this->get_mappingid('user', $data->fromuser);
        $data->touser = $this->get_mappingid('user', $data->touser);

        $DB->insert_record(tables::RESPONSE_TABLE, $data);
    }

    /**
     * Post-execution processing.
     */
    protected function after_execute() {
        // Add verbalfeedback related files, no need to match by itemname (just internally handled context).
        $this->add_related_files('mod_verbalfeedback', 'intro', null);
    }

    /**
     * Processes localized string data.
     *
     * @param string $foreigenkeymapping The itemname for the id mapping function.
     * @param array $data The criterion text data from the backup file.
     */
    private function process_localized_string($foreigenkeymapping, $data) {
        global $DB;

        $data = (object) $data;
        $data->foreignkey = $this->get_mappingid($foreigenkeymapping, $data->foreignkey);
        $data->languageid = $this->get_mappingid('language', $data->languageid);
        $data->typeid = $this->get_mappingid('typeid', $data->typeid);
        $data->instanceid = $this->instanceid;
        $DB->insert_record('verbalfeedback_local_string', $data);
    }
}
