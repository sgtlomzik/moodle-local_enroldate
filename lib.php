<?php
defined('MOODLE_INTERNAL') || die();

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