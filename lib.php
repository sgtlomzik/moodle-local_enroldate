<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Resolve the manual enrolment end timestamp from submitted form data.
 *
 * @param stdClass $data Submitted form data.
 * @return int Unix timestamp, or 0 for no end date.
 */
function local_enroldate_resolve_timeend(stdClass $data): int {
    $timestart = empty($data->timestart) ? 0 : (int)$data->timestart;

    if (!empty($data->timeend)) {
        return (int)$data->timeend;
    }

    if (!empty($data->duration)) {
        return $timestart + (int)$data->duration;
    }

    return 0;
}

/**
 * Keep checked search-result users that still exist in the search result set.
 *
 * @param array $selectedusers Checkbox values indexed by user id.
 * @param array $alloweduserids User ids that are allowed from the latest search.
 * @return array User ids keyed by user id.
 */
function local_enroldate_normalise_selected_users(array $selectedusers, array $alloweduserids): array {
    $allowed = array_fill_keys(array_map('intval', $alloweduserids), true);
    $normalised = [];

    foreach ($selectedusers as $userid => $checked) {
        $userid = (int)$userid;
        if (!empty($checked) && isset($allowed[$userid])) {
            $normalised[$userid] = $userid;
        }
    }

    return $normalised;
}

/**
 * Parse a pasted list of usernames/emails.
 *
 * @param string $userlist Raw textarea value.
 * @return array Trimmed non-empty tokens.
 */
function local_enroldate_parse_userlist(string $userlist): array {
    $tokens = preg_split('/[\r\n,]+/', trim($userlist), -1, PREG_SPLIT_NO_EMPTY);
    return array_values(array_filter(array_map('trim', $tokens), static function($token) {
        return $token !== '';
    }));
}

/**
 * Ensure grade reports include users whose enrolment period has ended.
 */
function local_enroldate_ensure_grade_report_history_visible(): void {
    if ((string)get_config('core', 'grade_report_showonlyactiveenrol') !== '0') {
        set_config('grade_report_showonlyactiveenrol', 0);
    }
}

function local_enroldate_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('enrol/manual:enrol', $context)) {
        $url = new moodle_url('/local/enroldate/index.php', array('id' => $course->id));
        $node = navigation_node::create(
            get_string('pluginname', 'local_enroldate'),
            $url,
            navigation_node::TYPE_SETTING,
            null,
            'local_enroldate',
            new pix_icon('i/enrolusers', '')
        );

        $usersnode = $navigation->find('users', navigation_node::TYPE_CONTAINER);
        if ($usersnode) {
            $usersnode->add_node($node);
        } else {
            $navigation->add_node($node);
        }
    }
}
