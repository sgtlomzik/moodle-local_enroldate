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
 * Unit tests for the local_enroldate library functions.
 *
 * @package    local_enroldate
 * @copyright  2026 SgtLomzik <lomzike@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_enroldate;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/enroldate/lib.php');

/**
 * Tests for the helper logic in lib.php.
 *
 * @package    local_enroldate
 * @copyright  2026 SgtLomzik <lomzike@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     ::local_enroldate_resolve_timeend
 * @covers     ::local_enroldate_normalise_selected_users
 * @covers     ::local_enroldate_parse_userlist
 * @covers     ::local_enroldate_search_users
 * @covers     ::local_enroldate_ensure_grade_report_history_visible
 */
final class lib_test extends \advanced_testcase {

    /**
     * Resolve timeend prefers explicit end date.
     */
    public function test_resolve_timeend_prefers_explicit_end_date(): void {
        $data = (object)[
            'timestart' => 1000,
            'timeend' => 2000,
            'duration' => DAYSECS,
        ];

        $this->assertSame(2000, local_enroldate_resolve_timeend($data));
    }

    /**
     * Resolve timeend uses duration when end date is empty.
     */
    public function test_resolve_timeend_uses_duration_when_end_date_is_empty(): void {
        $data = (object)[
            'timestart' => 1000,
            'timeend' => 0,
            'duration' => DAYSECS,
        ];

        $this->assertSame(1000 + DAYSECS, local_enroldate_resolve_timeend($data));
    }

    /**
     * Resolve timeend returns zero without end date or duration.
     */
    public function test_resolve_timeend_returns_zero_without_end_date_or_duration(): void {
        $data = (object)[
            'timestart' => 1000,
            'timeend' => 0,
            'duration' => 0,
        ];

        $this->assertSame(0, local_enroldate_resolve_timeend($data));
    }

    /**
     * Normalise selected users filters unchecked and unknown ids.
     */
    public function test_normalise_selected_users_filters_unchecked_and_unknown_ids(): void {
        $this->assertSame(
            [3 => 3, 5 => 5],
            local_enroldate_normalise_selected_users([3 => 1, 5 => '1', 9 => 0, 11 => 1], [3, 5, 7])
        );
    }

    /**
     * Parse userlist splits commas and new lines.
     */
    public function test_parse_userlist_splits_commas_and_new_lines(): void {
        $this->assertSame(
            ['a@example.test', 'user1', 'user2'],
            local_enroldate_parse_userlist(" a@example.test\r\nuser1,user2,, ")
        );
    }

    /**
     * Search users matches names and email.
     */
    public function test_search_users_matches_names_and_email(): void {
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $wanted = $generator->create_user([
            'firstname' => 'Zinaida',
            'lastname' => 'Searchable',
            'email' => 'zinaida@example.test',
        ]);
        $other = $generator->create_user([
            'firstname' => 'Nobody',
            'lastname' => 'Unrelated',
            'email' => 'nobody@example.test',
        ]);

        $bylastname = local_enroldate_search_users('Searchable');
        $this->assertArrayHasKey($wanted->id, $bylastname);
        $this->assertArrayNotHasKey($other->id, $bylastname);

        $byemail = local_enroldate_search_users('zinaida@example');
        $this->assertArrayHasKey($wanted->id, $byemail);
    }

    /**
     * Search users ignores empty and deleted.
     */
    public function test_search_users_ignores_empty_and_deleted(): void {
        $this->resetAfterTest();

        $deleted = $this->getDataGenerator()->create_user([
            'firstname' => 'Gone',
            'lastname' => 'Deletedperson',
        ]);
        delete_user($deleted);

        $this->assertSame([], local_enroldate_search_users('   '));
        $this->assertArrayNotHasKey($deleted->id, local_enroldate_search_users('Deletedperson'));
    }

    /**
     * Grade report visibility respects the plugin setting.
     */
    public function test_grade_report_visibility_respects_the_plugin_setting(): void {
        $this->resetAfterTest();

        set_config('grade_report_showonlyactiveenrol', 1);
        set_config('forcegradehistory', 0, 'local_enroldate');
        local_enroldate_ensure_grade_report_history_visible();
        $this->assertSame('1', (string)get_config('core', 'grade_report_showonlyactiveenrol'));

        set_config('forcegradehistory', 1, 'local_enroldate');
        local_enroldate_ensure_grade_report_history_visible();
        $this->assertSame('0', (string)get_config('core', 'grade_report_showonlyactiveenrol'));
    }
}
