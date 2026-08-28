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
 * Enrol users into a course with an explicit start date.
 *
 * @package    local_enroldate
 * @copyright  2026 SgtLomzik <lomzike@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/enroldate/lib.php');
require_once($CFG->dirroot . '/enrol/locallib.php');

$courseid = required_param('id', PARAM_INT);

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$context = context_course::instance($course->id);

require_login($course);
require_capability('enrol/manual:enrol', $context);

local_enroldate_ensure_grade_report_history_visible();

$PAGE->set_url('/local/enroldate/index.php', ['id' => $course->id]);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('pluginname', 'local_enroldate'));
$PAGE->set_heading($course->fullname);

$enrolplugin = enrol_get_plugin('manual');
$manualinstance = null;

foreach (enrol_get_instances($course->id, true) as $instance) {
    if ($instance->enrol === 'manual') {
        $manualinstance = $instance;
        break;
    }
}

if (!$manualinstance || !$enrolplugin) {
    throw new moodle_exception('nomaskenrol', 'local_enroldate');
}

// Read the raw submission first so that pressing "Search" can repopulate the form
// with results before it is validated.
$searchform = new \local_enroldate\form\enrol_form(null, ['context' => $context, 'searchresults' => []]);
$rawdata = $searchform->get_submitted_data();
$searchresults = [];

if (!empty($rawdata->searchbutton) && !empty($rawdata->searchquery)) {
    require_sesskey();
    $searchresults = local_enroldate_search_users((string)$rawdata->searchquery);
}

$mform = new \local_enroldate\form\enrol_form(null, ['context' => $context, 'searchresults' => $searchresults]);
$mform->set_data(['id' => $course->id]);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/user/index.php', ['id' => $course->id]));
} else if ($data = $mform->get_data()) {
    $timestart = (int)$data->timestart;
    $timeend = local_enroldate_resolve_timeend($data);
    $status = (int)$data->status;
    $roleid = (int)$data->roleid;

    $selectedusers = optional_param_array('selectedusers', [], PARAM_INT);
    $existingids = [];
    foreach (array_keys($selectedusers) as $userid) {
        if (!empty($selectedusers[$userid]) && $DB->record_exists('user', ['id' => (int)$userid, 'deleted' => 0])) {
            $existingids[] = (int)$userid;
        }
    }

    $userids = local_enroldate_normalise_selected_users($selectedusers, $existingids);
    $notfound = [];

    if (!empty($data->userlist)) {
        foreach (local_enroldate_parse_userlist($data->userlist) as $identifier) {
            $needle = core_text::strtolower(trim($identifier));
            $user = $DB->get_record_select(
                'user',
                'deleted = 0 AND (LOWER(email) = ? OR LOWER(username) = ?)',
                [$needle, $needle],
                'id',
                IGNORE_MULTIPLE
            );
            if ($user) {
                $userids[(int)$user->id] = (int)$user->id;
            } else {
                $notfound[] = $identifier;
            }
        }
    }

    foreach ($userids as $userid) {
        $enrolplugin->enrol_user($manualinstance, $userid, $roleid, $timestart, $timeend, $status, true);
    }

    if (!empty($userids)) {
        \core\notification::success(get_string('successenrol', 'local_enroldate', count($userids)));
    } else {
        \core\notification::warning(get_string('nousersselected', 'local_enroldate'));
    }

    if (!empty($notfound)) {
        // The identifiers come straight from the textarea, so they must be escaped
        // before they are put into a notification, which renders HTML.
        \core\notification::warning(
            get_string('notfoundusers', 'local_enroldate', s(implode(', ', $notfound)))
        );
    }

    redirect(new moodle_url('/local/enroldate/index.php', ['id' => $course->id]));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_enroldate'));
$mform->display();
echo $OUTPUT->footer();
