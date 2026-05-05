<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_local_enroldate_install() {
    set_config('grade_report_showonlyactiveenrol', 0);
}
