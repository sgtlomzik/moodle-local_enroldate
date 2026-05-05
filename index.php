<?php
require_once('../../config.php');
require_once($CFG->dirroot.'/enrol/locallib.php');

$courseid = required_param('id', PARAM_INT);
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$context = context_course::instance($course->id);

require_login($course);
require_capability('enrol/manual:enrol', $context);

$PAGE->set_url('/local/enroldate/index.php', array('id' => $course->id));
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_enroldate'));
$PAGE->set_heading($course->fullname);

$instances = enrol_get_instances($course->id, true);
$manualinstance = null;
$enrolplugin = enrol_get_plugin('manual');

foreach ($instances as $instance) {
    if ($instance->enrol === 'manual') {
        $manualinstance = $instance;
        break;
    }
}

if (!$manualinstance) {
    print_error('nomaskenrol', 'local_enroldate');
}

$mform_raw = new \local_enroldate\form\enrol_form(null, ['context' => $context, 'search_results' => []]);
$rawdata = $mform_raw->get_submitted_data();
$search_results = [];

if (!empty($rawdata->searchbutton) && !empty($rawdata->searchquery)) {
    $searchterm = '%' . trim($rawdata->searchquery) . '%';
    $sql = "SELECT id, firstname, lastname, email, middlename, alternatename, firstnamephonetic, lastnamephonetic FROM {user} WHERE deleted = 0 AND id <> :guestid 
            AND (".$DB->sql_like('firstname', ':s1', false)." OR ".$DB->sql_like('lastname', ':s2', false)." OR ".$DB->sql_like('email', ':s3', false).")";
    $search_results = $DB->get_records_sql($sql, ['guestid' => $CFG->siteguest, 's1' => $searchterm, 's2' => $searchterm, 's3' => $searchterm], 0, 20);
}

$mform = new \local_enroldate\form\enrol_form(null, ['context' => $context, 'search_results' => $search_results]);
$mform->set_data(['id' => $course->id]);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/user/index.php', ['id' => $course->id]));
} else if ($data = $mform->get_data()) {
    $success_count = 0;

    $timestart = $data->timestart;
    $timeend = 0;
    
    if (!empty($data->timeend)) {
        $timeend = $data->timeend;
    } 

    else if (!empty($data->duration)) {
        $timeend = $timestart + $data->duration;
    }

    $status = $data->status; 
    $roleid = $data->roleid;

    $selectedusers = optional_param_array('selectedusers', [], PARAM_INT);
    
    if (!empty($selectedusers)) {
        foreach ($selectedusers as $userid => $checked) {
            if ($checked) {
                // ПЕРЕДАЕМ НОВЫЕ ПАРАМЕТРЫ: $timeend и $status
                $enrolplugin->enrol_user($manualinstance, $userid, $roleid, $timestart, $timeend, $status);
                $success_count++;
            }
        }
    }

    if (!empty($data->userlist)) {
        $ids = preg_split('/[\r\n,]+/', trim($data->userlist), -1, PREG_SPLIT_NO_EMPTY);
        foreach ($ids as $id) {
            $user = $DB->get_record_select('user', 'deleted = 0 AND (LOWER(email) = ? OR LOWER(username) = ?)', [core_text::strtolower(trim($id)), core_text::strtolower(trim($id))], '*', IGNORE_MULTIPLE);
            if ($user) {
                $enrolplugin->enrol_user($manualinstance, $user->id, $roleid, $timestart, $timeend, $status);
                $success_count++;
            }
        }
    }

    if ($success_count > 0) {
        \core\notification::success(get_string('successenrol', 'local_enroldate', $success_count));
    } else {
        \core\notification::warning("Пользователи не выбраны для зачисления");
    }
    
    redirect(new moodle_url('/local/enroldate/index.php', ['id' => $course->id]));
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();