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

require_once('../../../config.php');
require_once('../lib.php');
require_once('../lib_chairman.php');

global $CFG, $DB, $USER;
$event_id = required_param('event_id', PARAM_INT);

//Get the event information
$event = $DB->get_record('chairman_events', array('id' => $event_id));
$cm = $DB->get_record('course_modules',array('id' => $event->chairman_id));
$chairman = $DB->get_record('chairman',array('id' => $cm->instance));

$moodle_event = new stdClass;
$moodle_event->name = $event->summary;
$moodle_event->description = $event->description;
$moodle_event->format = 1;
$moodle_event->courseid = 0;
$moodle_event->name = $event->summary;
$moodle_event->groupid = 0;
$moodle_event->userid = $USER->id;
$moodle_event->repeatid = 0;
$moodle_event->modulename = 0;
$moodle_event->instance = 0;
$moodle_event->eventtype = 'user';

if ($USER->timezone == 99) {
    $server_timezone = $CFG->timezone;
} else {
    $server_timezone = $USER->timezone;
}

//get the time that the event starts at and ends
$event_start = make_timestamp($event->year, $event->month, $event->day, $event->starthour, $event->startminutes, '00', $event->timezone);
$event_end = make_timestamp($event->year, $event->month, $event->day, $event->endhour, $event->endminutes,'00',$event->timezone );
$event_duration = $event_end - $event_start;

$moodle_event->timestart = $event_start;
$moodle_event->timeduration = $event_duration;
$moodle_event->visible = 1;
$moodle_event->sequence = 1;
$moodle_event->timemodified = time();

//Enter event into user calendar
if(! $new_event = $DB->insert_record('event', $moodle_event)){
    print_error('cannotinsertrecord');
} else {
    redirect($CFG->wwwroot."/mod/chairman/chairman_events/events.php?id=$event->chairman_id", get_string('event_added_to_mcal', 'mod_chairman'), 3);
}

?>
