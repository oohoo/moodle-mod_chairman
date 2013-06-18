<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

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
 * @copyright 2011 Raymond Wainman, Dustin Durand, Patrick Thibaudeau (Campus St. Jean, University of Alberta)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../../config.php');
require_once('../lib.php');
require_once('../lib_chairman.php');

require_once("$CFG->dirroot/mod/chairman/chairman_meetingagenda/util/ajax_lib.php");

$id = required_param('id',PARAM_INT);    // Course Module ID

$PAGE->requires->css('/mod/chairman/chairman_events/css/event_style.css');
$PAGE->requires->js('/mod/chairman/chairman_events/js/events.js');

chairman_check($id);
chairman_header($id,'addevent','add_event.php?id='.$id);

if(chairman_isadmin($id)) {

    echo '<div><div class="title">'.get_string('addevent', 'chairman').'</div>';

    echo '<form action="'.$CFG->wwwroot.'/mod/chairman/chairman_events/add_event_script.php?id='.$id.'" method="POST" name="newevent">';
    echo '<table width=100% border=0>';
    echo '<tr>';
    echo '<td valign="top">'.get_string('timezone_used','mod_chairman').'</td>';
    //Timezone
    require_once($CFG->dirroot.'/calendar/lib.php');
    $timezones = get_list_of_timezones();
    
    //get user timezone
    if (!$USER->timezone == '99'){
        $current = $USER->timezone;
    } else {
        $current = $CFG->timezone;
    }
    echo '<td>'.html_writer::select($timezones, "timezone", $current, array('99'=>get_string("serverlocaltime"))).'</td>';
    echo '</tr>';
    
    $now = time();

    $day = date('d',$now);
    $month = date('m',$now);
    $year = date('Y',$now);
    
    echo '<tr><td>'.get_string('date', 'chairman').' : </td>';
    echo '<td width=85%>';

    //inline date picker
    echo "<div class='date_time_picker' id='add_date_time_picker'/>";
    
    //hidden date picker elements for submission
    echo "<input type='hidden' name='day' id='date_time_day' value='$day'/>";
    echo "<input type='hidden' name='month' id='date_time_month' value='$month'/>";
    echo "<input type='hidden' name='year' id='date_time_year' value='$year'/>";
    

    echo '</td></tr>';

    //Start time

    $hour = date('G',$now);

    echo '<tr><td>'.get_string('starttime', 'chairman').' : </td>';
    echo '<td><select name="starthour">';
    for($i=0;$i<24;$i++) {
        if($i<10) {
            echo '<option value="0'.$i.'" ';
            if($i==$hour){echo 'SELECTED';}
            echo '>0'.$i.'</option>';
        }
        else {
            echo '<option value="'.$i.'" ';
            if($i==$hour){echo 'SELECTED';}
            echo '>'.$i.'</option>';
        }
    }
    echo '</select> : ';

    $minute = date('i',$now);

    echo '<select name="startminutes">';
    for($i=0;$i<60;$i++) {
        if($i<10) {
            echo '<option value="0'.$i.'" ';
            if($i=='0'.$minute){echo 'SELECTED';}
            echo '>0'.$i.'</option>';
        }
        else {
            echo '<option value="'.$i.'" ';
            if($i==$minute){echo 'SELECTED';}
            echo '>'.$i.'</option>';
        }
    }
    echo '</select>';

    //End time

    echo '<tr><td>'.get_string('endtime', 'chairman').' : </td>';
    echo '<td><select name="endhour">';
    for($i=0;$i<24;$i++) {
        if($i<10) {
            echo '<option value="0'.$i.'" ';
            if($i==$hour){echo 'SELECTED';}
            echo '>0'.$i.'</option>';
        }
        else {
            echo '<option value="'.$i.'" ';
            if($i==$hour+1){echo 'SELECTED';}
            echo '>'.$i.'</option>';
        }
    }
    echo '</select> : ';

    echo '<select name="endminutes">';
    for($i=0;$i<60;$i++) {
        if($i<10) {
            echo '<option value="0'.$i.'" ';
            if($i=='0'.$minute){echo 'SELECTED';}
            echo '>0'.$i.'</option>';
        }
        else {
            echo '<option value="'.$i.'" ';
            if($i==$minute){echo 'SELECTED';}
            echo '>'.$i.'</option>';
        }
    }
    echo '</select>';
    echo '</td></tr>';


    

$cm = get_coursemodule_from_id('chairman', $id);
$context = get_context_instance(CONTEXT_COURSE, $cm->course);


    //Summary
    echo '<tr><td>'.get_string('summary', 'chairman').' : </td>';
    echo '<td><input type="text" name="summary" style="width:500px;"></td>';

    //Detailed description
    echo '<tr><td valign="top">'.get_string('description', 'chairman').' : </td>';
    echo '<td><textarea rows="4" cols="70" name="description">';
    echo '</textarea></td></tr>';
    
    //Notifications
    
    echo '<tr><td valign="top" colspan="2">'.get_string('sendnotificationweek', 'chairman').' : ';
    echo '<input type="checkbox" name="notify_week" value="1">';
    echo '   '.get_string('sendnotification', 'chairman').' : ';
    echo '<input type="checkbox" name="notify" value="1">';
    echo '</td></tr>';
    
    //Buttons

    echo '<tr><td><br/></td><td></td></tr>';
    echo '<tr>';
    echo '<td></td><td><input type="submit" value="'.get_string('addevent', 'chairman').'">';
    echo '<input type="button" value="'.get_string('cancel', 'chairman').'" onClick="parent.location=\''.$CFG->wwwroot.'/mod/chairman/chairman_events/events.php?id='.$id.'\'"></td>';
    echo '</tr>';
    echo '</table>';
    echo '</form>';

    echo '<span class="content">';

    echo '</span></div>';

$cm = get_coursemodule_from_id('chairman', $id);
$context = get_context_instance(CONTEXT_COURSE, $cm->course);

}

chairman_footer();

?>
