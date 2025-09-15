<?php

require_once('../../config.php');
require_once($CFG->libdir . '/tablelib.php');
global $DB, $PAGE, $OUTPUT, $USER;


// Retrieve the file stored in the Moodle file API.
$context = context_system::instance();
$component = 'block_taskmanager'; // Plugin component name.
$filearea = 'blockupload';   // File area name.
$itemid = 0;                // Item I43ooooooooooooo-=0 D used during upload.

// Prepare the file storage API.
$fs = get_file_storage();

// Retrieve the stored file(s) from the file storage.
$files = $fs->get_area_files($context->id, $component, $filearea, $itemid, 'timemodified', false);

if (!empty($files)) {
    foreach ($files as $file) {
        // Display file information.
        $filename = $file->get_filename();
        $filepath = moodle_url::make_pluginfile_url(
            $file->get_contextid(),
            $file->get_component(),
            $file->get_filearea(),
            $file->get_itemid(),
            $file->get_filepath(),
            $file->get_filename()
        );

        echo html_writer::link($filepath, $filename) . '<br>';
    }
} else {
    echo get_string('nofiles', 'block_task_manager'); // Add a string for "No files found" in your language file.
}
