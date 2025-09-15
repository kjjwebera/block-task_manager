<?php

require_once('../../config.php');
require_once($CFG->libdir . '/tablelib.php');
global $DB, $PAGE, $OUTPUT, $USER;

// Ensure the user is logged in and has the necessary permissions.
require_login();

// Get the course ID and task ID from the URL.
$courseid = required_param('courseid', PARAM_INT);
$taskid = required_param('taskid', PARAM_INT); // Task ID for filtering submissions.

// Set up the page.
$context = context_course::instance($courseid);
$PAGE->set_url('/blocks/task_manager/evaluate_task.php', ['courseid' => $courseid, 'taskid' => $taskid]);
$PAGE->set_context($context);
$PAGE->set_title('Evaluate Task');
$PAGE->set_heading('Evaluate Task');
// $PAGE->requires->jquery();


$PAGE->requires->js(new moodle_url('https://code.jquery.com/jquery-3.6.0.min.js'),true);
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


$PAGE->requires->js('/blocks/task_manager/amd/src/custom.js');

if (!has_capability('block/task_manager:manage_tasks', $context)) {
    print_error('nopermission', 'block_task_manager');
}

// Fetch records from the database
$sql = "SELECT 
            stm.id AS submission_id,
            stm.task_id,
            stm.user_id,
            stm.file_path,
            stm.submission_date,
            stm.feedback_comment,
            stm.lastmodefied_mark,
            stm.timemodified,
            t.title AS task_title,
            t.description AS task_description,
            t.due_date AS task_due_date,
            t.total_mark,
            t.pass_mark,
            u.firstname,
            u.lastname,
            u.email,
            u.id,
            u.picture,
            ts.score 
        FROM {block_task_manager_sub} stm
        JOIN {block_task_manager_tasks} t ON stm.task_id = t.id
        JOIN {user} u ON stm.user_id = u.id
        LEFT JOIN {block_task_manager_score} ts ON ts.sub_id = stm.id 
        WHERE t.id = :taskid";

$records = $DB->get_records_sql($sql, ['taskid' => $taskid]);

//print_object($records);die;

// Output the page header.
echo $OUTPUT->header();

// Display the title.
echo html_writer::tag('h3', 'Evaluate Task: ' . format_string($taskid), ['style' => 'text-align: center; margin-bottom: 20px;']);

// Create an HTML form to submit updates.
echo html_writer::start_tag('form', ['method' => 'post', 'action' => new moodle_url('/blocks/task_manager/update_marks.php', ['courseid' => $courseid, 'taskid' => $taskid])]);

// Create an HTML table for displaying the data.
$table = new html_table();
$table->attributes['class'] = 'generaltable';
$table->head = [
    'Select',
    'User picture',
    'First Name/Surname',
    'Email',
    'Status',
    'Total Marks',
    'Marks',
    'Last modified (submission)',
    'File submission',
    'Online text',
    'Last modified (evaluate)',
];
$table->data = [];

