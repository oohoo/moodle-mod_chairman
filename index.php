<?php

/**
**************************************************************************
**                                Chairman                              **
**************************************************************************
* @package mod                                                          **
* @subpackage chairman                                                  **
* @name Chairman                                                        **
* @copyright oohoo.biz                                                  **
* @link http://oohoo.biz                                                **
* @author Raymond Wainman                                               **
* @author Patrick Thibaudeau                                            **
* @author Dustin Durand                                                 **
* @license                                                              **
http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later                **
**************************************************************************
**************************************************************************/

require_once('../../config.php');
require_once('lib.php');

$id = required_param('id', PARAM_INT); // course
$PAGE->set_url('/mod/chairman/index.php', array('id' => $id));

if (!$course = $DB->get_record('course', array('id' => $id))) {
    print_error('invalidcourseid');
}

require_login($course, true);
$PAGE->set_pagelayout('incourse');

/// Get all required stringswiki
$strchairmans = get_string("modulenameplural", "chairman");
$strchairman = get_string("modulename", "chairman");

/// Print the header
$PAGE->navbar->add($strchairmans, "index.php?id=$course->id");
$PAGE->set_title($strchairmans);
$PAGE->set_heading($course->fullname);
echo $OUTPUT->header();

/// Get all the appropriate data
if (!$chairmans = get_all_instances_in_course("chairman", $course)) {
    notice("There are no chairman modules", "../../course/view.php?id=$course->id");
    die;
}

$usesections = course_format_uses_sections($course->format);

/// Print the list of instances (your module will probably extend this)

$timenow = time();
$strsectionname = get_string('sectionname', 'format_' . $course->format);
$strname = get_string("name");
$table = new html_table();

if ($usesections) {
    $table->head = array($strsectionname, $strname);
} else {
    $table->head = array($strname);
}

foreach ($chairmans as $chairman) {
    $linkcss = null;
    if (!$chairman->visible) {
        $linkcss = array('class' => 'dimmed');
    }
    $link = html_writer::link(new moodle_url('/mod/chairman/view.php', array('id' => $chairman->coursemodule)), $chairman->name, $linkcss);

    if ($usesections) {
        $table->data[] = array(get_section_name($course, $chairman->section), $link);
    } else {
        $table->data[] = array($link);
    }
}

echo html_writer::table($table);

/// Finish the page
echo $OUTPUT->footer();
