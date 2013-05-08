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

/*Current inherited variables:
*   CommiteeID/Instance?: $cm->instance
*   EventID: $event_id
*   CommiteeID as in tables: $chairman_id
*   Role: $user_role IF they are part of committee
*
*/

require_once("$CFG->dirroot/mod/chairman/chairman_meetingagenda/lib.php");
require_once("$CFG->dirroot/mod/chairman/chairman_meetingagenda/util/ajax_lib.php");

//-------------------SECURITY---------------------------------------------------
//------------------------------------------------------------------------------
//Simple role cypher for code clarity
$role_cypher = array('1' => 'president', '2' => 'vice', '3' => "member", "4" => 'admin');

//check if user has a valid user role, otherwise give them the credentials of a guest
if (isset($user_role) && ($user_role == '1' || $user_role == '2' || $user_role == '3' || $user_role == '4')) {
    $credentials = $role_cypher[$user_role];
} else {
    $credentials = "guest";
}
//------------------------------------------------------------------------------




//--------------------------------LOADING SCREEN-------------------------------
//------------------------------------------------------------------------------
//Apply loading screen if the parameters are stripped
//this only occurs when the form submits, or partially submit using moodleform
print '<script type="text/javascript">';
print 'if(document.location.href=="';
print $CFG->wwwroot . '/mod/chairman/chairman_meetingagenda/view.php"){';
print<<<HERE
document.write('<center><img src="img/loading14.gif" alt="Loading..." /></center>');

}
</script>
HERE;
//------------------------------------------------------------------------------



//-----------EDITING ACCESS-----------------------------------------------------
//------------------------------------------------------------------------------
//Check if user should be able to edit/create the agenda
//Check that the agenda is not completed(completed agendas cannot be edited)
if ($credentials == 'president' || $credentials == 'vice' || $credentials == 'admin'|| is_siteadmin()) {

   agenda_editable($agenda,$chairman_id,$event_id,$cm,$selected_tab,$agenda_id);

//-----USER IS A MEMBER OR AGENDA IS COMPLETED AND USER IS A PART OF COMMITTEE--
//-----------------------------------------------------------------------------
} elseif ($credentials == 'member') {

    agenda_viewonly($agenda,$chairman_id,$event_id,$cm,$selected_tab,$agenda_id);

} else {

    //Not part of the committee in any way!
    
}


//-------------------FUNCTIONS--------------------------------------------------
//------------------------------------------------------------------------------


/*
 * Prints a link to a script that will create agenda for the given event.
 *
 * @param int $event_id The ID for the event.
 *
 */
function pdf_version($event_id){
    global $CFG;

$url = "$CFG->wwwroot/mod/chairman/chairman_meetingagenda/util/pdf_script.php?event_id=" . $event_id;
$url .= '&plain_pdf=1';


print<<<HERE
<style type='text/css'>
#chairman_save_pdf {
float: right;
vertical-align:top;
}
</style>
HERE;

//-------------------DOWNLOAD PDF VERSION OF AGENDA-----------------------------
print '<div id="chairman_save_pdf">';
print '<form action="'.$url.'">';
print '<span style="display:inline-block; vertical-align:top; text-align: center;">'.get_string('save_pdf','chairman').":<br>";
print '<input type="image" id="save_image" SRC="../pix/pdf_icon.gif" VALUE="Submit now"/></span>';
print '<input type="hidden" name="event_id" value="'.$event_id.'"/>';
print '<input type="hidden" name="plain_pdf" value="1"/>';


print '</form>';
print '</div>';
//------------------------------------------------------------------------------
}

/*
 * Displays agenda content for an admin committee user
 *
 * @param object $agenda The database entry for current agenda -- Can Be NULL
 * @param int $chairman_id The ID for the current chairman
 * @param int $event_id THe ID for the current event
 * @param object $cm The course module object.
 * @param int $selected_tab The ID of the current tab
 * @param int $agenda_id The ID for the current agenda -- Can be NULL
 *
 */
