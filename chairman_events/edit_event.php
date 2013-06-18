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
 * @copyright 2011 Raymond Wainman, Patrick Thibaudeau, Dustin Durand (Campus St. Jean, University of Alberta)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../../config.php');
require_once('../lib.php');
require_once('../chairman_meetingagenda/lib.php');
require_once('../lib_chairman.php');

require_once("$CFG->dirroot/mod/chairman/chairman_meetingagenda/util/ajax_lib.php");

$PAGE->requires->css('/mod/chairman/chairman_events/css/event_style.css');
$PAGE->requires->js('/mod/chairman/chairman_events/js/events.js');

$id = required_param('id',PARAM_INT);    // Course Module ID
$event_id = required_param('event_id', PARAM_INT);



chairman_check($id);
chairman_header($id,'editevent','edit_event.php?id='.$id.'&event_id='.$event_id);

$event = $DB->get_record('chairman_events', array('id'=>$event_id));

if(chairman_isadmin($id)) {

    echo '<div><div class="title">'.get_string('editevent', 'chairman').'</div>';

    echo '<form action="'.$CFG->wwwroot.'/mod/chairman/chairman_events/edit_event_script.php" method="POST" name="newevent">';
    echo '<table width=100% border=0>';
    
    echo '<tr>';
    echo '<td valign="top">'.get_string('timezone_used','mod_chairman').'</td>';
    //Timezone
    require_once($CFG->dirroot.'/calendar/lib.php');
    $timezones = get_list_of_timezones();
    
    $current = $event->timezone;
    
    echo '<td>'.html_writer::select($timezones, "timezone", $current, array('99'=>get_string("serverlocaltime"))).'</td>';
    echo '</tr>';

    echo '<tr><td>'.get_string('date', 'chairman').' : </td>';
    echo '<td width=85%>';
   
      //inline date picker
    echo "<div class='date_time_picker' id='add_date_time_picker'/>";
    
    //hidden date picker elements for submission
    echo "<input type='hidden' name='day' id='date_time_day' value='$event->day'/>";
    echo "<input type='hidden' name='month' id='date_time_month' value='$event->month'/>";
    echo "<input type='hidden' name='year' id='date_time_year' value='$event->year'/>";

    echo '</td></tr>';

    //Start time

    echo '<tr><td>'.get_string('starttime', 'chairman').' : </td>';
    echo '<td><select name="starthour">';
    for($i=0;$i<24;$i++) {
        if ($event->starthour == $i) {
            if($i<10) {

                echo '<option value="0'.$i.'" selected="selected">0'.$i.'</option>';
            }
            else {
                echo '<option value="'.$i.'" selected="selected">'.$i.'</option>';
            }
        } else {
            if($i<10) {

                echo '<option value="0'.$i.'" >0'.$i.'</option>';
            }
            else {
                echo '<option value="'.$i.'" >'.$i.'</option>';
            }
        }
    }
    echo '</select> : ';

    echo '<select name="startminutes">';
    for($i=0;$i<60;$i++) {
        if ($event->startminutes == $i) {
            if($i<10) {
                echo '<option value="0'.$i.'" selected="selected">0'.$i.'</option>';
            }
            else {
                echo '<option value="'.$i.'" selected="selected">'.$i.'</option>';
            }
        } else {
            if($i<10) {
                echo '<option value="0'.$i.'">0'.$i.'</option>';
            }
            else {
                echo '<option value="'.$i.'">'.$i.'</option>';
            }
        }
    }
    echo '</select>';

    //End time

    echo '<tr><td>'.get_string('endtime', 'chairman').' : </td>';
    echo '<td><select name="endhour">';
    for($i=0;$i<24;$i++) {
        if ($event->endhour == $i) {
            if($i<10) {
                echo '<option value="0'.$i.'" selected="selected">0'.$i.'</option>';
            }
            else {
                echo '<option value="'.$i.'" selected="selected">'.$i.'</option>';
            }
        } else {
            if($i<10) {
                echo '<option value="0'.$i.'">0'.$i.'</option>';
            }
            else {
                echo '<option value="'.$i.'">'.$i.'</option>';
            }
        }
    }
    echo '</select> : ';

    echo '<select name="endminutes">';
    for($i=0;$i<60;$i++) {
        if ($event->endminutes == $i) {
            if($i<10) {
                echo '<option value="0'.$i.'" selected="selected">0'.$i.'</option>';
            }
            else {
                echo '<option value="'.$i.'" selected="selected">'.$i.'</option>';
            }
        } else {
            if($i<10) {
                echo '<option value="0'.$i.'">0'.$i.'</option>';
            }
            else {
                echo '<option value="'.$i.'">'.$i.'</option>';
            }
        }
    }
    echo '</select>';
    echo '</td></tr>';

    
    //Summary
    echo '<tr><td>'.get_string('summary', 'chairman').' : </td>';
    echo '<td><input type="text" name="summary" style="width:500px;" value="'.$event->summary.'"></td>';

    //Detailed description
    echo '<tr><td>'.get_string('description', 'chairman').' : </td>';
    echo '<td><textarea rows="4" cols="70" name="description">';
    echo $event->description;
    echo '</textarea></td></tr>';
    echo '<input type="hidden" name="id" value="'.$event->id.'">';
    echo '<input type="hidden" name="chairman_id" value="'.$id.'">';
    
    //Notifications
    echo '<tr><td valign="top" colspan="2">'.get_string('sendnotification', 'chairman').' : ';
    //if notify is one then tis should be checked
    if ($event->notify == 1) {
        $checked = 'checked';
    } else {
        $checked = '';
    }
    //Same for week
    if ($event->notify_week == 1) {
        $checked_week = 'checked';
    } else {
        $checked_week = '';
    }
    echo '<input type="checkbox" name="notify" value="1" '.$checked.' >';
    echo '&nbsp;&nbsp;'.get_string('sendnotificationweek', 'chairman').'<input type="checkbox" name="notify_week" value="1" '.$checked_week.'>';
    echo '</textarea></td></tr>';
    //Buttons

    echo '<tr><td><br/></td><td></td></tr>';
    echo '<tr>';
    echo '<td></td><td><input type="submit" value="'.get_string('editevent', 'chairman').'">';
    echo '<input type="button" value="'.get_string('cancel', 'chairman').'" onClick="parent.location=\''.$CFG->wwwroot.'/mod/chairman/chairman_events/events.php?id='.$id.'\'"></td>';
    echo '</tr>';
    echo '</table>';
    echo '</form>';

    echo '<span class="content">';

    echo '</span></div>';



} elseif(chairman_isMember($id)) {//view only
    
    echo '<div><div class="title">'.get_string('event', 'chairman').'</div>';

    echo '<table width=100% border=0>';
    
    echo '<tr>';
    echo '<td valign="top">'.get_string('timezone_used','mod_chairman').'</td>';
    //Timezone
    require_once($CFG->dirroot.'/calendar/lib.php');
    $timezones = get_list_of_timezones();
    
    $current = $event->timezone;
    
    echo '<td>'.html_writer::select($timezones, "timezone", $current, array('99'=>get_string("serverlocaltime"))).'</td>';
    echo '</tr>';

    echo '<tr><td>'.get_string('date', 'chairman').' : </td>';
    echo '<td width=85%>';
   
      //inline date picker
    echo "<div class='date_time_picker' id='add_date_time_picker'/>";
    
    //hidden date picker elements for submission
    echo "<input type='hidden' name='day' id='date_time_day' value='$event->day'/>";
    echo "<input type='hidden' name='month' id='date_time_month' value='$event->month'/>";
    echo "<input type='hidden' name='year' id='date_time_year' value='$event->year'/>";

    echo '</td></tr>';

    //Start time

    echo '<tr><td>'.get_string('starttime', 'chairman').' : </td>';
    echo '<td>'.$event->starthour . ':'. $event->startminutes;

    //End time
    echo '<tr><td>'.get_string('endtime', 'chairman').' : </td>';
    echo '<td>' . $event->endhour . ':' . $event->endminutes;
            
    echo '</td></tr>';

    
    //Summary
    echo '<tr><td>'.get_string('summary', 'chairman').' : </td>';
    echo '<td><span name="summary" style="width:500px;">'.$event->summary.'</span></td>';

    //Detailed description
    echo '<tr><td>'.get_string('description', 'chairman').' : </td>';
    echo '<td><span name="description">';
    echo $event->description;
    echo '</span></td></tr>';
    
    
    echo '</table>';

    echo '<span class="content">';

    echo '</span></div>';
    
}

chairman_footer();

?>
