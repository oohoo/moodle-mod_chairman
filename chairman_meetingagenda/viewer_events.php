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
 * A page displaying all events for the events tab of the overall agenda viewer. (viewer.php)
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package   Agenda/Meeting Extension to Committee Module
 * @copyright 2011 Dustin Durand
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("$CFG->dirroot/mod/chairman/chairman_meetingagenda/lib.php");
require_once("$CFG->dirroot/mod/chairman/chairman_meetingagenda/agenda/css/agenda_viewer.css");
require_once("$CFG->dirroot/mod/chairman/chairman_events/EventOutputRenderer.php");

global $DB;


//If agenda doesn't exist, don't load content
if (!$agenda) {
    print '<center><h3>' . get_string('no_agenda', 'chairman') . '</h3></center>';
    return;
}


//Simple cypher for code clarity
$role_cypher = array('1' => 'president', '2' => 'vice', '3' => "member", "4" => 'admin');

//check if user has a valid user role, otherwise give them the credentials of a guest
if (isset($user_role) && ($user_role == '1' || $user_role == '2' || $user_role == '3' || $user_role == '4')) {
    $credentials = $role_cypher[$user_role];
} else {
    $credentials = "guest";
}

//----------SECURITY------------------------------------------------------------
//------------------------------------------------------------------------------
//Check if they are a memeber
if ($credentials == 'president' || $credentials == 'vice' || $credentials == 'admin'|| $credentials == 'member' || is_siteadmin()) {

print '<div class="viwer_menu">';
$sql = "SELECT ce.id, ce.year, ce.year, ce.month, ce.day, ce.summary ".
"FROM {chairman_events} ce ".
"WHERE ce.chairman_id = ? ".
"ORDER BY year DESC, month DESC, day DESC";

$agendas = $DB->get_records_sql($sql, array($chairman_id,$event_id), $limitfrom=0, $limitnum=0);

print '<h3 class="headerbar">'.get_string('events','chairman').'</h3><ul>';


$renderer = new EventOutputRenderer($chairman_id);
$renderer->output_year_events();

print '</div>';
}

?>