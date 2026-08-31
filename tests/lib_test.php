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
 * @covers     ::local_enroldate_extend_navigation_course
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

    /**
     * Search users caps the number of results.
     */
    public function test_search_users_caps_the_number_of_results(): void {
        $this->resetAfterTest();

        for ($i = 0; $i < LOCAL_ENROLDATE_SEARCH_LIMIT + 5; $i++) {
            $this->getDataGenerator()->create_user(['lastname' => 'Cappedperson']);
        }

        $this->assertCount(LOCAL_ENROLDATE_SEARCH_LIMIT, local_enroldate_search_users('Cappedperson'));
    }

    /**
     * Search users excludes the guest account.
     */
    public function test_search_users_excludes_the_guest_account(): void {
        global $CFG, $DB;

        $this->resetAfterTest();

        $guest = $DB->get_record('user', ['id' => $CFG->siteguest], '*', MUST_EXIST);

        $this->assertArrayNotHasKey($guest->id, local_enroldate_search_users($guest->lastname));
        $this->assertArrayNotHasKey($guest->id, local_enroldate_search_users($guest->email));
    }

    /**
     * Search users matches without regard to case.
     */
    public function test_search_users_matches_without_regard_to_case(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user([
            'firstname' => 'Ekaterina',
            'lastname' => 'Mixedcase',
            'email' => 'Ekaterina.Mixedcase@example.test',
        ]);

        $this->assertArrayHasKey($user->id, local_enroldate_search_users('MIXEDCASE'));
        $this->assertArrayHasKey($user->id, local_enroldate_search_users('ekaterina.mixedcase@'));
    }

    /**
     * Search users escapes wildcards in the search term.
     */
    public function test_search_users_escapes_wildcards(): void {
        $this->resetAfterTest();

        $this->getDataGenerator()->create_user(['lastname' => 'Wildcardtarget']);

        // A bare % must be matched literally rather than matching every user.
        $this->assertSame([], local_enroldate_search_users('%'));
    }

    /**
     * Normalise selected users returns nothing without a search result set.
     */
    public function test_normalise_selected_users_without_allowed_ids(): void {
        $this->assertSame([], local_enroldate_normalise_selected_users([3 => 1, 5 => 1], []));
    }

    /**
     * Normalise selected users accepts ids submitted as strings.
     */
    public function test_normalise_selected_users_accepts_string_ids(): void {
        $this->assertSame(
            [7 => 7],
            local_enroldate_normalise_selected_users(['7' => '1'], ['7'])
        );
    }

    /**
     * Parse userlist returns nothing for an empty value.
     *
     * @param string $userlist Raw textarea value.
     * @dataProvider empty_userlist_provider
     */
    public function test_parse_userlist_returns_nothing_for_empty_input(string $userlist): void {
        $this->assertSame([], local_enroldate_parse_userlist($userlist));
    }

    /**
     * Data provider for {@see test_parse_userlist_returns_nothing_for_empty_input()}.
     *
     * @return array[] Textarea values that hold no identifiers.
     */
    public static function empty_userlist_provider(): array {
        return [
            'empty string' => [''],
            'whitespace only' => ["  \n\t "],
            'separators only' => [",,\n,\r\n,"],
        ];
    }

    /**
     * Grade report visibility leaves an already visible setting alone.
     */
    public function test_grade_report_visibility_leaves_the_setting_alone_when_already_set(): void {
        $this->resetAfterTest();

        set_config('forcegradehistory', 1, 'local_enroldate');
        set_config('grade_report_showonlyactiveenrol', 0);

        local_enroldate_ensure_grade_report_history_visible();

        $this->assertSame('0', (string)get_config('core', 'grade_report_showonlyactiveenrol'));
    }

    /**
     * Navigation adds the plugin page for a user who may enrol.
     */
    public function test_extend_navigation_course_adds_the_node_for_an_enroller(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);

        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $this->setUser($teacher);

        $navigation = new \navigation_node(['text' => 'Course', 'type' => \navigation_node::TYPE_COURSE]);
        $navigation->add(
            'Users',
            null,
            \navigation_node::TYPE_CONTAINER,
            null,
            'users'
        );

        local_enroldate_extend_navigation_course($navigation, $course, $context);

        $node = $navigation->find('local_enroldate', \navigation_node::TYPE_SETTING);
        $this->assertNotFalse($node);
        $this->assertSame(get_string('pluginname', 'local_enroldate'), (string)$node->text);
        $this->assertSame(
            (new \moodle_url('/local/enroldate/index.php', ['id' => $course->id]))->out(),
            $node->action()->out()
        );

        // It belongs under the participants section rather than at the top level.
        $usersnode = $navigation->find('users', \navigation_node::TYPE_CONTAINER);
        $this->assertNotFalse($usersnode->find('local_enroldate', \navigation_node::TYPE_SETTING));
    }

    /**
     * Navigation falls back to the root when there is no users section.
     */
    public function test_extend_navigation_course_falls_back_to_the_root(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);

        $navigation = new \navigation_node(['text' => 'Course', 'type' => \navigation_node::TYPE_COURSE]);

        local_enroldate_extend_navigation_course($navigation, $course, $context);

        $this->assertNotFalse($navigation->find('local_enroldate', \navigation_node::TYPE_SETTING));
    }

    /**
     * Navigation hides the plugin page from users who may not enrol.
     */
    public function test_extend_navigation_course_hides_the_node_from_students(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);

        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');
        $this->setUser($student);

        $navigation = new \navigation_node(['text' => 'Course', 'type' => \navigation_node::TYPE_COURSE]);

        local_enroldate_extend_navigation_course($navigation, $course, $context);

        $this->assertFalse($navigation->find('local_enroldate', \navigation_node::TYPE_SETTING));
    }
}
