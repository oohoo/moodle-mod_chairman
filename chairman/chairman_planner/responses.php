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

$id = required_param('planner',PARAM_INT);    // Planner ID
$responses = optional_param('responses',null,PARAM_RAW);

$planner = $DB->get_record('chairman_planner', array('id'=>$id));

$memberobj = $DB->get_record('chairman_members', array('user_id'=>$USER->id,'chairman_id'=>$planner->chairman_id));
$planneruserobj = $DB->get_record('chairman_planner_users', array('chairman_member_id'=>$memberobj->id,'planner_id'=>$id));

$dates = $DB->get_records('chairman_planner_dates', array('planner_id'=>$id));
foreach($dates as $date){
    $DB->delete_records('chairman_planner_response', array('planner_user_id'=>$planneruserobj->id,'planner_date_id'=>$date->id));
}

if(is_array($responses)){

foreach($responses as $key => $value){
    $responseobj = new stdClass;
    $responseobj->planner_user_id = $planneruserobj->id;
    $responseobj->planner_date_id = $key;
    $responseobj->response = 1;
    $DB->insert_record('chairman_planner_response', $responseobj);
}
}
echo '<script type="text/javascript">';
echo 'window.location.href="'.$CFG->wwwroot.'/mod/chairman/chairman_planner/planner.php?id='.$planner->chairman_id.'";';
echo '</script>';

?>
