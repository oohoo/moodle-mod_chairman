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
require_once("$CFG->dirroot/mod/chairman/chairman_meetingagenda/util/moodle_user_selector.php");
require_once("$CFG->dirroot/mod/chairman/chairman_meetingagenda/business/css/business.css");
require_once("$CFG->dirroot/mod/chairman/chairman_meetingagenda/util/ajax_lib.php");
require_once("$CFG->dirroot/lib/form/selectgroups.php");

$PAGE->requires->js("/mod/chairman/chairman_meetingagenda/business/js/business.js");

global $DB;

//Access to the content of this tab is valid only if an agenda is created
//An error is displayed, and loading stops if no agenda is created
if (!$agenda) {

    print '<center><h3>' . get_string('no_agenda', 'chairman') . '</h3></center>';
    return;
}

//------------SECURITY----------------------------------------------------------
//------------------------------------------------------------------------------
//Simple role cypher for code clarity
$role_cypher = array('1' => 'president', '2' => 'vice', '3' => "member", "4" => 'admin');

//check if user has a valid user role, otherwise give them the credentials of a guest
if (isset($user_role) && ($user_role == '1' || $user_role == '2' || $user_role == '3' || $user_role == '4' || is_siteadmin())) {
    $credentials = $role_cypher[$user_role];
} else {
    $credentials = "guest";
}


//------------LOADING SCREEN----------------------------------------------------
//------------------------------------------------------------------------------
//Apply loading screen if the parameters are stripped
//this only occurs when the form submits, or partially submit using moodleform
print '<script type="text/javascript">';
print 'if(document.location.href=="';
print $CFG->wwwroot . '/mod/chairman/chairman_meetingagenda/view.php"){';
print<<<HERE
$("#chairman_main").append('<center><img src="img/loading14.gif" alt="Loading..." /></center>');

}
</script>
HERE;


//Check if user should be able to edit/create the agenda
//Check that the agenda is not completed(completed agendas cannot be edited)
if ($credentials == 'president' || $credentials == 'vice' || $credentials == 'admin'|| is_siteadmin()) {

    minutes_editable($event_id, $agenda, $agenda_id, $chairman_id, $cm, $selected_tab);


//---------VIEW ONLY PERMISSIONS------------------------------------------------
//------------------------------------------------------------------------------
} elseif ($credentials == 'president' || $credentials == 'vice' || $credentials == 'admin' || $credentials == 'member' || is_siteadmin()) {

    minutes_viewonly($event_id, $agenda, $agenda_id, $chairman_id, $cm, $selected_tab);

    
}

export_pdf_dialog($event_id, $agenda->id, $chairman_id, $cm->instance, 1);



//----------------------------------------------------------------------------




/*
 * Prints contents of the minutes tab, with the ability to edit content
 *
 * @param int $event_id The ID for the current event of the agenda.
 * @param object $agenda The object representing the database entry for the current agenda.
 * @param int $agenda_id The ID for the current agenda.
 * @param int $chairman_id The ID for the current committee.
 * @param object $cm The course module object.
 * @param int $selected_tab The current tab for the minutes.
 *
 * 
 */
