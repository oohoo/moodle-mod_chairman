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
echo '<link rel="stylesheet" type="text/css" href="../style.php">';

$id = optional_param('id',0,PARAM_INT);    // Course Module ID

$member = optional_param('member',0,PARAM_INT);

chairman_check($id);
chairman_header($id,'deletemember','view.php?id='.$id);

echo '<div><div class="title">'.get_string('deletemember', 'chairman').'</div>';

echo get_string('deletingmember', 'chairman');

echo '<span class="content">';

echo '</span></div>';

chairman_footer();


$DB->delete_records('chairman_planner_response', array('planner_user_id'=>$member));
$DB->delete_records('chairman_planner_users', array('chairman_member_id'=>$member));
$DB->delete_records('chairman_members', array('id'=>$member));

echo '<script type="text/javascript">';
echo 'window.location.href="'.$CFG->wwwroot.'/mod/chairman/view.php?id='.$id.'";';
echo '</script>';

?>
