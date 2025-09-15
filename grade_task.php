<?php

require_once('../../config.php');

// Ensure the user is logged in and has the necessary permissions.
require_login();

// Get the course ID and task ID from the URL.
$courseid = required_param('courseid', PARAM_INT);
$taskid = required_param('taskid', PARAM_INT); // Task ID for filtering submissions.

// Set up the page.
$context = context_course::instance($courseid);
$PAGE->set_url('/blocks/task_manager/grade_task.php', ['courseid' => $courseid, 'taskid' => $taskid]);
$PAGE->set_context($context);
$PAGE->set_title('Evaluate Task');
$PAGE->set_heading('Evaluate Task');

// Ensure the user has the required capability to grade.
require_capability('mod/assign:grade', $context);

// Fetch all participants for the course.
$participants = get_enrolled_users($context);
$total_participants = count($participants);

// Fetch the total number of submissions for the given task ID.
$submissions_count = $DB->count_records('block_task_manager_sub', ['task_id' => $taskid]);

// Fetch the number of submissions that need grading (marks column is empty).
$need_grading_count = $DB->count_records_select(
    'block_task_manager_sub,
    'task_id = :taskid AND (marks IS NULL OR marks = \'\')',
    ['taskid' => $taskid]
);

// Output the page header.
echo $OUTPUT->header();

// Display the title.
echo html_writer::start_tag('h3', ['style' => 'text-align: center; margin-bottom: 20px;']);
echo 'Participants and Submissions for Course ID: ' . $courseid;
echo html_writer::end_tag('h3');

// Create a table to display total participants, submissions, and need grading.
$table = new html_table();
$table->head = ['Description', 'Count']; // Table headers
$table->data = [
    [
        html_writer::tag('strong', 'Total Participants'),
        $total_participants
    ],
    [
        html_writer::tag('strong', 'Total Submissions'),
        $submissions_count
    ],
    [
        html_writer::tag('strong', 'Need Grading'),
        $need_grading_count
    ],
];

// Display the table.
echo html_writer::table($table);

// Add buttons for "Evaluate" and "View All Submissions".
$evaluate_url = new moodle_url('/blocks/task_manager/evaluate_task.php', ['courseid' => $courseid, 'taskid' => $taskid]);
$submissions_url = new moodle_url('/blocks/task_manager/view_submissions.php', ['courseid' => $courseid, 'taskid' => $taskid]);

echo html_writer::start_div('d-flex justify-content-center align-items-center', ['style' => 'gap: 15px; margin-top: 20px;']);

echo html_writer::link(
    $evaluate_url,
    'Evaluate',
    ['class' => 'btn btn-primary']
);

echo html_writer::link(
    $submissions_url,
    'View All Submissions',
    ['class' => 'btn btn-success']
);

echo html_writer::end_div();

// Output the page footer.
echo $OUTPUT->footer();
