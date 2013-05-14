<?php
 
/**
* Simple script for backing up a module, creating a new course, and then restoring
* the backed up module to the new course.
*/

require_once('config.php');
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

$course_module_to_backup = 9; // Set this to one existing choice cmid in your dev site
$user_doing_the_backup   = 2; // Set this to the id of your admin accouun
 
$bc = new backup_controller(backup::TYPE_1ACTIVITY, $course_module_to_backup, backup::FORMAT_MOODLE,
                            backup::INTERACTIVE_NO, backup::MODE_GENERAL, $user_doing_the_backup);
$bc->execute_plan();

$fullname = "Billy Bob's Backup";
$shortname = "BBBackup";
$categoryid = 1;//misc
$folder = $bc->get_backupid();

// Transaction
$transaction = $DB->start_delegated_transaction();

// Create new course
$courseid = restore_dbops::create_new_course($fullname, $shortname, $categoryid);

// Restore backup into course
$controller = new restore_controller($folder, $courseid,
		backup::INTERACTIVE_NO, backup::MODE_SAMESITE, 2,
		backup::TARGET_NEW_COURSE);
$controller->execute_precheck();
$controller->execute_plan();

// Commit
$transaction->allow_commit();
