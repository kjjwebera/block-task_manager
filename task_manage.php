<?php

require_once('../../config.php');
//Ensure the user is logged in and has access.
require_login();
global $USER;
// Get parameters from the URL if needed (optional, adjust as per your requirements).
$courseid = optional_param('courseid', 0, PARAM_INT);
// Set up the page.
$PAGE->set_url('/blocks/task_manager/task_manage.php', ['courseid' => $courseid]);
$context = context_system::instance();
$coursecontext = context_course::instance($courseid);
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$title = get_string('tasklist', 'block_task_manager');
$PAGE->set_title('Task Management');
$PAGE->set_heading('Task Management');
$PAGE->requires->jquery();

if (!has_capability('block/task_manager:manage_tasks', $coursecontext)) {
    print_error('nopermission', 'block_task_manager');
}


echo $OUTPUT->header();
$html='';
$html.=html_writer::start_div('pt-5 pb-3');
$html.=html_writer::start_tag('h2');
$html.=get_string('tasklist','block_task_manager');
$html.=html_writer::end_tag('h2');
$html.=html_writer::end_div('');
echo $html;

//creating a table to display already created autoenrollment rules.
$rulestable  = new \html_table();
$rulestable->id = 'usertable';
$rulestable->head = array(get_string('slno', 'block_task_manager'),
    get_string('taskname', 'block_task_manager'),
    get_string('createdby', 'block_task_manager'),
    get_string('coursename', 'block_task_manager'),
    get_string('actions', 'block_task_manager'),
);

//Current Tasks status.
//$tasks = $DB->get_records('block_task_manager_tasks');
$userbatchcodes = isset($USER->profile['batchcode']) ? array_map('trim', explode(',', $USER->profile['batchcode'])) : [];

$params = ['course_id' => $courseid];
$wheresql = "course_id = :course_id";

// Admins can see all tasks
if (!is_siteadmin($USER)) {
    // Build dynamic LIKE conditions
    $like_clauses = [];
    foreach ($userbatchcodes as $i => $code) {
        $paramkey = "bc{$i}";
        $like_clauses[] = "CONCAT(',', batchcode, ',') LIKE :$paramkey";
        $params[$paramkey] = "%," . $code . ",%";
    }

    // Include tasks available for all
    $like_clauses[] = "batchcode = 'all'";
    $wheresql .= " AND (" . implode(" OR ", $like_clauses) . ")";
}

// Fetch only matching tasks
$tasks = $DB->get_records_select('block_task_manager_tasks', $wheresql, $params);

// Count the filtered records
$totalTasks = count($tasks);



$slno = 1;

if (!empty($tasks)) {
    foreach ($tasks as $task) {
        // Get creator name.
        $user = $DB->get_record('user', array('id'=>$task->created_by));
        $creatorname = fullname($user);

        // Get course name.
        $course = $DB->get_record('course', ['id' => $task->course_id], 'id, fullname');
        $coursename = $course ? $course->fullname : '-';

        // Action buttons
        $editurl = new moodle_url('/blocks/task_manager/taskedit.php', ['taskid' => $task->id, 'courseid' => $task->course_id]);
        $deleteurl = new moodle_url('/blocks/task_manager/delete_task.php', ['taskid' => $task->id, 'courseid' => $task->course_id]);
        $evaluateurl = new moodle_url('/blocks/task_manager/evaluate_task.php', ['taskid' => $task->id, 'courseid' => $task->course_id]);

        $dropdownid = 'task-actions-' . $task->id;

        $actionbuttons = html_writer::start_div('dropdown');
        $actionbuttons .= html_writer::tag('button', 
            html_writer::tag('i', '', ['class' => 'fa fa-cog']),
            [
                'class' => 'btn btn-secondary btn-sm dropdown-toggle',
                'type' => 'button',
                'id' => $dropdownid,
                'data-toggle' => 'dropdown',
                'aria-haspopup' => 'true',
                'aria-expanded' => 'false'
            ]
        );

        $actionbuttons .= html_writer::start_div('dropdown-menu', ['aria-labelledby' => $dropdownid]);
        $actionbuttons .= html_writer::link($editurl, get_string('edit'), ['class' => 'dropdown-item']);
        $actionbuttons .= html_writer::link($deleteurl, get_string('delete'), ['class' => 'dropdown-item']);
        $actionbuttons .= html_writer::link($evaluateurl, 'Evaluate', ['class' => 'dropdown-item']);
        $actionbuttons .= html_writer::end_div(); // end dropdown-menu
        $actionbuttons .= html_writer::end_div(); // end dropdown


        // Add row to the table.
        $rulestable->data[] = [
            $slno++,
            format_string($task->title),
            $creatorname,
            $coursename,
            $actionbuttons,
        ];
    }
} else {
    $rulestable->data[] = [get_string('notasks', 'block_task_manager'), '', '', '', ''];
}
echo html_writer::table($rulestable);
echo $OUTPUT->footer();












