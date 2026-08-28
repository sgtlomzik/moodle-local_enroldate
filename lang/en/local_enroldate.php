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
 * English strings for local_enroldate.
 *
 * @package    local_enroldate
 * @copyright  2026 SgtLomzik <lomzike@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['bulksection'] = 'Or enter a list (email/username)';
$string['enrolenddate'] = 'Enrolment end date';
$string['enrolenddate_help'] = 'An explicit end date takes precedence over the enrolment duration above. Leave both empty to enrol without an end date.';
$string['enrolstatus'] = 'Status';
$string['enrolusers'] = 'Enrol users';
$string['forcegradehistory'] = 'Keep past enrolments visible in grade reports';
$string['forcegradehistory_desc'] = 'Enrolling somebody for a period that has already ended hides them from grade reports, because those reports list only actively enrolled users by default. With this setting on, the plugin sets the site-wide "Show only active enrolments" option (grade_report_showonlyactiveenrol) to No so that those learners stay visible. Turn it off to keep your own value for that core setting.';
$string['nomaskenrol'] = 'The "Manual enrolments" method is not enabled in this course.';
$string['notfoundusers'] = 'Users not found: {$a}';
$string['nousersselected'] = 'No users selected for enrolment.';
$string['pluginname'] = 'Enrolment with date selection';
$string['privacy:metadata'] = 'The Enrolment with date selection plugin stores no personal data. The enrolments it creates are stored by the core manual enrolment plugin.';
$string['role'] = 'Assign role';
$string['search'] = 'Search';
$string['searchquery'] = 'First name, last name or email';
$string['searchsection'] = 'Search for a user';
$string['searchtruncated'] = 'Only the first {$a} matches are shown. Narrow your search to find other users.';
$string['selectfromresults'] = 'Select users from the list:';
$string['settings'] = 'Access settings';
$string['startdate'] = 'Enrolment start date';
$string['successenrol'] = 'Successfully enrolled users: {$a}';
$string['userlist'] = 'List of users';
