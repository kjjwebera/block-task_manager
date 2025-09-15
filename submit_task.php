<?php

require_once('../../config.php');
require_once($CFG->libdir . '/formslib.php');

$courseid = required_param('courseid', PARAM_INT);
$taskid = required_param('taskid', PARAM_INT);
require_login($courseid);
$context = context_course::instance($courseid);


// Set the page URL, context, and title

$PAGE->set_url(new moodle_url('/blocks/task_manager/submit_task.php', ['taskid' => $taskid, 'courseid' => $courseid]));
$PAGE->set_context($context);
$PAGE->set_title(get_string('uploadfile', 'block_task_manager'));
$PAGE->set_heading(get_string('uploadfile', 'block_task_manager'));

echo $OUTPUT->header();

// Define the form
class submit_task extends moodleform {
    public function definition() {
        global $USER;
        $courseid = required_param('courseid', PARAM_INT);
        $taskid = required_param('taskid', PARAM_INT);
        $mform = $this->_form;

        // File Upload Field
        $mform->addElement('filepicker', 'file_path', get_string('uploadfile', 'block_task_manager'), null, [
            'maxbytes' => 1048576,
            'accepted_types' => '*',
        ]);
        $mform->addRule('file_path', get_string('required'), 'required', null, 'client');

        // Online Text Editor
        $mform->addElement('editor', 'onlinetext', get_string('response', 'block_task_manager'));
        $mform->setType('onlinetext', PARAM_RAW);

        // Hidden Fields
        $mform->addElement('hidden', 'task_id', $taskid);
        $mform->setType('task_id', PARAM_INT);

        $mform->addElement('hidden', 'user_id', $USER->id);
        $mform->setType('user_id', PARAM_INT);

        $mform->addElement('hidden', 'course_id', $courseid);
        $mform->setType('course_id', PARAM_INT);

        // Submit Button
        $this->add_action_buttons(true, get_string('savechanges'));
    }
}

$mform = new submit_task(new moodle_url('/blocks/task_manager/submit_task.php', ['courseid' => $courseid, 'taskid' => $taskid]));

// Instantiate the form
// $mform = new submit_task(null, ['taskid' => $taskid, 'courseid' => $courseid]);
$mform->set_data(['taskid' => $taskid, 'courseid' => $courseid]);

// Process form submission
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
} elseif ($data = $mform->get_data()) {

    $context = context_course::instance($data->course_id);
    $blockinstance = $DB->get_record('block_instances', [
        'parentcontextid' => $context->id,  // Ensure it's inside the course
        'blockname' => 'task_manager'              // Adjust to your block's actual name
    ]);

    if (!$blockinstance) {
        throw new moodle_exception('blocknotfound', 'block_file_upload');
    }

$blockid = $blockinstance->id;  // Now we have the correct block ID

// Get the block context dynamically.
$blockcontext = context_block::instance($blockid);	

    
    

    $file_storage = get_file_storage();

    // Process file upload
    $file_id = null;
    if ($draft_itemid = file_get_submitted_draft_itemid('file_path')) {
        $unique_itemid = time();
        file_save_draft_area_files(
            $draft_itemid,  
            $blockcontext->id,  
            'block_task_manager', 
            'uploadfiles',   
            $unique_itemid,  
            ['subdirs' => false, 'maxbytes' => 1048576, 'accepted_types' => '*']
        );

        // Retrieve the file information
        $files = $file_storage->get_area_files($blockcontext->id, 'block_task_manager', 'uploadfiles', $unique_itemid, '', false);
        if ($files) {
            $file = reset($files);  // Get the first uploaded file
            $file_id = $file->get_id(); // Get unique mdl_files record id
        }
    }
 
    // Prepare submission data for insertion.
    $record = new stdClass();
    $record->task_id = $data->task_id;
    $record->user_id = $USER->id;
    $record->feedback_comment = $data->onlinetext['text'];
    $record->file_path = $file_id; // Save only filename
    $record->submission_date = time();
    $record->timecreated = time();
    $record->timemodified = time();
    $DB->insert_record('block_task_manager_sub', $record);

    redirect(new moodle_url('/course/view.php', ['id' => $data->course_id]), get_string('tasksubmitted', 'block_task_manager'));
}

// Display the page and form.

$mform->display();
echo $OUTPUT->footer();
?>
