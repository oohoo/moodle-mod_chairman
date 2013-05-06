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
require_once('../../config.php');
require_once('lib.php');
require_once('lib_chairman.php');
echo '<link rel="stylesheet" type="text/css" href="style.php">';

$id = optional_param('id',0,PARAM_INT);    // Course Module ID

chairman_check($id);
chairman_header($id,'events','events.php?id='.$id);

//If a person is not a member(ANY ROLE-pres, member, etc), no content shown.
/*
if(!chairman_isMember($id)){
   print "<b>".get_string('not_member','chairman')."</b>";
chairman_footer();
exit();
}
*/
echo '<div><div class="title">'.get_string('events', 'chairman').'</div>';

echo '<span class="content">';

//Current time stamp
$current_year = date('Y');
$current_month = date('m');
$current_day = date('d');
$current_hour = date('H');
$current_minutes = date('i');
$current_time = $current_year.$current_month.$current_day.$current_hour.$current_minutes.'00';

$now = time();

$events = $DB->get_records('chairman_events', array('chairman_id'=>$id), 'stamp_end ASC');

$nextevent = 0;
foreach($events as $event) {

    //needed to calculate the proper timestamp values for thebackground color
    $eventstart = $event->day.'-'.$event->month.'-'.$event->year.' '.$event->starthour.':'.$event->startminutes;
    $eventtimestamp = strtotime($eventstart);

    //Find out if event is the next one in line. If so change background color
    if (($eventtimestamp >= $now) && ($nextevent==0)) {
        $eventstyle= 'style=background-color:#FFFFC7';
        $nextevent = 1;
    } else {
        $eventstyle='';
    }
    //if($event->stamp_end>$current_time){
    //display event
    echo '<div class="file" '.$eventstyle.'>';
    echo '<table><tr><td>';
    echo '<a href="'.$CFG->wwwroot.'/mod/chairman/export_event.php?event_id='.$event->id.'"><img id="icon" src="'.$CFG->wwwroot.'/mod/chairman/pix/cal.png"></a>';
    echo '</td>';
    echo '<td style="padding:6px;">';
    echo '<b>'.get_string('summary', 'chairman').' : </b>';
    echo $event->summary;
    if(chairman_isadmin($id)) {
        echo ' - <a href="'.$CFG->wwwroot.'/mod/chairman/delete_event_script.php?id='.$id.'&event_id='.$event->id.'" onClick="return confirm(\''.get_string('deleteeventquestion', 'chairman').'\');"><img src="'.$CFG->wwwroot.'/mod/chairman/pix/delete.gif"></a>';
        echo '<a href="edit_event.php?id='.$id.'&event_id='.$event->id.'"><img src="'.$CFG->wwwroot.'/mod/chairman/pix/edit.gif"></a>';
    }
    
	
    //Timezone adjustments
    //convert string into timestamp
    $event_start_str = chairman_convert_strdate_time($event->year, $event->day, $event->month, $event->starthour, $event->startminutes);
    $event_end_str = chairman_convert_strdate_time($event->year, $event->day, $event->month, $event->endhour, $event->endminutes);
  	//echo "Event year = $event->year-$event->day-$event->month event start str = $event_start_str";
	
	
    $user_timezone = $USER->timezone;
    if ($user_timezone == '99'){
        $region_tz = $CFG->timezone;
    } else {
        $region_tz = $USER->timezone;
    }
     
    $offset = chairman_get_timezone_offset($event->timezone,$region_tz);
	
    //Calculate offset
    $local_user_starttime = $event_start_str + $offset;
    $local_user_endtime = $event_end_str + $offset;
    //Convert timestamp back to string
    $event->day = date('d',$local_user_starttime);
    $event->month = date('m',$local_user_starttime);
    $event->year = date('Y',$local_user_starttime);
    $event->starthour = date('H',$local_user_starttime);
    $event->startminutes = date('i',$local_user_starttime);
    $event->endhour = date('H',$local_user_endtime);
    $event->endminutes = date('i',$local_user_endtime);
    
    echo '<br/>';
    echo '<b>'.get_string('date', 'chairman').' : </b>';
    echo $event->day.' ';
    if($event->month == 1) {
        echo get_string('january', 'chairman');
    }
    if($event->month == 2) {
        echo get_string('february', 'chairman');
    }
    if($event->month == 3) {
        echo get_string('march', 'chairman');
    }
    if($event->month == 4) {
        echo get_string('april', 'chairman');
    }
    if($event->month == 5) {
        echo get_string('may', 'chairman');
    }
    if($event->month == 6) {
        echo get_string('june', 'chairman');
    }
    if($event->month == 7) {
        echo get_string('july', 'chairman');
    }
    if($event->month == 8) {
        echo get_string('august', 'chairman');
    }
    if($event->month == 9) {
        echo get_string('september', 'chairman');
    }
    if($event->month == 10) {
        echo get_string('october', 'chairman');
    }
    if($event->month == 11) {
        echo get_string('november', 'chairman');
    }
    if($event->month == 12) {
        echo get_string('december', 'chairman');
    }
    echo ', '.$event->year.'<br/>';

    echo '<b>'.get_string('starttime', 'chairman').' : </b>';
    echo $event->starthour.':';
    /*
    if($event->startminutes <10) {
        echo '0'.$event->startminutes.'<br/>';
    }
    else {
        echo $event->startminutes.'<br/>';
    }
    */
    echo $event->startminutes.'<br/>';
    
    echo '<b>'.get_string('endtime', 'chairman').' : </b>';

    echo $event->endhour.':';
    /*
    if($event->endminutes <10) {
        echo '0'.$event->endminutes.'<br/>';
    }
    else {
        echo $event->endminutes.'<br/>';
    }
     * *
     */
     echo $event->endminutes.'<br/>';

    echo '<b>'.get_string('description', 'chairman').' : </b>';
    echo $event->description.'<br/>';

    echo '<br/>';
    echo '<a href="'.$CFG->wwwroot.'/mod/chairman/add_event_moodle_cal.php?event_id='.$event->id.'">'.get_string('addtomoodlecalendar','chairman').'</a></br>';
    echo '<a href="'.$CFG->wwwroot.'/mod/chairman/export_event.php?event_id='.$event->id.'">'.get_string('addtocalendar','chairman').'</a></br>';
	echo '<a href="'.$CFG->wwwroot.'/mod/chairman/meetingagenda/view.php?event_id='.$event->id.'">'.get_string('meeting_agenda','chairman').'</a>';
    echo '</td></tr></table>';
    echo '</div>';
    //}
}

echo '</span></div>';

if(chairman_isadmin($id)) {
    echo '<div class="add">';
    echo '<a href="'.$CFG->wwwroot.'/mod/chairman/add_event.php?id='.$id.'"><img src="'.$CFG->wwwroot.'/mod/chairman/pix/switch_plus.gif'.'">'.get_string('addevent', 'chairman').'</a>';
    echo '</div>';
}

chairman_footer();

?>
