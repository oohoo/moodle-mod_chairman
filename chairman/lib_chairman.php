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

/**
 * Gets Firstname Lastname and email of chairman members.
 *
 * @global object
 * @param id The chairman id
 * @return array of users
 */
function get_chairman_members($id){
    global $DB;
    $membersql = "SELECT {user}.id, {user}.firstname, {user}.lastname, {user}.email
                  FROM {user} INNER JOIN {chairman_members} ON {user}.id = {chairman_members}.user_id
                  WHERE {chairman_members}.chairman_id = $id";
    
    
    $members = $DB->get_records_sql($membersql);

    return $members;

}

function print_content_header($style) {
    $styles = 'style="'.$style.'"';
    echo '<div class="cornerBox" '.$styles.'>'."\n";
    echo '<div class="corner TL"></div>'."\n";
    echo '<div class="corner TR"></div>'."\n";
    echo '<div class="corner BL"></div>'."\n";
    echo '<div class="corner BR"></div>'."\n";
    echo '	<div class="cornerBoxInner">'."\n";

    return;
}

function print_content_footer() {
    echo ' </div>'."\n"; //cornerbox inner 1
    echo '</div>'."\n"; //cornerbox

    return;
}

function print_inner_content_header($style) {

    $styles = 'style="'.$style.'"';
    echo '<div class="lightcornerBox" '.$styles.'">'."\n";
    echo '<div class="lightcorner TL"></div>'."\n";
    echo '<div class="lightcorner TR"></div>'."\n";
    echo '<div class="lightcorner BL"></div>'."\n";
    echo '<div class="lightcorner BR"></div>'."\n";
    echo '	<div class="cornerBoxInner">'."\n";

    return;
}

function print_inner_content_footer() {
    echo ' </div>'."\n"; //lightcornerbox inner
    echo '</div>'."\n"; //lightcornerbox

    return;
}

function chairman_printnavbar($id) {
    global $CFG, $DB;
    
    //Get chairman information
    $mod = $DB->get_record('course_modules',array('id' => $id));
    $chairman = $DB->get_record('chairman', array('id' => $mod->instance));
	echo '<div id="chairman_menu" style="font-size: 14px">';
    echo '<table width=100% border=0>';
    echo '<tr><td>';
    echo '<a href="'.$CFG->wwwroot.'/mod/chairman/view.php?id='.$id.'">'.get_string('members', 'chairman').'</a> ';
    echo '</td></tr>';
    echo '<tr><td>';
    echo '<a href="'.$CFG->wwwroot.'/mod/chairman/planner.php?id='.$id.'">'.get_string('planner', 'chairman').'</a>';
    echo '</td></tr>';
    echo '<tr><td>';
    echo '<a href="'.$CFG->wwwroot.'/mod/chairman/events.php?id='.$id.'">'.get_string('events', 'chairman').'</a>';
    echo '</td></tr>';
    echo '<tr><td>';
    echo '<a href="'.$CFG->wwwroot.'/mod/chairman/meetingagenda/viewer.php?chairman_id='.$id.'">'.get_string('agendas', 'chairman').'</a>';
    echo '</td></tr>';
    echo '<tr><td>';
    echo '<a href="'.$CFG->wwwroot.'/mod/chairman/files.php?id='.$id.'">'.get_string('files', 'chairman').'</a>';
    echo '</td></tr>';
    if ($chairman->use_forum == 1) {
        echo '<tr><td>';
        echo '<a href="'.$CFG->wwwroot.'/mod/forum/view.php?id='.$chairman->forum.'" target="_blank">'.get_string('menu_forum', 'chairman').'</a>';
        echo '</td></tr>'; 
    }
    if ($chairman->use_wiki == 1) {
        echo '<tr><td>';
        echo '<a href="'.$CFG->wwwroot.'/mod/wiki/view.php?id='.$chairman->wiki.'" target="_blank">'.get_string('menu_wiki', 'chairman').'</a>';
        echo '</td></tr>'; 
    }
    if (isset($chairman->use_questionnaire) && $chairman->use_questionnaire == 1) {
        echo '<tr><td>';
        echo '<a href="'.$CFG->wwwroot.'/mod/questionnaire/view.php?id='.$chairman->questionnaire.'" target="_blank">'.get_string('menu_questionnaire', 'chairman').'</a>';
        echo '</td></tr>'; 
    }
    echo '</table>';
	echo '</div>';
}

