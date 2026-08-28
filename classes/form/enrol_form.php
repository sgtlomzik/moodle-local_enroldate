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
 * Enrolment form for local_enroldate.
 *
 * @package    local_enroldate
 * @copyright  2026 SgtLomzik <lomzike@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_enroldate\form;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/enroldate/lib.php');

/**
 * Lets an operator pick users, a role and an enrolment period, then enrol them.
 *
 * @package    local_enroldate
 * @copyright  2026 SgtLomzik <lomzike@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_form extends \moodleform {

    /**
     * Build the form.
     */
    public function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;

        $mform->addElement('header', 'searchheader', get_string('searchsection', 'local_enroldate'));
        $mform->setExpanded('searchheader', true);

        $mform->addElement('text', 'searchquery', get_string('searchquery', 'local_enroldate'));
        $mform->setType('searchquery', PARAM_TEXT);

        $mform->addElement('submit', 'searchbutton', get_string('search', 'local_enroldate'));
        $mform->registerNoSubmitButton('searchbutton');

        $searchresults = $customdata['searchresults'] ?? [];

        if (!empty($searchresults)) {
            $mform->addElement('static', 'info', '',
                \html_writer::tag('strong', get_string('selectfromresults', 'local_enroldate')));

            foreach ($searchresults as $user) {
                $label = s(fullname($user)) . ' (' . s($user->email) . ')';
                $mform->addElement('checkbox', 'selectedusers[' . (int)$user->id . ']', '', $label);
            }

            if (count($searchresults) >= LOCAL_ENROLDATE_SEARCH_LIMIT) {
                $mform->addElement('static', 'searchtruncated', '',
                    get_string('searchtruncated', 'local_enroldate', LOCAL_ENROLDATE_SEARCH_LIMIT));
            }
        }

        $mform->addElement('header', 'settingsheader', get_string('settings', 'local_enroldate'));
        $mform->setExpanded('settingsheader', true);

        $roles = get_assignable_roles($customdata['context'], ROLENAME_BOTH);
        $mform->addElement('select', 'roleid', get_string('role', 'local_enroldate'), $roles);

        $statuschoices = [
            ENROL_USER_ACTIVE => get_string('participationactive', 'enrol'),
            ENROL_USER_SUSPENDED => get_string('participationsuspended', 'enrol'),
        ];
        $mform->addElement('select', 'status', get_string('enrolstatus', 'local_enroldate'), $statuschoices);
        $mform->setDefault('status', ENROL_USER_ACTIVE);

        $mform->addElement('date_time_selector', 'timestart', get_string('startdate', 'local_enroldate'));
        $mform->setDefault('timestart', time());

        $mform->addElement('duration', 'duration', get_string('enrolperiod', 'enrol'),
            ['optional' => true, 'defaultunit' => DAYSECS]);
        $mform->setDefault('duration', 0);

        $mform->addElement('date_time_selector', 'timeend', get_string('enrolenddate', 'local_enroldate'),
            ['optional' => true]);
        $mform->setDefault('timeend', 0);
        $mform->addHelpButton('timeend', 'enrolenddate', 'local_enroldate');

        $mform->addElement('header', 'bulkheader', get_string('bulksection', 'local_enroldate'));
        $mform->setExpanded('bulkheader', false);
        $mform->addElement('textarea', 'userlist', get_string('userlist', 'local_enroldate'),
            'rows="3" cols="50"');
        $mform->setType('userlist', PARAM_TEXT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons(true, get_string('enrolusers', 'local_enroldate'));
    }

    /**
     * Validate the submitted enrolment period.
     *
     * @param array $data Submitted data.
     * @param array $files Submitted files.
     * @return array Validation errors, keyed by form element name.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (!empty($data['timeend']) && !empty($data['timestart']) && $data['timeend'] <= $data['timestart']) {
            $errors['timeend'] = get_string('enroltimeendinvalid', 'enrol');
        }

        return $errors;
    }
}
