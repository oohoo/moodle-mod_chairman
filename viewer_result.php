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

require_once('../../config.php');
require_once('lib.php');
require_once('lib_chairman.php');

$id = optional_param('id',0,PARAM_INT);    // Planner ID
$planner = $DB->get_record('chairman_planner', array('id'=>$id));

//content
echo '<table class="generaltable" style="margin-left:0;">';
echo '<tr>';
echo '<th class="header"></th>';
$dates = $DB->get_records('chairman_planner_dates', array('planner_id'=>$planner->id), 'to_time ASC');
$count = 0;
$date_col = array();        //Keep track of what id is which column
$date_col_count = array();  //Keep track of how many people can make this date
$date_flag = array();       //If a required person cannot make it, flag it here
foreach($dates as $date) {
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


?>
