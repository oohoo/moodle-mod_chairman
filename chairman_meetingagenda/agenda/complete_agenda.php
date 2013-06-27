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

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php');
require_once('../../lib_chairman.php');

$event_id = optional_param('event_id', 0, PARAM_INT); // event ID, or

global $DB,$PAGE,$USER,$CFG;

$agenda = null;

if ($event_id) {
	$agenda  = $DB->get_record('chairman_agenda', array('chairman_events_id' => $event_id), '*', $ignoremultiple=false);
	if($agenda){
	$chairman_id = $agenda->chairman_id;
	$event_id = $agenda->chairman_events_id;
	$agenda_id =$agenda->id;
	//$DB->delete_records('chairman_agenda', array('chairman_events_id' => $event_id));
       //exit();
	} else {
          print_error('Unable to Complete Agenda');
        }
} else {
print_error('Unable to Complete Agenda');
}

chairman_check($chairman_id);
$cm = get_coursemodule_from_id('chairman', $chairman_id); //get course module

//Get Credentials for this user
if ($current_user_record = $DB->get_record("chairman_members", array("chairman_id"=>$chairman_id,"user_id"=>$USER->id))){
$user_role = $current_user_record->role_id;
}


//Simple cypher for code clarity
$role_cypher = array('1' => 'president', '2' => 'vice', '3' => "member", "4" => 'admin');

//check if user has a valid user role, otherwise give them the credentials of a guest
if (isset($user_role) && ($user_role == '1' || $user_role == '2' || $user_role == '3' || $user_role == '4')) {
    $credentials = $role_cypher[$user_role];
} else {
    $credentials = "guest";
}

if ($credentials == 'president' || $credentials == 'vice' || $credentials == 'admin') {

 if ($DB->record_exists('chairman_agenda', array('id' => $agenda->id))) {
                $agenda_object = new stdClass();
                $agenda_object->id = $agenda->id;
                $agenda_object->completed = 1;

                 //Add to logs
                $event = $DB->get_record('chairman_events', array('id' => $event_id));
                add_to_log($cm->course, 'chairman', 'update', '', get_string('agendas', 'chairman') . ' - ' .  $event->summary , $cm->id);
                $DB->update_record('chairman_agenda', $agenda_object, $bulk = false);
            }
redirect($CFG->wwwroot."/mod/chairman/chairman_meetingagenda/view.php?event_id=".$event_id);
} else {
print_error("Access Restriction");
}




?>