function agenda_editable($agenda,$chairman_id,$event_id,$cm,$selected_tab,$agenda_id){
    global $DB,$USER,$CFG;


    $topic_count = 0; //initalize as having zero topics

    if ($agenda) { // agenda already created

		//get actual count of topics of topics
        $topic_count = $DB->count_records('chairman_agenda_topics', array('chairman_agenda' => $agenda_id), '*', $ignoremultiple = false);
        $topic_count++; //Introducing one empty set of fields to add new topic

		//If no topics exist, then the database returns null, and we replace with zero topic count
        if (!$topic_count) {
            $topic_count = 0;//if no database items(null return) make count zero
        }
    } else {
      create_agenda($chairman_id,$event_id);
    }


//Form class for editing/updating form -- for users that can edit page
require_once('agenda_mod_form.php');

//Create new object, with topic count as parameter
$mform = new mod_chairman_agenda_form($topic_count,$agenda_id); //One empty field


//----------PARTIAL SUBMIT------------------------------------------------------
//------------------------------------------------------------------------------
    if ($mform->no_submit_button_pressed()) {

//-----------------REMOVE AGENDA BUTTON PRESSED-----------------
        if (isset($_REQUEST['remove_agenda'])) {

            //Simple html form containing a confirm delete button, and applicable warnings of doing so
            print '<center><h3><font color="red">' . get_string('remove_agenda_warning', 'chairman') . '</h3></br>';
            print '<h4>' . get_string('remove_agenda_warning_subscript', 'chairman') . '</h4></font></br>';
            print '<form action="agenda/delete_agenda.php">';
            print '<input type="submit" value="Confirm Removal" name="removal_confirmed"/>';
            print '<input type="hidden" name="event_id" id="event_id" value="' . $event_id . '"/>';
            print '</form></center>';


            return; //Ignore all following content.
        }




//------------REMOVE TOPIC BUTTON PRESSED---------------------------------------
        $topic_id = optional_param('topic_id',0,PARAM_INT);//$_REQUEST['topic_id']; //Get topic_id from hidden field

        if (isset($_REQUEST['remove_topic'])) { //Hidden field identifier, that is set when button pressed

            //$_REQUEST is an array, so use foreach for convience only -- will only be one item in array
            foreach ($_REQUEST['remove_topic'] as $key => $removeButton) {

                if (isset($removeButton)) { //double check if pressed(not really needed)


                    //If the topic exists in database, delete it
                    if ($DB->record_exists('chairman_agenda_topics', array('id' => $topic_id[$key]))) {
                        $DB->delete_records('chairman_agenda_topics', array('id' => $topic_id[$key]));
                        $DB->delete_records('chairman_agenda_motions', array('chairman_agenda_topics' => $topic_id[$key]));
                    }
                }
            }
        }

//-----------ADD TOPIC BUTTON PRESSED------------------------------------------
        if (isset($_REQUEST['option_add_fields'])) {

            //Retrieve all relevant information from form elements
            $topic_title = $_REQUEST['topic_title'];
            $duration_topic = $_REQUEST['duration_topic'];
            $topic_description = $_REQUEST['topic_description'];
            $topic_files = $_REQUEST['attachments'];

//for each topic_id that is returned in array
            foreach ($_REQUEST['topic_id'] as $key => $topic_id) {

                if ($topic_id == '') { //if the topic_id is empty, then it is not in the database
                    $topic_object = new stdClass(); //object representing new database row
                    $topic_object->chairman_agenda = $agenda_id;
                    $topic_object->title = $topic_title[$key];
                    $topic_object->description = $topic_description[$key];
                    $topic_object->duration = $duration_topic[$key];
                    $topic_object->notes = NULL;
                    $topic_object->follow_up = NULL;
                    $topic_object->follow_up = NULL;
                    $topic_object->hidden = 0;
                    $topic_object->status = 'open';
                    $topic_object->modifiedby = $USER->id;
                    $topic_object->timemodified = time();
                    $topic_object->timecreated = time();

                    //Set up files for topic
                    //search database to find first unused filename ID for the current instance of this course plugin
                    $count = 1;
                    $sql = "SELECT * FROM {chairman_agenda} ca, {chairman_agenda_topics} cat ".
                    "WHERE ca.id = cat.chairman_agenda AND ca.chairman_id = ? AND cat.filename = ?";


                    //Search for open filenameID
                    while ($DB->record_exists_sql($sql,array($chairman_id,$count))) {
                        $count++;
                    }

                    //Assign unused filename id to this new topic
                    $topic_object->filename = $count;

                    //prepare file area for topic
                    file_save_draft_area_files($topic_files[$key], $cm->instance, 'mod_chairman', 'attachment', $count, array('subdirs' => 0, 'maxfiles' => 50));



                //Save topic to database
                    $DB->insert_record('chairman_agenda_topics', $topic_object, $returnid = true, $bulk = false);

                }
            }
        }

        //Anytime a parital submit button is pressed, ultimatly you are redirected back to agenda
        redirect($CFG->wwwroot . '/mod/chairman/chairman_meetingagenda/view.php?event_id=' . $event_id . '&selected_tab=' . $selected_tab);





//---------------PRESSED CANCEL BUTTON FORM----------------------------------
//---------------------------------------------------------------------------
		} elseif ($mform->is_cancelled()) {
		//Simply reload the page -- effectively killing all changes they made
        redirect($CFG->wwwroot . '/mod/chairman/chairman_meetingagenda/view.php?event_id=' . $event_id . '&selected_tab=' . $selected_tab);
//---------------------------------------------------------------------------



//---------------FULL SUBMIT-----------------------------------------------------
//-------------------------------------------------------------------------------
	} elseif ($fromform = $mform->get_data()) {


	// if agenda object from database(called within view.php exists -- then we are updating)
        if ($agenda) {

            //These items are all arrays. ex. Array of ids, descriptions, title, durations...
            $topic_ids = $fromform->topic_id;
            $topic_descriptions = $fromform->topic_description;
            $topic_title = $fromform->topic_title;
            $topic_duration = $fromform->duration_topic;

            //for each topic_id
            foreach ($topic_ids as $key => $topic_id) {
                
                    if (empty($topic_id)){
                        $topic_id = 0;
                    }
                if ($DB->record_exists('chairman_agenda_topics', array('id' => $topic_id))) { //Topic Exists
                    $record = $DB->get_record('chairman_agenda_topics', array('id' => $topic_id));//get topic record

                    if ($record) { //count represents filename_id, if exists then use the one from the form(that is from database)
                        $count = $record->filename;
                    } else {
                        $count = 0; // otherwise it is a new topic, and something has gone terribly wrong(should never happen)
                    }

                    //Get all updated information from form elements
                    $topic_object = new stdClass();
                    $topic_object->id = $topic_id;
                    $topic_object->description = $topic_descriptions[$key];
                    $topic_object->title = $topic_title[$key];
                    $topic_object->duration = $topic_duration[$key];
                     $topic_object->timemodified = time();
                    $topic_object->modifiedby = $USER->id;


                    //Update topic
                    $DB->update_record('chairman_agenda_topics', $topic_object, $bulk = false);

                    //Set up files for each topic
                    //print_object($cm);
                    //print_object($fromform);

                    //Save state of files draft area
                    file_save_draft_area_files($fromform->attachments[$key], $cm->instance, 'mod_chairman', 'attachment', $count, array('subdirs' => 0, 'maxfiles' => 50));

                    //Topic doesn't exist and name has been changed from Default "New Topic", therefore we add to database
                } elseif ($topic_id == "" && $topic_title[$key] != get_string('topic_title_default', 'chairman')) {
                    $topic_object = new stdClass();
                    $topic_object->chairman_agenda = $agenda_id;
                    $topic_object->title = $topic_title[$key];
                    $topic_object->description = $topic_descriptions[$key];
                    $topic_object->duration = $topic_duration[$key];
                    $topic_object->notes = NULL;
                    $topic_object->filename = NULL;
                    $topic_object->follow_up = NULL;
                    $topic_object->hidden = 0;
                    $topic_object->status = 'open';
                    $topic_object->modifiedby = $USER->id;
                    $topic_object->timemodified = time();
                    $topic_object->timecreated = time();

                    //Set up files for topic
                    //Find first unused fileID for current instance of course plugin
                    //Set up files for topic
                    //search database to find first unused filename ID for the current instance of this course plugin
                    $count = 1;
                    $sql = "SELECT * FROM {chairman_agenda} ca, {chairman_agenda_topics} cat ".
                    "WHERE ca.id = cat.chairman_agenda AND ca.chairman_id = ? AND cat.filename = ?";

                    while ($DB->record_exists_sql($sql,array($chairman_id,$count))) {
                        $count++;
                    }

                    //Assign fileID to object
                    $topic_object->filename = $count;

                    //Updating record, and file draft area state
                    $DB->insert_record('chairman_agenda_topics', $topic_object, $returnid = true, $bulk = false);
                    file_save_draft_area_files($fromform->attachments[$key], $cm->instance, 'mod_chairman', 'attachment', $count, array('subdirs' => 0, 'maxfiles' => 50));
                }
            } // end topic updating

        //If record exists then update location(redudent check, but added for clarity)
            if ($DB->record_exists('chairman_agenda', array('id' => $agenda->id))) {
                $agenda_object = new stdClass();
                $agenda_object->id = $agenda->id;
                $agenda_object->location = $fromform->location;
                 $topic_object->timemodified = time();


                $DB->update_record('chairman_agenda', $agenda_object, $bulk = false);
            }

            //send email notification
            if (isset($fromform->notify_now) && $fromform->notify_now == 1){
                //get meeting
                $meeting = $DB->get_record('chairman_events',array('id' => $event_id));
                //get president
                $president = $DB->get_record('chairman_members', array('chairman_id' => $chairman_id, 'role_id' => 1));
                //email information
                $from = $DB->get_record('user', array('id' => $president->user_id));
                $subject = get_string('notify_reminder', 'chairman').$meeting->summary;
                $emailmessage = get_string('notify_week_message', 'chairman').'<p>'.$meeting->description.'</p>';
                //get all member emails.
                $members = $DB->get_records('chairman_members', array('chairman_id' => $chairman_id));

                $i=0;

                foreach ($members as $member){
                    $user = $DB->get_record('user',array('id' => $member->user_id));
                    $message[$i] = str_replace('{a}', "$user->firstname", $emailmessage);
                    $message[$i] = str_replace('{c}', "$meeting->summary", $message[$i]);
                    $message[$i] = str_replace('{b}', "$meeting->day/$meeting->month/$meeting->year", $message[$i]);
                    $i++;
                    email_to_user($user, $from, $subject, $message[$i]);
                }
            }

        //agenda exists -- update information
        } else {

            //agenda doesn't exist -- create agenda!

            //----NOTE:----------------------
            //The design for this page originally had the user create the agenda
            //by click a create button, allowing events to be created without agendas.

            //The design specs where changed, that eliminating this feature.

        }

//--------COMPLETE AGENDA BUTTON PRESSED----------------------------------------
        //Redirect to event page
        if(isset($_REQUEST['complete_agenda'])){
        redirect($CFG->wwwroot . '/mod/chairman/chairman_events/events.php?id=' . $chairman_id);

        }

        redirect($CFG->wwwroot . '/mod/chairman/chairman_meetingagenda/view.php?event_id=' . $event_id . '&selected_tab=' . $selected_tab);


//--------------LOAD FORM----------------------------------------------------
//---------------------------------------------------------------------------
    } else { //No button pressed aka fresh load: load form.



        $toform = new stdclass(); //Form object
        $toform->is_editable = 'yes'; //Since we are in editing, set hidden field to yes

        if ($agenda) {
            $toform->created = 'yes'; //If agenda exists, change hidden field to yes
        }

        $url = "<a href=$CFG->wwwroot/mod/chairman/chairman_event/edit_event.php?id=$chairman_id&event_id=$event_id>Change/Edit</a>";

        $toform->edit_url = $url;

	//get record for event agenda is attached to
        $event_record = $DB->get_record('chairman_events', array('id' => $event_id), '*', $ignoremultiple = false);

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




//


//----Description ------------------------------------------------------
        //Committee has already been called in view.php, and is still a valid object but we re-queried for code claridy
        $chairman = $DB->get_record("chairman", array("id" => $cm->instance)); // get chairman record for our instance
        $chairman_event = $DB->get_record('chairman_events', array('id' => $event_id), '*', $ignoremultiple = false);

        $toform->committee = $chairman->name;
        $toform->summary = $chairman_event->summary;
        $toform->description = $chairman_event->description;


        $toform->event_id = $event_id;
        $toform->selected_tab = $selected_tab;

        //--Every Agenda Must have these 2 topic -- they are standard




        if ($agenda) { //data specific to when the agenda exists

//-------------------IMAGE TO DOWNLOAD PDF VERSION OF AGENDA--------------------
pdf_version($event_id);
//------------------------------------------------------------------------------

            $toform->created = 'yes';
            $toform->location = $agenda->location;
            $topics = $DB->get_records('chairman_agenda_topics', array('chairman_agenda' => $agenda_id), $sort = 'timecreated ASC', $fields = '*', $limitfrom = 0, $limitnum = 0);


            $index = 0;

            // print_object($topics);

            foreach ($topics as $topic) { //For each topic

                $toform->topic_title[$index] = $topic->title;
                $toform->duration_topic[$index] = $topic->duration;
                $toform->topic_description[$index] = $topic->description;
                $toform->topic_id[$index] = $topic->id;

                //Set up files for each topic
                if (!isset($toform->id)) {
                    $toform->id = null;
                }

                if ($topic->filename) {
                    $entry = $topic->filename;
                } else {
                    $entry = 0;
                }



                $draftitemid = file_get_submitted_draft_itemid('attachments[' . $index . "]");
                file_prepare_draft_area($draftitemid, $cm->instance, 'mod_chairman', 'attachment', $entry, array('subdirs' => 0, 'maxfiles' => 50));
                $toform->attachments[$index] = $draftitemid;



                $index++;
            }
            //SupplyDefault Value to New Topic
            $toform->topic_title[$index] = get_string('topic_title_default', 'chairman');
        }


        $mform->set_data($toform);
        $mform->display();

print '<input type="hidden" name="base_url" value="'.$CFG->wwwroot.'"/>';
print '<input type="hidden" name="courseid" value="'.$chairman->course.'"/>';

    }

}

