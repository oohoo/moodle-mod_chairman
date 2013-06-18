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

$id = optional_param('id',0,PARAM_INT);    // Planner ID
$planner = $DB->get_record('chairman_planner', array('id'=>$id));

// print header
chairman_check($planner->chairman_id);
chairman_header($planner->chairman_id,'viewplanner','viewplanner.php?id='.$id);

//content
echo '<form method="POST" action="responses.php">';
echo '<input type="hidden" name="planner" value="'.$id.'">';
echo get_string('user_timezone','mod_chairman')."<br>";
echo '<table class="generaltable" style="margin-left:0;">';
echo '<tr>';
echo '<th class="header"></th>';
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
    $date->from_to = $local_user_time_to;
    
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
    echo '<td class="cell" style="'.$style.'">'.$userobj->firstname.' '.$userobj->lastname.'</th>';
    for($i=0;$i<$count;$i++) {
        echo '<td class="cell" style="text-align:center;"><input type="checkbox" ';
        if($USER->id != $userobj->id || $planner->active==0) {
            echo 'DISABLED ';
        }
        else {
            echo 'name="responses['.$date_col[$i].']" ';
        }
        if($DB->get_record('chairman_planner_response', array('planner_user_id'=>$member->id, 'planner_date_id'=>$date_col[$i]))){
            echo 'CHECKED';
            $date_col_count[$i]++;
        }
        else if($member->rule==1){
            $date_flag[$i] = true;
        }
        echo '></td>';
    }
    echo '</tr>';
}

echo '<tr>';
echo '<td></td>';
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
    echo '<td class="cell" style="font-size:10px;height:20px;background-color:'.$background.';">'.$percentage.'%</td>';
}
echo '</tr>';
echo '</table>';

if($planner->active != 0){
    echo '<input type="submit" value="'.get_string('save','chairman').'">';
}
echo '<input type="button" value="'.get_string('back','chairman').'" onclick="window.location.href=\''.$CFG->wwwroot.'/mod/chairman/chairman_planner/planner.php?id='.$planner->chairman_id.'\';">';
echo '</form>';

//footer
chairman_footer();

?>
