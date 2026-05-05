<?php
defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

/**
 * Tests for local_enroldate helper logic.
 *
 * @covers ::local_enroldate_resolve_timeend
 * @covers ::local_enroldate_normalise_selected_users
 * @covers ::local_enroldate_parse_userlist
 */
class local_enroldate_lib_test extends advanced_testcase {

    public function test_resolve_timeend_prefers_explicit_end_date(): void {
        $data = (object)[
            'timestart' => 1000,
            'timeend' => 2000,
            'duration' => DAYSECS,
        ];

        $this->assertSame(2000, local_enroldate_resolve_timeend($data));
    }

    public function test_resolve_timeend_uses_duration_when_end_date_is_empty(): void {
        $data = (object)[
            'timestart' => 1000,
            'timeend' => 0,
            'duration' => DAYSECS,
        ];

        $this->assertSame(87400, local_enroldate_resolve_timeend($data));
    }

    public function test_normalise_selected_users_filters_unchecked_and_unknown_ids(): void {
        $this->assertSame(
            [3 => 3, 5 => 5],
            local_enroldate_normalise_selected_users([3 => 1, 5 => '1', 9 => 0, 11 => 1], [3, 5, 7])
        );
    }

    public function test_parse_userlist_splits_commas_and_new_lines(): void {
        $this->assertSame(
            ['a@example.test', 'user1', 'user2'],
            local_enroldate_parse_userlist(" a@example.test\r\nuser1,user2,, ")
        );
    }
}