function minutes_editable($event_id, $agenda, $agenda_id, $chairman_id, $cm, $selected_tab){
global $DB, $CFG;

pdf_version($event_id);

    $topic_count = 0; //initalize as having zero topics

    if ($agenda) { // agenda already created
        //get actual count of topics of topics
        $topic_count = $DB->count_records('chairman_agenda_topics', array('chairman_agenda' => $agenda_id), '*', $ignoremultiple = false);
        $topic_count++; //Introducing one empty set of fields to add new topic
        //If no topics exist, then the database returns null, and we replace with zero topic count
        if (!$topic_count) {
            $topic_count = 0; //if no database items(null return) make count zero
        }
    }

//----------FORM OBJECT---------------------------------------------------------
    require_once('business_mod_form.php'); //Form for users that can view
    $mform = new mod_business_mod_form($event_id, $agenda_id, $chairman_id, $cm->instance);



//---------------CANCEL BUTTON PRESSED------------------------------------------
//------------------------------------------------------------------------------
    if ($mform->is_cancelled()) {
        //Do nothing
  chairman_basic_footer();
  redirect($CFG->wwwroot . '/mod/chairman/chairman_meetingagenda/view.php?event_id=' . $event_id . '&selected_tab=' . $selected_tab);
  

//---------------PARTIAL SUBMIT-------------------------------------------------
//------------------------------------------------------------------------------
    } elseif ($mform->no_submit_button_pressed()) {

//--------------ADD MOODLE USER BUTTON PRESSED----------------------------------
        update_moodle_users();
        update_guest_users();
        update_attendance($agenda_id); //update attendance

//--------------ADD PREVIOUS GUEST BUTTON PRESSED-------------------------------
//Within form a sql query is made to find all quests ever added in the current
//committee, and the user can select from them in a select menu
        if (isset($_REQUEST['add_prev_guest'])) {

            $dataString = $_REQUEST['prev_guests']; // selected previous guest
            $dataArray = explode("{x}", $dataString);//{x} delimites first/last names: firstname{x}lastname{x}email

            //Parts of name
            $firstname = $dataArray[0];
            $lastname = $dataArray[1];
            $email = $dataArray[2];

            conditionally_add_guest($agenda_id, $firstname, $lastname, $email);



//------------ADD NEW GUEST BUTTON PRESSED--------------------------------------
        } elseif (isset($_REQUEST['add_new_guest'])) {

            //Retrieve sent information
            $guest_firstname = trim($_REQUEST['guest_firstname']);
            $guest_lastname = trim($_REQUEST['guest_lastname']);
            $guest_email = trim($_REQUEST['guest_email']);
            
            conditionally_add_guest($agenda_id, $guest_firstname, $guest_lastname, $guest_email);
        }


        //Function to update current status, and status notes of committee members
        update_attendance($agenda_id);

        chairman_basic_footer();
        //Every Submit ultimatley causes a redirection to refresh page
        redirect($CFG->wwwroot . '/mod/chairman/chairman_meetingagenda/view.php?event_id=' . $event_id . '&selected_tab=' . $selected_tab);
//----------END PARTIAL SUBMIT--------------------------------------------------



//--------------------FULL SUBMIT-----------------------------------------------
//------------------------------------------------------------------------------
    } elseif ($fromform = $mform->get_data()) {

        //update moodle users
        update_moodle_users();
        update_guest_users();

//-----------General Saving-----------------------------------------------------


        //Save all topic updates
        updatetopics($cm);
        update_attendance($agenda_id); //update attendance

//----ADD/UPADTE MOTION BUTTON PRESSED------------------------------------------
        if (isset($_REQUEST['add_motion'])) { //If specific update button pressed
            addAndUpdate_Motion($event_id, $selected_tab,$agenda_id);
        }

        addAndUpdate_Motions($event_id, $selected_tab,$agenda_id);
 //Add to logs
 $event = $DB->get_record('chairman_events', array('id' => $event_id));
 add_to_log($cm->course, 'chairman', 'update', '', get_string('arising_issues', 'chairman') . ' - ' .  $event->summary , $cm->id);
chairman_basic_footer();
//Submit ultimatly ends up redirecting the user back to tab
redirect($CFG->wwwroot . '/mod/chairman/chairman_meetingagenda/view.php?event_id=' . $event_id . '&selected_tab=' . $selected_tab);


//-----------LOAD FORM----------------------------------------------------------
//------------------------------------------------------------------------------
        } else { //FRESH LOAD OF PAGE

        $toform = $mform->getDefault_toform();//Get Values
        $toform->event_id = $event_id;
        $toform->selected_tab = $selected_tab;

        //Add to logs
        $event = $DB->get_record('chairman_events', array('id' => $event_id));
        add_to_log($cm->course, 'chairman', 'view', '', get_string('arising_issues', 'chairman') . ' - ' .  $event->summary , $cm->id);
        $mform->set_data($toform); //Set values

        //Display Menu
        require_once("$CFG->dirroot/mod/chairman/chairman_meetingagenda/business/business_sidebar.php");

        //Display Form
        print '<div class="form">';
        $mform->display(false);
        print '</div>';
        
        }


}

