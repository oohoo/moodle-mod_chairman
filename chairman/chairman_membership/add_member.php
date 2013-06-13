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

$id = optional_param('id',0,PARAM_INT);    // Course Module ID

$PAGE->requires->js('/mod/chairman/ajaxlib.js');

// print header
chairman_check($id);
chairman_header($id,'addmember','add_member.php?id='.$id);

if(chairman_isadmin($id)){

    echo '<div><div class="title">'.get_string('addmember', 'chairman').'</div>';

    echo '<form action="'.$CFG->wwwroot.'/mod/chairman/chairman_membership/add_member_script.php?id='.$id.'" method="POST" name="newmember">';
    echo '<table width=100% border=0>';

    $users = $DB->get_records('user', array('deleted'=>'0'),'lastname');

    echo '<tr><td>'.get_string('name', 'chairman').' : </td>';
    echo '<td width=85%><div id="user"><select name="user">';
    foreach($users as $user) {
        if(!$DB->get_record('chairman_members', array('user_id'=>$user->id,'chairman_id'=>$id))) {
            echo '<option value="'.$user->id.'">'.$user->lastname.', '.$user->firstname.'</option>';
        }
    }
    echo '</select></div>';

    echo '</td></tr>';
    echo '<tr><td></td>';
    echo '<td><input style="color:grey;" type="text" onkeyup="ajax_post(\'searchmember.php?id='.$id.'&str=\'+this.value,\'user\');" value="'.get_string('search','chairman').'" onfocus="if(this.value==\''.get_string('search','chairman').'\'){this.value=\'\';}" onblur="if(this.value==\'\'){this.value=\''.get_string('search','chairman').'\';}"></td>';
    echo '</tr>';
    echo '<tr><td>'.get_string('role', 'chairman').' : </td>';
    echo '<td><select name="role">';
    echo '<option value="3">'.get_string('member', 'chairman').'</option>';
    //Only one president
    if(!$DB->get_record('chairman_members', array('chairman_id'=>$id, 'role_id'=>1))) {
        echo '<option value="1">'.get_string('president', 'chairman').'</option>';
    }
    //Only one co-president
    if(!$DB->get_record('chairman_members', array('chairman_id'=>$id, 'role_id'=>2))) {
        echo '<option value="2">'.get_string('vicepresident', 'chairman').'</option>';
    }
    //Only one administrator
    if(!$DB->get_record('chairman_members', array('chairman_id'=>$id, 'role_id'=>4))) {
        echo '<option value="4">'.get_string('administrator', 'chairman').'</option>';
    }
    echo '</select></td></tr>';
    echo '<tr><td><br/></td><td></td></tr>';
    echo '<tr>';
    echo '<td></td>';
    echo '<td><input type="submit" value="'.get_string('addmember', 'chairman').'">';
    echo '<input type="button" value="'.get_string('cancel', 'chairman').'" onClick="parent.location=\''.$CFG->wwwroot.'/mod/chairman/view.php?id='.$id.'\'"></td>';
    echo '</tr>';
    echo '</table>';
    echo '</form>';

    echo '<span class="content">';

    echo '</span></div>';

}

chairman_footer();

?>