function chairman_header($chairman,$pagename,$pagelink) {
    global $PAGE,$OUTPUT,$CFG,$DB,$style_nav,$style_content;

    $course_mod = $DB->get_record('course_modules', array('id'=>$chairman));
    $chairman_instance = $DB->get_record('chairman', array('id'=>$course_mod->instance));

    $chairman_name = $chairman_instance->name;

    $context = get_context_instance(CONTEXT_MODULE, $chairman);
    //print_r($context);

    //Print header
    $page = get_string($pagename, 'chairman');
    $title = $chairman_name . ': ' . $page;

    $navlinks = array(
            array('name' => get_string($pagename,'chairman'), 'link' => $CFG->wwwroot.'/mod/chairman/'.$pagelink, 'type' => 'misc')
    );
    $navigation = build_navigation($navlinks);

    //$PAGE->set_context();
    $PAGE->set_url('/mod/chairman/'.$pagelink);
    $PAGE->set_title($chairman_name);
    $PAGE->set_heading($title);
    $PAGE->set_context($context);
    echo $OUTPUT->header();

//---------------CONTENT---------------//

    echo '<link rel="stylesheet" type="text/css" href="style.php">';

    
    echo "<h2>$chairman_name</h2>";
    print_content_header('');
       
    print_inner_content_header($style_nav);

    chairman_printnavbar($chairman);

    print_inner_content_footer();

    print_inner_content_header($style_content);
}

function chairman_check($id) {
    global $USER,$DB, $CFG, $SESSION;

    // checks
    if ($id) {
        if (! $cm = get_coursemodule_from_id('chairman', $id)) {
            print_error("Course Module ID was incorrect");
        }

        if (! $course = $DB->get_record("course", array("id"=>$cm->course))) {
            print_error("Course is misconfigured");
        }

        if (! $chairman = $DB->get_record("chairman", array("id"=>$cm->instance))) {
            print_error("Course module is incorrect");
        }

    } else {
        if (! $chairman = $DB->get_record("chairman", array("id"=>$l))) {
            print_error("Course module is incorrect");
        }
        if (! $course = $DB->get_record("course", array("id"=>$chairman->course))) {
            print_error("Course is misconfigured");
        }
        if (! $cm = get_coursemodule_from_instance("chairman", $chairman->id, $course->id)) {
            print_error("Course Module ID was incorrect");
        }
    }
    
    require_course_login($course, true, $cm);
    
    //context needed for access rights
    $context = get_context_instance(CONTEXT_USER, $USER->id);

    global $cm;
    //Set session logo
	if (!empty($chairman->logo)){
		$SESSION->chairman_logo = "$CFG->dirroot/mod/chairman/img/logos/$chairman->logo";
		
	} else {
		$SESSION->chairman_logo = "$CFG->dirroot/mod/chairman/img/blank.jpg";
	}
    //If not a member, get out
    if ($chairman->secured == 1){
        if ((!chairman_isMember($id)) AND (!chairman_isadmin($id))) {
            redirect($CFG->wwwroot.'/course/view.php?id='.$course->id, get_string('not_member', 'mod_chairman'), 10);
        }
    }
}

function chairman_isadmin($id) {
    global $DB,$USER, $PAGE;
    $instance = $PAGE->course->id;
    $context = get_context_instance(CONTEXT_COURSE, $instance);
    if($DB->get_record('chairman_members', array('chairman_id'=>$id,'role_id'=>2,'user_id'=>$USER->id))
            || $DB->get_record('chairman_members', array('chairman_id'=>$id, 'role_id'=>1, 'user_id'=>$USER->id))
            || $DB->get_record('chairman_members', array('chairman_id'=>$id, 'role_id'=>4, 'user_id'=>$USER->id))
            || has_capability('moodle/course:update', $context)
            || is_siteadmin()) {
        return true;
    }
    else {
        return false;
    }
}

