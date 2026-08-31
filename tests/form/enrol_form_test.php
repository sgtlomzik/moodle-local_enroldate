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
 * Unit tests for the local_enroldate enrolment form.
 *
 * @package    local_enroldate
 * @copyright  2026 SgtLomzik <lomzike@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_enroldate\form;

/**
 * Tests for the form definition and the enrolment period validation.
 *
 * @package    local_enroldate
 * @copyright  2026 SgtLomzik <lomzike@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_enroldate\form\enrol_form
 */
final class enrol_form_test extends \advanced_testcase {
    /**
     * Build the form the way index.php does.
     *
     * @param array $searchresults User records to offer as search results.
     * @return array{0:enrol_form,1:\stdClass} The form and the course it is bound to.
     */
    private function make_form(array $searchresults = []): array {
        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);

        $form = new enrol_form(null, ['context' => $context, 'searchresults' => $searchresults]);

        return [$form, $course];
    }

    /**
     * Reach the QuickForm the form built, which moodleform keeps protected.
     *
     * @param enrol_form $form The form to read.
     * @return \MoodleQuickForm
     */
    private function inner_form(enrol_form $form): \MoodleQuickForm {
        $property = new \ReflectionProperty(\moodleform::class, '_form');

        return $property->getValue($form);
    }

    /**
     * An end date before the start date is rejected.
     */
    public function test_validation_rejects_an_end_date_before_the_start(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        [$form] = $this->make_form();

        $errors = $form->validation([
            'timestart' => 2000,
            'timeend' => 1000,
        ], []);

        $this->assertArrayHasKey('timeend', $errors);
        $this->assertSame(get_string('enroltimeendinvalid', 'enrol'), $errors['timeend']);
    }

    /**
     * An end date equal to the start date leaves no enrolment period at all.
     */
    public function test_validation_rejects_an_end_date_equal_to_the_start(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        [$form] = $this->make_form();

        $errors = $form->validation(['timestart' => 2000, 'timeend' => 2000], []);

        $this->assertArrayHasKey('timeend', $errors);
    }

    /**
     * Valid and open-ended enrolment periods are accepted.
     *
     * @param array $data Submitted period fields.
     * @dataProvider valid_period_provider
     */
    public function test_validation_accepts_valid_periods(array $data): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        [$form] = $this->make_form();

        $this->assertSame([], $form->validation($data, []));
    }

    /**
     * Data provider for {@see test_validation_accepts_valid_periods()}.
     *
     * @return array[] Submitted period fields that must pass validation.
     */
    public static function valid_period_provider(): array {
        return [
            'end after start' => [['timestart' => 1000, 'timeend' => 2000]],
            'no end date' => [['timestart' => 1000, 'timeend' => 0]],
            'no dates at all' => [['timestart' => 0, 'timeend' => 0]],
            // A backdated enrolment with no end is the main use case for the plugin.
            'backdated start' => [['timestart' => 1, 'timeend' => 0]],
        ];
    }

    /**
     * The form carries the fields index.php reads back.
     */
    public function test_definition_declares_the_enrolment_fields(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        [$form] = $this->make_form();
        $mform = $this->inner_form($form);

        foreach (['searchquery', 'roleid', 'status', 'timestart', 'duration', 'timeend', 'userlist', 'id'] as $name) {
            $this->assertTrue($mform->elementExists($name), "Missing form element {$name}");
        }

        $this->assertSame(ENROL_USER_ACTIVE, $mform->getElement('status')->getValue()[0]);
    }

    /**
     * Search results become one checkbox each, and nothing is shown without them.
     */
    public function test_definition_offers_a_checkbox_per_search_result(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $userone = $this->getDataGenerator()->create_user();
        $usertwo = $this->getDataGenerator()->create_user();

        [$form] = $this->make_form([$userone->id => $userone, $usertwo->id => $usertwo]);
        $mform = $this->inner_form($form);

        $this->assertTrue($mform->elementExists("selectedusers[{$userone->id}]"));
        $this->assertTrue($mform->elementExists("selectedusers[{$usertwo->id}]"));
        $this->assertTrue($mform->elementExists('info'));

        // Two results is well under the cap, so no truncation notice is shown.
        $this->assertFalse($mform->elementExists('searchtruncated'));

        [$emptyform] = $this->make_form();
        $this->assertFalse($this->inner_form($emptyform)->elementExists('info'));
    }

    /**
     * A full page of results warns the operator that the list was cut short.
     */
    public function test_definition_warns_when_the_search_was_truncated(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $results = [];
        for ($i = 0; $i < LOCAL_ENROLDATE_SEARCH_LIMIT; $i++) {
            $user = $this->getDataGenerator()->create_user();
            $results[$user->id] = $user;
        }

        [$form] = $this->make_form($results);

        $this->assertTrue($this->inner_form($form)->elementExists('searchtruncated'));
    }
}
