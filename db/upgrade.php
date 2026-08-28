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
 * Upgrade steps for local_enroldate.
 *
 * @package    local_enroldate
 * @copyright  2026 SgtLomzik <lomzike@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade the local_enroldate plugin.
 *
 * @param int $oldversion The version we are upgrading from.
 * @return bool Always true.
 */
function xmldb_local_enroldate_upgrade($oldversion) {
    if ($oldversion < 2026082800) {
        // Earlier releases forced the core grade_report_showonlyactiveenrol setting to 0
        // from db/install.php and again on every page load. That is now a plugin setting
        // which is on by default, so behaviour is unchanged, but administrators can turn
        // it off and keep their own value for the core setting.
        set_config('forcegradehistory', 1, 'local_enroldate');

        upgrade_plugin_savepoint(true, 2026082800, 'local', 'enroldate');
    }

    return true;
}