/*
 * Displays agenda content for an normal committee member
 *
 * @param object $agenda The database entry for current agenda -- Can Be NULL
 * @param int $chairman_id The ID for the current chairman
 * @param int $event_id THe ID for the current event
 * @param object $cm The course module object.
 * @param int $selected_tab The ID of the current tab
 * @param int $agenda_id The ID for the current agenda -- Can be NULL
 *
 */
function agenda_viewonly($agenda,$chairman_id,$event_id,$cm,$selected_tab,$agenda_id){
    global $DB,$USER,$CFG;

    if (!$agenda) {

        print '<center><h3>' . get_string('no_agenda', 'chairman') . '</h3></center>';
        return;
    }


//-------------------IMAGE TO DOWNLOAD PDF VERSION OF AGENDA--------------------
pdf_version($event_id);
//------------------------------------------------------------------------------

    //Is set when the refresh button is pressed (from repeater form elements) -- want to do full refresh
    if (isset($_REQUEST['refresh_page'])) {
    redirect($CFG->wwwroot . '/mod/chairman/chairman_meetingagenda/view.php?event_id=' . $event_id . '&selected_tab=' . $selected_tab);

    }

     $topic_count = 0;
        $topic_count = $DB->count_records('chairman_agenda_topics', array('chairman_agenda' => $agenda_id), '*', $ignoremultiple = false);

        if (!$topic_count) { //if no database items(null return) make count zero
            $topic_count = 0;
        }



require_once('agenda_mod_form_view.php'); //Form for users that can view
    $mform = new mod_chairman_agenda_form_view($topic_count,$agenda_id); //One empty field



     $toform = new stdclass();
        $toform->is_editable = 'yes';

        if ($agenda) {
            $toform->created = 'yes';
        }


        $event_record = $DB->get_record('chairman_events', array('id' => $event_id), '*', $ignoremultiple = false);

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


        $toform->duration = formatTime($durationInSecs);

        //----Description ------
        //Committee has already been called in view.php, and is still a valid object but we re-queried for code claridy
        $chairman = $DB->get_record("chairman", array("id" => $cm->instance)); // get chairman record for our instance
        $chairman_event = $DB->get_record('chairman_events', array('id' => $event_id), '*', $ignoremultiple = false);

        $toform->committee = $chairman->name;
        $toform->summary = $chairman_event->summary;
        $toform->description = $chairman_event->description;


        $toform->event_id = $event_id;
        $toform->selected_tab = $selected_tab;

        //--Every Agenda Must have these 2 topic -- they are standard




        if ($agenda) {
            $toform->created = 'yes';
            $toform->location = $agenda->location;
            $topics = $DB->get_records('chairman_agenda_topics', array('chairman_agenda' => $agenda_id), $sort = 'timecreated ASC', $fields = '*', $limitfrom = 0, $limitnum = 0);

            $index = 0;

            // print_object($topics);

            foreach ($topics as $topic) {

                $toform->topic_title[$index] = $topic->title;
                $toform->duration_topic[$index] = $topic->duration;
                $toform->topic_description[$index] = $topic->description;
                $toform->topic_id[$index] = $topic->id;

                //Set up files for each topic
                if (!isset($toform->id)) {
                    $toform->id = null;
                }

                if ($topic->filename) {
                    $entry = $topic->filename;
                } else {
                    $entry = 0;
                }

                //print $entry;

//print_object($mform->attributes);
                $draftitemid = file_get_submitted_draft_itemid('attachments[' . $index . "]");
                file_prepare_draft_area($draftitemid, $cm->instance, 'mod_chairman', 'attachment', $entry, array('subdirs' => 1, 'maxfiles' => 50));
                $toform->attachments[$index] = $draftitemid;

//print $cm->instance . " " . $entry . " " . $draftitemid . '</br>';

                $index++;
            }
            //SupplyDefault Value to New Topic
            $toform->topic_title[$index] = get_string('topic_title_default', 'chairman');
        }




        $mform->set_data($toform);
        $mform->display();

print '<input type="hidden" name="base_url" value="'.$CFG->wwwroot.'"/>';
print '<input type="hidden" name="courseid" value="'.$chairman->course.'"/>';


}


