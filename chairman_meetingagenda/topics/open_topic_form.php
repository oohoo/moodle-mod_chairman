<?php

/// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

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
 * **DEPRECATED: Detailed View
 *              -Detailed View functionality was removed from release version
 *              -Replaced with list view only (Can be re-enabled by removing commented sections from view.php & viewer.php)
 *
 * @package   Agenda/Meeting Extension to Committee Module
 * @copyright 2011 Dustin Durand
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
require_once("$CFG->dirroot/mod/chairman/chairman_meetingagenda/util/moodle_user_selector.php");
require_once("$CFG->dirroot/mod/chairman/chairman_meetingagenda/lib.php");
require_once("$CFG->dirroot/mod/chairman/chairman_meetingagenda/agenda/css/agenda_link.css");

class mod_agenda_open_topics_form extends moodleform {

    private $instance;
    private $event_id;
    private $agenda_id;
    private $chairman_id;
    private $default_toform;
    private $topicNames; //Used by menu to determine which html anchor points are to what name

    function __construct($event_id, $agenda_id, $chairman_id, $cm) {
        $this->event_id = $event_id;
        $this->agenda_id = $agenda_id;
        $this->chairman_id = $chairman_id;
        $this->instance = $cm;
        parent::__construct();
    }

    function definition() {
global $DB,$CFG;

$mform = & $this->_form;
$toform = new stdClass();

$exclusion_id = array();


//--convience--
$event_id = $this->event_id;
$agenda_id = $this->agenda_id;
$chairman_id = $this->chairman_id;
$instance = $this->instance;

$commityRecords = $DB->get_records('chairman_agenda_members', array('chairman_id' => $chairman_id), '', '*', $ignoremultiple = false);

//--------Comittee Members------------------------------------------------------
        $chairmanmembers = array();//Used to store commitee members in an array

if ($commityRecords) {
           
    $index = 0;
    foreach ($commityRecords as $member) {

       // print_object($member);

        $name = $this->getUserName($member->user_id);
        $toform->participant_name[$index] = $name.": ";
        $chairmanmembers[$member->id] = $name;
        }
    }



//---------TOPICS---------------------------------------------------------------

$sql = "SELECT DISTINCT t.*, e.day, e.month, e.year, e.id as EID FROM {chairman_agenda} a, {chairman_agenda_topics} t, {chairman_events} e ".
        "WHERE t.chairman_agenda = a.id AND e.id = a.chairman_events_id AND e.chairman_id = a.chairman_id ".
        "AND a.chairman_id = $chairman_id AND t.status <> 'closed' ".
        "ORDER BY year ASC, month ASC, day ASC";;

$topics =  $DB->get_records_sql($sql, array(), $limitfrom=0, $limitnum=0);


if($topics){ //check if any topics actually exist

    //possible topic status:
    $topic_statuses = array('open'=>get_string('topic_open', 'chairman'),
                            'in_progress'=>get_string('topic_inprogress', 'chairman'),
                            'closed'=>get_string('topic_closed', 'chairman'));
    //possible motion status
    $motion_result = array( '-1'=>'-----',
                            '1'=>'<font color="#4AA02C">'.get_string('motion_accepted', 'chairman').'</font>',
                           '0'=>get_string('motion_rejected', 'chairman'));

$index=1;
foreach($topics as $key=>$topic){

$this->topicNames[$index] = $topic->title;
$mform->addElement('html', "<a name=\"topic_$index\"></a>");
$mform->addElement('header', 'topic_header', get_string('topics_header', 'chairman'). " " . $index);
$mform->addElement('static', "", "", "<h3>$topic->title</h3>");


//-----LINK TO AGENDA-----------------------------------------------------------
$url = "$CFG->wwwroot/mod/chairman/chairman_meetingagenda/view.php?event_id=" . $topic->eid . "&selected_tab=" . 3;
$mform->addElement('html','<div class="agenda_link_topic"><li><a href="'.$url.'">'.toMonth($topic->month) ." ".$topic->day.", ".$topic->year.'</a></li></div>');


//NOTES FOR TOPIC && DEFAULT VALUE FOR NOTES------------------------------------
$mform->addElement('html', '</br>');
$test = format_text($topic->notes, $format = FORMAT_MOODLE, $options = NULL, $courseid_do_not_use = NULL);
$notes = print_collapsible_region($test, 'topic_notes', "topic_status_$index", get_string('topics_notes', 'chairman'), $userpref = false, $default = false, $return = true);
$mform->addElement('html', $notes);
$mform->addElement('html', '</br>');
//------------------------------------------------------------------------------

//STATUS OF TOPIC
$mform->addElement('html','</br>');
$mform->addElement('static', "topic_status[$index]", get_string('topic_status', 'chairman'), '');
$mform->addElement('hidden', "topic_ids[$index]", $topic->id);
$mform->setType('topic_ids', PARAM_INT);
//$mform->addElement('static', "follow_up[$index]", get_string('topic_followup', 'chairman'), '');

//-------FILE MANAGER -- VIEW ONLY----------------------------------------------
$mform->registerElementType('filemanager_view_only', "$CFG->dirroot/mod/chairman/chairman_meetingagenda/util/filemanager_view_only.php", 'MoodleQuickForm_Modified_Filemanager');
$mform->addElement('filemanager_view_only', "attachments[".$index."]", get_string('attachments', 'chairman'), null,array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 10, 'accepted_types' => array('*')) );

$draftitemid = file_get_submitted_draft_itemid('attachments[' . $index . "]");
$context = get_context_instance(CONTEXT_MODULE,$this->chairman_id);
file_prepare_draft_area($draftitemid, $context->id, 'mod_chairman', 'attachment', $topic->filename, array('subdirs' => 0, 'maxfiles' => 50));
$toform->attachments[$index] = $draftitemid;
//------------------------------------------------------------------------------

$motions = $DB->get_records('chairman_agenda_motions', array('chairman_agenda_topics'=>$topic->id), '', '*', $ignoremultiple = false);
$mform->addElement('html', "</br></br>");

//-----MOTIONS------------------------------------------------------------------
if($motions){

    $sub_index=1;
    foreach($motions as $key=>$motion){

        

        $proposing_choices = $chairmanmembers;
        $supporting_choices = $chairmanmembers;

        $proposing_choices['-1']=get_string('proposedby', 'chairman');
        $supporting_choices['-1']=get_string('supportedby', 'chairman');

        //print_object($proposing_choices);
       // print $chairman_id;

$mform->addElement('static', "", "", "<h6>".get_string('motion', 'chairman')." $index.$sub_index:</h6>");
//$mform->addElement('static', "proposition[$index][$sub_index]", get_string('motion_proposal', 'chairman'), array('size'=>'50'));

$notes = print_collapsible_region($motion->motion, 'motion_proposal', "proposition_".$index."_".$sub_index, get_string('motion_proposal', 'chairman'), $userpref = false, $default = false, $return = true);
$mform->addElement('html', $notes);


$mform->addElement('static', "proposed[$index][$sub_index]", get_string('motion_by', 'chairman') , $proposing_choices, $attributes=null);
$mform->addElement('static', "supported[$index][$sub_index]", get_string('motion_second', 'chairman') , $supporting_choices, $attributes=null);;



$votes = array();
    $votes[] =$mform->createElement('static', "aye_label[$index][$sub_index]", "", "Aye: ");
    $votes[] =$mform->createElement('static', "aye[$index][$sub_index]", '', array('size'=>'1 em','maxlength'=>"3"));
    $votes[] =$mform->createElement('static', "nay_label[$index][$sub_index]", "", "Nay: ");
    $votes[] =$mform->createElement('static', "nay[$index][$sub_index]", '', array('size'=>'1 em','maxlength'=>"3"));
    $votes[] =$mform->createElement('static', "abs_label[$index][$sub_index]", "", "Abs: ");
    $votes[] =$mform->createElement('static', "abs[$index][$sub_index]", '', array('size'=>'1 em','maxlength'=>"3"));
    $votes[] = $mform->createElement('static', "unanimous[$index][$sub_index]", '', "");


    $mform->addGroup($votes, "motion_votes[$index][$sub_index]", get_string('motion_votes', 'chairman') , array(' '), false);

 $results = array();
    $results[] = $mform->createElement('static', "motion_result[$index][$sub_index]", '', $motion_result, $attributes=null);
    $mform->addGroup($results, "result[$index][$sub_index]", get_string('motion_outcome', 'chairman') , array(' '), false);


$mform->addElement('hidden', "motion_ids[$index][$sub_index]", $motion->id);
$mform->setType('motion_ids', PARAM_INT);
$mform->addElement('html', "</br>");


//-------DEFAULT VALUEs FOR MOTIONS---------------------------------------------
$toform->proposition[$index][$sub_index] = $motion->motion;

if(isset($motion->motionby)){
$mform->setDefault("proposed[$index][$sub_index]", $proposing_choices[$motion->motionby]);
} else {
$mform->setDefault("proposed[$index][$sub_index]", "");
}

if(isset($motion->secondedby)){
 $mform->setDefault("supported[$index][$sub_index]", $supporting_choices[$motion->secondedby]);
} else {
$mform->setDefault("supported[$index][$sub_index]", "");
}

if(isset($motion->carried)){
 $mform->setDefault("motion_result[$index][$sub_index]", $motion_result[$motion->carried]);
} else {
$mform->setDefault("motion_result[$index][$sub_index]", "");
}


if(isset($motion->unanimous)){

$toform->unanimous[$index][$sub_index] = "(".get_string('unanimous','chairman').")";
}

$toform->aye[$index][$sub_index] = $motion->yea;
$toform->nay[$index][$sub_index] = $motion->nay;
$toform->abs[$index][$sub_index] = $motion->abstained;

//-------------SET Highest vote as bolded---------------------------------------
if($motion->yea > $motion->nay){
    if($motion->yea > $motion->abstained){
        $toform->aye[$index][$sub_index] = "<b>".$toform->aye[$index][$sub_index]."</b>";
        $toform->aye_label[$index][$sub_index] = "<b>Aye: </b>";
    } elseif($motion->abstained!=$motion->yea) {
$toform->abs[$index][$sub_index] = "<b>".$toform->abs[$index][$sub_index]."</b>";
$toform->abs_label[$index][$sub_index] = "<b>Abs: </b>";
    }
} else{
    if($motion->nay > $motion->abstained){
$toform->nay[$index][$sub_index] = "<b>".$toform->nay[$index][$sub_index]."</b>";
$toform->nay_label[$index][$sub_index] = "<b>Nay: </b>";
    } elseif($motion->abstained!=$motion->nay) {
$toform->abs[$index][$sub_index] = "<b>".$toform->abs[$index][$sub_index]."</b>";
$toform->abs_label[$index][$sub_index] = "<b>Abs: </b>";
    }

}



 $mform->addGroupRule("motion_votes[$index][$sub_index]",
 array("aye[$index][$sub_index]" => array(array(get_string('numeric_only','chairman'), 'numeric', null, 'client', false, true)),
       "nay[$index][$sub_index]" => array(array(get_string('numeric_only','chairman'), 'numeric', null, 'client', false, true)),
       "abs[$index][$sub_index]" => array(array(get_string('numeric_only','chairman'), 'numeric', null, 'client', false, true))
     ));


//---------END DEFAULTS---------------------------------------------------------

$sub_index++;
    }



}//end if any motions exist

//-------DEFAULT TOPIC VALUEs---------------------------------------------------

//$test = format_text($topic->notes, $format = FORMAT_MOODLE, $options = NULL, $courseid_do_not_use = NULL);

//$notes = print_collapsible_region($test, 'test', $index, '$test', $userpref = '', $default = false, $return = true);
$toform->topic_notes1[$index] = $topic->notes;
$toform->topic_notes[$index] = $topic->notes;

if(isset($topic->status)){
$toform->topic_status[$index] = $topic_statuses[$topic->status];
} else {
 $toform->topic_status[$index] = $topic_statuses['open'];
}

//$toform->follow_up[$index] = $topic->follow_up;
//---------END DEFAULTS---------------------------------------------------------


$index++;

}//end foreach topic

}//end topics

        //Hidden Values
        $mform->addElement('hidden', 'event_id', '');
        $mform->setType('event_id', PARAM_TEXT);
        $mform->addElement('hidden', 'selected_tab', '');
        $mform->setType('selected_tab', PARAM_TEXT);
        
        //Set deafults to private varaible
        $this->default_toform = $toform;
}

/*
 * Returns the default values for the form.
 */
    function getDefault_toform() {
        return $this->default_toform;
    }

/*
 * Converts a given moodle ID into a FirstName LastName String.
 *
 *  @param $int $userID An unique moodle ID for a moodle user.
 */
    function getUserName($userID){
    Global $DB;

    $user = $DB->get_record('user', array('id' => $userID), '*', $ignoremultiple = false);
    $name = null;
    if($user){
    $name = $user->firstname . " " . $user->lastname;
    }
    return $name;
    }

/*
 * Returns An array of topic names with array keys being the index that the topic
 * is on the page. Used for menu sidebar creation.
 */
    function getIndexToNamesArray(){
        return $this->topicNames;
    }

 
}
