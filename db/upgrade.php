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

// This file keeps track of upgrades to
// the feedback module
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installation to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the methods of database_manager class
//
// Please do not forget to use upgrade_set_timeout()
// before any action that may take longer time to finish.

/**
 * Upgrade code for the verbalfeedback activity plugin.
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_verbalfeedback\repository\model\localized_string_type;

/**
 * Upgrade code for the verbalfeedback activity plugin.
 *
 * @package   mod_verbalfeedback
 * @copyright 2020 Kevin Tippenhauer <kevin.tippenhauer@bfh.ch>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * The verbalfeedback upgrade function.
 *
 * @param int $oldversion The old version number.
 * @return bool
 * @throws downgrade_exception
 * @throws upgrade_exception
 */
function xmldb_verbalfeedback_upgrade($oldversion) {

    global $CFG, $DB;

    require_once($CFG->libdir.'/db/upgradelib.php'); // Core Upgrade-related functions.

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    // Put any upgrade step following this.

    if ($oldversion < 2021100103) {

        // Add new grade field to verbalfeedback table.
        $table = new xmldb_table('verbalfeedback');
        $field = new xmldb_field('grade', XMLDB_TYPE_INTEGER, '3', XMLDB_UNSIGNED, 0, null, null, 0);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Main savepoint reached.
        upgrade_main_savepoint(true, 2021100103);
    }

    if ($oldversion < 2024101700) {
        $table = new xmldb_table('verbalfeedback_local_string');
        $field = new xmldb_field('typeid', XMLDB_TYPE_INTEGER, '3', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Convert all strings to ids.
        foreach (localized_string_type::getStringTypes() as $type) {
            $DB->execute(
                'UPDATE {verbalfeedback_local_string} SET typeid = ? WHERE type = ?',
                [localized_string_type::str2id($type), $type]
            );
        }
        $dbman->drop_field($table, new xmldb_field('type'));
        $table->add_index('subitemtype', XMLDB_INDEX_NOTUNIQUE, ['foreignkey', 'typeid']);

        upgrade_mod_savepoint(true, 2024101700, 'verbalfeedback');
    }

    return true;
}
