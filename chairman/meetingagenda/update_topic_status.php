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

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');

global $DB;


if(isset($_POST['topic_id'])){
$topic_id = $_POST['topic_id'];
$topic_status = $_POST['status'];
$return_url = $_POST['return_url'];


if($DB->record_exists('chairman_agenda_topics', array('id'=>$topic_id)))  {

    $dataobject = new stdClass();
    $dataobject->id = $topic_id;
    $dataobject->status = $topic_status;

$DB->update_record('chairman_agenda_topics', $dataobject, $bulk=false);

}


redirect($return_url);

}




?>
