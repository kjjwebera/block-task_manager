<?php
require_once('../../config.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/blocks/task_manager/classes/form/task_assignform.php');
global $USER,$COURSE;
require_login();
//print_object($COURSE);die;
$context = context_system::instance();
$courseid = optional_param('courseid', 0, PARAM_INT);
$userid = $USER->id; // Current logged-in user
$usercontext = context_user::instance($userid);
$coursecontext = context_course::instance($courseid);
// Check capabilities.
if (!has_capability('block/task_manager:manage_tasks', $coursecontext)) {
    //print_r($usercontext);       
    print_error('nopermissions', '', '', 'assign task');
}

// Set up the page.
$PAGE->set_context($context);
$PAGE->set_url('/blocks/task_manager/assign_task.php');
$PAGE->set_title(get_string('cttask', 'block_task_manager'));
$PAGE->set_heading(get_string('cttask', 'block_task_manager'));

// Create and process the form.
$mform = new \block_task_manager\form\task_assignform();

if ($mform->is_cancelled()) {
    // Handle form cancellation.
    redirect(new moodle_url('/course/view.php',['id' => $courseid])); // Redirect to dashboard or another page.
} else if ($data = $mform->get_data()) {
    // Process form submission.
    // print_object($data); exit;
    $expld = implode(',', $data->batchcode);
    global $DB, $USER;
    $record = new stdClass();
    $record->course_id = $data->course_id;
    $record->title = $data->task_title;
    $record->short_desc = $data->task_short_desc;
    $record->description = $data->task_description['text'];
    $record->due_date = $data->due_date;
    $record->filesize = $data->filesize;
    $record->typeofwork = $data->typeofwork;
    $record->created_by = $USER->id;
    $record->total_mark = $data->totalmarks;
    $record->pass_mark = $data->marks;
    $record->visibility = $data->task_visibility;
    $record->batchcode = $expld;

    $DB->insert_record('block_task_manager_tasks', $record);
    // Redirect with a success message.
    redirect(new moodle_url('/course/view.php',['id' => $record->course_id]), get_string('tasksuccess', 'block_task_manager'));
}

// Display the page and form.
echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