/*
 * Creates an agenda for the event given.
 *
 * @param int $chairman_id The id for the Committee that the agenda will be addded to.
 * @param int $event_id The id for the event that the agenda will be added to.
 * @param string $location The optional description for the location of the agenda meeting.
 */
function create_agenda($chairman_id,$event_id,$location=""){
    global $DB,$USER,$CFG;

    $agenda_object = new stdClass();
            $agenda_object->chairman_id = $chairman_id;
            $agenda_object->chairman_events_id = $event_id;
            $agenda_object->location =$location;
            $agenda_object->room_reservation_id = 0; //Agenda is not complete

            $agenda_id = $DB->insert_record('chairman_agenda', $agenda_object, $returnid = true, $bulk = false);


            if ($agenda_id) { //check that agenda was correctly created


//------------Snapshot of members-----------------------------------------------
//------------------------------------------------------------------------------

//We create a snapshot of committee members for agenda, from the current state
//of the chairman_members table entries for the committee
$commityRecords = $DB->get_records('chairman_members', array('chairman_id' => $chairman_id), '', '*', $ignoremultiple = false);

  if ($commityRecords) {//If any committee members present

            foreach ($commityRecords as $member) {
               $dataobject = new stdClass();
               $dataobject->chairman_id = $chairman_id;
               $dataobject->user_id = $member->user_id;
               $dataobject->role_id = $member->role_id;
               $dataobject->agenda_id = $agenda_id;

              $DB->insert_record('chairman_agenda_members', $dataobject, $bulk = false);
            }

  }



//------------DEFAULT TOPICS---------------------------------------------------
//-----------------------------------------------------------------------------


                //default topic 1: Accept Agenda
                //Set up files for topic
		//search database to find first unused filename ID for the current instance of this course plugin
                    $count = 1;
                    $sql = "SELECT * FROM {chairman_agenda} ca, {chairman_agenda_topics} cat ".
                    "WHERE ca.id = cat.chairman_agenda AND ca.chairman_id = ? AND cat.filename = ?";

                    //Find find the first unused filename id
                    while ($DB->record_exists_sql($sql,array($chairman_id,$count))) {
                        $count++;
                    }

                //Create topic object
                 $topic_object = new stdClass();
                $topic_object->chairman_agenda = $agenda_id;
                $topic_object->title = get_string('topic_agenda_title', 'chairman');
                $topic_object->description = get_string('topic_agenda_description', 'chairman');
                $topic_object->duration = get_string('topic_agenda_duration', 'chairman');
                $topic_object->notes = NULL;
                $topic_object->filename = $count;
                $topic_object->follow_up = NULL;
                $topic_object->hidden = 1;
                $topic_object->status = 'closed';
                $topic_object->modifiedby = $USER->id;
                $topic_object->timemodified = time();
                $topic_object->timecreated = time();

                $DB->insert_record('chairman_agenda_topics', $topic_object, $returnid = true, $bulk = false);


                //Set up files for topic
		//search database to find first unused filename ID for the current instance of this course plugin
                    $count = 1;
                    $sql = "SELECT * FROM {chairman_agenda} ca, {chairman_agenda_topics} cat ".
                    "WHERE ca.id = cat.chairman_agenda AND ca.chairman_id = ? AND cat.filename = ?";

                    while ($DB->record_exists_sql($sql,array($chairman_id,$count))) {
                        $count++;
                    }


                //default topic 2: Previous Minutes
                $topic_object = new stdClass();
                $topic_object->chairman_agenda = $agenda_id;
                $topic_object->title = get_string('topic_min_title', 'chairman');
                $topic_object->description = get_string('topic_min_description', 'chairman');
                $topic_object->duration = get_string('topic_min_duration', 'chairman');
                $topic_object->notes = NULL;
                $topic_object->filename = $count;
                $topic_object->follow_up = NULL;
                $topic_object->hidden = 1;
                $topic_object->status = 'closed';
                $topic_object->modifiedby = $USER->id;
                $topic_object->timemodified = time();
                $topic_object->timecreated = time();

               
                $DB->insert_record('chairman_agenda_topics', $topic_object, $returnid = true, $bulk = false);
            }
            
print<<<HERE
<script type="text/javascript">
document.write('<center><img src="img/loading14.gif" alt="Loading..." /></center>');
</script>
HERE;

            redirect("$CFG->wwwroot/mod/chairman/chairman_meetingagenda/view.php?event_id=".$event_id);
}
?>