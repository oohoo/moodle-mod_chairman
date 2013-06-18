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

$user = optional_param('user',0,PARAM_INT);
$role = optional_param('role',0,PARAM_INT);

chairman_check($id);
chairman_header($id,'addmember','add_member.php?id='.$id);

echo '<div><div class="title">'.get_string('addmember', 'chairman').'</div>';

echo get_string('addingmemberpleasewait', 'chairman');

echo '<span class="content">';

echo '</span></div>';

chairman_footer();

$new_member = new stdClass();
$new_member->user_id = $user;
$new_member->role_id = $role;
$new_member->chairman_id =$id;

if(!$new_member->user_id==0){ //Check if not adding an invalid user
$DB->insert_record('chairman_members', $new_member);
}


echo '<script type="text/javascript">';
echo 'window.location.href="'.$CFG->wwwroot.'/mod/chairman/view.php?id='.$id.'";';
echo '</script>';

?>
