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

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
require_once("$CFG->dirroot/mod/chairman/chairman_meetingagenda/util/moodle_user_selector.php");
require_once("$CFG->dirroot/mod/chairman/chairman_meetingagenda/lib.php");
require_once("$CFG->dirroot/mod/chairman/chairman_meetingagenda/agenda/css/agenda_link.css");

class mod_business_mod_form extends moodleform {

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


//-- variable convience--
$event_id = $this->event_id;
$agenda_id = $this->agenda_id;
$instance = $this->instance;
$chairman_id = $this->chairman_id;


$event_record = $DB->get_record('chairman_events', array('id' => $event_id), '*', $ignoremultiple = false);
$agenda = $DB->get_record('chairman_agenda', array('id' => $agenda_id), '*', $ignoremultiple = false);


//-------GENERAL INFORMATION----------------------------------------------------
//------------------------------------------------------------------------------
$mform->addElement('header', 'general', get_string('general', 'form'));
$mform->addElement('static', 'committee', get_string('committee_agenda', 'chairman'), "");
$mform->addElement('static', 'date', get_string('date_agenda', 'chairman'), "");
$mform->addElement('static', 'time', get_string('time_agenda', 'chairman'), "");
$mform->addElement('static', 'duration', get_string('duration_agenda', 'chairman'), "");

//Conditionally add items if they use data
conditionally_add_static($mform, $agenda->location, 'location', get_string('location_agenda', 'chairman'));
conditionally_add_static($mform, $event_record->summary, 'summary', get_string('summary_agenda', 'chairman'));
conditionally_add_static($mform, $event_record->description, 'description', get_string('desc_agenda', 'chairman'));

//----------------CHANGE DEFAULT VARIABLES--------------------------------------
//------------------------------------------------------------------------------

//Add zero to minutes ex: 4 -> 04
$month = toMonth($event_record->month);

$toform->date = $month . " " . $event_record->day . ", " . $event_record->year;
$toform->time = $event_record->starthour . ":" . ZeroPaddingTime($event_record->startminutes) . "-" . $event_record->endhour . ":" . ZeroPaddingTime($event_record->endminutes);

//Find Duration
//Start TimeStamp
$eventstart = $event_record->day . '-' . $event_record->month . '-' . $event_record->year . ' ' . $event_record->starthour . ':' . $event_record->startminutes;
$Start = strtotime($eventstart);

//End TImestamp
$eventstart = $event_record->day . '-' . $event_record->month . '-' . $event_record->year . ' ' . $event_record->endhour . ':' . $event_record->endminutes;
$End = strtotime($eventstart);

//Durations in secs
$durationInSecs = $End - $Start;

//Assign value to duration
$toform->duration = formatTime($durationInSecs);

//----Description --------------------------------------------------------------
//Committee has already been called in view.php, and is still a valid object but we re-queried for code claridy
$chairman = $DB->get_record("chairman", array("id" => $instance)); // get chairman record for our instance
$chairman_event = $DB->get_record('chairman_events', array('id' => $event_id), '*', $ignoremultiple = false);

$toform->committee = $chairman->name;
$toform->summary = $chairman_event->summary;
$toform->description = $chairman_event->description;
$toform->event_id = $event_id;
$toform->location = $agenda->location;

//------------END DEFAULT VALUES------------------------------------------------




//-----------Participants-------------------------------------------------------
//------------------------------------------------------------------------------
$mform->addElement('header', 'participants_header', get_string('participants_header', 'chairman'));
$chairmanMemberRecords = $DB->get_records('chairman_agenda_members', array('chairman_id' => $chairman_id,'agenda_id'=>$agenda_id), '', '*', $ignoremultiple = false);

//--------Comittee Members------------------------------------------------------
        $chairmanmembers = array();//Used to store commitee members in an array

