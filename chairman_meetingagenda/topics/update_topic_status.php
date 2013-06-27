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
 * A script to update the status of a given topic.
 *
 *
 * @package   Agenda/Meeting Extension to Committee Module
 * @copyright 2011 Dustin Durand
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php');

global $DB;


if(isset($_POST['topic_id'])){
$topic_id = $_POST['topic_id'];
$topic_status = $_POST['status'];
$return_url = $_POST['return_url'];

//Get chairman info for logs
$agenda_topic = $DB->get_record('chairman_agenda_topics', array('id' => $topic_id));
$agenda = $DB->get_record('chairman_agenda', array('id' => $agenda_topic->chairman_agenda));
$cm = get_coursemodule_from_id('chairman', $agenda->chairman_id);

if($DB->record_exists('chairman_agenda_topics', array('id'=>$topic_id)))  {

    $dataobject = new stdClass();
    $dataobject->id = $topic_id;
    $dataobject->status = $topic_status;

$DB->update_record('chairman_agenda_topics', $dataobject, $bulk=false);
$event = $DB->get_record('chairman_events', array('id' => $agenda->chairman_events_id));
add_to_log($cm->course, 'chairman', 'update', '', get_string('open_topics_tab', 'chairman') . ' - ' .  $event->summary . ' - ' . $agenda_topic->title . ' - ' . $topic_status, $cm->id);


}


redirect($return_url);

}




?>
