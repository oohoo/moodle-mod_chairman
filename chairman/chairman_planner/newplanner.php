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
echo '<link rel="stylesheet" type="text/css" href="../style.php">';

$id = required_param('id',PARAM_INT);    // Course Module ID
$planner = optional_param('planner',0,PARAM_INT);

if($planner != 0){
    $plannerobj = $DB->get_record('chairman_planner', array('id'=>$planner));
}

?>
<script type="text/javascript">
moodleMsgs = {

<?php
	echo 'planner_empty_name:"'.get_string('planner_empty_name','chairman').'",';
	echo 'planner_empty_dates:"'.get_string('planner_empty_dates','chairman').'"';
?>
};
</script>

<?php
//get dependencies
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->js('/mod/chairman/chairman_planner/planner.js');
$PAGE->requires->js('/mod/chairman/chairman_planner/planner_calendar.js');


// print header
chairman_check($id);
chairman_header($id,'newplanner','newplanner.php?id='.$id);


//content
echo '<div class="title">'.get_string('newplanner','chairman').'</div>';

echo '<form action="'.$CFG->wwwroot.'/mod/chairman/chairman_planner/new_planner_script.php?id='.$id.'" method="POST" id="newplanner" name="newplanner">';
echo '<input type="hidden" name="id" value="'.$id.'">';
if($planner != 0){
    echo '<input type="hidden" name="edit" value="'.$planner.'">';
}
echo '<table width=100% border=0>';
echo '<tr>';
echo '<td>'.get_string('name').':</td>';
echo '<td><input type="text" name="name" id="name" value="';
if(isset($plannerobj->name)){
    echo $plannerobj->name;
}
echo '" size="60"><br/><span id="nameerror" style="font-size:10px;color:red;"></span></td>';
echo '</tr>';
echo '<tr>';
echo '<td valign="top">'.get_string('details','chairman').':</td>';
echo '<td><textarea name="description" cols="50" rows="3">';
if(isset($plannerobj->description)){
    echo $plannerobj->description;
}
echo '</textarea></td>';
echo '</tr>';
echo '<tr>';
echo '<td valign="top">'.get_string('members','chairman').':</td>';
echo '<td>';
$members = $DB->get_records('chairman_members', array('chairman_id'=>$id));
echo '<table cellspacing="0" cellpadding="0" border="1">';
foreach($members as $member){
    if($planner != 0){
        $member_obj = $DB->get_record('chairman_planner_users',array('planner_id'=>$planner,'chairman_member_id'=>$member->id));
    }
    echo '<tr>';
    $userobj = $DB->get_record('user', array('id'=>$member->user_id));
    echo '<td>'.$userobj->firstname.' '.$userobj->lastname.'</td>';
    echo '<td><select name="rule['.$member->id.']">';
    echo '<option value="0" ';
    if(isset($member_obj->rule) && $member_obj->rule==0){ echo 'SELECTED'; }
    echo '>'.get_string('optional','chairman').'</option>';
    echo '<option value="1" ';
    if(isset($member_obj->rule) && $member_obj->rule==1){ echo 'SELECTED'; }
    echo '>'.get_string('required','chairman').'</option>';
    echo '</select></td>';
    echo '</tr>';
}
echo '</table>';
echo '</td>';
echo '<tr>';
echo '<td valign="top">'.get_string('timezone_used','mod_chairman').'</td>';
//Timezone
require_once($CFG->dirroot.'/calendar/lib.php');
$timezones = get_list_of_timezones();
//get user timezone


$current = 99;
if(isset($plannerobj)){
	if ($plannerobj->timezone == '99'){
		$current = $USER->timezone;
		if ($current == '99') {
			$current = $CFG->timezone;
		}
	} else {
		$current = $plannerobj->timezone;
	}
}
echo '<td>'.html_writer::select($timezones, "timezone", $current, array('99'=>get_string("serverlocaltime"))).'</td>';
echo '</tr>';
echo '<tr>';
echo '<td valign="top">'.get_string('dates','chairman').':</td>';
echo '<td>';
echo '<table><tr>';
echo '<td style="border:1px solid black;" valign="top">';
echo '<input type="hidden" name="dates[]" id="dates">';
echo '<div style="display: table">';
echo '<div style="width=180px;display: table-cell;">';
echo '<b>'.get_string('day','chairman').'</b><br/>';
echo '<div id="datepicker"></div>';
echo '</div>';


echo '<div style="padding-left:10px;width=80px;display: table-cell;">';
echo '<br/><b>'.get_string('from','chairman').'</b><br/>';
echo render_timepicker('from',time()).'<br/>';
echo '<br/><b>'.get_string('to','chairman').'</b><br/>';
echo render_timepicker('to',(time()+(60*60)));
echo '</div>';

echo '</div>';

echo '</td>';
echo '<td><button type="button" onclick="planner_add_date();"><img src="../pix/right.gif"></button><br/><button type="button" onclick="planner_remove_date();"><img src="../pix/garbage.gif"></button></td>';
echo '<td style="border:1px solid black;"><div id="listerror" style="font-size:10px;color:red;"></div><ol id="list" style="overflow-x: hidden;overflow: scroll;width:300px;height:150px;">';
if($planner != 0){
    $dates_obj = $DB->get_records('chairman_planner_dates',array('planner_id'=>$planner));
    foreach($dates_obj as $date_obj){
        $value = date('d/n/Y@H:i@',$date_obj->from_time).date('H:i',$date_obj->to_time);
        $text = date('M j, Y H:i - ',$date_obj->from_time).date('H:i',$date_obj->to_time);
        echo '<li value="'.$value.'">'.$text.'</li>';
    }
}
echo '</ol></td>';
echo '</tr></table>';
echo '</td>';
echo '</tr>';
//Notifications
    echo '<tr><td valign="top" colspan="2">'.get_string('sendnotificationnow', 'chairman').' : ';
    echo '<input type="checkbox" name="notify_now" value="1">';
    echo '</td></tr>';
echo '<tr>';
echo '<td></td>';
echo '<td><input type="button" value="'.get_string('submit','chairman').'" onclick="planner_submit()"/>';
echo '<input type="button" value="'.get_string('back','chairman').'" onclick="window.location.href=\''.$CFG->wwwroot.'/mod/chairman/chairman_planner/planner.php?id='.$id.'\';" />';
echo '</td></tr>';
echo '</table></form>';

//footer
chairman_footer();

?>
