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
 * @copyright 2011 Raymond Wainman, Dustin Durand, Patrick Thibaudeau (Campus St. Jean, University of Alberta)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once('../lib.php');
require_once('../lib_chairman.php');

$id = optional_param('id', 0, PARAM_INT);    // Course Module ID
$chairmanid = optional_param('chairmanid', 0, PARAM_INT);    // Course Module ID
$cm = get_coursemodule_from_id('chairman', $chairmanid);//Course Module Object

global $CFG,$DB;
//Get name of Planner for logs
$planner = $DB->get_record('chairman_planner',array('id' => $id));
chairman_check($chairmanid);
//chairman_header($chairmanid, 'deleteplanner', 'planner.php?id=' . $chairmanid);
//delete planner dates
$DB->delete_records('chairman_planner_dates', array('planner_id'=> $id));
//Delete planner users
$DB->delete_records('chairman_planner_users', array('planner_id'=> $id));
//delete planner event
if($DB->delete_records('chairman_planner',array('id' => $id))) {
        //Add to logs
        add_to_log($cm->course, 'chairman','delete','',$planner->name,$chairmanid);
	redirect($CFG->wwwroot.'/mod/chairman/chairman_planner/planner.php?id='.$chairmanid, 'Planner event has been deleted', 3);
}

//chairman_footer();