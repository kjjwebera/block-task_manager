<?php

require_once('../../config.php');
global $DB, $USER;

// Ensure the user is logged in.
require_login();

// Get the course ID and task ID from the request.
$courseid = required_param('courseid', PARAM_INT);
$taskid = required_param('taskid', PARAM_INT);



if (isset($_POST['setsubmission']) && is_array($_POST['setsubmission'])) {
    $selectedSubmissions = $_POST['setsubmission'];   
} else {
    redirect(new moodle_url('/blocks/task_manager/evaluate_task.php', ['courseid' => $courseid, 'taskid' => $taskid]), 'Please check the checkbox and then submit the form.', null, \core\output\notification::NOTIFY_ERROR);
}
// exit;
// Get the form data (marks array).
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['marks'])) {
    $marks = required_param_array('marks', PARAM_RAW); // Get the marks array.

    // $submissionMarks = array_combine($selectedSubmissions, $marks);
   
    
    foreach ($selectedSubmissions as $submission_id => $setsubmission) {
        $submission = $DB->get_record('block_task_manager_sub', ['id' => $setsubmission], 'id, task_id, user_id', MUST_EXIST);



        if ($submission->id == $setsubmission) {
            //check if whethere the score exists or not.
            $checkscore = $DB->get_record('block_task_manager_score', ['sub_id'=> $submission->id]);

            $scoredata = new stdClass();
            if(!empty($checkscore->score)){
                $scoredata->id = $checkscore->id;
                $scoredata->score = $marks[$submission_id];
                $scoredata->timemodified = time();
                $res = $DB->update_record('block_task_manager_score',$scoredata);
            }else{
                $scoredata->sub_id = $submission->id;
                $scoredata->task_id = $submission->task_id;
                $scoredata->user_id = $submission->user_id;
                $scoredata->score = $marks[$submission_id];
                $scoredata->updatedby_userid = $USER->id;
                $scoredata->apiupdate_status = 'pending';
                $scoredata->timemodified = time();
                $scoredata->timecreated = time();
                $res = $DB->insert_record('block_task_manager_score',$scoredata);
            }

            if(!empty($res)){
                $submissionobject = new stdClass();
                $submissionobject->id =  $submission->id;
                $submissionobject->lastmodefied_mark =  $marks[$submission_id];
                $submissionobject->timemodified =  time();
                $DB->update_record('block_task_manager_sub',$submissionobject);

            }



            // Update the submission record with the new marks and evaluator info.
            // $updateData = new stdClass();
            // $updateData->id = $submission_id;
            // $updateData->marks = $marks[$sub_id];
            // $updateData->evaluated_by = $USER->id;
            // $updateData->lastmodefied_mark = time();
            // $updateData->final_grade = '-';
            // $updateData->timecreated = time();
            // $updateData->timemodified = time();
            // $records = $DB->get_records('block_task_manager_score',array('task_id'=>$submission_id));
            
            // print_r($marks);
            // print_r($submission);die;
            // $scoredata->sub_id = $submission->id;
            // $scoredata->task_id = $submission->task_id;
            // $scoredata->user_id = $submission->user_id;
            // $scoredata->score = $marks[$submission_id];
            // $scoredata->updatedby_userid = $USER->id;
            // $scoredata->apiupdate_status = 'pending';
            // $scoredata->timemodified = time();
            // $scoredata->timecreated = time();
            // $DB->insert_record('block_task_manager_score',$scoredata);
            // $DB->update_record('block_task_manager_sub', $updateData);





            //inserting the records task_manager_score table.
            // $insertrecords = new stdClass();
            // $insertrecords->task_id = $submission->task_id;
            // $insertrecords->user_id = $submission->user_id;
            // $insertrecords->score = $marks[$submission_id];
            // $insertrecords->updatedby_userid = $USER->id;
            // $insertrecords->apiupdate_status = 'pending';
            // $insertrecords->timemodified = time();
            // $insertrecords->timecreated = time();
            // $DB->insert_record('block_task_manager_score',$insertrecords);
        }
    }

    // Redirect back to the evaluate page with a success message.
    redirect(new moodle_url('/blocks/task_manager/evaluate_task.php', ['courseid' => $courseid, 'taskid' => $taskid]), 'Marks updated successfully.', null, \core\output\notification::NOTIFY_SUCCESS);
} else {
    // Redirect back with an error message if no marks were provided.
    redirect(new moodle_url('/blocks/task_manager/evaluate_task.php', ['courseid' => $courseid, 'taskid' => $taskid]), 'No marks were provided.', null, \core\output\notification::NOTIFY_ERROR);
}