        if ($chairmanMemberRecords) {
            $mform->addElement('static', "", '', "<h4>" . get_string('committee_header', 'chairman') . '</h4>');
            $FORUM_TYPES = array(
                '0' => get_string('agenda_present', 'chairman'),
                '1' => get_string('agenda_absent', 'chairman'),
                '2' => get_string('agenda_uabsent', 'chairman'));

            $index = 0;
            foreach ($chairmanMemberRecords as $member) {
                $count_label = $index + 1;

                $participant = array();
                    $participant[] = & $mform->createElement('static', "participant_num[$index]", '', "$count_label.&nbsp;");
                    $participant[] = & $mform->createElement('static', "participant_name[$index]", '', "");
                    $participant[] = & $mform->createElement('static', "participant_status[$index]","","");
                    $participant[] = & $mform->createElement('static', "participant_status_notes[$index]","","");
                $mform->addGroup($participant, "participant[$index]", '', array(' '), false);


//----Disable participant_status_notes if present-------------------------------
                $mform->disabledIf("participant_status_notes[$index]", "participant_status[$index]", 'eq', 0);
                $mform->disabledIf("participant_status_notes[$index]", "participant_status[$index]", 'eq', -1);
                $mform->addElement('hidden', "participant_id[$index]", $member->id);
                $mform->setType('participant_id', PARAM_INT);
//--DEFAULT VALUES--------------------------------------------------------------
//------------------------------------------------------------------------------
                //Get Real name from moodle
                $name = $this->getUserName($member->user_id);
                $toform->participant_name[$index] = $name.": ";

                //USED LATER IN TOPIC MOTIONS FOR SELECTION OF MEMBERS
                $chairmanmembers[$member->id] = $name;

                $exclusion_id[] = $member->user_id;

$attendance = $DB->get_record('chairman_agenda_attendance', array('chairman_agenda' => $agenda_id,'chairman_members'=>$member->id), '*', $ignoremultiple = false);

        if($attendance){

            if(isset($attendance->absent)){
                if($attendance->absent == 0){ //present
                $toform->participant_status[$index] = $FORUM_TYPES[0];
                } elseif($attendance->absent == 1) { //absent
                $toform->participant_status[$index] = $FORUM_TYPES[1];
                $toform->participant_status_notes[$index] = $attendance->notes;
                }
            }
            if($attendance->unexcused_absence == 1){
              $toform->participant_status[$index] = $FORUM_TYPES[2];
              $toform->participant_status_notes[$index] = $attendance->notes;
            }

        }


                $index++;
            }


        }

//------------MOODLE USERS------------------------------------------------------
 
$sql = "SELECT * FROM {chairman_agenda_guests} WHERE chairman_agenda = ? AND moodleid IS NOT NULL";

$moodle_members = $DB->get_records_sql($sql, array($agenda_id), $limitfrom=0, $limitnum=0);

            if($moodle_members){
            $mform->addElement('static', "", '', "");
            $mform->addElement('static', "", '', "<h4>" . get_string('moodle_members', 'chairman') . '</h4>');

            $index = 0;

            foreach($moodle_members as $moodle_user){
            $count_label = $index + 1;

            $participant = array();
                $participant[] = & $mform->createElement('static', "participant_moodle_num[$index]", '', "$count_label.&nbsp;");
                $participant[] = & $mform->createElement('static', "participant_moodle_name[$index]", '', "");
                $participant[] = & $mform->createElement('html', "");//remove_moodle_user[$index]

            $mform->addElement('hidden', "participant_moodle_id[$index]", $moodle_user->moodleid);
            $exclusion_id[] = $moodle_user->moodleid;
            $mform->setType('participant_moodle_id', PARAM_INT);

            $mform->addGroup($participant, "participant_moodle[$index]", '', array(' '), false);

//----------DEFAULT VALUES------------------------------------------------------
 $name = $this->getUserName($moodle_user->moodleid);
$toform->participant_moodle_name[$index] = $name;
//------------------------------------------------------------------------------

            $index++;
            }
}
//----------ADD New Moodle MEMBERS----------------------------------------------
//------------------------------------------------------------------------------
 $mform->addElement('static', "", '', "");
 //$mform->addElement('static', "", '', "----------------------------------");
$mform->registerNoSubmitButton('new_moodle_member');

//-----------GUESTS-------------------------------------------------------------

$sql = "SELECT * FROM {chairman_agenda_guests} WHERE chairman_agenda = ? AND moodleid IS NULL";

$guests = $DB->get_records_sql($sql, array($agenda_id), $limitfrom=0, $limitnum=0);

