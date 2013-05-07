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
echo '<link rel="stylesheet" type="text/css" href="../style.php">';

require_once("$CFG->dirroot/mod/chairman/chairman_meetingagenda/ajax_lib.php");

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
    echo '<td width=85%><select name="day">';
    for($i=1; $i<=31; $i++) {
        if ($event->day == $i) {
            echo '<option value="'.$i.'" selected="selected">'.$i.'</option>';
        } else {
            echo '<option value="'.$i.'">'.$i.'</option>';
        }
    }
    echo '</select>';
    $selected = 'selected="selected"';

    echo '<select name="month">';
    echo '<option value="01" ';
    if ($event->month == 1) {
        echo $selected;
    }
    echo' >'.get_string('january', 'chairman').'</option>';
    echo '<option value="02" ';
    if ($event->month == 2) {
        echo $selected;
    }
    echo ' >'.get_string('february', 'chairman').'</option>';
    echo '<option value="03" ';
    if ($event->month == 3) {
        echo $selected;
    }
    echo ' >'.get_string('march', 'chairman').'</option>';
    echo '<option value="04" ';
    if ($event->month == 4) {
        echo $selected;
    }
    echo ' >'.get_string('april', 'chairman').'</option>';
    echo '<option value="05" ';
    if ($event->month == 5) {
        echo $selected;
    }
    echo ' >'.get_string('may', 'chairman').'</option>';
    echo '<option value="06" ';
    if ($event->month == 6) {
        echo $selected;
    }
    echo ' >'.get_string('june', 'chairman').'</option>';
    echo '<option value="07" ';
    if ($event->month == 7) {
        echo $selected;
    }
    echo ' >'.get_string('july', 'chairman').'</option>';
    echo '<option value="08" ';
    if ($event->month == 8) {
        echo $selected;
    }
    echo ' >'.get_string('august', 'chairman').'</option>';
    echo '<option value="09" ';
    if ($event->month == 9) {
        echo $selected;
    }
    echo ' >'.get_string('september', 'chairman').'</option>';
    echo '<option value="10" ';
    if ($event->month == 10) {
        echo $selected;
    }
    echo ' >'.get_string('october', 'chairman').'</option>';
    echo '<option value="11" ';
    if ($event->month == 11) {
        echo $selected;
    }
    echo ' >'.get_string('november', 'chairman').'</option>';
    echo '<option value="12" ';
    if ($event->month == 12) {
        echo $selected;
    }
    echo ' >'.get_string('december', 'chairman').'</option>';
    echo '</select>';

    echo '<select name="year">';
    $year = date('Y');
 echo '<option value="'.($year-5).'">'.($year-5).'</option>';
    echo '<option value="'.($year-4).'">'.($year-4).'</option>';
    echo '<option value="'.($year-3).'">'.($year-3).'</option>';
    echo '<option value="'.($year-2).'">'.($year-2).'</option>';
    echo '<option value="'.($year-1).'">'.($year-1).'</option>';
    echo '<option value="'.$year.'" selected="selected" >'.$year.'</option>';
    echo '<option value="'.($year+1).'">'.($year+1).'</option>';
    echo '<option value="'.($year+2).'">'.($year+2).'</option>';
    echo '<option value="'.($year+3).'">'.($year+3).'</option>';
    echo '<option value="'.($year+4).'">'.($year+4).'</option>';
    echo '<option value="'.($year+5).'">'.($year+5).'</option>';
    echo '</select>';

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



//----CHECK FOR SCHEDULER PLUGIN -----------------------------------------------
 $dbman = $DB->get_manager();
$table = new xmldb_table('roomscheduler_reservations');
$scheduler_plugin_installed = $dbman->table_exists($table);

$cm = get_coursemodule_from_id('chairman', $id);
$context = get_context_instance(CONTEXT_COURSE, $cm->course);

    
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



}

chairman_footer();

?>
