<?php
namespace block_task_manager\form;

require_once("$CFG->libdir/formslib.php");

require_once($CFG->dirroot . '/config.php');

class task_submissionform extends \moodleform {
    public function definition() {
        global $DB, $USER;
        $taskid = optional_param('taskid',0, PARAM_INT);
$courseid = optional_param('courseid',0, PARAM_INT);
       
$mform = $this->_form;

        // Add filepicker for file uploads.
        $mform->addElement('filepicker', 'file_path', get_string('uploadfile', 'block_task_manager'), null, [
            'maxbytes' => 1048576,
            'accepted_types' => '*',
        ]);
        $mform->addRule('file_path', get_string('required'), 'required', null, 'client');


        // Add editor for response text.
        $mform->addElement('editor', 'onlinetext', get_string('response', 'block_task_manager'));
        $mform->setType('response', PARAM_RAW);
        $mform->addElement('hidden', 'task_id', $taskid);
        $mform->setType('task_id', PARAM_INT);
        $mform->addElement('hidden', 'user_id', $USER->id);
        $mform->setType('user_id', PARAM_INT);
        $mform->addElement('hidden', 'course_id', $courseid);
        $mform->setType('course_id', PARAM_INT);
        // Add submit button.
        $this->add_action_buttons($cancel = true, $submitlabel = 'Save changes');
    }
}