            if($guests){
            $mform->addElement('static', "", '', "");
            $mform->addElement('static', "", '', "<h4>" . get_string('guest_members', 'chairman') . '</h4>');

            $index = 0;

            foreach($guests as $guest){
            $count_label = $index + 1;

            $participant = array();
                $participant[] = & $mform->createElement('static', "participant_guest_num[$index]", '', "$count_label.&nbsp;");
                $participant[] = & $mform->createElement('static', "participant_guest_name[$index]", '', "");

                $mform->addElement('hidden', "participant_guest_id[$index]", $guest->id);
                $mform->setType('participant_guest_id', PARAM_INT);

            $mform->addGroup($participant, "participant_guest[$index]", '', array(' '), false);


//----------DEFAULT VALUES------------------------------------------------------
            $toform->participant_guest_name[$index] = $guest->firstname . " " . $guest->lastname;
//------------------------------------------------------------------------------


            $index++;
            }
        }

//---------TOPICS---------------------------------------------------------------
//------------------------------------------------------------------------------

$topics = $DB->get_records('chairman_agenda_topics', array('chairman_agenda' => $agenda_id), $sort = 'timecreated ASC', '*', $ignoremultiple = false);

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
//NOTES FOR TOPIC
//$mform->addElement('htmleditor', "topic_notes[$index]", '', "$topic->title");
//NOTES FOR TOPIC && DEFAULT VALUE FOR NOTES------------------------------------
$mform->addElement('html', '</br>');
$notes_htmlformated = format_text($topic->notes, $format = FORMAT_MOODLE, $options = NULL, $courseid_do_not_use = NULL);
$notes = print_collapsible_region($notes_htmlformated, 'topic_notes', "topic_status_".$index, get_string('topics_notes', 'chairman'), $userpref = false, $default = false, $return = true);
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

                $context = get_context_instance(CONTEXT_MODULE,$this->chairman_id); 
                $draftitemid = file_get_submitted_draft_itemid('attachments[' . $index . "]");
               file_prepare_draft_area($draftitemid, $context->id, 'mod_chairman', 'attachment', $topic->filename, array('subdirs' => 0, 'maxfiles' => 50));
               $toform->attachments[$index] = $draftitemid;
//------------------------------------------------------------------------------

$motions = $DB->get_records('chairman_agenda_motions', array('chairman_agenda' => $agenda_id,'chairman_agenda_topics'=>$topic->id), '', '*', $ignoremultiple = false);
$mform->addElement('html', "</br></br>");

//-----MOTIONS------------------------------------------------------------------
//------------------------------------------------------------------------------
if($motions){

    $sub_index=1;
    foreach($motions as $key=>$motion){

        $proposing_choices = $chairmanmembers;
        $supporting_choices = $chairmanmembers;

        $proposing_choices['-1']=get_string('proposedby', 'chairman');
        $supporting_choices['-1']=get_string('supportedby', 'chairman');

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
//$toform->proposition[$index][$sub_index] = $motion->motion;

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

$toform->unanimous[$index][$sub_index] = "   (".get_string('unanimous','chairman').")";
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
        $mform->addElement('hidden', 'base_url', "$CFG->wwwroot");
        $mform->setType('base_url', PARAM_TEXT);
        $mform->addElement('hidden', 'courseid', "$chairman->course");
        $mform->setType('courseid', PARAM_INT);
        
        //Set default values to private variable
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