/*
 * Prints contents of the minutes tab, with only viewing capabilities
 *
 * @param int $event_id The ID for the current event of the agenda.
 * @param object $agenda The object representing the database entry for the current agenda.
 * @param int $agenda_id The ID for the current agenda.
 * @param int $chairman_id The ID for the current committee.
 * @param object $cm The course module object.
 * @param int $selected_tab The current tab for the minutes.
 *
 *
 */
function minutes_viewonly($event_id, $agenda, $agenda_id, $chairman_id, $cm, $selected_tab){
global $DB, $CFG;

pdf_version($event_id);
require_once('business_mod_form_view.php'); //Form for users that can view
$mform = new mod_business_mod_form($event_id, $agenda_id, $chairman_id, $cm->instance);

$toform = $mform->getDefault_toform();
$toform->event_id = $event_id;
$toform->selected_tab = $selected_tab;
$mform->set_data($toform);

//Add to logs
 $event = $DB->get_record('chairman_events', array('id' => $event_id));
 add_to_log($cm->course, 'chairman', 'view', '', get_string('arising_issues', 'chairman') . ' - ' .  $event->summary , $cm->id);

//Display Menu
require_once("$CFG->dirroot/mod/chairman/chairman_meetingagenda/business/business_sidebar.php");
print '<div class="form">';
$mform->display(false);
print '</div>';

}


/*
 * The function converts the information subbmitted for attendance and creates
 * an object that is used to create a database entry.
 *
 * @param int $key The index signifying which attendance record we want to convert within the submitted arrays
 * @param int $attendance An int signifying if the person was present(0), absent(1), or unexcused absent(2)
 */
function attendance_toobject_conversion($key, $attendance) {
    $dataobject = new stdClass();

//    $note = null;
//    if (isset($_REQUEST["participant_status_notes"])) {
//        $notes = $_REQUEST["participant_status_notes"];
//
//
//        if (isset($notes[$key])) {
//            $note = $notes[$key];
//        }
//    }

    switch ($attendance) {
        case 'present'://Present
            $dataobject->absent = 0;
            $dataobject->unexcused_absence = NULL;
            //$dataobject->notes = NULL;

            break;

        case 'absent'://absent
            $dataobject->absent = 1;
            $dataobject->unexcused_absence = NULL;
            //$dataobject->notes = $note;

            //if ($dataobject->notes == "") {
                //$dataobject->notes = NULL;
            //}

            break;

        case 'unexcused'://unexcused absent
            $dataobject->absent = NULL;
            $dataobject->unexcused_absence = 1;
            //$dataobject->notes = $note;

            //if ($dataobject->notes == "") {
               // $dataobject->notes = NULL;
            //}

            break;

        default://Should never happen
            $dataobject->absent = NULL;
            $dataobject->unexcused_absence = NULL;
            //$dataobject->notes = NULL;

            break;
    }
    return $dataobject;
}


/*
 * This function updates all instances of attendance for committee members
 *
 * @param int $agenda_id The unique agenda ID for this Agenda.
 *
 */
function update_attendance($agenda_id) {

    global $DB; //Global Database Variable
    
    //Arrays of submitted data from form
    if(!isset($_REQUEST["participants_attendance"])) return;
    $participants_attend = $_REQUEST["participants_attendance"];
    
    //Foreach of the committtee ID(committee members)
    foreach ($participants_attend as $part_id => $attendance) {

        if ($DB->record_exists('chairman_agenda_attendance', array('chairman_agenda' => $agenda_id, 'chairman_members' => $part_id))) {
            //update record

            $old_record = $DB->get_record('chairman_agenda_attendance', array('chairman_agenda' => $agenda_id, 'chairman_members' => $part_id));

            $dataobject = attendance_toobject_conversion($part_id, $attendance);
            $dataobject->id = $old_record->id;


            $DB->update_record('chairman_agenda_attendance', $dataobject, $bulk = false);


        } else {//Attendance record is new

            

            //create attendance record
            $dataobject = attendance_toobject_conversion($part_id, $attendance);
            $dataobject->chairman_agenda = $agenda_id;
            $dataobject->chairman_members = $part_id;

            $DB->insert_record('chairman_agenda_attendance', $dataobject, $returnid = false, $bulk = false);

        }
    }//end for loop for committee members


}

