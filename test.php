
<?php
require_once('../../config.php');
require_login();

global $OUTPUT;

echo $OUTPUT->header();
echo html_writer::tag('h3', 'Testing API Data Preparation', ['style' => 'color: green;']);

 function execute() {
        global $DB;

            $pending = $DB->get_records_select(
                'block_task_manager_score',
                'apiupdate_status = :status',
                ['status' => 'pending'],
                '',
                '*',
                0,
                100
            );
        if(!empty($pending)){
           foreach ($pending as $record) {
           	print_r($record);
           	echo "<br><br><br>";

           	$userid = $record->user_id;
			$taskid = $record->task_id;

			// Get user details.
			$user = $DB->get_record('user', ['id' => $userid], 'username, idnumber', MUST_EXIST);

			// Get task details.
			$task = $DB->get_record('block_task_manager_tasks', ['id' => $taskid], 'title, total_mark, course_id', MUST_EXIST);

			// Get course shortname.
			$course = $DB->get_record('course', ['id' => $task->course_id], 'shortname', MUST_EXIST);

			// Now construct the API record.
			$api_record = [
			    "student_idnumber" => $user->username,
			    "student_code"     => $user->idnumber,
			    "course_alias"     => $course->shortname,
			    "vplname"          => $task->title,
			    "type"             => "tdlab",
			    "marks"            => $record->score,
			    "maxmarks"         => $task->total_mark
			];
			print_r($api_record);
			$x=$api_record['marks'];
			echo ("<br>".$x."<br>");

            //$response = \block_task_manager\api\client::call_my_external_api($record);
            //$record->apiupdate_status = $response['status'];
            //$record->timemodified = time();
            //$DB->update_record('block_task_manager_score', $record);
        } 
    }
        
    }
execute();

echo $OUTPUT->footer();