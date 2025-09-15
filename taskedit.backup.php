<?php
require_once('../../config.php'); // Include the Moodle configuration file.

// Ensure that the user is logged in and has proper access.
require_login();

// Get parameters from the URL.
$taskid = required_param('taskid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);

// Set up the page.
$context = context_course::instance($courseid);
$PAGE->set_context($context);
$PAGE->set_url('/blocks/task_manager/update_task.php', ['taskid' => $taskid, 'courseid' => $courseid]);
$PAGE->set_title(get_string('updatetask', 'block_task_manager'));
$PAGE->set_heading(get_string('updatetask', 'block_task_manager'));
if (!has_capability('block/task_manager:manage_tasks', $context)) {
    print_error('nopermission', 'block_task_manager');
}
// Include the form library.
require_once($CFG->libdir . '/formslib.php');

// Fetch the task details.
global $DB, $OUTPUT;
$task = $DB->get_record('block_task_manager_tasks', ['id' => $taskid], '*', MUST_EXIST);


class update_task_form extends moodleform {
    public function definition() {
        global $DB;
        $mform = $this->_form;

        // Task Title.
        $mform->addElement('text', 'title', get_string('tasktitle', 'block_task_manager'));
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', get_string('required'), 'required', null, 'client');

        // Short Description
        $mform->addElement('textarea', 'taskshortdesc', get_string('taskshortdesc', 'block_task_manager'), [
            'cols' => 70,
            'rows' => 1
        ]);

        // Task Description.
        $mform->addElement('editor', 'description', get_string('taskdescription', 'block_task_manager'));
        $mform->setType('description', PARAM_RAW);

        // Hidden fields.
        $mform->addElement('hidden', 'taskid');
        $mform->setType('taskid', PARAM_INT);
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        // Submission period section
        $mform->addElement('header', 'submissionperiod', get_string('submissionperiod', 'block_task_manager'));
        $mform->setExpanded('submissionperiod', true);
        $mform->addElement('date_selector', 'due_date', get_string('duedate', 'block_task_manager'));

        // Submission restrictions section
        $mform->addElement('header', 'submissionrestric', get_string('submissionrestric', 'block_task_manager'));
        $mform->setExpanded('submissionrestric', true);

        // Maximum File Size
        $mform->addElement('text', 'filesize', get_string('maximum', 'block_task_manager'));
        $mform->setType('filesize', PARAM_TEXT);

        // Type of Work
        $mform->addElement('select', 'typeofwork', get_string('typeofwork', 'block_task_manager'), [
            'individualwork' => get_string('individualwork', 'block_task_manager'),
            'teamwork' => get_string('teamwork', 'block_task_manager'),
        ]);
        $mform->setType('typeofwork', PARAM_ALPHA);

       
        $batchcodes = $DB->get_records_sql("
    SELECT DISTINCT d.data
    FROM {user_info_field} f
    JOIN {user_info_data} d ON f.id = d.fieldid
    WHERE f.shortname = 'batchcode'
");
        $batchcode_options = [
            'no_one' => 'No one',
            'all' => 'All'
        ];
        foreach ($batchcodes as $batchcode) {
            $batchcode_options[$batchcode->data] = $batchcode->data;
        }
        $mform->addElement('select', 'batchcode', get_string('batchcode', 'block_task_manager'), $batchcode_options, ['multiple' => 'multiple']);
        $mform->setType('batchcode', PARAM_RAW);

       
        $mform->addElement('header', 'evaluate', get_string('evaluate', 'block_task_manager'));
        $mform->setExpanded('evaluate', false);
        $mform->addElement('text', 'totalmarks', get_string('totalmarks', 'block_task_manager'));
        $mform->setType('totalmarks', PARAM_TEXT);
        $mform->addElement('text', 'marks', get_string('marks', 'block_task_manager'));
        $mform->setType('marks', PARAM_TEXT);

     
        $mform->addElement('select', 'task_visibility', get_string('taskvisibility', 'block_task_manager'), [
            'yes' => get_string('yes'),
            'no' => get_string('no'),
        ]);
        $mform->setDefault('task_visibility', 'yes');
        $mform->setType('task_visibility', PARAM_ALPHA);

        // Submit button
        $this->add_action_buttons($cancel = true, $submitlabel = 'Update');
    }
}


$mform = new update_task_form();
$mform->set_data([
    'title' => $task->title,
    'taskshortdesc' => $task->short_desc,
    'description' => ['text' => $task->description, 'format' => FORMAT_HTML],
    'taskid' => $taskid,
    'courseid' => $courseid,
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
    // Redirect if the form is cancelled.
    redirect(new moodle_url('/course/view.php',['id' => $courseid]));
} elseif ($data = $mform->get_data()) {
    // Update the task in the database.
    $task_update = new stdClass();
    $task_update->id = $data->taskid;
    $task_update->title = $data->title;
    $task_update->short_desc = $data->taskshortdesc;
    $task_update->description = $data->description['text'];
    $task_update->due_date = $data->due_date;
    $task_update->filesize = $data->filesize;
    $task_update->typeofwork = $data->typeofwork;
    $task_update->total_mark = $data->totalmarks;
    $task_update->pass_mark = $data->marks;
    $task_update->visibility = $data->task_visibility;
    
   
    $task_update->batchcode = is_array($data->batchcode) ? implode(',', $data->batchcode) : $data->batchcode;

    // Update the record in the database
    $DB->update_record('block_task_manager_tasks', $task_update);

    // Redirect with a success message.
    redirect(new moodle_url('/course/view.php',['id' => $courseid]), get_string('taskupdated', 'block_task_manager'));

    //redirect(new moodle_url('/blocks/task_manager/taskedit.php', ['taskid' => $taskid,'courseid' => $courseid]), get_string('taskupdated', 'block_task_manager'));
}

// Output the page.
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('updatetask', 'block_task_manager'));

// Display the form.
$mform->display();

echo $OUTPUT->footer();