/*
 * This function updates all instances of topics.
 *
 * @param object $cm An object representing the course module. We need this object to determine the instance of this course module.
 */
function updatetopics($cm) {
    global $USER, $DB;
    
    //Get submitted form information
    $topics = $_REQUEST['topic_ids'];
    $topics_notes = $_REQUEST['topic_notes'];
    $topics_statuses = $_REQUEST['topic_status'];
   // $topics_followup = $_REQUEST['follow_up'];
    $attachments = $_REQUEST['attachments'];
    $filearea_ids = $_REQUEST['topic_fileareaid'];

    //If at least one topic exists
    if ($topics) {

        //Itterate every topic that has an ID(ie. is already created)-- do an update
        foreach ($topics as $index => $topicid) {
            $note = $topics_notes[$index];
            $status = $topics_statuses[$index];
            //$followup = $topics_followup[$index];
            $modifiedtime = time();
            $modifiedby = $USER->id;

            $dataobject = new stdClass();
            $dataobject->id = $topicid;
            $dataobject->notes = $note;
            //$dataobject->follow_up = $followup;
            $dataobject->status = $status;
            $dataobject->modifiedby = $modifiedby;
            $dataobject->timemodified = $modifiedtime;

            //Double check topic exists in database
            if ($DB->record_exists('chairman_agenda_topics', array('id' => $topicid))) {
                $DB->update_record('chairman_agenda_topics', $dataobject, $bulk = false);
                $context = get_context_instance(CONTEXT_MODULE,$cm->id); 
                file_save_draft_area_files($attachments[$index], $context->id, 'mod_chairman', 'attachment', $filearea_ids[$index], array('subdirs' => 0, 'maxfiles' => 50));
            }
        }
    }
}


/*
 * This function adds/updates a SINGULAR motion depending on which update button was pressed.
 * Also adds new motion from that topic if exists.
 *
 * @param int $event_id The unique ID that uniquely identifies which event this agenda is created within.
 * @param int $selected_tab The ID for which tab we are currently on.
 *@param int $agenda_id The unique ID signifiying which Agenda we are currently in.
 */
