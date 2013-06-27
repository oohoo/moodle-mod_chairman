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

/**
 * @package   chairman
 * @copyright 2010 Raymond Wainman, Patrick Thibaudeau (Campus St. Jean, University of Alberta)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../../config.php');
require_once('../lib.php');
require_once('../lib_chairman.php');

$id = required_param('id',PARAM_INT);
$chairman_id = required_param('chairman_id',PARAM_INT);
$cm = get_coursemodule_from_id('chairman', $chairman_id); //Course Module Object

//Values from form

$day = optional_param('day',0, PARAM_INT);
$day = trim($day);
if (strlen($day) == 1) {
    $day = '0'.$day;
}
$month = optional_param('month',0, PARAM_INT);

$month = trim($month);
if (strlen($month) == 1) {
    $month = '0'.$month;
}
$year = optional_param('year',0, PARAM_INT);

$starthour = optional_param('starthour',0, PARAM_INT);
$startminutes = optional_param('startminutes',0, PARAM_INT);

$endhour = optional_param('endhour',0, PARAM_INT);
$endminutes = optional_param('endminutes',0, PARAM_INT);

$summary = optional_param('summary',null, PARAM_TEXT);
$description = optional_param('description',null, PARAM_TEXT);
$notify = optional_param('notify',0, PARAM_INT);
$notify_week = optional_param('notify_week',0, PARAM_INT);
$timezone = optional_param('timezone','99', PARAM_TEXT);

//Just incase anumber is returned
if ($timezone == '99') {
	$timezone = $CFG->timezone;
}
//

chairman_check($chairman_id);
chairman_header($chairman_id,'editevent','edit_event.php?id='.$chairman_id.'&event_id='.$id);

echo '<div><div class="title">'.get_string('editevent', 'chairman').'</div>';

echo get_string('modifyingeventpleasewait', 'chairman');

echo '<span class="content">';

echo '</span></div>';

chairman_footer();


$edit_event = new stdClass();
$edit_event->id = $id;
$edit_event->user_id = $USER->id;
$edit_event->chairman_id = $chairman_id;
$edit_event->day = $day;
$edit_event->month = $month;
$edit_event->year = $year;
$edit_event->starthour = $starthour;
$edit_event->startminutes = $startminutes;
$edit_event->endhour = $endhour;
$edit_event->endminutes = $endminutes;
$edit_event->summary = $summary;
$edit_event->description = $description;
$edit_event->stamp_start = $year.$month.$day.$starthour.$startminutes.'00';
$edit_event->stamp_end = $year.$month.$day.$endhour.$endminutes.'00';
$edit_event->stamp_t_start = $year.$month.$day.'T'.$starthour.$startminutes.'00';
$edit_event->stamp_t_end = $year.$month.$day.'T'.$endhour.$endminutes.'00';
$edit_event->notify= $notify;
$edit_event->notify_week= $notify_week;
$edit_event->timezone= $timezone;

if (!$DB->update_record('chairman_events', $edit_event)) {
    echo 'Data was not saved';
} else {
    //Add to logs
    add_to_log($cm->course, 'chairman', 'update', '', $summary, $chairman_id);
}

echo '<script type="text/javascript">';
echo 'window.location.href="'.$CFG->wwwroot.'/mod/chairman/chairman_events/events.php?id='.$chairman_id.'";';
echo '</script>';

?>
