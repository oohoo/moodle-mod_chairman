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
require_once('../../config.php');
require_once('lib.php');
require_once('lib_chairman.php');

global $CFG, $PAGE, $OUTPUT, $COURSE;

$id = optional_param('id',0,PARAM_INT);    // Course Module ID

// print header
chairman_check($id);
chairman_header($id,'members','view.php?id='.$id);


//If there is a president, show here
if($president = $DB->get_record('chairman_members', array('chairman_id'=>$id , 'role_id'=>1))) {
    $president_user = $DB->get_record('user', array('id'=>$president->user_id));

    echo '<div><div class="title">'.get_string('president', 'chairman').'</div>';

    echo '<span class="content"><a href="mailto:'.$president_user->email.'">'.$president_user->firstname.' '.$president_user->lastname.'</a>';
    
    if(chairman_isadmin($id)) {
        echo ' - <a href="'.$CFG->wwwroot.'/mod/chairman/chairman_membership/delete_member_script.php?id='.$id.'&member='.$president->id.'" onClick="return confirm(\''.get_string('deletememberquestion', 'chairman').'\');"><img src="'.$CFG->wwwroot.'/mod/chairman/pix/delete.gif"></a>';
    }
    echo '<br/><br/></span></div>';
}


//If there is a vice-president, show here
if($vicepresident = $DB->get_record('chairman_members', array('chairman_id'=>$id, 'role_id'=>2))) {
    $vicepresident_user = $DB->get_record('user', array('id'=>$vicepresident->user_id));

    echo '<div><div class="title">'.get_string('vicepresident', 'chairman').'</div>';

    echo '<span class="content"><a href="mailto:'.$vicepresident_user->email.'">'.$vicepresident_user->firstname.' '.$vicepresident_user->lastname.'</a>';

    if(chairman_isadmin($id)) {
        echo ' - <a href="'.$CFG->wwwroot.'/mod/chairman/chairman_membership/delete_member_script.php?id='.$id.'&member='.$vicepresident->id.'" onClick="return confirm(\''.get_string('deletememberquestion', 'chairman').'\');"><img src="'.$CFG->wwwroot.'/mod/chairman/pix/delete.gif"></a>';
    }
    echo '<br/><br/></span></div>';
}

//If there is an administrator, show here
if($administrator = $DB->get_record('chairman_members', array('chairman_id'=>$id, 'role_id'=>4))) {
    $administrator_user = $DB->get_record('user', array('id'=>$administrator->user_id));

    echo '<div><div class="title">'.get_string('administrator', 'chairman').'</div>';

    echo '<span class="content"><a href="mailto:'.$administrator_user->email.'">'.$administrator_user->firstname.' '.$administrator_user->lastname.'</a>';

    if(chairman_isadmin($id)) {
        echo ' - <a href="'.$CFG->wwwroot.'/mod/chairman/chairman_membership/delete_member_script.php?id='.$id.'&member='.$administrator->id.'" onClick="return confirm(\''.get_string('deletememberquestion', 'chairman').'\');"><img src="'.$CFG->wwwroot.'/mod/chairman/pix/delete.gif"></a>';
    }
    echo '<br/><br/></span></div>';
}

//All other members show up here
$memberssql = 'SELECT mdl_user.id, mdl_user.email, mdl_user.lastname, mdl_user.firstname, mdl_chairman_members.chairman_id, mdl_chairman_members.id
            FROM mdl_chairman_members INNER JOIN mdl_user ON mdl_chairman_members.user_id = mdl_user.id
            WHERE mdl_chairman_members.chairman_id ='.$id.' AND mdl_chairman_members.role_id =3 ORDER BY mdl_user.lastname';

//echo $memberssql;
//   if($member_check = get_record('chairman_members', 'chairman_id', $id, 'role_id', 3)){
if ($members = $DB->get_records_sql($memberssql)) {
    
    echo '<div><div class="title">'.get_string('members', 'chairman').'</div>';

    foreach($members as $member) {

        echo '<span class="content"><a href="mailto:'.$member->email.'">'.$member->firstname.' '.$member->lastname.'</a>';

        if(chairman_isadmin($id)) {
            echo ' - <a href="'.$CFG->wwwroot.'/mod/chairman/chairman_membership/delete_member_script.php?id='.$id.'&member='.$member->id.'" onClick="return confirm(\''.get_string('deletememberquestion', 'chairman').'\');"><img src="'.$CFG->wwwroot.'/mod/chairman/pix/delete.gif"></a>';
        }
        echo '<br/>';

    }

    echo '<br/></span></div>';
}

//use to create email button
$sql = 'SELECT '.$CFG->prefix.'user.email, '.$CFG->prefix.'chairman_members.chairman_id
            FROM '.$CFG->prefix.'chairman_members INNER JOIN '.$CFG->prefix.'user ON '.$CFG->prefix.'chairman_members.user_id = '.$CFG->prefix.'user.id
            WHERE '.$CFG->prefix.'chairman_members.chairman_id='.$id;
$email = '';
if($chairman_members = $DB->get_records_sql($sql)) {
    foreach ($chairman_members as $chairman_member) {
        $email .= $chairman_member->email.';';
    }
    $email_button = '<a href="mailto:'.$email.'"><img src="'.$CFG->wwwroot.'/mod/chairman/img/email.png'.'">'.get_string('sendto','chairman').'</a><br/>';
}
else {
    $email_button = '';
}

if(chairman_isMember($id) || chairman_isadmin($id)) {

    echo '<div class="add">';
    echo $email_button;
    if (chairman_isadmin($id)){
        echo '<a href="'.$CFG->wwwroot.'/mod/chairman/chairman_membership/add_member.php?id='.$id.'"><img src="'.$CFG->wwwroot.'/mod/chairman/pix/switch_plus.gif'.'">'.get_string('addmember', 'chairman').'</a>';
    }
    echo '</div>';

}
//Add to Moodle log
add_to_log($COURSE->id, 'chairman', 'view','',get_string('members', 'chairman'),$id);

chairman_footer();

?>
