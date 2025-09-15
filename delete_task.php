<?php
// require_once('../../config.php'); // Include the Moodle configuration file.

// // Ensure that the user is logged in and has proper access.
// require_login();

// // Get the required parameters from the URL.
// $taskid = required_param('taskid', PARAM_INT);
// $courseid = required_param('courseid', PARAM_INT);

// // Set up the page.
// $context = context_course::instance($courseid);
// $PAGE->set_context($context);
// $PAGE->set_url('/blocks/task_manager/delete_task.php', ['taskid' => $taskid, 'courseid' => $courseid]);

// // Check the user's capability to delete tasks.
// if (!has_capability('block/task_manager:manage_tasks', $context)) {
//     print_error('nopermission', 'block_task_manager');
// }

// // Access the database to delete the task.
// global $DB;

// // Check if the task exists before attempting to delete it.
// if ($DB->record_exists('block_task_manager_tasks', ['id' => $taskid, 'course_id' => $courseid])) {
//     // Delete the task.
//     $DB->delete_records('block_task_manager_tasks', ['id' => $taskid, 'course_id' => $courseid]);

//     // Redirect with a success message.
//     redirect(
//         new moodle_url('/blocks/task_manager/view_tasks.php', ['courseid' => $courseid]),
//         get_string('taskdeleted', 'block_task_manager'),
//         null,
//         \core\output\notification::NOTIFY_SUCCESS
//     );
// } else {
//     // If the task doesn't exist, display an error message.
//     print_error('tasknotfound', 'block_task_manager');
// }

// // Output the page (not typically needed due to redirect).
// echo $OUTPUT->header();
// echo $OUTPUT->footer();


require_once(__DIR__ . '/../../config.php');
require_login();

$taskid = required_param('taskid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);

$context = context_course::instance($courseid);
require_capability('block/task_manager:manage_tasks', $context);

global $DB;

// Delete related user submissions (change table names as needed)
$DB->delete_records('block_task_manager_sub', ['task_id' => $taskid]);

// Delete the task
$DB->delete_records('block_task_manager_tasks', ['id' => $taskid]);

redirect(new moodle_url('/course/view.php', ['id' => $courseid]), 'Task deleted successfully.', 2);