foreach ($records as $record) {

    // Generate the editable marks input field
    $readonly = !empty($record->score) ? 'readonly' : '';
    $marksInput = html_writer::empty_tag('input', [
        'type' => 'text',
        'class' => 'mark-box',
        'name' => "marks[{$record->submission_id}]",
        'value' => $record->score,
        'min' => 0,
        'max' => $record->total_mark,
        'style' => 'width: 60px;',
        $readonly => $readonly,
        'data-id' => $record->submission_id, // Add a data attribute for JS
    ]);

    // Generate the Edit link
    $editLink = !empty($record->score)
        ? html_writer::tag('a', 'Edit', [
            'href' => '#',
            'class' => 'edit-link',
            'data-id' => $record->submission_id,
            'style' => 'margin-left: 10px; color: blue; text-decoration: underline; cursor: pointer;',
        ])
        : '';

    // Generate the checkbox input
    $checkbox = html_writer::empty_tag('input', [
        'type' => 'checkbox',
        'name' => "setsubmission[{$record->submission_id}]",
        'value' => $record->submission_id,
        'class' => 'select-checkbox',
    ]);

    // Retrieve user information
    if (!empty($record->user_id) && is_numeric($record->user_id)) {
        try {
            $userStudent = $DB->get_record('user', ['id' => $record->user_id]);
            if ($userStudent) {
                $email = $userStudent->email;
            }
        } catch (Exception $e) {
            $email = "Error: " . $e->getMessage();
        }
    } else {
        $email = "Invalid user ID.";
    }

    // Handle file link generation
    $file_link = 'No file uploaded';
    if (!empty($record->file_path)) {
        $blockinstance = $DB->get_record('block_instances', [
            'parentcontextid' => $context->id,
            'blockname' => 'task_manager'
        ]);
        if (!$blockinstance) {
            throw new moodle_exception('blocknotfound', 'block_file_upload');
        }
        $blockid = $blockinstance->id;
        $blockcontext = context_block::instance($blockid);

        $file_storage = get_file_storage();
        $file = $file_storage->get_file_by_id($record->file_path);
        if ($file) {
            $file_url = moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                'block_task_manager',
                'uploadfiles',
                $file->get_itemid(),
                '/',
                $file->get_filename(),
                true
            );
            $file_link = html_writer::link($file_url, 'Download File');
        } else {
            $file_link = 'File not found!';
        }
    }

    // Determine submission status
    $status = empty($record->marks) ? 'Submitted for evaluating' : 'Submitted for evaluated';

    // Feedback trimming
    $trimed_feedback = '';
    $feedbacktext = $record->feedback_comment;
    $feedbacklength = strlen($feedbacktext);
    if ($feedbacklength > 40) {
        $trimed_feedback = substr($feedbacktext, 0, 30);
    }

    // Final grade handling
    // $finalGrades = $record->final_grade;
    // $gradesArray = explode(',', $finalGrades);
    // $lastGrade = $record->final_grade;

    // $lastGrade = end($gradesArray);
    // print_object($lastGrade);die;

    // Create a flex container for marks input and edit link
    $flexContainer = html_writer::tag('div', $marksInput . $editLink, [
        'style' => 'display: flex; align-items: center; gap: 10px;',
    ]);

    $last_modified_mark = empty($record->timemodified) ? null : userdate($record->timemodified);
    // Add the row to the table
    $table->data[] = [
        $checkbox,
        $OUTPUT->user_picture($userStudent, array('size' => 50)),
        fullname($userStudent),
        $record->email,
        $status,
        $record->total_mark,
        $flexContainer,
        userdate($record->submission_date),
        $file_link,
        strlen($record->feedback_comment) > 70 ? substr($record->feedback_comment, 0, 70) : $record->feedback_comment,
        $last_modified_mark,
        // $lastGrade
    ];
}

echo html_writer::table($table);
echo html_writer::script("
    $(document).ready(function() {
        console.log('jQuery Loaded:', typeof jQuery); 
        console.log('DataTable Loaded:', $.fn.dataTable); 
        console.log('Table Exists:', $('.generaltable').length);

        if ($('.generaltable').length) {
            $('.generaltable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'csv',
                        text: 'CSV',
                        exportOptions: {
                             columns: [2, 3, 4, 5, 6, 7, 8, 9, 10, 11]
                        }
                    },
                    {
                        extend: 'excel',
                        text: 'Excel',
                        exportOptions: {
                             columns: [2, 3, 4, 5, 6, 7, 8, 9, 10, 11]
                        }
                    }
                ]
            });
        } else {
            console.log('⚠️ Table not found!');
        }
    });
");

// Add a submit button.
echo html_writer::tag('div', html_writer::empty_tag('input', [
    'type' => 'submit',
    'value' => 'Save Changes',
    'class' => 'btn btn-primary savechanges',
    'style' => 'margin-top: 20px;',
]), ['style' => 'text-align: center;']);

// Close the form.
echo html_writer::end_tag('form');
// $PAGE->requires->js(new moodle_url('https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js'));
// $PAGE->requires->js(new moodle_url('https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js'));
// Output the page footer.
echo $OUTPUT->footer();

// Add the DataTable initialization script


?>
