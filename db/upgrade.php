<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_local_enroldate_upgrade($oldversion) {
    if ($oldversion < 2026050500) {
        set_config('grade_report_showonlyactiveenrol', 0);
        upgrade_plugin_savepoint(true, 2026050500, 'local', 'enroldate');
    }

    return true;
}