/*
 * Given an chairman id check to determine if the user is a member of the committee.
 * Does not check for just committee role of member, but any role of member, president, Vpresident, and admin.
 *
 */
function chairman_isMember($id) {
    global $DB,$USER;

    if($DB->get_record('chairman_members', array('chairman_id'=>$id,'role_id'=>2,'user_id'=>$USER->id))
            || $DB->get_record('chairman_members', array('chairman_id'=>$id, 'role_id'=>1, 'user_id'=>$USER->id))
            || $DB->get_record('chairman_members', array('chairman_id'=>$id, 'role_id'=>3, 'user_id'=>$USER->id))
            || $DB->get_record('chairman_members', array('chairman_id'=>$id, 'role_id'=>4, 'user_id'=>$USER->id))) {
            
        return true;
    
            } else {
        return false;
    }
}

function chairman_footer() {
    global $OUTPUT;
    print_inner_content_footer();

    echo '<div style="clear:both;"></div>';

    print_content_footer();
    
    echo $OUTPUT->footer();
}

function breadcrumb($folderid) {
    global $DB,$id,$CFG;

    $folderobj = $DB->get_record('chairman_files',array('id'=>$folderid));

    if($folderid==0) {
        $obj = new stdClass();
        $obj->name = get_string('root','chairman');
        $obj->url = $CFG->wwwroot.'/mod/chairman/files.php?id='.$id.'&file=0';
        $obj->private = 0;
        //Make array (easier to add objects after)
        $returned[] = $obj;
        return $returned;
    }
    else {
        $obj = new stdClass();
        $obj->name = $folderobj->name;
        $obj->url = $CFG->wwwroot.'/mod/chairman/files.php?id='.$id.'&file='.$folderobj->id;
        $obj->private = $folderobj->private;

        $returned = breadcrumb($folderobj->parent);
        //Add new object to array
        array_push($returned,$obj);

        return $returned;
    }
}

/**
 * Returns markup for dropdown for time selection.
 *
 * @param string $name name and id to give our form element
 * @param int $time timestamp to match pre-selected value
 *
 * return string with HTML markup ready for output
 */
function render_timepicker($name,$time) {
    $hourtime = date('G',$time);
    $minutetime = date('i',$time);
    if($minutetime<10){
        $minutetime = 10;
    }
    else if($minutetime>=50){
        $hourtime += 1;
        $minutetime = 0;
    }
    else{
        $minutetime = ceil($minutetime/10)*10;
    }

    $string = '<select name="'.$name.'_time" id="'.$name.'_time">';
    for($hour=0;$hour<=23;$hour++){
        for($minutes=0;$minutes<60;$minutes+=10){
            if($minutes==0){
                $output = '00';
            }
            else{
                $output = $minutes;
            }
            $string .= '<option value="'.$hour.':'.$output.'" ';
            if($hourtime==$hour && $minutetime==$minutes){
                $string .= 'SELECTED';
            }
            $string .= '>'.$hour.':'.$output.'</option>';
        }
    }
    $string .= '</select>';

    return $string;
}

/*
 * Function to display the planner results as a table
 *
 * @param int $planner_id The database id for the planner.
 */
