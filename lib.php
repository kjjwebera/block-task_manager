<?php
defined('MOODLE_INTERNAL') || die();

function block_task_manager_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    global $DB;

    require_login(); // Ensure user is authenticated

    // Debugging Output
    error_log("DEBUG: ContextID={$context->id}, Filearea={$filearea}, Args=" . json_encode($args));

    // Validate Context Level
    if ($context->contextlevel != CONTEXT_BLOCK) {
        error_log("DEBUG: Invalid Context Level ({$context->contextlevel}), expected CONTEXT_BLOCK.");
        send_file_not_found();
    }

    // Extract ItemID & Filename from $args
    if (count($args) < 2) {
        error_log("DEBUG: Invalid Args Count. Args=" . json_encode($args));
        send_file_not_found();
    }

    $itemid = array_shift($args); // First argument is itemid
    $filename = array_pop($args); // Last argument is filename
    $filepath = '/' . implode('/', $args) . '/';

    error_log("DEBUG: Extracted -> ItemID={$itemid}, Filepath={$filepath}, Filename={$filename}");

    // Retrieve file from File Storage API
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'block_task_manager', $filearea, $itemid, $filepath, $filename);

    if (!$file || $file->is_directory()) {
        error_log("DEBUG: File Not Found in File Storage!");
        send_file_not_found();
    }

    error_log("DEBUG: File Found! Serving now...");
    send_stored_file($file, 0, 0, true);
}

// function calling_api_after_graded(){
//     $curl = curl_init();

//     curl_setopt_array($curl, array(
//       CURLOPT_URL => 'https://cmis4api.anudip.org/public/api/insertThursdayLabFromLMS',
//       CURLOPT_RETURNTRANSFER => true,
//       CURLOPT_ENCODING => '',
//       CURLOPT_MAXREDIRS => 10,
//       CURLOPT_TIMEOUT => 0,
//       CURLOPT_FOLLOWLOCATION => true,
//       CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//       CURLOPT_CUSTOMREQUEST => 'POST',
//       CURLOPT_POSTFIELDS =>'{
//         "student_idnumber": "542345",
//         "student_code": "AF04951390",
//         "course_alias": "AJP",
//         "vplname": "male",
//         "type": "tdlab",
//         "marks": "10",
//         "maxmarks": "20"
//     }',
//     CURLOPT_HTTPHEADER => array(
//         'Authorization: a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6',
//         'Content-Type: application/json'
//     ),
// ));

//     $response = curl_exec($curl);

//     curl_close($curl);
//     echo $response;

// }