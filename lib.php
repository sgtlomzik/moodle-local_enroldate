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
 * Library functions and navigation callback for local_enroldate.
 *
 * @package    local_enroldate
 * @copyright  2026 SgtLomzik <lomzike@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Maximum number of users shown by one search.
 */
const LOCAL_ENROLDATE_SEARCH_LIMIT = 20;

/**
 * Resolve the manual enrolment end timestamp from submitted form data.
 *
 * An explicit end date wins over a duration; with neither, the enrolment has no end.
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
 * Parse a pasted list of usernames or email addresses.
 *
 * @param string $userlist Raw textarea value.
 * @return array Trimmed non-empty tokens.
 */
function local_enroldate_parse_userlist(string $userlist): array {
    $tokens = preg_split('/[\r\n,]+/', trim($userlist), -1, PREG_SPLIT_NO_EMPTY);
    return array_values(array_filter(array_map('trim', $tokens), static function ($token) {
        return $token !== '';
    }));
}

/**
 * Find users matching a search term, for the enrolment form.
 *
 * @param string $searchterm Free text matched against first name, last name and email.
 * @return array User records keyed by user id, capped at LOCAL_ENROLDATE_SEARCH_LIMIT.
 */
function local_enroldate_search_users(string $searchterm): array {
    global $CFG, $DB;

    $searchterm = trim($searchterm);
    if ($searchterm === '') {
        return [];
    }

    $like = '%' . $DB->sql_like_escape($searchterm) . '%';

    // The explicit field list is what fullname() needs; selecting the whole user
    // record would pull far more personal data than this form ever displays.
    $sql = "SELECT id, firstname, lastname, email, middlename, alternatename,
                   firstnamephonetic, lastnamephonetic
              FROM {user}
             WHERE deleted = 0
               AND id <> :guestid
               AND (" . $DB->sql_like('firstname', ':s1', false) . "
                 OR " . $DB->sql_like('lastname', ':s2', false) . "
                 OR " . $DB->sql_like('email', ':s3', false) . ")
          ORDER BY lastname ASC, firstname ASC";

    return $DB->get_records_sql($sql, [
        'guestid' => $CFG->siteguest,
        's1' => $like,
        's2' => $like,
        's3' => $like,
    ], 0, LOCAL_ENROLDATE_SEARCH_LIMIT);
}

/**
 * Make sure grade reports keep showing users whose enrolment period has ended.
 *
 * Enrolling somebody with a past start date and a past end date is the whole point of
 * this plugin, but by default grade reports only list actively enrolled users, so those
 * learners disappear from the gradebook. Setting the core `grade_report_showonlyactiveenrol`
 * option to 0 keeps them visible.
 *
 * This changes a site-wide core setting, so it only happens while the plugin's
 * "Keep past enrolments visible in grade reports" setting is on.
 */
function local_enroldate_ensure_grade_report_history_visible(): void {
    if (!get_config('local_enroldate', 'forcegradehistory')) {
        return;
    }

    if ((string)get_config('core', 'grade_report_showonlyactiveenrol') !== '0') {
        set_config('grade_report_showonlyactiveenrol', 0);
    }
}

/**
 * Add the plugin's page to the course navigation.
 *
 * @param navigation_node $navigation The course navigation node.
 * @param stdClass $course The course.
 * @param context_course $context The course context.
 */
function local_enroldate_extend_navigation_course($navigation, $course, $context) {
    if (!has_capability('enrol/manual:enrol', $context)) {
        return;
    }

    $url = new moodle_url('/local/enroldate/index.php', ['id' => $course->id]);
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