function display_planner_results($planner_id, $chairman_id){

    global $DB, $USER, $CFG;

    $planner = $DB->get_record('chairman_planner', array('id'=>$planner_id));
    
    

//content
echo '<table class="generaltable" style="margin-left:0;">';
echo '<tr>';
$dates = $DB->get_records('chairman_planner_dates', array('planner_id'=>$planner->id), 'to_time ASC');
$count = 0;
$date_col = array();        //Keep track of what id is which column
$date_col_count = array();  //Keep track of how many people can make this date
$date_flag = array();       //If a required person cannot make it, flag it here

foreach($dates as $date) {
    //Timezone adjustment
    $user_timezone = $USER->timezone;
    if ($user_timezone == '99'){
        $region_tz = $CFG->timezone;
    } else {
        $region_tz = $USER->timezone;
    }
    $offset = chairman_get_timezone_offset($planner->timezone,$region_tz);
    //Calculate offset
    $local_user_time_from = $date->from_time + $offset;
    $local_user_time_to = $date->to_time + $offset;
    
    $date->from_time = $local_user_time_from;
    $date->to_time = $local_user_time_to;
  
    echo '<th class="header">'.strftime('%a %d %B, %Y', $date->from_time).'<br/>';
    echo '<span style="font-size:10px;font-weight:normal;">'.date('H:i', $date->from_time).' - '.date('H:i', $date->to_time).'</span>';
    echo '</th>';
    $date_col[$count] = $date->id;
    $date_flag[$count] = false; //Initialise
    $date_col_count[$count] = 0;    //Initialise
    $count++;
}
echo '</tr>';

$members = $DB->get_records('chairman_planner_users', array('planner_id'=>$planner->id));
$numberofmembers = $DB->count_records('chairman_planner_users', array('planner_id'=>$planner->id));
foreach($members as $member) {
    echo '<tr>';
    $memberobj = $DB->get_record('chairman_members', array('id'=>$member->chairman_member_id));
    $userobj = $DB->get_record('user', array('id'=>$memberobj->user_id));
    if($member->rule==1) {
        $style = 'font-weight:bold;';
    }
    else {
        $style = '';
    }

    for($i=0;$i<$count;$i++) {

        if($DB->get_record('chairman_planner_response', array('planner_user_id'=>$member->id, 'planner_date_id'=>$date_col[$i]))){
            $date_col_count[$i]++;
        }
        else if($member->rule==1){
            $date_flag[$i] = true;
        }

    }
    echo '</tr>';
}

echo '<tr>';

for($i=0;$i<$count;$i++) {
    if($date_flag[$i]){
        $background = 'red';
        $percentage = '0';
    }
    else{
        $brilliance = ($date_col_count[$i])/($numberofmembers);
        $background = 'rgba(33,204,33,'.$brilliance.')';
        $percentage = number_format($brilliance*100,0);

    }
    echo '<td class="cell" style="font-size:10px;height:20px;background-color:'.$background.';">'.
    '<center><form method="post" action="planner_to_event_script.php">'.$percentage.'%';
    

    if(chairman_isadmin($chairman_id) && $percentage > 0){
    
     echo ' <input type="image" src="pix/accept.png" />';
     echo '<input type="hidden" name="date_id" value ="'.$date_col[$i].'"/>';
     echo '<input type="hidden" name="chairman_id" value ="'.$chairman_id.'"/>';
     echo '</form>';

    }

    echo '</center></td>';
}
echo '</tr>';



echo '</table>';

}

/**    Returns the offset from the origin timezone to the remote timezone, in seconds.
*    @param $remote_tz;
*    @param $origin_tz; If null the servers current timezone is used as the origin.
*    @return int;
*/
function chairman_get_timezone_offset($remote_tz, $origin_tz = null) {
    if($origin_tz === null) {
        if(!is_string($origin_tz = date_default_timezone_get())) {
            return false; // A UTC timestamp was returned -- bail out!
        }
    }
    $offset = 3600;
    $remote_timezonename = timezone_name_from_abbr("", $remote_tz*$offset, false);
    $origin_timezonename = timezone_name_from_abbr("", $origin_tz*$offset, false);
    
    if($remote_timezonename === false || $origin_timezonename===false) return false;
   
    $origin_dtz = new DateTimeZone($origin_timezonename);
    $remote_dtz = new DateTimeZone($remote_timezonename);
    $origin_dt = new DateTime("now", $origin_dtz);
    $remote_dt = new DateTime("now", $remote_dtz);
    $offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);
    return $offset;
}

function chairman_convert_strdate_time($year, $day, $month, $hour, $minute){
    if ($minute < 10) {
        $minute = '0'.$minute;
    }
    if ($hour < 10) {
        $hour = '0'.$hour;
    }
    if ($month < 10) {
        $month = '0'.$month;
    }
    if ($day < 10) {
        $day = '0'.$day;
    }
    $date_string = "$year-$month-$day"." "."$hour:$minute:00";
    $date = strtotime($date_string);
    return $date;
}

$style_nav = 'width: 29%;float: left;margin-right: 0%';
$style_content = 'width: 66%;float: right;margin-left: 0%';

?>
