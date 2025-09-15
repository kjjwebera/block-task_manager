<?php
// $tasks = [ 
// 	[ 

// 		'classname' => 'block_task_manager\task\sync_pending_records', 
// 		'blocking' => 0, 
// 		'minute' => '0', 
// 		'hour' => '2', // Runs at 2:00 AM 
// 		'day' => '*', 
// 		'month' => '*', 
// 		'dayofweek' => '*', 
// 	],
//  ];

 $tasks = [
    [
        'classname' => 'block_task_manager\task\sync_pending_records',
        'blocking' => 0,
        'minute' => '*',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ],
];