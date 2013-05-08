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
 * The content for the business Arising tab of the Agenda/Meeting Extension to Committee Module.
 *
 * @package   Agenda/Meeting Extension to Committee Module
 * @copyright 2011 Dustin Durand
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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
if ($credentials == 'president' || $credentials == 'vice' || $credentials == 'admin'|| $credentials == 'member'|| is_siteadmin()) {

    //Load Form
    require_once('../topics/open_topic_form.php'); //Form for users that can view
    $mform = new mod_agenda_open_topics_form($event_id, $agenda_id, $chairman_id, $cm->instance);

    //Get default values
    $toform = $mform->getDefault_toform();
    $toform->event_id = $event_id;
    $toform->selected_tab = $selected_tab;
    //Set data
    $mform->set_data($toform);

    //Display Menu
    require_once("$CFG->dirroot/mod/chairman/chairman_meetingagenda/business/business_sidebar.php");

    //Display Form
    print '<div class="form">';
    $mform->display();
    print '</div>';

}

?>