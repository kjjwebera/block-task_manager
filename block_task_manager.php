<?php

/**
 * Plugin Name: Task manager Plugin
 * Description: A Moodle block plugin that displays tasks in a course.
 * Version: 1.0
 * Author: Your Name
 */

defined('MOODLE_INTERNAL') || die();

class block_task_manager extends block_base {

    /**
     * Initialize the block with a title.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_task_manager');
    }

    /**
     * Specify where the block can be added.
     */
    public function applicable_formats() {
        return [
            'course-view' => true, // Allow only on course pages
            'site-index' => false, // Disallow on the site home
        ];
    }

    /**
     * Get the block content.
     */
    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }

        global $COURSE, $DB, $PAGE, $USER;
        $courseid = $COURSE->id;
        
        $context = context_course::instance($courseid);
        $this->content = new stdClass();
        $this->content->text = '';
       
        // Add required JS for the dropdown to work.
        $PAGE->requires->js_call_amd('core/first', 'init'); // Ensures Moodle loads Bootstrap JS.
        $blockinstance = $DB->get_record('block_instances', [
            'parentcontextid' => $context->id,  
            'blockname' => 'task_manager'
        ]);
        $blockid = $blockinstance->id;
	    $blockcontext = context_block::instance($blockid);

        // Check if the user has the capability to manage tasks.
        //print_r($context); die();
        if (has_capability('block/task_manager:manage_tasks', $context)) {
            // Add the "Assign Task" button.
            $url = new moodle_url('/blocks/task_manager/assign_task.php', [
                'courseid' => $courseid // Pass the current course ID as a parameter
            ]);

            $assign_button = html_writer::link($url, get_string('addtask', 'block_task_manager'), [
                'class' => 'btn btn-primary'
            ]);

            $this->content->text .= $assign_button;
        }

        // Fetch tasks for the current course.
        /*$tasks = $DB->get_records('block_task_manager_tasks', ['course_id' => $courseid, 'batchcode' => 'cgfhg']);
        $totalTasks = $DB->count_records('block_task_manager_tasks', ['course_id' => $courseid]);*/

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

      
// Display tasks in a table.
if (!empty($tasks)) {
    $table = new html_table();
    $table->head = [
        get_string('heading', 'block_task_manager') . "<span class='task-count'>" . $totalTasks . "</span>",
    ];
    
    $userBatchcode = isset($USER->profile['batchcode']) ? $USER->profile['batchcode'] : '';

    foreach ($tasks as $task) {
        $task_display = ''; 

        if (has_capability('block/task_manager:manage_tasks', $context)) {
            $task_submission_url = new moodle_url('/blocks/task_manager/task_manage.php', [
                'taskid' => $task->id,
                'courseid' => $courseid
            ]);
            
            // $task_display = html_writer::tag('div', 
            //     html_writer::link($task_submission_url, format_string($task->title), ['class' => 'task-title'])
            // ) . html_writer::tag('div', userdate($task->due_date, '%B %d, %I:%M %p'), [
            //     'class' => 'task-due-date',
            //     'style' => 'font-size: 0.9em; color: gray; margin-top: 4px;'
            // ]);

        //--------------------------------------------------------------------------------------
        $editurl = new moodle_url('/blocks/task_manager/taskedit.php', ['taskid' => $task->id, 'courseid' => $courseid]);
        $evaluateurl = new moodle_url('/blocks/task_manager/evaluate_task.php', ['taskid' => $task->id, 'courseid' => $courseid]);
        $deleteurl = new moodle_url('/blocks/task_manager/delete_task.php', ['taskid' => $task->id, 'courseid' => $courseid]);

        $actions = html_writer::div('
            <div class="dropdown">
                <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Actions">
                    <i class="fa fa-cog"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="' . $editurl . '"><i class="fa fa-edit mr-1"></i> Edit</a>
                    <a class="dropdown-item" href="' . $evaluateurl . '"><i class="fa fa-check mr-1"></i> Evaluate</a>
                    <a class="dropdown-item text-danger" href="' . $deleteurl . '" onclick="return confirm(\'Are you sure you want to delete this task and related user data?\');"><i class="fa fa-trash mr-1"></i> Delete</a>
                </div>
            </div>
        ', 'task-actions');

        $title_with_actions = html_writer::div(
            html_writer::div(
                html_writer::link($task_submission_url, format_string($task->title), ['class' => 'task-title mb-1'])
                . html_writer::div(userdate($task->due_date, '%B %d, %I:%M %p'), 'text-muted small'),
                'task-text'
            ) .
            $actions,
            'd-flex justify-content-between align-items-start task-header'
        );

        $task_display = html_writer::div($title_with_actions, 'task-item p-3 mb-3 border rounded bg-white shadow-sm');
        //-------------------------------------------------------------------------------------
        } else if (!empty($task->batchcode)) {
            $batchcodeArray = array_map('trim', explode(',', $task->batchcode));

           
            if (in_array('no_one', $batchcodeArray)) {
                continue; 
            }

            if (in_array('all', $batchcodeArray) || in_array($userBatchcode, $batchcodeArray)) {
                $task_submission_url = new moodle_url('/blocks/task_manager/submission_status.php', [
                    'taskid' => $task->id,
                    'courseid' => $courseid
                ]);

                $task_display = html_writer::tag('div', 
                    html_writer::link($task_submission_url, format_string($task->title), ['class' => 'task-title'])
                ) . html_writer::tag('div', userdate($task->due_date, '%B %d, %I:%M %p'), [
                    'class' => 'task-due-date',
                    'style' => 'font-size: 0.9em; color: gray; margin-top: 4px;'
                ]);
            }
        }
        if (!empty($task_display)) {
            $table->data[] = [$task_display];
        }
    }

        if (!empty($table->data)) {
            $this->content->text .= html_writer::table($table);
        } else {
            $this->content->text .= html_writer::div(get_string('notasks', 'block_task_manager'), 'alert alert-info');
        }
    } else {
        $this->content->text .= html_writer::div(get_string('notasks', 'block_task_manager'), 'alert alert-info');
    }


            return $this->content;


    }
}
