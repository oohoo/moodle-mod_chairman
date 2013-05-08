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
 * A script to envoke the creation of the pdf version of the agenda.
 *
 *
 * @package   Agenda/Meeting Extension to Committee Module
 * @copyright 2011 Dustin Durand
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php');
require_once('../../lib_chairman.php');




$event_id = optional_param('event_id', 0, PARAM_INT); // event ID
$plain_pdf = optional_param('plain_pdf', 0, PARAM_INT); // event ID

global $DB,$PAGE,$USER;

$agenda = null;

//If Event ID provided
if ($event_id) {
        //Check if agenda created
	$agenda  = $DB->get_record('chairman_agenda', array('chairman_events_id' => $event_id), '*', $ignoremultiple=false);
	if($agenda){
	$chairman_id = $agenda->chairman_id;
	$event_id = $agenda->chairman_events_id;
	$agenda_id =$agenda->id;

        //No Agenda Created
	} else {

	$chairman_event = $DB->get_record('chairman_events', array('id' => $event_id), '*', $ignoremultiple=false);

	if($chairman_event){
	$chairman_id = $chairman_event->chairman_id;
	$event_id = $chairman_event->id;

        //NO EVENT
	} else {
	error('You must create an meeting agenda from an event.');
	}
	}

//No EVENT ID
} else {
    error('You must create an meeting agenda from an event.');
}



chairman_check($chairman_id);

//Get Course Module Object
$cm = get_coursemodule_from_id('chairman', $chairman_id); //get course module

//Get chairman Object
$chairman = $DB->get_record("chairman", array("id"=>$cm->instance));

//Get Credentials for this user
if ($current_user_record = $DB->get_record("chairman_members", array("chairman_id"=>$chairman_id,"user_id"=>$USER->id))){
$user_role = $current_user_record->role_id;
}
elseif (is_siteadmin($USER))
{
    $user_role = 4; //If site admin => Admin committee profile
}

//Simple cypher for code clarity
$role_cypher = array('1' => 'president', '2' => 'vice', '3' => "member", "4" => 'admin');

//check if user has a valid user role, otherwise give them the credentials of a guest
if (isset($user_role) && ($user_role == '1' || $user_role == '2' || $user_role == '3' || $user_role == '4')) {
    $credentials = $role_cypher[$user_role];
} else {
    $credentials = "guest";
}

if ($credentials == 'president' || $credentials == 'vice' || $credentials == 'admin'|| $credentials == 'member') {


require_once('pdf.php');
$pdf = new pdf_creator($event_id, $agenda_id, $chairman_id, $cm->instance);
$pdf->create_pdf($plain_pdf);

} else {

    print_error("Access Restricted");
}
?>
