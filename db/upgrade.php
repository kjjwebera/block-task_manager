<?php
function xmldb_block_task_manager_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2025061100) {  // your new version number
        $table = new xmldb_table('block_task_manager_score');
        $field = new xmldb_field('sub_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Add field if it doesn't exist
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_block_savepoint(true, 2025061101, 'task_manager');
    }

    return true;
}

