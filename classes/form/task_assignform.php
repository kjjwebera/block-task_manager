<?php
namespace block_task_manager\form;

require_once("$CFG->libdir/formslib.php");
require_once($CFG->dirroot . '/config.php');

class task_assignform extends \moodleform {
    public function definition() {
        $mform = $this->_form;
        
        $courseid = optional_param('courseid', 0, PARAM_INT);
        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);

        // Add form fields.
        $mform->addElement('text', 'task_title', get_string('tasktitle', 'block_task_manager'));
        $mform->setType('task_title', PARAM_TEXT);
        $mform->addRule('task_title', get_string('required'), 'required', null, 'client');
        $mform->addElement('textarea', 'task_short_desc', get_string('taskshortdesc', 'block_task_manager'), array(
            'cols' => 70,
            'rows' => 1
        ));
        $mform->addElement('editor', 'task_description', get_string('taskdescription', 'block_task_manager'));
        $mform->setType('task_description', PARAM_RAW);
        $mform->addElement('hidden', 'course_id', $courseid);
        $mform->setType('course_id', PARAM_INT);
        
        // Submission period section
        $mform->addElement('header', 'submissionperiod', get_string('submissionperiod', 'block_task_manager'));
        $mform->setExpanded('submissionperiod', true);
        $mform->addElement('date_selector', 'due_date', get_string('duedate', 'block_task_manager'));
        
        $mform->addElement('select', 'days', get_string('selectdays', 'block_task_manager'), array_combine(range(0, 31), range(0, 31)));
        $mform->setType('days', PARAM_INT);
        $mform->setDefault('days', 0);
        
        // Submission restrictions section
        $mform->addElement('header', 'submissionrestric', get_string('submissionrestric', 'block_task_manager'));
        $mform->setExpanded('submissionrestric', true);
        
        $mform->addElement('text', 'filesize', get_string('maximum', 'block_task_manager'));
        $mform->setType('filesize', PARAM_TEXT);
        $mform->addElement('select', 'typeofwork', get_string('typeofwork', 'block_task_manager'), [
            'individualwork' => get_string('individualwork', 'block_task_manager'),
            'teamwork' => get_string('teamwork', 'block_task_manager'),
        ]);
        // Fetch the batchcode values from user profile
        global $DB, $USER;
        /*$batchcodes = $DB->get_records_sql("
        SELECT DISTINCT d.data
        FROM {user_info_field} f
        JOIN {user_info_data} d ON f.id = d.fieldid
        WHERE f.shortname = 'batchcode'
    ");
        $batchcode_options = [
            'no_one' => 'No one',
            'all' => 'All'
        ];
        foreach ($batchcodes as $batchcode) {
            $batchcode_options[$batchcode->data] = $batchcode->data;
        }*/
        //Logic changed to have batchcode level validation based on user profile field
        $batchcode_options = [
    'no_one' => 'No one',
    'all' => 'All'
];

if (is_siteadmin($USER)) {
    // Admin: show all available batchcodes from DB
    $batchcodes = $DB->get_records_sql("
        SELECT DISTINCT d.data
        FROM {user_info_field} f
        JOIN {user_info_data} d ON f.id = d.fieldid
        WHERE f.shortname = 'batchcode'
    ");
    foreach ($batchcodes as $batchcode) {
        if (!empty($batchcode->data)) {
            $batchcode_options[$batchcode->data] = $batchcode->data;
        }
    }

} else {
    // Non-admin: use user's own batchcode field
    if (!empty($USER->profile['batchcode'])) {
        $usercodes = array_map('trim', explode(',', $USER->profile['batchcode']));
        foreach ($usercodes as $code) {
            if (!empty($code)) {
                $batchcode_options[$code] = $code;
            }
        }
    }
}



        // Add the select field for batchcode
        $mform->addElement('autocomplete', 'batchcode', get_string('batchcode', 'block_task_manager'), $batchcode_options, [
    'multiple' => true,
    'noselectionstring' => get_string('selectbatchcodes', 'block_task_manager') // Optional custom placeholder
]);
        //$mform->addElement('select', 'batchcode', get_string('batchcode', 'block_task_manager'), $batchcode_options, ['multiple' => 'multiple']);
        $mform->setType('batchcode', PARAM_RAW);
        $mform->addRule('batchcode', get_string('required'), 'required', null, 'client');
        $selectedBatchcodes = optional_param_array('batchcode', [], PARAM_RAW);
        if (!empty($selectedBatchcodes)) {
            $mform->setDefault('batchcode', $selectedBatchcodes);
        }
        // Other fields in evaluate section
        $mform->addElement('header', 'evaluate', get_string('evaluate', 'block_task_manager'));
        $mform->setExpanded('evaluate', false);
        $mform->addElement('text', 'totalmarks', get_string('totalmarks', 'block_task_manager'));    
        $mform->setType('totalmarks', PARAM_TEXT);
        $mform->addRule('totalmarks', get_string('required'), 'required', null, 'client');
        $mform->addElement('text', 'marks', get_string('marks', 'block_task_manager'));    
        $mform->setType('marks', PARAM_TEXT);
        $mform->addRule('marks', get_string('required'), 'required', null, 'client');
        // Task visibility
        $mform->addElement('select', 'task_visibility', get_string('taskvisibility', 'block_task_manager'), [
            'yes' => get_string('yes'),
            'no' => get_string('no'),
        ]);
        $mform->setDefault('task_visibility', 'yes');
        $mform->setType('task_visibility', PARAM_ALPHA);
        
        // Action buttons
        $this->add_action_buttons($cancel = true, $submitlabel = 'Submit');
    }
}
