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

namespace mod_verbalfeedback\repository;

use mod_verbalfeedback\repository\model\localized_string_type;

/**
 * Class for performing DB actions for the verbal feedback activity module.
 *
 * @package   mod_verbalfeedback
 * @copyright 2026 Stephan Robotta <stephan.robotta@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class subrating_repository {
    /**
     * Deletes a subrating by its id.
     * @param int $id The id of the subrating to delete.
     */
    public function delete_by_id(int $id): void {
        global $DB;
        $dbosubrating = $DB->get_record(tables::TEMPLATE_SUBRATINGS_TABLE, ['id' => $id]);
        if ($dbosubrating) {
            $constants = [
                localized_string_type::TEMPLATE_SUBRATING_TITLE,
                localized_string_type::TEMPLATE_SUBRATING_DESCRIPTION,
                localized_string_type::TEMPLATE_SUBRATING_VERY_NEGATIVE,
                localized_string_type::TEMPLATE_SUBRATING_NEGATIVE,
                localized_string_type::TEMPLATE_SUBRATING_POSITIVE,
                localized_string_type::TEMPLATE_SUBRATING_VERY_POSITIVE,
            ];
            foreach ($constants as $constant) {
                $DB->delete_records(
                    tables::LOCALIZED_STRING_TABLE,
                    [
                        'foreignkey' => $dbosubrating->id,
                        'typeid' => localized_string_type::str2id($constant),
                    ],
                );
            }
            $DB->delete_records(tables::TEMPLATE_SUBRATINGS_TABLE, ['id' => $id]);
        }
    }
}
