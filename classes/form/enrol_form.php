<?php
namespace local_enroldate\form;

defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/formslib.php");

class enrol_form extends \moodleform {
    public function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;

        $mform->addElement('header', 'search_header', get_string('searchsection', 'local_enroldate'));
        $mform->setExpanded('search_header', true);
        
        $mform->addElement('text', 'searchquery', get_string('searchquery', 'local_enroldate'));
        $mform->setType('searchquery', PARAM_TEXT);
        
        $mform->addElement('submit', 'searchbutton', get_string('search', 'local_enroldate'));
        $mform->registerNoSubmitButton('searchbutton');

        if (!empty($customdata['search_results'])) {
            $mform->addElement('html', '<div style="margin:15px 0; padding:15px; border:1px dashed #444; background:#fcfcfc;">');
            $mform->addElement('static', 'info', '', '<strong>' . get_string('selectfromresults', 'local_enroldate') . '</strong>');
            foreach ($customdata['search_results'] as $user) {
                $label = fullname($user) . ' (' . $user->email . ')';
                $mform->addElement('checkbox', 'selectedusers['.$user->id.']', '', $label);
            }
            $mform->addElement('html', '</div>');
        }

        $mform->addElement('header', 'settings_header', get_string('settings', 'local_enroldate'));
        $mform->setExpanded('settings_header', true);
        
        $roles = get_assignable_roles($customdata['context'], ROLENAME_BOTH);
        $mform->addElement('select', 'roleid', get_string('role', 'local_enroldate'), $roles);

        $statuschoices = array(
            0 => get_string('participationactive', 'enrol'), // Эти две строки Moodle точно найдет, они глобальные
            1 => get_string('participationsuspended', 'enrol')
        );
        $mform->addElement('select', 'status', get_string('enrolstatus', 'local_enroldate'), $statuschoices);
        $mform->setDefault('status', 0); // По умолчанию - Активно

        $mform->addElement('date_time_selector', 'timestart', get_string('startdate', 'local_enroldate'));
        $mform->setDefault('timestart', time());

        $mform->addElement('duration', 'duration', get_string('enrolperiod', 'enrol'), array('optional' => true, 'defaultunit' => 86400));
        $mform->setDefault('duration', 0);

        $mform->addElement('date_time_selector', 'timeend', get_string('enrolenddate', 'local_enroldate'), array('optional' => true));
        $mform->setDefault('timeend', 0); // По умолчанию отключено

        $mform->addElement('header', 'bulk_header', get_string('bulksection', 'local_enroldate'));
        $mform->setExpanded('bulk_header', false);
        $mform->addElement('textarea', 'userlist', get_string('userlist', 'local_enroldate'), 'rows="3" cols="50"');
        $mform->setType('userlist', PARAM_RAW);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons(true, get_string('enrolusers', 'local_enroldate'));
    }
}