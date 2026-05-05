<?php
define('MOODLE_INTERNAL', true);
require_once(__DIR__ . '/../lib.php');

function assert_same($expected, $actual, $message) {
    if ($expected !== $actual) {
        fwrite(STDERR, $message . PHP_EOL);
        fwrite(STDERR, 'Expected: ' . var_export($expected, true) . PHP_EOL);
        fwrite(STDERR, 'Actual:   ' . var_export($actual, true) . PHP_EOL);
        exit(1);
    }
}

$data = (object)['timestart' => 1000, 'timeend' => 2000, 'duration' => 86400];
assert_same(2000, local_enroldate_resolve_timeend($data), 'Explicit end date must take precedence over duration.');

$data = (object)['timestart' => 1000, 'timeend' => 0, 'duration' => 86400];
assert_same(87400, local_enroldate_resolve_timeend($data), 'Duration must be added to start date when end date is empty.');

$data = (object)['timestart' => 1000, 'timeend' => 0, 'duration' => 0];
assert_same(0, local_enroldate_resolve_timeend($data), 'Empty duration and end date must mean no end date.');

assert_same(
    [3 => 3, 5 => 5],
    local_enroldate_normalise_selected_users([3 => 1, 5 => '1', 9 => 0], [3, 5, 7]),
    'Selected users must be deduplicated and restricted to existing user ids.'
);

assert_same(
    ['a@example.test', 'user1', 'user2'],
    local_enroldate_parse_userlist(" a@example.test\r\nuser1,user2,, "),
    'Bulk user list must split by comma and new lines, trim tokens, and drop empty tokens.'
);

echo "local_enroldate logic tests passed\n";
