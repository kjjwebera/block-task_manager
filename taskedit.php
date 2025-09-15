<?php
require_once('../../config.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/blocks/task_manager/classes/form/task_assignform.php');

require_login();

$taskid = required_param('taskid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);

$context = context_course::instance($courseid);
$PAGE->set_context($context);
$PAGE->set_url('/blocks/task_manager/taskedit.php', ['taskid' => $taskid, 'courseid' => $courseid]);
$PAGE->set_title(get_string('updatetask', 'block_task_manager'));
$PAGE->set_heading(get_string('updatetask', 'block_task_manager'));

if (!has_capability('block/task_manager:manage_tasks', $context)) {
    print_error('nopermission', 'block_task_manager');
}

global $DB, $OUTPUT;

$task = $DB->get_record('block_task_manager_tasks', ['id' => $taskid], '*', MUST_EXIST);

// Instantiate form.

$url = new moodle_url('/blocks/task_manager/taskedit.php', [
    'taskid' => $taskid,
    'courseid' => $courseid
]);
$mform = new \block_task_manager\form\task_assignform($url);

// Pre-fill data.
$mform->set_data([
    'task_title' => $task->title,
    'task_short_desc' => $task->short_desc,
    'task_description' => ['text' => $task->description, 'format' => FORMAT_HTML],
    'taskid' => $taskid,
    'course_id' => $courseid,
    'due_date' => $task->due_date,
    'filesize' => $task->filesize,
    'typeofwork' => $task->typeofwork,
    'batchcode' => explode(',', $task->batchcode),
    'totalmarks' => $task->total_mark,
    'marks' => $task->pass_mark,
    'task_visibility' => $task->visibility
]);

// Handle form submission.
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
} elseif ($data = $mform->get_data()) {
    $updated = new stdClass();
    $updated->id = $taskid;
    $updated->title = $data->task_title;
    $updated->short_desc = $data->task_short_desc;
    $updated->description = $data->task_description['text'];
    $updated->due_date = $data->due_date;
    $updated->filesize = $data->filesize;
    $updated->typeofwork = $data->typeofwork;
    $updated->batchcode = is_array($data->batchcode) ? implode(',', $data->batchcode) : $data->batchcode;
    $updated->total_mark = $data->totalmarks;
    $updated->pass_mark = $data->marks;
    $updated->visibility = $data->task_visibility;

    $DB->update_record('block_task_manager_tasks', $updated);

    redirect(new moodle_url('/course/view.php', ['id' => $courseid]), get_string('taskupdated', 'block_task_manager'));
}

// Output.
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('updatetask', 'block_task_manager'));
$mform->display();
echo $OUTPUT->footer();