function addAndUpdate_Motion($event_id, $selected_tab,$agenda_id) {
    global $USER, $DB, $CFG;

    //These are arrays -- each [index] is a different topic(based on submitted index), [subindex] is a different motion of that topic
    //Used parallel arrays to submit information from form, which isn't always the best way, but it works

    $topic_return = 1;

    $buttonPressed = $_REQUEST['add_motion']; //get button pressed
    if ($buttonPressed) {//double check if button was pressed
        if (isset($_REQUEST['motion_ids'])) {//check if there are any motions to update
            $motion_ids = $_REQUEST['motion_ids']; //motion id in table: chairman_agenda_motions
            $proposal_array = $_REQUEST['proposition']; //text detailing proposal
            $proposalby_array = $_REQUEST['proposed']; //member id (id in chairman_members table) of who gave motion
            $supportedby_array = $_REQUEST['supported']; //member id (id in chairman_members table) of who supported motion
            $result_array = $_REQUEST['motion_result']; //result of voting on motion
            $yesCount_array = $_REQUEST['aye']; //count of yes votes
            $noCount_array = $_REQUEST['nay']; //count of no votes
            $abstainCount_array = $_REQUEST['abs']; //count of abstains

            
            if (isset($_REQUEST['unanimous'])) { //checkboxes disappear if not checked
                $unanimous = $_REQUEST['unanimous'];
            }

        }
            //There will only be one item in array(one button pressed), but
            //nice way to get the array key, which represents which topic we are looking at ($index in business_mod_form.php)

            foreach ($buttonPressed as $index => $garbage) { //topic level
                $topic_return = $index; //used to get to <a> tag anchor(html) on page redirect

//------------UPDATING PREVIOUS MOTIONS-----------------------------------------

                if (isset($motion_ids[$index])) {
                    foreach ($motion_ids[$index] as $sub_index => $motionid) {//itterate through motions
                        if ($DB->record_exists('chairman_agenda_motions', array('id' => $motionid))) {

                            $dataobject = new stdClass();
                            $dataobject->id = $motionid;
                            $dataobject->motion = $proposal_array[$index][$sub_index];

                            //SELECTOR FOR MOTION BY
                            //The default value for empty is '-1', we replace this value with NULL
                            if ($proposalby_array[$index][$sub_index] == "-1") {
                                $dataobject->motionby = NULL;
                            } else {
                                $dataobject->motionby = $proposalby_array[$index][$sub_index];
                            }

                            //SELECTOR FOR SECONDED BY
                            //The default value for empty is '-1', we replace this value with NULL
                            if ($supportedby_array[$index][$sub_index] == "-1") {
                                $dataobject->secondedby = NULL;
                            } else {
                                $dataobject->secondedby = $supportedby_array[$index][$sub_index];
                            }

                            //RESULT SELECTOR
                            //The default value for empty is '-1', we replace this value with NULL
                            if ($result_array[$index][$sub_index] == "-1") {
                                $dataobject->carried = NULL;
                            } else {
                                $dataobject->carried = $result_array[$index][$sub_index];
                            }

                            //VOTE COUNTS
                            $dataobject->yea = $yesCount_array[$index][$sub_index];
                            $dataobject->nay = $noCount_array[$index][$sub_index];
                            $dataobject->abstained = $abstainCount_array[$index][$sub_index];
                            $dataobject->timemodified = time();
                            $dataobject->unanimous = NULL;

                            //check if checkbox is checked
                            if (isset($unanimous)) {
                                if (isset($unanimous[$index][$sub_index])) {

                                    $dataobject->unanimous = $unanimous[$index][$sub_index];
                                } else {
                                    $dataobject->unanimous = null;
                                }
                            } else {
                                $dataobject->unanimous = null;
                            }

                            //print_object($dataobject);
                            $DB->update_record('chairman_agenda_motions', $dataobject, $bulk = false);
                        }
                    }//end motion itterations
                }//--------------END UPDATES----------------
                //



//-------------------ADDING NEW MOTION FROM TOPIC-------------------------------
                //Get subbmitted information
                $proposal_array = $_REQUEST['proposition_new']; //text detailing proposal
                $proposalby_array = $_REQUEST['proposed_new']; //member id (id in chairman_members table) of who gave motion
                $supportedby_array = $_REQUEST['supported_new']; //member id (id in chairman_members table) of who supported motion
                $result_array = $_REQUEST['motion_result_new']; //result of voting on motion
                $yesCount_array = $_REQUEST['aye_new']; //count of yes votes
                $noCount_array = $_REQUEST['nay_new']; //count of no votes
                $abstainCount_array = $_REQUEST['abs_new']; //count of abstains
                $topicids_array = $_REQUEST['topic_ids'];

                if (isset($_REQUEST['unanimous_new'])) { //checkboxes disappear if not checked
                    $unanimous_array = $_REQUEST['unanimous_new'];
                }

                //$index specifies which topic we are currently adding this motion for
                $proposal = $proposal_array[$index];
                $proposalby = $proposalby_array[$index];
                $supportedby = $supportedby_array[$index];
                $result = $result_array[$index];
                $yesCount = $yesCount_array[$index];
                $noCount = $noCount_array[$index];
                $abstainCount = $abstainCount_array[$index];
                $topicid = $topicids_array[$index];

                //Checkbox for unanimous
                $unanimous = null;
                if (isset($unanimous_array[$index])) {
                   $unanimous = $unanimous_array[$index];
                }

                $proposal = trim("$proposal");

                //If proposal's name is nothinig, do not create record
                if($proposal!=""){

                   $dataobject = new stdClass();
                   $dataobject->chairman_agenda = $agenda_id;
                   $dataobject->chairman_agenda_topics = $topicid;
                   $dataobject->motion = $proposal;
                   $dataobject->motionby = replaceNegativeOneWithNull($proposalby);
                   $dataobject->secondedby = replaceNegativeOneWithNull($supportedby);
                   $dataobject->carried = replaceNegativeOneWithNull($result);
                   $dataobject->unanimous = $unanimous;
                   $dataobject->yea = $yesCount;
                   $dataobject->nay = $noCount;
                   $dataobject->abstained = $abstainCount;
                   $dataobject->timemodified= time();

                  $DB->insert_record('chairman_agenda_motions', $dataobject, $returnid=false, $bulk=false);

                }
            }//End topics
        
    }
    
    chairman_basic_footer();
   //Redirect back to topic achor point on page("#topic_" . $topic_return)
   redirect("$CFG->wwwroot/mod/chairman/chairman_meetingagenda/view.php?event_id=" . $event_id . "&selected_tab=" . $selected_tab . "#topic_" . $topic_return);
}

 /*
 * A function to replace our default value of '-1' with NULL for database input
 *
 * @param int $value If value is -1 return null, else return $value.
 *
 */
