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
$PAGE->requires->css('/mod/chairman/chairman_planner/css/planner.css');
$PAGE->requires->css('/mod/chairman/jquery/plugins/multiselect/css/jquery.uix.multiselect.css');

// print header
chairman_check($id);
chairman_header($id,'newplanner','newplanner.php?id='.$id);

$PAGE->requires->js('/mod/chairman/chairman_planner/script/planner.js');
$PAGE->requires->js('/mod/chairman/jquery/plugins/multiselect/js/jquery.uix.multiselect.js');
$PAGE->requires->js('/mod/chairman/jquery/plugins/multiselect/js/locales/jquery.uix.multiselect_en.js');
$PAGE->requires->js('/mod/chairman/chairman_planner/script/planner_calendar.js');

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

//member required/optional multiselect
echo '<div class="ui-helper-clearfix">';
get_member_list($id, $planner, 'list_members_required', 'list_members_required', 1);
echo '</div>';

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
echo '<td valign="top">';
echo '<div class="date_selection_container">';
echo '<input type="hidden" name="dates[]" id="dates">';
echo '<div style="display: table">';
echo '<div style="width=180px;display: table-cell;">';
echo '<b><div class="date_selection_label">'.get_string('day','chairman').'</div></b>';
echo '<div id="datepicker"></div>';
echo '</div>';


echo '<div style="padding-left:10px;width=80px;display: table-cell;">';
echo '<br/><b>'.get_string('from','chairman').'</b><br/>';
echo render_timepicker('from',time()).'<br/>';
echo '<br/><b>'.get_string('to','chairman').'</b><br/>';
echo render_timepicker('to',(time()+(60*60)));
echo '</div>';

echo '</div>';
echo '</div>';
echo '</td>';
echo '<td><button type="button" onclick="planner_add_date();"><img src="../pix/right.gif"></button><br/><button type="button" onclick="planner_remove_date();"><img src="../pix/garbage.gif"></button></td>';
echo '<td><div id="listerror" style="font-size:10px;color:red;"></div>';
echo '<div class="date_selection_container">';    
echo '<ol id="selected_dates_list" class="selected_dates_list">';
if($planner != 0){
    $dates_obj = $DB->get_records('chairman_planner_dates',array('planner_id'=>$planner));
    foreach($dates_obj as $date_obj){
        $value = date('d/n/Y@H:i@',$date_obj->from_time).date('H:i',$date_obj->to_time);
        $text = date('M j, Y H:i - ',$date_obj->from_time).date('H:i',$date_obj->to_time);
        echo '<li datevalue="'.$value.'">'.$text.'</li>';
    }
}
echo '</ol>';
echo '</div>';    
echo '</td>';
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

/**
 * A function to setup of the select html table for selecting which members
 * are required and which are optional.
 * 
 * 
 * @global type $DB
 * @param type $id
 * @param type $planner
 * @param type $elementid
 * @param type $class
 * @param type $rule_type
 */
function get_member_list($id, $planner, $elementid, $class, $rule_type) {
    global $DB;
    $members = $DB->get_records('chairman_members', array('chairman_id' => $id));

    //select tag start
    echo "<select name= '$elementid' id='$elementid' class='$class multiselect' multiple='multiple'>";
    
    //add each member to select
    foreach ($members as $member) {
        if ($planner != 0) {
            $member_obj = $DB->get_record('chairman_planner_users', array('planner_id' => $planner, 'chairman_member_id' => $member->id));
        }
 
        //get user's name from moodle user table
        $userobj = $DB->get_record('user', array('id' => $member->user_id));
        
        //mark if selected
        $selected = "";
        if (isset($member_obj->rule) && $member_obj->rule == $rule_type) {
            $selected = "selected='selected'";
        }
        
        //output member as option
        echo "<option value='$member->id' $selected >" . $userobj->firstname . ' ' . $userobj->lastname . '</option>';
        
    }
    
    echo "</select>";
}
//Add to logs
add_to_log($COURSE->id, 'chairman', 'view','',get_string('newplanner', 'chairman'),$id);
?>
