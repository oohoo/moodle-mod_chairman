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
 * @copyright 2010 Raymond Wainman, Dustin Durand, Patrick Thibaudeau (Campus St. Jean, University of Alberta)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../../config.php');
require_once('../lib.php');
require_once('../lib_chairman.php');

$id = optional_param('id',0,PARAM_INT);    // Course Module ID


// print header
chairman_check($id);
chairman_header($id,'planner','planner.php?id='.$id);

//content
if(chairman_isadmin($id)){
    echo '<input type="button" value="'.get_string('newplanner','chairman').'" onclick="window.location.href=\''.$CFG->wwwroot.'/mod/chairman/chairman_planner/newplanner.php?id='.$id.'\';" /><br/><br/>';
}

//If a person is not a member(ANY ROLE-pres, member, etc), no content shown.
if(!chairman_isMember($id) && !chairman_isadmin($id)){
   if (chairman_isadmin($id)){
       chairman_footer();
       return;
   } else {
   print "<b>".get_string('not_member','chairman')."</b>";

}
chairman_footer();
exit();
}

$planners = $DB->get_records('chairman_planner',array('chairman_id'=>$id),'id DESC');



foreach($planners as $planner){
    echo '<div class="title">'.$planner->name;
    if(chairman_isadmin($id)){
        echo '&nbsp;&nbsp;&nbsp;<a href="'.$CFG->wwwroot.'/mod/chairman/chairman_planner/newplanner.php?id='.$id.'&planner='.$planner->id.'"><img src="'.$CFG->wwwroot.'/pix/t/edit.gif"></a>';
        if($planner->active==1){
            echo '&nbsp;&nbsp;&nbsp;<a href="'.$CFG->wwwroot.'/mod/chairman/chairman_planner/planner_active.php?id='.$planner->id.'"><img src="'.$CFG->wwwroot.'/pix/i/hide.gif"></a>';
        }
        else{
            echo '&nbsp;&nbsp;&nbsp;<a href="'.$CFG->wwwroot.'/mod/chairman/chairman_planner/planner_active.php?id='.$planner->id.'"><img src="'.$CFG->wwwroot.'/pix/t/show.gif"></a>';
        }
        echo '&nbsp;&nbsp;&nbsp;<a href="'.$CFG->wwwroot.'/mod/chairman/chairman_planner/planner_delete.php?id='.$planner->id.'&chairmanid='.$id.'" onClick="return confirm(\''.get_string('deleteplannerquestion', 'chairman').'\');"><img src="'.$CFG->wwwroot.'/mod/chairman/pix/delete.gif"></a>';
    }
 echo '</div>';
    print '<table width="100%"><tr><td>';
    echo get_string('timezone_acutally_used','mod_chairman')." $planner->timezone<br>";
    if ($USER->timezone == '99'){
        if ($CFG->timezone == '99'){
            $current_timezone = get_string("serverlocaltime");
        } else {
            $current_timezone = $CFG->timezone;
        }
        
    } else {
        $current_timezone = $USER->timezone;
        
    }
     
    echo get_string('user_timezone','mod_chairman')."<br><br>";
    
    echo $planner->description;
    if($planner->active==0){
        echo '<div style="font-size:10px;font-weight:bold;margin-top:10px;margin-bottom:10px;">'.get_string('closed','chairman').'</div>';
        echo '<input type="button" value="'.get_string('viewresults','chairman').'" onclick="window.location.href=\''.$CFG->wwwroot.'/mod/chairman/chairman_planner/viewplanner.php?id='.$planner->id.'\';">';
    }
    else {
        
        $chairman_member = $DB->get_record('chairman_members', array('chairman_id' => $id, 'user_id' => $USER->id));
        $planner_user = false;
        
        if ($chairman_member) {//allow site admins who aren't in chairman to view planners
            $planner_user = $DB->get_record('chairman_planner_users', array('planner_id' => $planner->id, 'chairman_member_id' => $chairman_member->id));

            //Member added to committee, trying to view event -- must add them
            if (!$planner_user) {
                $planner_user = new stdClass;
                $planner_user->planner_id = $planner->id;
                ;
                $planner_user->chairman_member_id = $chairman_member->id;
                $planner_user->rule = 0;  //added as optional -- someone with edit can update in planner if needed

                $planner_id = $DB->insert_record('chairman_planner_users', $planner_user, $returnid = true, $bulk = false);
                $planner_user = $DB->get_record('chairman_planner_users', array('planner_id' => $planner->id, 'chairman_member_id' => $chairman_member->id));
            }
        } 


      if(chairman_isMember($id)) {//site admins cannot respond if not an actual member
        if($chairman_member && $DB->get_records('chairman_planner_response', array('planner_user_id'=>$planner_user->id))){
            echo '<div style="color:green;font-size:10px;font-weight:bold;margin-top:10px;margin-bottom:10px;">'.get_string('youhaveresponded','chairman').'</div>';
        }
        else{
            echo '<div style="color:red;font-size:10px;font-weight:bold;margin-top:10px;margin-bottom:10px;">'.get_string('youhavenotresponded','chairman').'</div>';
        }
      }

        print '</td><td>';
        display_planner_results($planner->id, $id);
        print '</td></tr></table>';

        if(chairman_isMember($id))//site admins cannot respond if not an actual member
            echo '<input type="button" value="'.get_string('respond','chairman').'" onclick="window.location.href=\''.$CFG->wwwroot.'/mod/chairman/chairman_planner/viewplanner.php?id='.$planner->id.'\';">';
    }
    echo '</table><br/><br/>';
}

//Add to logs
add_to_log($COURSE->id, 'chairman', 'view','',get_string('viewplanner', 'chairman'),$id);

//footer
chairman_footer();

?>
