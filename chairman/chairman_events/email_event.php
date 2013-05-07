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

//get sender email
$sender = $DB->get_record('user', array('id' => $USER->id));
$senderemail = $sender->email;

//$Filename = $eventname.".ics";

//Get committee members
$members = get_chairman_members($event->chairman_id);

//Send email for each member
    foreach ($members as $member){
    //Create mail body
    $mailbody = header("Content-Type: text/x-vCalendar");
    $mailbody .= header("Content-Disposition: inline;");

    $DescDump = str_replace("\r", "=0D=0A=", $event->description);

    $mailbody .= 'BEGIN:VCALENDAR'."\n";
    $mailbody .= 'BEGIN:VEVENT'."\n";
    $mailbody .= 'TZ:-0700'."\n";
    $mailbody .= "ATTENDEE;ROLE=REQ-PARTICIPANT;RSVP=TRUE:MAILTO:$member->email\n";
    $mailbody .= "ORGANIZER:MAILTO:$senderemail\n";
    $mailbody .= 'DTSTART;TZID="Mountain Standard Time":'.$event->stamp_t_start ."\n";
    $mailbody .= 'DTEND;TZID="Mountain Standard Time":'.$event->stamp_t_end . "\n";
    $mailbody .= 'SUMMARY:'.$event->summary . "\n";
    $mailbody .= 'DESCRIPTION;ENCODING=QUOTED-PRINTABLE:'.$DescDump . "\n";
    $mailbody .= 'END:VEVENT'."\n";
    $mailbody .= 'END:VCALENDAR'."\n";
    $user = $DB->get_record('user',array('id' => $member->id));
    email_to_user($user, $senderemail, $event->summary, $mailbody) ;
    }
}
?>
