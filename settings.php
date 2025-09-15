<?php

$ADMIN->add('reports', new admin_externalpage(
    'taskmanagelink',
    get_string('pluginname', 'block_task_manager'),
    new moodle_url('/blocks/task_manager/task_manage.php'),
    'block/task_manager:manage_tasks'
));
