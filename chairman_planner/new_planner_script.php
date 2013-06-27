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

global $CFG, $USER;

$id = required_param('id',PARAM_INT);    // Course Module ID
$cm = get_coursemodule_from_id('chairman', $id);//Course Module Object
$edit = optional_param('edit',0,PARAM_INT);   //If editing, this points to the planner id

$name = required_param('name',PARAM_TEXT);  // Planner name
$description = optional_param('description',null,PARAM_TEXT);    // Planner description
$user_rules = optional_param('rule',null,PARAM_RAW);     // Array of member id's + rule
$dates = optional_param('dates',null,PARAM_RAW);      // Array of all potential dates

$notify_now = optional_param('notify_now',0,PARAM_INT); 
$timezone = optional_param('timezone',99,PARAM_TEXT);

if ($timezone == '99'){
	$timezone = $CFG->timezone;
}

//First add entry to chairman_planner table
$planner = new stdClass;
$planner->name = $name;
$planner->description = $description;
$planner->timezone = $timezone;
if($edit != 0){
    $planner->id = $edit;
    $DB->update_record('chairman_planner',$planner);
    $planner_id = $edit;
    //Add to logs
    add_to_log($cm->course, 'chairman','update','',$name,$id);
}
else{
    $planner->active = 1;
    $planner->chairman_id = $id;
    $planner_id = $DB->insert_record('chairman_planner', $planner);
    //Add to logs
    add_to_log($cm->course, 'chairman','add','',$name,$id);
}


//Next add entries to chairman_planner_users table
foreach($user_rules as $member_id => $rule){
    //If updating
    if($currentuser = $DB->get_record('chairman_planner_users', array('planner_id'=>$planner_id,'chairman_member_id'=>$member_id))){
        $currentuser->rule = $rule;
        $DB->update_record('chairman_planner_users', $currentuser);
    }
    //New entry
    else{
        $planner_user = new stdClass;
        $planner_user->planner_id = $planner_id;
        $planner_user->chairman_member_id = $member_id;
        $planner_user->rule = $rule;

        $DB->insert_record('chairman_planner_users', $planner_user);
    }
}

//Finally add entries to chairman_planner_dates table
//If Editing, get all existing entries, remove them later (easier this way)
if($edit != 0){
    $current_dates = $DB->get_records('chairman_planner_dates', array('planner_id'=>$planner_id));
}

foreach($dates as $date){
	
    $date_arr = explode('@',$date);
    
    $date_raw = explode('/',$date_arr[0]);
    $day = $date_raw[0];
    $month = $date_raw[1];
    $year = $date_raw[2];

    $from_raw = $date_arr[1];
    $to_raw = $date_arr[2];

    $from_string = $year.'-'.$month.'-'.$day.' '.$from_raw;
    $to_string = $year.'-'.$month.'-'.$day.' '.$to_raw;

    $from_time = strtotime($from_string);
    $to_time = strtotime($to_string);

    $planner_date = new stdClass;
    $planner_date->planner_id = $planner_id;
    $planner_date->from_time = $from_time;
    $planner_date->to_time = $to_time;

    $newid = $DB->insert_record('chairman_planner_dates',$planner_date);
    //echo 'New Date ID: '.$newid.'<br/>';

    if($olddate = $DB->get_record_select('chairman_planner_dates', "planner_id='$planner_id' AND from_time='$from_time' AND to_time='$to_time' AND id<>'$newid'")){
        $responseobjects = $DB->get_records('chairman_planner_response',array('planner_date_id'=>$olddate->id));
        foreach($responseobjects as $response){
            $response->planner_date_id = $newid;
            //echo 'Response : ';
            //print_object($response);
            $DB->update_record('chairman_planner_response',$response);
        }
    }
}

//If editing, remove all old entries
if($edit != 0){
    foreach($current_dates as $current_date){
        $DB->delete_records('chairman_planner_dates', array('id'=>$current_date->id));
    }
}

//send email notification
if ($notify_now == 1){
    //email information
    $from = $DB->get_record('user', array('id' => $USER->id));
    $subject = get_string('plannernotificationsubject', 'chairman');
    $subject = str_replace('{c}', $planner->name, $subject);
        
        $emailmessage = get_string('plannernotificationmessage', 'chairman');
	$emailmessage = str_replace('{link}', "$CFG->wwwroot/mod/chairman/chairman_planner/planner.php?id=$id", $emailmessage);
	$emailmessage = str_replace('{c}', $planner->name, $emailmessage);
	$emailmessage = str_replace('{p}', "$from->firstname $from->lastname", $emailmessage);
    //get all member emails.
    $members = $DB->get_records('chairman_members', array('chairman_id' => $id));
                
    $i=0;
                
    foreach ($members as $member){
        $user = $DB->get_record('user',array('id' => $member->user_id));
        $message[$i] = str_replace('{a}', "$user->firstname $user->lastname", $emailmessage);
        email_to_user($user, $from, $subject, $message[$i]);
		$i++;
		
        
    }
	
}
echo '<script type="text/javascript">';
echo 'window.location.href="'.$CFG->wwwroot.'/mod/chairman/chairman_planner/planner.php?id='.$id.'";';
echo '</script>';

?>
