<?php

/**
 * *************************************************************************
 * *                                Chairman                              **
 * *************************************************************************
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
 * *************************************************************************
 * ************************************************************************ */
/**
 * @package   chairman
 * @copyright 2010 Raymond Wainman, Dustin Durand, Patrick Thibaudeau (Campus St. Jean, University of Alberta)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../../config.php');
require_once('../lib.php');
require_once('../lib_chairman.php');

$id = optional_param('id', 0, PARAM_INT);    // Course Module ID
$cm = get_coursemodule_from_id('chairman', $id); //Course Module Object
//Values from form

$day = optional_param('day', null, PARAM_INT);
$day = trim($day);
if (strlen($day) == 1) {
    $day = '0' . $day;
}
$month = optional_param('month', null, PARAM_INT);
$month = trim($month);
if (strlen($month) == 1) {
    $month = '0' . $month;
}
$year = optional_param('year', null, PARAM_INT);

$starthour = optional_param('starthour', null, PARAM_INT);
$startminutes = optional_param('startminutes', null, PARAM_INT);

$endhour = optional_param('endhour', null, PARAM_INT);
$endminutes = optional_param('endminutes', null, PARAM_INT);

$summary = optional_param('summary', null, PARAM_TEXT);
$description = optional_param('description', null, PARAM_TEXT);
$notify = optional_param('notify', 0, PARAM_INT);
$notify_week = optional_param('notify_week', 0, PARAM_INT);
$notify_now = optional_param('notify_now', 0, PARAM_INT);
$timezone = optional_param('timezone', '99', PARAM_TEXT);

if ($timezone == '99') {
    $timezone = $CFG->timezone;
}
$room_reservation_id = optional_param('room_reservation_id', 0, PARAM_TEXT);
//

chairman_check($id);
chairman_header($id, 'addevent', 'add_event.php?id=' . $id);

echo '<div><div class="title">' . get_string('addevent', 'chairman') . '</div>';

echo get_string('addingeventpleasewait', 'chairman');

echo '<span class="content">';

echo '</span></div>';

chairman_footer();

$new_event = new stdClass();
$new_event->user_id = $USER->id;
$new_event->chairman_id = $id;
$new_event->day = $day;
$new_event->month = $month;
$new_event->year = $year;
$new_event->starthour = $starthour;
$new_event->startminutes = $startminutes;
$new_event->endhour = $endhour;
$new_event->endminutes = $endminutes;
$new_event->summary = $summary;
$new_event->description = $description;
$new_event->room_reservation_id = $room_reservation_id;
$new_event->stamp_start = $year . $month . $day . $starthour . $startminutes . '00';
$new_event->stamp_end = $year . $month . $day . $endhour . $endminutes . '00';
$new_event->stamp_t_start = $year . $month . $day . 'T' . $starthour . $startminutes . '00';
$new_event->stamp_t_end = $year . $month . $day . 'T' . $endhour . $endminutes . '00';
$new_event->notify = $notify;
$new_event->notify_week = $notify_week;
$new_event->timezone = $timezone;

if (!$DB->insert_record('chairman_events', $new_event)) {
    echo 'Data was not saved';
} else {
    //Add to logs
    add_to_log($cm->course, 'chairman', 'add', '', $summary, $id);
}
echo '<script type="text/javascript">';
echo 'window.location.href="' . $CFG->wwwroot . '/mod/chairman/chairman_events/events.php?id=' . $id . '";';
echo '</script>';
?>