function replaceNegativeOneWithNull($value){

    if($value=='-1'){
        return NULL;
    } else {
        return $value;
    }

}
/*
 * This function adds/updates a ALL motions.
 * Also adds new motion from topics if applicable.
 *
 * @param int $event_id The unique ID that uniquely identifies which event this agenda is created within.
 * @param int $selected_tab The ID for which tab we are currently on.
 *@param int $agenda_id The unique ID signifiying which Agenda we are currently in.
 */
function addAndUpdate_Motions($event_id, $selected_tab,$agenda_id) {
    global $USER, $DB, $CFG;

    //These are arrays -- each [index] is a different topic(based on submitted index), [subindex] is a different motion of that topic
    //Used parallel arrays to submit information from form, which isn't always the best way, but it works

    $topic_return = 1;

            if (isset($_REQUEST['topic_ids'])) {//check if there are any motions to update
            $topic_ids = $_REQUEST['topic_ids'];


            if (isset($_REQUEST['motion_ids'])){
         $motion_ids = $_REQUEST['motion_ids']; //motion id in table: chairman_agenda_motions
        }

            //There will only be one item in array(one button pressed), but
            //nice way to get the array key, which represents which topic we are looking at ($index in business_mod_form.php)

            //print_object($_REQUEST);exit();

            foreach ($topic_ids as $index => $topic_id) { //topic level

             if (isset($motion_ids)){
            $topic_ids = $_REQUEST['topic_ids'];
            $proposal_array = $_REQUEST['proposition']; //text detailing proposal
            $proposalby_array = $_REQUEST['proposed']; //member id (id in chairman_members table) of who gave motion
            $supportedby_array = $_REQUEST['supported']; //member id (id in chairman_members table) of who supported motion
            $result_array = $_REQUEST['motion_result']; //result of voting on motion
            $yesCount_array = $_REQUEST['aye']; //count of yes votes
            $noCount_array = $_REQUEST['nay']; //count of no votes
            $abstainCount_array = $_REQUEST['abs']; //count of abstains


            if (isset($_REQUEST['unanimous'])) { //checkboxes disappear if not checked
                $unanimous = $_REQUEST['unanimous'];
            }

        

        


//------------UPDATING PREVIOUS MOTIONS-----------------------------------------

                if (isset($motion_ids[$index])) {
                    foreach ($motion_ids[$index] as $sub_index => $motionid) {//itterate through motions
                        if ($DB->record_exists('chairman_agenda_motions', array('id' => $motionid))) {

                           //print "[$index][$sub_index]-!".$result_array[2]."</br>";
                           

                           
                            $dataobject = new stdClass();
                            $dataobject->id = $motionid;
                            $dataobject->motion = $proposal_array[$index][$sub_index];

                            //SELECTOR FOR MOTION BY
                            //The default value for empty is '-1', we replace this value with NULL
                            if ($proposalby_array[$index][$sub_index] == "-1") {
                                $dataobject->motionby = NULL;
                            } else {
                                $dataobject->motionby = $proposalby_array[$index][$sub_index];
                            }

                            //SELECTOR FOR SECONDED BY
                            //The default value for empty is '-1', we replace this value with NULL
                            if ($supportedby_array[$index][$sub_index] == "-1") {
                                $dataobject->secondedby = NULL;
                            } else {
                                $dataobject->secondedby = $supportedby_array[$index][$sub_index];
                            }

                            //RESULT SELECTOR
                            //The default value for empty is '-1', we replace this value with NULL
                            if ($result_array[$index][$sub_index] == "-1") {
                                $dataobject->carried = NULL;
                            } else {
                                $dataobject->carried = $result_array[$index][$sub_index];
                            }

                            //VOTE COUNTS
                            $dataobject->yea = $yesCount_array[$index][$sub_index];
                            $dataobject->nay = $noCount_array[$index][$sub_index];
                            $dataobject->abstained = $abstainCount_array[$index][$sub_index];
                            $dataobject->timemodified = time();
                            $dataobject->unanimous = NULL;

                            //check if checkbox is checked
                            if (isset($unanimous)) {
                                if (isset($unanimous[$index][$sub_index])) {

                                    $dataobject->unanimous = $unanimous[$index][$sub_index];
                                } else {
                                    $dataobject->unanimous = null;
                                }
                            } else {
                                $dataobject->unanimous = null;
                            }

                            //print_object($dataobject);
                            $DB->update_record('chairman_agenda_motions', $dataobject, $bulk = false);
                        }
                    }//end motion itterations
                }//--------------END UPDATES----------------
                //
}


//-------------------ADDING NEW MOTION FROM TOPIC-------------------------------
                //Get subbmitted information
                $proposal_array = $_REQUEST['proposition_new']; //text detailing proposal
                $proposalby_array = $_REQUEST['proposed_new']; //member id (id in chairman_members table) of who gave motion
                $supportedby_array = $_REQUEST['supported_new']; //member id (id in chairman_members table) of who supported motion
                $result_array = $_REQUEST['motion_result_new']; //result of voting on motion
                $yesCount_array = $_REQUEST['aye_new']; //count of yes votes
                $noCount_array = $_REQUEST['nay_new']; //count of no votes
                $abstainCount_array = $_REQUEST['abs_new']; //count of abstains
                $topicids_array = $_REQUEST['topic_ids'];

                if (isset($_REQUEST['unanimous_new'])) { //checkboxes disappear if not checked
                    $unanimous_array = $_REQUEST['unanimous_new'];
                }

                //$index specifies which topic we are currently adding this motion for
                $proposal = $proposal_array[$index];
                $proposalby = $proposalby_array[$index];
                $supportedby = $supportedby_array[$index];
                $result = $result_array[$index];
                $yesCount = $yesCount_array[$index];
                $noCount = $noCount_array[$index];
                $abstainCount = $abstainCount_array[$index];
                $topicid = $topicids_array[$index];

                //Checkbox for unanimous
                $unanimous = null;
                if (isset($unanimous_array[$index])) {
                   $unanimous = $unanimous_array[$index];
                }

                $proposal = trim("$proposal");

                //If proposal's name is nothinig, do not create record
                if($proposal!=""){

                   $dataobject = new stdClass();
                   $dataobject->chairman_agenda = $agenda_id;
                   $dataobject->chairman_agenda_topics = $topic_id;
                   $dataobject->motion = $proposal;
                   $dataobject->motionby = replaceNegativeOneWithNull($proposalby);
                   $dataobject->secondedby = replaceNegativeOneWithNull($supportedby);
                   $dataobject->carried = replaceNegativeOneWithNull($result);
                   $dataobject->unanimous = $unanimous;
                   $dataobject->yea = $yesCount;
                   $dataobject->nay = $noCount;
                   $dataobject->abstained = $abstainCount;
                   $dataobject->timemodified= time();

                  $DB->insert_record('chairman_agenda_motions', $dataobject, $returnid=false, $bulk=false);

                }
            }//End topics
            }

  }

  /*
 * Prints a link to a script that will create agenda minutes for the given event.
 * This pdf is more detailed than the plain agenda PDF.
 *
 * @param int $event_id The ID for the event.
 *
 */
