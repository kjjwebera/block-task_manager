<?php


defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'block/task_manager:manage_tasks' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,  // Capability is at block level.
        'archetypes' => [
            'manager' => CAP_ALLOW,  // Allow managers to manage tasks.
	    'editingteacher' => CAP_ALLOW,
          // Allow editing teachers to manage tasks.
	    
        ],
        //'clonepermissionsfrom' => 'moodle/site:manageblocks', // Cloning permissions from an existing capability.
    ],
    'block/task_manager:managefiles' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ],
    ],
    'block/task_manager:viewfiles' => [
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'student' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
        ],
    ],
    
];

