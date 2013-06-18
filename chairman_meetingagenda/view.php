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
 * The view controller for the agenda side of Agenda/Meeting Extension to Committee Module.
 *
 *
 * @package   Agenda/Meeting Extension to Committee Module
 * @copyright 2011 Dustin Durand
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once('../lib_chairman.php');
require_once('lib.php');

$event_id = optional_param('event_id', 0, PARAM_INT); // event ID

global $DB,$PAGE,$USER,$COURSE, $OUTPUT;

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
        $agenda_id =null;

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

$url = '/mod/chairman/chairman_meetingagenda/view.php?event_id='.$event_id;


$thispageurl = $CFG->wwwroot;

//Attempt to get current tab param
$selected_tab = optional_param('selected_tab', -1, PARAM_INT);


// Print the tabs to switch modes.----------------------------------------------
//------------------------------------------------------------------------------
//Minutes for the meeting
if ($selected_tab==3) {
	$currenttab = 'arising_issues';
	$thispageurl = $url;
	$contents = 'business/business.php';

//Topics by year list
} elseif ($selected_tab==7) {
	$currenttab = 'topics_by_year_list';
	$thispageurl = $url;
	$contents = 'topics/topics_by_year_list.php';


//Motions By Year
} elseif ($selected_tab==5) {
        $currenttab = 'motions_by_year';
        $thispageurl = $url;
        $contents = 'motions/motions_by_year.php';

//Business Arising List
} elseif ($selected_tab==6) {
        $currenttab = 'open_topic_list';
        $thispageurl = $url;
        $contents = 'topics/open_topic_list.php';        

} elseif($selected_tab==4) {
    $currenttab = 'agenda_archives';
	$thispageurl = $url;
	$contents = 'archive/archive.php';         
        
//Agenda
} else {

    $currenttab = 'agenda';
	$thispageurl = $url;
	$contents = 'agenda/agenda.php';
	$selected_tab = 1;
}

//Create Tab Objects
$tabs = array(array(
   new tabobject('agenda', new moodle_url($thispageurl, array('selected_tab' => 1)), get_string('agenda_tab', 'chairman')),
   new tabobject('arising_issues', new moodle_url($thispageurl, array('selected_tab' => 3)), get_string('minutes_tab', 'chairman')),
   new tabobject('open_topic_list', new moodle_url($thispageurl, array('selected_tab' => 6)), get_string('open_topic_list_tab', 'chairman')),
   new tabobject('archive', new moodle_url($thispageurl, array('selected_tab' => 4)), get_string('agenda_archives', 'chairman')),
    
    //new tabobject('topics_by_year_list', new moodle_url($thispageurl, array('selected_tab' => 7)), get_string('topics_by_year_list_tab', 'chairman')),

    //new tabobject('motions_by_year', new moodle_url($thispageurl, array('selected_tab' => 5)), get_string('motions_by_year_tab', 'chairman')),

));

$PAGE->requires->css('/mod/chairman/chairman_meetingagenda/meetingagenda.css');
$PAGE->requires->css('/mod/chairman/jquery/plugins/multipane_transfer/css/jquery.oohoo.multipaneTransfer.css');
$PAGE->requires->css('/mod/chairman/jquery/plugins/select2/select2.css');

chairman_header($cm->id, $currenttab, "/chairman_meetingagenda/view.php?event_id='.$event_id");

$PAGE->requires->js('/mod/chairman/chairman_meetingagenda/init.js');
$PAGE->requires->js('/mod/chairman/jquery/plugins/multipane_transfer/js/jquery.oohoo.multipaneTransfer.js');
$PAGE->requires->js('/mod/chairman/jquery/plugins/select2/select2.js');
$PAGE->requires->js('/mod/chairman/jquery/plugins/select2/select2_locale_moodle.js');
$PAGE->requires->js('/mod/chairman/chairman_meetingagenda/business/js/moodle_users_selector.js');
$PAGE->requires->js('/mod/chairman/chairman_meetingagenda/topics/js/topics.js');

//Print Tabs
print_tabs($tabs, $currenttab);

//Include tab content
require_once($contents);

chairman_footer();
