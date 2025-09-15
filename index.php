<?php
// This file is used to manage tasks in the task manager block
require_once('../../config.php');
require_once($CFG->dirroot . '/blocks/task_manager/classes/task_manager.php');
require_once($CFG->dirroot . '/blocks/task_manager/classes/form/task_assignform.php');
global $DB, $PAGE;



$PAGE->requires->js(new moodle_url('https://code.jquery.com/jquery-3.6.0.min.js'));
$PAGE->requires->js(new moodle_url('https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js'), true);
$PAGE->requires->css(new moodle_url('https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css'), true);
$PAGE->requires->css(new moodle_url('https://cdn.datatables.net/buttons/1.6.4/css/buttons.dataTables.min.css'), true);
$PAGE->requires->js(new moodle_url('https://cdn.datatables.net/buttons/1.6.4/js/dataTables.buttons.min.js'), true);
$PAGE->requires->js(new moodle_url('https://cdn.datatables.net/buttons/1.6.4/js/buttons.flash.min.js'), true);
$PAGE->requires->js(new moodle_url('https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js'), true);
$PAGE->requires->js(new moodle_url('https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js'), true);
$PAGE->requires->js(new moodle_url('https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js'), true);
$PAGE->requires->js(new moodle_url('https://cdn.datatables.net/buttons/1.6.4/js/buttons.html5.min.js'), true);
$PAGE->requires->js(new moodle_url('https://cdn.datatables.net/buttons/1.6.4/js/buttons.print.min.js'), true);
// Get course and block instance
$courseid = required_param('courseid', PARAM_INT);  // Course ID
$context = context_course::instance($courseid);
$PAGE->set_context($context);

// Check if the user has permission to manage tasks
require_login($courseid);
if (!has_capability('block/task_manager:manage_tasks', $context)) {
    throw new moodle_exception('nopermissiontomanage', 'block_task_manager');
}

// Set the page URL
$url = new moodle_url('/blocks/task_manager/index.php', array('courseid' => $courseid));
$PAGE->set_url($url);
$PAGE->set_title(get_string('taskmanager', 'block_task_manager'));
$PAGE->set_heading(get_string('taskmanager', 'block_task_manager'));

// Output header
echo $OUTPUT->header();

// Check if the form has been submitted for adding a new task
if ($form = new \block_task_manager\form\task_assignform()) {
    if ($form->is_cancelled()) {
        // Handle form cancellation
    } else if ($fromform = $form->get_data()) {
        // Handle form submission (task assignment)
        // Save the task in the database
        $task = new stdClass();
        $task->course_id = $courseid;
        $task->title = $fromform->title;
        $task->description = $fromform->description['text'];
        $task->due_date = $fromform->due_date;
        $task->created_by = $USER->id;
        
        // Insert the task into the database
        $DB->insert_record('block_task_manager_tasks', $task);
        echo $OUTPUT->notification(get_string('taskassigned', 'block_task_manager'), 'notifysuccess');
    }

    // Display the task assignment form
    $form->display();
}

// Display a list of tasks
$tasks = $DB->get_records('block_task_manager_tasks', array('course_id' => $courseid));

if ($tasks) {
    echo '<h2>' . get_string('taskslist', 'block_task_manager') . '</h2>';
    echo '<table class="generaltable boxaligncenter">';
    echo '<tr><th>' . get_string('title', ' ') . '</th><th>' . get_string('duedate', 'block_task_manager') . '</th><th>' . get_string('actions', 'block_task_manager') . '</th></tr>';
    foreach ($tasks as $task) {
        echo '<tr>';
        echo '<td>' . s($task->title) . '</td>';
        echo '<td>' . userdate($task->due_date) . '</td>';
        
        if (has_capability('block/task_manager:manage_tasks', $context)) {
            // Teacher-specific actions: view submissions or grade
            echo '<td><a href="' . $CFG->wwwroot . '/blocks/task_manager/view_submissions.php?taskid=' . $task->id . '">' . get_string('viewsubmissions', 'block_task_manager') . '</a></td>';
        } else {
            // Student-specific actions: submit task
            echo '<td><a href="' . $CFG->wwwroot . '/blocks/task_manager/submit_task.php?taskid=' . $task->id . '">' . get_string('submittask', 'block_task_manager') . '</a></td>';
        }
        echo '</tr>';
    }
    echo '</table>';
} else {
    echo $OUTPUT->notification(get_string('notasksfound', 'block_task_manager'), 'notifyproblem');
}

echo $OUTPUT->footer();
