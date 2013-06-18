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

require_once('../../../config.php');

global $DB,$USER,$CFG;

$chairman_id = required_param('chairman_id',PARAM_INT);
$date_id = required_param('date_id',PARAM_INT);


//--------SECURITY--------------------------------------------------------------
//------------------------------------------------------------------------------
//Get Credentials for this user
if ($current_user_record = $DB->get_record("chairman_members", array("chairman_id"=>$chairman_id,"user_id"=>$USER->id))){
$user_role = $current_user_record->role_id;
}

//Simple role cypher for code clarity
$role_cypher = array('1' => 'president', '2' => 'vice', '3' => "member", "4" => 'admin');


if (isset($user_role) && ($user_role == '1' || $user_role == '2' || $user_role == '3' || $user_role == '4')) {
    $credentials = $role_cypher[$user_role];
} else {
    $credentials = "guest";
}

if(!($credentials == 'president') && !($credentials == 'vice') && !($credentials == 'admin')){
redirect("$CFG->wwwroot/mod/chairman/chairman_events/events.php?id=$chairman_id");
    exit();
}

if (!$credentials == 'president' && !$credentials == 'vice' && !$credentials == 'admin') {
redirect("$CFG->wwwroot/mod/chairman/chairman_events/events.php?id=$chairman_id");
}
//------------------------------------------------------------------------------
//------------------------------------------------------------------------------

$date = $DB->get_record('chairman_planner_dates', array('id'=>$date_id));
$cm = $DB->get_record('course_modules', array('id' => $chairman_id));
$planner = $DB->get_record('chairman_planner',array('id' => $date->planner_id));

//DAY
$day = date('d', $date->from_time);
$month = date('m', $date->from_time);
$year = date('Y', $date->from_time);

//START TIME
$starthour = date('H', $date->from_time);
$startminutes = date('i', $date->from_time);

//END TIME
$endhour = date('H', $date->to_time);
$endminutes = date('i', $date->to_time);

$summary = $planner->name;
$description = $planner->description;


$event = new stdClass();

if ($planner->timezone == 99){
	$planner->timezone = $CFG->timezone;
}

$new_event = new stdClass();
$new_event->user_id = $USER->id;
$new_event->chairman_id = $chairman_id;
$new_event->day = $day;//
$new_event->month = $month;
$new_event->year = $year;
$new_event->starthour = $starthour;
$new_event->startminutes = $startminutes;
$new_event->endhour = $endhour;
$new_event->endminutes = $endminutes;
$new_event->summary = $summary;
$new_event->description = $description;
$new_event->stamp_start = $year.$month.$day.$starthour.$startminutes.'00';
$new_event->stamp_end = $year.$month.$day.$endhour.$endminutes.'00';
$new_event->stamp_t_start = $year.$month.$day.'T'.$starthour.$startminutes.'00';
$new_event->stamp_t_end = $year.$month.$day.'T'.$endhour.$endminutes.'00';
$new_event->timezone = $planner->timezone;


$DB->insert_record('chairman_events', $new_event, $returnid=false, $bulk=false);

redirect("$CFG->wwwroot/mod/chairman/chairman_events/events.php?id=$chairman_id");

?>