function pdf_version($event_id){
    global $CFG;

$url = "$CFG->wwwroot/mod/chairman/chairman_meetingagenda/util/pdf_script.php?event_id=" . $event_id;

//-------------------DOWNLOAD PDF VERSION OF AGENDA-----------------------------
output_export_pdf_image();
//------------------------------------------------------------------------------
}

function update_guest_users()
{
    global $DB, $agenda_id;
    $guests_ids = optional_param('guest_members', NULL, PARAM_RAW);
    
    $guests_ids = ($guests_ids == NULL) ? array() : $guests_ids;
    
    $sql = "SELECT * FROM {chairman_agenda_guests} WHERE chairman_agenda = ? AND moodleid IS NULL";
    $guest_members = $DB->get_records_sql($sql, array($agenda_id));
    
    //remove left out moodle users
    foreach ( $guest_members as $guest_member )
    {
        if(!in_array($guest_member->id, $guests_ids))
            $DB->delete_records('chairman_agenda_guests', array('id' => $guest_member->id));
    }
}

/**
 * 
 * Adds a guest if they don't already exist - same firstname, lastname , and email.
 * Will not add is username or lastname is empty
 * 
 * @param int $agenda_id
 * @param string $guest_firstname Guest first name
 * @param string $guest_lastname Guest last name
 * @param string $guest_email guest email
 */
