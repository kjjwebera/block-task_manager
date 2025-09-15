<?php

defined('MOODLE_INTERNAL') || die();

/**
 * Install function for the block_task_manager plugin.
 *
 * @return void
 */
function xmldb_block_task_manager_install() {
    global $DB;

    // Check if the 'batchcode' field already exists in user_info_field table.
    $existingfield = $DB->get_record('user_info_field', ['shortname' => 'batchcode']);

    if (!$existingfield) {
        // Prepare field data for insertion.
        $fielddata = new stdClass();
        $fielddata->shortname = 'batchcode';
        $fielddata->name = 'Batch Code';
        $fielddata->datatype = 'text';
        $fielddata->description = 'Batch Code for Task Manager';
        $fielddata->descriptionformat = 1;
        $fielddata->categoryid = 1;  // Default category (can be changed if needed)
        $fielddata->sortorder = 1;
        $fielddata->required = 0;
        $fielddata->locked = 0;
        $fielddata->visible = 2;  // Field visible to admins and users
        $fielddata->forceunique = 0;
        $fielddata->signup = 0;
        $fielddata->defaultdata = '';
        $fielddata->defaultdataformat = 0;
        $fielddata->param1 = '30';  // Field size
        $fielddata->param2 = '2048';  // Max length of the field value
        $fielddata->param3 = '0';
        $fielddata->param4 = '';
        $fielddata->param5 = '';

        // Insert the field into the database
        $DB->insert_record('user_info_field', $fielddata);
    }
}
