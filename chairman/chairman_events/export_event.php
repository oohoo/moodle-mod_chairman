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
require_once('../lib_chairman.php');

global $CFG, $USER;

$event_id = optional_param('event_id',null, PARAM_INT);

if(isset($event_id)){

$event = $DB->get_record('chairman_events', array('id'=>$event_id));

$eventname = $event->summary;
$eventname = str_replace(' ', '_', $eventname);
$eventname = str_replace('\'', '_', $eventname);
$eventname = str_replace('.','_' ,$eventname);


//If user did nt set timezone use server timezone
if ($event->timezone == 99) {
    $server_timezone = $CFG->timezone;
} else {
    $server_timezone = $USER->timezone;
}

//calculate offset
$offset = chairman_get_timezone_offset($event->timezone,$server_timezone);

//Convert event into unix timestamp
$event_start = chairman_convert_strdate_time($event->year, $event->day, $event->month, $event->starthour, $event->startminutes);
$event_end = chairman_convert_strdate_time($event->year, $event->day, $event->month, $event->endhour, $event->endminutes);

//calculate offset
$event_start = $event_start + $offset;
$event_end = $event_end + $offset;
//Set timezone to event timezone
//date_default_timezone_set($event->timezone);
//Convert event start and end date to GMT
$event_start = date("Ymd\THis000\Z",$event_start);
$event_end = date("Ymd\THis000\Z",$event_end);

//Currentdate in GMT
//$current_date = gmdate("Ymd\THis\Z",time());
$current_date = date("Ymd\THi00\Z",time());

//organizer
$created_by = $DB->get_record('user', array('id' => $event->user_id));
$organizer = "CN=".fullname($created_by).":MAILTO:".$created_by->email;

$Filename = $eventname.".ics";
header("Content-Type: text/x-vCalendar");
header("Content-Disposition: inline; filename=$Filename");

//$event = $DB->get_record('chairman_events', array('id'=>$event_id));

$DescDump = str_replace("\r", "=0D=0A=", $event->description);

//print out the ical
?>
BEGIN:VCALENDAR
METHOD:PUBLISH
PRODID:-//Patrick Thibaudeau/NONSGML Bennu 0.1//EN
VERSION:2.0
BEGIN:VEVENT
UID:1@localhost/moodle
DTSTAMP:<?php echo $current_date . "\n"; ?>
ORGANIZER;<?php echo $organizer . "\n";?>
DTSTART:<?php echo $event_start . "\n"; ?>
DTEND:<?php echo $event_end . "\n"; ?>
SUMMARY:<?php echo $event->summary . "\n"; ?>
DESCRIPTION;ENCODING=QUOTED-PRINTABLE: <?php echo $DescDump . "\n"; ?>
END:VEVENT
END:VCALENDAR

<?php } ?>