function conditionally_add_guest($agenda_id, $guest_firstname, $guest_lastname, $guest_email) {
            global $DB;
            
                //Guest must have a non-empty first/last name
            if ($guest_firstname != "" && $guest_firstname != "") {

                //If they don't exist for this agenda, add them
                if (!$DB->record_exists('chairman_agenda_guests', array('chairman_agenda' => $agenda_id, 'firstname' => $guest_firstname, 'email' => $guest_email, 'lastname' => $guest_lastname, 'moodleid' => NULL))) {

                    $dataobject = new stdClass();
                    $dataobject->chairman_agenda = $agenda_id;
                    $dataobject->firstname = $guest_firstname;
                    $dataobject->lastname = $guest_lastname;
                    $dataobject->email = $guest_email;
                    $dataobject->moodleid = NULL; //GUESTS have no moodle id

                    $DB->insert_record('chairman_agenda_guests', $dataobject, $returnid = false, $bulk = false);
                }
            }
}

/**
 * Update external moodle users that are part of this meeting.
 * 
 * @global moodle_database $DB
 * @global int $agenda_id
 * 
 */
function update_moodle_users() {
    global $DB, $agenda_id;

    $user_list = optional_param('moodle_users', "", PARAM_SEQUENCE);
    $user_ids = array();

    
    //add new moodle users
    if (strlen($user_list) > 0) {

        $user_ids = explode(",", $user_list);
        
        foreach ($user_ids as $user_id) {
            $user = $DB->get_record('user', array('id' => $user_id));
            
            if ($user) { //Check if any user was valid
                if (!$DB->record_exists('chairman_agenda_guests', array('chairman_agenda' => $agenda_id, 'moodleid' => $user->id))) {
                    
                    $dataobject = new stdClass();
                    $dataobject->chairman_agenda = $agenda_id;
                    $dataobject->firstname = NULL; //NOT A GUEST, therefore null
                    $dataobject->lastname = NULL; //NOT A GUEST, therefore null
                    $dataobject->moodleid = $user->id; //NOT A GUEST, therefore CANNOT BE NULL

                    $DB->insert_record('chairman_agenda_guests', $dataobject, $returnid = false, $bulk = false);
                } 
            }
        }        
    }
    
    $sql = "SELECT * FROM {chairman_agenda_guests} WHERE chairman_agenda = ? AND moodleid IS NOT NULL";
    $moodle_members = $DB->get_records_sql($sql, array($agenda_id));
    
    //remove left out moodle users
    foreach ( $moodle_members as $moodle_member )
    {
        if(!in_array($moodle_member->moodleid, $user_ids))
            $DB->delete_records('chairman_agenda_guests', array('chairman_agenda' => $agenda_id, 'moodleid' => $moodle_member->moodleid));
    }
    
    
}
?>