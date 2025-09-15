<?php

require_once('../../config.php');
require_once($CFG->libdir . '/formslib.php');
global $DB, $PAGE, $OUTPUT, $USER;

$courseid = required_param('courseid', PARAM_INT); // Get course ID from URL parameter.
$taskid = required_param('taskid', PARAM_INT); // Get task ID from URL parameter.

// Ensure the user is logged in.
require_login($courseid);

$context = context_course::instance($courseid);
$PAGE->set_context($context);
$PAGE->set_url('/blocks/task_manager/grade_task.php', ['courseid' => $courseid, 'task_id' => $taskid]);
$PAGE->set_title(get_string('gradetask', 'block_task_manager'));
$PAGE->set_heading(get_string('gradetask', 'block_task_manager'));

// Fetch task submission details from the database.
$submission = $DB->get_record('block_task_manager_sub', ['task_id' => $taskid, 'user_id'=> $USER->id ]);
$taskcurrent = $DB->get_record('block_task_manager_tasks', ['id' => $taskid]);
// Determine submission status.
$singlesubmission = $taskcurrent->filesize;

if ($submission ) {
    $submission_status = get_string('submitted', 'block_task_manager'); // 'Submitted' if the record exists.

    // Check grading status.
    if (!empty($submission->lastmodefied_mark)) {
        //print_r($submission); print_r($taskcurrent); die();
        $graded_status = $submission->lastmodefied_mark .'/'. $taskcurrent->total_mark; // Replace 10 with total marks if stored elsewhere.
    } else {
        $graded_status = get_string('notgraded', 'block_task_manager'); // 'Not graded' if marks are empty.
    }

    // Get last modified date.
    $last_modified = userdate($submission->submission_date); // Format date for user-friendly display.
} else {
    $submission_status = get_string('notattempted', 'block_task_manager'); // 'Not Attempted' if no record exists.
    $graded_status = get_string('notgraded', 'block_task_manager'); // Default to 'Not graded'.
    $last_modified = get_string('na', 'block_task_manager'); // 'N/A' if no submission date.
}

// Output the page header.
echo $OUTPUT->header();

// Create the table.
$table = new html_table();
$table->attributes['class'] = 'generaltable';

// Add table rows.
$table->data[] = [
    html_writer::tag('strong', get_string('submissionstatus', 'block_task_manager')),
    $submission_status
];

$table->data[] = [
    html_writer::tag('strong', get_string('graded', 'block_task_manager')),
    $graded_status
];

$table->data[] = [
    html_writer::tag('strong', get_string('lastmodified', 'block_task_manager')),
    $last_modified
];

// Display the table.
echo html_writer::table($table);

if (!$submission)  {
echo html_writer::div(
    html_writer::link(
        new moodle_url('/blocks/task_manager/submit_task.php', [
            'courseid' => $courseid,
            'taskid' => $taskid
        ]),
        get_string('addsubmission', 'block_task_manager'),
        ['class' => 'btn btn-primary']
    ),
    'text-center'
);
}
// Output the page footer.
echo $OUTPUT->footer();
