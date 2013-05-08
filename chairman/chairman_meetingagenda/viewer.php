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
 * The view controller for the general view of Agenda/Meeting Extension to Committee Module.
 *
 *
 * @package   Agenda/Meeting Extension to Committee Module
 * @copyright 2011 Dustin Durand
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once('../lib_chairman.php');


$chairman_id = optional_param('chairman_id', -1, PARAM_INT); // event ID

//If not a member, get out
if ((!chairman_isMember($chairman_id)) AND (!chairman_isadmin($chairman_id))) {
    redirect($CFG->wwwroot.'/course/view.php?id='.chairman_check($chairman_id), 'You are not a member of this committee and therefore do not have permission to view this page.', 2);
}

global $DB,$PAGE,$USER;

$is_viewer = true; //Used in sidebar to activate agenda menu

//If Event ID provided
if ($chairman_id) {
  $agenda_id = null;
  $event_id = null;
  $agenda = new stdClass();

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


$PAGE->set_url('/mod/chairman/chairman_meetingagenda/viewer.php?chairman_id='.$chairman_id);
    $PAGE->set_title("$chairman->name");
    $PAGE->set_heading("$chairman->name");

    $navlinks = array(
     array('name' => get_string('modulename','chairman'), 'link' => $CFG->wwwroot.'/mod/chairman/view.php?id='.$chairman_id, 'type' => 'misc'),
     array('name' => get_string('event','chairman'), 'link' => $CFG->wwwroot.'/mod/chairman/chairman_events/events.php?id='.$chairman_id, 'type' => 'misc')
    );
    $navigation = build_navigation($navlinks);
// Output starts here
echo $OUTPUT->header();

$thispageurl = $CFG->wwwroot;

//Attempt to get current tab param
$selected_tab = optional_param('selected_tab', -1, PARAM_INT);


// Print the tabs to switch modes.
if ($selected_tab==6) {
	$currenttab = 'topics_by_year_list';
	$thispageurl = $PAGE->url;
	$contents = 'topics/topics_by_year_list.php';
/*
  DISABLED--TOPICS BE YEAR
} elseif ($selected_tab==4) {
      $currenttab = 'topics_by_year';
	$thispageurl = $PAGE->url;
	$contents = 'topics/topics_by_year.php';
 */


} elseif ($selected_tab==5) {

        $currenttab = 'motions_by_year';
        $thispageurl = $PAGE->url;
        $contents = 'motions/motions_by_year.php';
} elseif($selected_tab==3) {
    $currenttab = 'open_topics';
	$thispageurl = $PAGE->url;
	$contents = 'topics/open_topics.php';
} elseif($selected_tab==2) {
    $currenttab = 'open_topics_list';
	$thispageurl = $PAGE->url;
	$contents = 'topics/open_topic_list.php';
} else {
    $currenttab = 'viewer_events';
	$thispageurl = $PAGE->url;
	$contents = 'viewer_events.php';

}

//Create Tab Objects
$tabs = array(array(
    new tabobject('viewer_events', new moodle_url($thispageurl, array('selected_tab' => 1)), get_string('events', 'chairman')),
    //new tabobject('open_topics', new moodle_url($thispageurl, array('selected_tab' => 3)), get_string('open_topics_tab', 'chairman')),
    new tabobject('open_topics_list', new moodle_url($thispageurl, array('selected_tab' => 2)), get_string('open_topic_list_tab', 'chairman')),
   // new tabobject('topics_by_year', new moodle_url($thispageurl, array('selected_tab' => 4)), get_string('topics_by_year_tab', 'chairman')),
    new tabobject('topics_by_year_list', new moodle_url($thispageurl, array('selected_tab' => 6)), get_string('topics_by_year_list_tab', 'chairman')),
    new tabobject('motions_by_year', new moodle_url($thispageurl, array('selected_tab' => 5)), get_string('motions_by_year_tab', 'chairman'))
    


));

//Print Tabs
print_tabs($tabs, $currenttab);

//Include tab content
require($contents);

echo $OUTPUT->footer();