<?php
namespace block_task_manager\api;

class client {
    public static function call_my_external_api($api_record) {
        global $CFG;

        require_once($CFG->libdir . '/filelib.php'); // Required for \curl

        $curl = new \curl();

        $url = 'https://cmis4api.anudip.org/public/api/insertThursdayLabFromLMS';

        $headers = [
            'Authorization: a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6',
            'Content-Type: application/json'
        ];

        $payload = [
            "student_idnumber" => $api_record['student_idnumber'],
            "student_code"     => $api_record['student_code'],
            "course_alias"     => $api_record['course_alias'],
            "vplname"          => $api_record['vplname'],
            "type"             => "tdlab",
            "marks"            => $api_record['marks'],
            "maxmarks"         => $api_record['maxmarks']
        ];
        // print_object($record);die;

        try {
            $response = $curl->post($url, json_encode($payload), [
                'CURLOPT_HTTPHEADER' => $headers
            ]);

            $httpcode = $curl->get_info()['http_code'];

            return [
                'status' => $httpcode,
                'body'   => json_decode($response, true)
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'body'   => ['error' => $e->getMessage()]
            ];
        }
    }
}


// class client {
//     public static function call_my_external_api($record) {
//         $curl = curl_init();

// 		curl_setopt_array($curl, array(
// 		  CURLOPT_URL => 'https://cmis4api.anudip.org/public/api/insertThursdayLabFromLMS',
// 		  CURLOPT_RETURNTRANSFER => true,
// 		  CURLOPT_ENCODING => '',
// 		  CURLOPT_MAXREDIRS => 10,
// 		  CURLOPT_TIMEOUT => 0,
// 		  CURLOPT_FOLLOWLOCATION => true,
// 		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
// 		  CURLOPT_CUSTOMREQUEST => 'POST',
// 		  CURLOPT_POSTFIELDS =>'{
// 		    "student_idnumber": "542345",
// 		    "student_code": "AF04951390",
// 		    "course_alias": "AJP",
// 		    "vplname": "male",
// 		    "type": "tdlab",
// 		    "marks": "10",
// 		    "maxmarks": "20"
// 		}',
// 		  CURLOPT_HTTPHEADER => array(
// 		    'Authorization: a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6',
// 		    'Content-Type: application/json'
// 		  ),
// 		));

// 		$response = curl_exec($curl);

// 		curl_close($curl);
// 		json_encode($response);


//         try {
//             $response = $curl->post($url, $postdata, ['CURLOPT_HTTPHEADER' => $headers]);
//             $code = $curl->get_info()['http_code'];

//             return [
//                 'status' => $code,
//                 'body' => json_decode($response, true)
//             ];
//         } catch (\Exception $e) {
//             return [
//                 'status' => 'error',
//                 'body' => $e->getMessage()
//             ];
//         }
//     }
// }

