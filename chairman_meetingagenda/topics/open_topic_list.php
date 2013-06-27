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
 * The form for the business arising tab, but only with viewing permissions, for the Agenda/Meeting Extension to Committee Module.
 *
 *      **LIST VIEW
 *
 *
 * @package   Agenda/Meeting Extension to Committee Module
 * @copyright 2011 Dustin Durand
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once("$CFG->dirroot/mod/chairman/chairman_meetingagenda/lib.php");

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
if ($credentials == 'president' || $credentials == 'vice' || $credentials == 'admin') {

output_open_topics('edit',$chairman_id,$event_id,$selected_tab);

//---------TOPICS---------------------------------------------------------------



} elseif($credentials == 'member') {


output_open_topics('',$chairman_id,$event_id,$selected_tab);

}

/*
 * A function to check if the current status should be selected in the dropdown menu
 *
 * @param string $mode The dropdown value.
 * @param string $status The database value for the topic status.
 *
 * @return string Returns selected if the $mode matches the $status
 */

function checkSelected($mode,$status) {

    if($mode == $status){
        return "selected";
    } else {
        return "";
    }


}

/**
 * 
 * Outputs the list of open topics by year.
 * 
 * @global moodle_database $DB
 * @param string $control
 * @param int $chairman_id
 * @param int $event_id
 * @param int $selected_tab
 */
function output_open_topics($control, $chairman_id, $event_id, $selected_tab) {

    global $DB;
    $cm = get_coursemodule_from_id('chairman', $chairman_id);
    $date_time = new DateTime();
    $min_year = getMinEventYear($chairman_id);
    list($a, $b, $itteration_date) = chairman_get_year_definition($chairman_id);

    $end_year = new DateTime();
    $end_year->setDate($min_year, $date_time->format('m'), $date_time->format('d'));

    $interval = $itteration_date->diff($end_year, false);

    echo "<ul class='yearly_accordian'>";
    
    while (($interval->invert === 1) ||
    ($interval->invert === 0 && ($interval->y === 0))) {

            $sql = "SELECT DISTINCT t.*, e.day, e.month, e.year, e.id as EID FROM {chairman_agenda} a, {chairman_agenda_topics} t, {chairman_events} e " .
            "WHERE t.chairman_agenda = a.id AND e.id = a.chairman_events_id AND e.chairman_id = a.chairman_id " .
            "AND a.chairman_id = $chairman_id AND t.status <> 'closed' and ((e.year=? and e.month>=?) or (e.year=? and e.month<=?)) " .
            "ORDER BY e.year DESC, e.month DESC, e.day DESC";

            $topics = $DB->get_records_sql($sql, array($itteration_date->format('Y') - 1, $itteration_date->format('m'), $itteration_date->format('Y'), $itteration_date->format('m')));

        if (count($topics) === 0) {
            $interval = $itteration_date->sub(new DateInterval("P1Y"))->diff($end_year, false);
            continue;
        }
        
        
        echo '<li>';
        echo'<h3>' . chairman_get_month($itteration_date->format('m')) . " " . ($itteration_date->format('Y') - 1) . " - " .
                    chairman_get_month($itteration_date->format('m')) . " " . ($itteration_date->format('Y')) . '</h3>';
        
        
        echo '<div>';
        create_topics_table($topics, $control,$chairman_id,$event_id,$selected_tab);
        echo '</div>';
        
        echo '</li>';
        
        $interval = $itteration_date->sub(new DateInterval("P1Y"))->diff($end_year, false);
    }
    
    echo "</ul>";
    
    $event = $DB->get_record('chairman_events', array('id' => $event_id));
    add_to_log($cm->course, 'chairman', 'view', '', get_string('open_topics_tab', 'chairman') . ' - ' .  $event->summary , $cm->id);


}

/*
 * A function to create the table for provided topics.
 * No. | Date | Topic Title | Status | Save Image(if editable)
 *
 * @param string $control If equals edit, submit button is added for submission
 * 
 */
function create_topics_table($topics, $control,$chairman_id,$event_id,$selected_tab){

    global $DB,$CFG,$is_viewer;

    if($control=='edit'){
    $post_url = "topics/update_topic_status.php";
    } else {
    $post_url = "";
    }



if($topics){ //check if any topics actually exist

    //possible topic status:
    $topic_statuses = array('open'=>get_string('topic_open', 'chairman'),
                            'in_progress'=>get_string('topic_inprogress', 'chairman'),
                            'closed'=>get_string('topic_closed', 'chairman'));


print '<table style="width:100%">';



$index=1;
foreach($topics as $key=>$topic){

//$this->topicNames[$index] = $topic->title;


//-----LINK TO AGENDA-----------------------------------------------------------
$url = "$CFG->wwwroot/mod/chairman/chairman_meetingagenda/view.php?event_id=" . $topic->eid . "&selected_tab=" . 3;
//$mform->addElement('html','<div class="agenda_link_topic"><li><a href="'.$url.'" >'.toMonth($topic->month) ." ".$topic->day.", ".$topic->year.'</a></li></div>');


print '<tr><form method="post" action="'.$post_url.'">';

print "<td>$index. </td>";

print '<td><a href="'.$url.'" >'.toMonth($topic->month) ." ".$topic->day.", ".$topic->year.'</a>';
print '</td><td>';

print $topic->title."</td>";

$status = $topic->status;


if($control=='edit'){
print '<td><select name="status" id="chairman_status_selector_'.$index."_".$topic->year.'" onchange=\'change_open_topic_save_visiblity("'.$topic->status.'",'."$index".',"'.$topic->year.'")\'>';
print '<option value="open" '.checkSelected("open",$topic->status).'>'.$topic_statuses['open'].'</option>';
print '<option value="in_progress" '.checkSelected("in_progress",$topic->status).'>'.$topic_statuses['in_progress'].'</option>';
print '<option value="closed" '.checkSelected("closed",$topic->status).'>'.$topic_statuses['closed'].'</option>';

print '</select></td><td>';

print '<input type="image" id="save_image_'.$index."_".$topic->year.'" SRC="../pix/save.png" VALUE="Submit now"/></td>';




} else {

 print "<td>".$topic_statuses[$topic->status]."</td>";

}

if(isset($is_viewer)){
$return_url = $CFG->wwwroot."/mod/chairman/chairman_meetingagenda/viewer.php?chairman_id=".$chairman_id."&selected_tab=".$selected_tab;
} else {
$return_url = $CFG->wwwroot."/mod/chairman/chairman_meetingagenda/view.php?event_id=".$event_id."&selected_tab=".$selected_tab;
}


print '<input type="hidden" name="return_url" value="'.$return_url.'"/> ';
print '<input type="hidden" name="topic_id" value="'.$topic->id.'"/> ';
print '</form></tr>';


$index++;

}//end foreach topic

print '</table>';

}//end topics

}





