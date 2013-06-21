<?php

/**
 * *************************************************************************
 * *                                Chairman                              **
 * *************************************************************************
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
 * *************************************************************************
 * ************************************************************************ */
defined('MOODLE_INTERNAL') || die();


require_once("$CFG->libdir/formslib.php");
require_once("$CFG->dirroot/mod/chairman/chairman_meetingagenda/util/moodle_user_selector.php");
require_once("$CFG->dirroot/mod/chairman/chairman_meetingagenda/lib.php");
require_once("$CFG->dirroot/mod/chairman/chairman_meetingagenda/util/ajax_lib.php");

class mod_business_mod_form extends moodleform {

    private $instance;
    private $event_id;
    private $agenda_id;
    private $chairman_id;
    private $default_toform;
    private $topicNames; //Used by menu to determine which html anchor points are to what name

    /*
     * Extended Constructor for moodleform
     *
     * @param int $event_id Event ID
     * @param int $agenda_id Agenda ID
     * @param int $chairman_id chairman ID
     * @param object $cm Course Module Object
     */

    function __construct($event_id, $agenda_id, $chairman_id, $cm) {
        $this->event_id = $event_id;
        $this->agenda_id = $agenda_id;
        $this->chairman_id = $chairman_id;
        $this->instance = $cm;
        parent::__construct();
    }

    /*
     * Function that determines form layout, and default values.
     */

    function definition() {

        global $DB, $CFG; //Required Globals
        $mform = & $this->_form;

//$toform will contain all default values for form objects
        $toform = new stdClass();

//Exclusion IDs for members in committee, so they don't appear in moodle user selector
        $exclusion_id = array();

//-- variable convience--
        $event_id = $this->event_id;
        $agenda_id = $this->agenda_id;
        $chairman_id = $this->chairman_id;
        $instance = $this->instance;

        $agenda = $DB->get_record('chairman_agenda', array('id' => $agenda_id), '*', $ignoremultiple = false);
        $event_record = $DB->get_record('chairman_events', array('id' => $event_id), '*', $ignoremultiple = false);

//-------GENERAL INFORMATION----------------------------------------------------
//------------------------------------------------------------------------------
        $mform->addElement('header', 'mod-committee-general', get_string('general', 'form'));
        $mform->addElement('static', 'committee', get_string('committee_agenda', 'chairman'), "");
        $mform->addElement('static', 'date', get_string('date_agenda', 'chairman'), "");
        $mform->addElement('static', 'time', get_string('time_agenda', 'chairman'), "");
        $mform->addElement('static', 'duration', get_string('duration_agenda', 'chairman'), "");

        conditionally_add_static($mform, $agenda->location, 'location', get_string('location_agenda', 'chairman'));



        conditionally_add_static($mform, $event_record->summary, 'summary', get_string('summary_agenda', 'chairman'));
        conditionally_add_static($mform, $event_record->description, 'description', get_string('desc_agenda', 'chairman'));


        $mform->addElement('static', 'edit_url', '');

//---------EDIT URL-------------------------------------------------------------
        $url = "<a href=$CFG->wwwroot/mod/chairman/chairman_events/edit_event.php?id=$chairman_id&event_id=$event_id>Change/Edit</a>";
        $toform->edit_url = $url;

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

//----Description -------------------------------------------------------------
//chairman has already been called in view.php, and is still a valid object but we re-queried for code clarity
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

        $mform->addElement('header', 'mod-committee-participants_header', get_string('participants_header', 'chairman'));

//Current Records
        $commity_members = $DB->get_records('chairman_agenda_members', array('chairman_id' => $chairman_id, 'agenda_id' => $agenda_id), '', '*', $ignoremultiple = false);

//--------Comittee Members------------------------------------------------------
        $chairmanmembers = convert_members_to_select_options($commity_members);
        
        if ($commity_members) {//If any committee members present
            generate_participants_multiselect($mform, $agenda_id, $commity_members);
        }


//------------MOODLE USERS------------------------------------------------------

        $sql = "SELECT * FROM {chairman_agenda_guests} WHERE chairman_agenda = ? AND moodleid IS NOT NULL";
        $moodle_members = $DB->get_records_sql($sql, array($agenda_id), $limitfrom = 0, $limitnum = 0);

        $idList = "";
        //For each moodle user create Number, Name, Remove Image
        foreach ($moodle_members as $moodle_user) {
            if (strlen($idList) > 0)
                $idList.= ",";

            $idList.= $moodle_user->moodleid;
        }

        $mform->addElement('html', "<table width='100%'><td><h4>" . get_string('moodle_members', 'chairman') . '</h4></td>');
        $mform->addElement('html', "<td><input type='hidden' width='100%' class='moodle_users_selector' name='moodle_users' id='id_moodle_users' value='$idList'></td></tr>");
        $mform->setType('moodle_users', PARAM_SEQUENCE);

//-----------GUESTS-------------------------------------------------------------
//------------------------------------------------------------------------------

        $sql = "SELECT * FROM {chairman_agenda_guests} WHERE chairman_agenda = ? AND moodleid IS NULL";
        $guests = $DB->get_records_sql($sql, array($agenda_id));

        $multiselect = "<select id='id_guest_members' name='guest_members[]' class='guest_members' multiple>";

        foreach ($guests as $guest) {
            $email = ($guest->email == '') ? '' : "(" . $guest->email . ")";
            $guest_display = $guest->firstname . " " . $guest->lastname . "   " . $email;
            $multiselect.= "<option selected='selected' value='$guest->id'>" . $guest_display . "</option>";
        }

        $multiselect .= "</select>";

        $mform->addElement('html', "<tr><td><h4>" . get_string('guest_members', 'chairman') . '</h4></td>');
        $mform->addElement('html', "<td>$multiselect</td></tr></table>");

//-----------ADD GUESTS---------------------------------------------------------
//------------------------------------------------------------------------------
        $mform->addElement('static', "", '', "");
        // $mform->addElement('static', "", '', "----------------------------------");
        //Previous Guests!

        $sql = "SELECT DISTINCT cag.id, cag.firstname,cag.lastname, cag.email FROM {chairman_agenda_guests} cag,{chairman_events} ce,{chairman_agenda} ca WHERE cag.moodleid IS NULL " .
                "AND cag.chairman_agenda = ca.id AND " .
                "ca.chairman_id = ce.chairman_id AND " .
                "ca.chairman_events_id = ce.id AND " .
                "ce.chairman_id = ?"
        ;

        //Get all previous guests for this committee
        $prev_guests = $DB->get_records_sql($sql, array($chairman_id), $limitfrom = 0, $limitnum = 0);

        if ($prev_guests) {

            $FORUM_TYPES = array();

            //Create dropdown options for previous guests
            foreach ($prev_guests as $guest) {
                $email = ($guest->email == '') ? '' : "(" . $guest->email . ")";
                $FORUM_TYPES[$guest->firstname . "{x}" . $guest->lastname . "{x}" . $guest->email] = $guest->firstname . " " . $guest->lastname . "   " . $email;
            }

            $add_prev_guest = array();
            $add_prev_guest[] = $mform->createElement('select', 'prev_guests', '', $FORUM_TYPES, $attributes = null);
            $add_prev_guest[] = $mform->createElement('submit', 'add_prev_guest', get_string('add'));
            $mform->addGroup($add_prev_guest, "prev_guest_group", get_string('add_previous_guest', 'chairman'), array(' '), false);
            $mform->registerNoSubmitButton('add_prev_guest');
        }
        $mform->addElement('static', "", "", '');

        $mform->addElement('static', "", "<b>" . get_string('add_new_guest', 'chairman') . "</b>", '');

        $addmembergroup = array();
        $addmembergroup[] = & $mform->createElement('static', "", '&nbsp;', get_string('add_new_guest_first', 'chairman'));
        $addmembergroup[] = & $mform->createElement('text', 'guest_firstname', get_string('add_new_guest_first', 'chairman'), "");

        $addmembergroup[] = & $mform->createElement('static', "", '&nbsp;', '&nbsp;');

        $addmembergroup[] = & $mform->createElement('static', "", '&nbsp;', get_string('add_new_guest_last', 'chairman'));
        $addmembergroup[] = & $mform->createElement('text', 'guest_lastname', get_string('add_new_guest_last', 'chairman'), "");

        $mform->addGroup($addmembergroup, 'guest_member_name', '', ' ', false);

        $addmembergroup = array();

        $addmembergroup[] = & $mform->createElement('static', "", '&nbsp;', get_string('add_new_guest_email', 'chairman'));
        $addmembergroup[] = & $mform->createElement('static', "", '&nbsp;', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
        $addmembergroup[] = & $mform->createElement('text', 'guest_email', get_string('add_new_guest_email', 'chairman'), "");



        $mform->addGroup($addmembergroup, 'guest_member_email', '', ' ', false);

        $mform->addElement('submit', 'add_new_guest', get_string('add'));
        $mform->registerNoSubmitButton('add_new_guest');

        $mform->setType('guest_email', PARAM_EMAIL);
        $mform->setType('guest_firstname', PARAM_TEXT);
        $mform->setType('guest_lastname', PARAM_TEXT);

        $mform->registerNoSubmitButton('add_new_guest');

//---------TOPICS---------------------------------------------------------------
//------------------------------------------------------------------------------

        $topics = $DB->get_records('chairman_agenda_topics', array('chairman_agenda' => $agenda_id), $sort = 'topic_order ASC, timecreated ASC', '*', $ignoremultiple = false);

        if ($topics) { //check if any topics actually exist
            //possible topic status:
            $topic_statuses = array('open' => get_string('topic_open', 'chairman'),
                'in_progress' => get_string('topic_inprogress', 'chairman'),
                'closed' => get_string('topic_closed', 'chairman'));
            //possible motion status
            $motion_result = array('-1' => '-----',
                '1' => get_string('motion_accepted', 'chairman'),
                '0' => get_string('motion_rejected', 'chairman'));

            $index = 1;
            //For each topic print required elements
            foreach ($topics as $key => $topic) {

                $this->topicNames[$index] = $topic->title;
                $mform->addElement('html', "<a name=\"topic_$index\"></a>");
                $mform->addElement('header', 'mod-committee-topic_header', get_string('topics_header', 'chairman') . " " . $index);
                $mform->addElement('static', "", "&nbsp;", "<h3>$topic->title</h3>");

                /*
                 * Hidden text for dynamic headings
                 */
                $mform->addElement('text', 'topic_header_' . $index, '', array('class'=>"topic_header_text", 'style'=>"display:none"));
                $mform->setType('topic_header_'. $index, PARAM_TEXT);
                         
                $header_id = 'topic_header_'.$index;
                $toform->$header_id = $topic->topic_header;
                
                //NOTES FOR TOPIC
                $mform->addElement('htmleditor', "topic_notes[$index]", '&nbsp;', array('cols' => '100%'));
                //STATUS OF TOPIC
                $mform->addElement('html', '</br>');
                
                
                
                conditionally_add_static($mform, get_presentedby_static_value($topic), "presentedby[$index]", get_string('agenda_presentedby', 'chairman'));
                
                $mform->addElement('select', "topic_status[$index]", get_string('topic_status', 'chairman'), $topic_statuses, $attributes = null);
                $mform->setType('topic_status', PARAM_TEXT);
                
                
                conditionally_add_static($mform, $event_record->description, 'description', get_string('desc_agenda', 'chairman'));
                
                $mform->addElement('hidden', "topic_ids[$index]", $topic->id);
                $mform->setType('topic_ids', PARAM_INT);
                //$mform->addElement('text', "follow_up[$index]", get_string('topic_followup', 'chairman'), array('size'=>'50'));
                //-------FILE MANAGER ----------------------------------------------------------
                $mform->addElement('filemanager', "attachments[$index]", get_string('attachments', 'chairman'), null, array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 10, 'accepted_types' => array('*')));

                $context = get_context_instance(CONTEXT_MODULE, $this->chairman_id);
                $draftitemid = file_get_submitted_draft_itemid('attachments[' . $index . "]");
                file_prepare_draft_area($draftitemid, $context->id, 'mod_chairman', 'attachment', $topic->filename, array('subdirs' => 0, 'maxfiles' => 50));
                $toform->attachments[$index] = $draftitemid;
//------------------------------------------------------------------------------
                $mform->addElement('hidden', "topic_fileareaid[" . $index . "]", $topic->filename);
                $mform->setType('topic_fileareaid', PARAM_TEXT);

//Get motions for this topic
                $motions = $DB->get_records('chairman_agenda_motions', array('chairman_agenda' => $agenda_id, 'chairman_agenda_topics' => $topic->id), '', '*', $ignoremultiple = false);

//Break
                $mform->addElement('html', "</br></br>");

//-----MOTIONS------------------------------------------------------------------
//------------------------------------------------------------------------------
                if ($motions) {

                    $sub_index = 1;
                    foreach ($motions as $key => $motion) {

                        $proposing_choices = $chairmanmembers;
                        $supporting_choices = $chairmanmembers;

                        $proposing_choices['-1'] = get_string('proposedby', 'chairman');
                        $supporting_choices['-1'] = get_string('supportedby', 'chairman');

                        $mform->addElement('html', "<div class='fitem'>");
                        $mform->addElement('html', "<div class='felement motion_accordian' id='motion_accordian_" . $index . "_" . $sub_index . "'><h3>" . get_string('motion', 'chairman') . " $index.$sub_index</h3>");
                        $mform->addElement('html', "<div id='motion_header_" . $index . "_" . $sub_index . "'>");

                        $mform->addElement('htmleditor', "proposition[$index][$sub_index]", '&nbsp;', array('cols' => '100%'));


                        $member_support = array();
                        $member_support[] = $mform->createElement('select', "proposed[$index][$sub_index]", '', $proposing_choices, $attributes = null);
                        $member_support[] = $mform->createElement('select', "supported[$index][$sub_index]", '', $supporting_choices, $attributes = null);
                        ;
                        $mform->addGroup($member_support, "member_support[$index][$sub_index]", get_string('motion_support', 'chairman'), array(' '), false);
                        $mform->setType('proposed', PARAM_INT);
                        $mform->setType('supported', PARAM_INT);

                        $votes = array();
                        $votes[] = $mform->createElement('static', "", "", "Aye: ");
                        $votes[] = $mform->createElement('text', "aye[$index][$sub_index]", '', array('size' => '3', 'maxlength' => "3"));
                        $mform->setType('aye', PARAM_INT);
                        $votes[] = $mform->createElement('static', "", "", "Nay: ");
                        $votes[] = $mform->createElement('text', "nay[$index][$sub_index]", '', array('size' => '3', 'maxlength' => "3"));
                        $mform->setType('nay', PARAM_INT);
                        $votes[] = $mform->createElement('static', "", "", "Abs: ");
                        $votes[] = $mform->createElement('text', "abs[$index][$sub_index]", '', array('size' => '3', 'maxlength' => "3"));
                        $mform->setType('abs', PARAM_INT);
                        $votes[] = $mform->createElement('checkbox', "unanimous[$index][$sub_index]", '', " " . get_string('unanimous', 'chairman'));
                        $mform->setType('unanimous', PARAM_INT);

                        $mform->addGroup($votes, "motion_votes[$index][$sub_index]", get_string('motion_votes', 'chairman'), array(' '), false);

                        $results = array();
                        $results[] = $mform->createElement('select', "motion_result[$index][$sub_index]", '', $motion_result, $attributes = null);

                        $mform->addGroup($results, "result[$index][$sub_index]", get_string('motion_outcome', 'chairman'), array(' '), false);



                        $mform->addElement('hidden', "motion_ids[$index][$sub_index]", $motion->id);
                        $mform->setType('motion_ids', PARAM_INT);
                        $mform->addElement('html', "</div></div></div>");

//-------DEFAULT VALUEs FOR MOTIONS---------------------------------------------
                        $toform->proposition[$index][$sub_index] = $motion->motion;

                        if (isset($motion->motionby)) {
                            $mform->setDefault("proposed[$index][$sub_index]", "$motion->motionby");
                        } else {
                            $mform->setDefault("proposed[$index][$sub_index]", '-1');
                        }

                        if (isset($motion->secondedby)) {
                            $mform->setDefault("supported[$index][$sub_index]", "$motion->secondedby");
                        } else {
                            $mform->setDefault("supported[$index][$sub_index]", '-1');
                        }

                        if (isset($motion->carried)) {
                            $mform->setDefault("motion_result[$index][$sub_index]", "$motion->carried");
                        } else {
                            $mform->setDefault("motion_result[$index][$sub_index]", '-1');
                        }


                        if (isset($motion->unanimous)) {

                            $toform->unanimous[$index][$sub_index] = $motion->unanimous;
                        }

                        $toform->aye[$index][$sub_index] = $motion->yea;
                        $toform->nay[$index][$sub_index] = $motion->nay;
                        $toform->abs[$index][$sub_index] = $motion->abstained;
//------------------------------------------------------------------------------

                        $mform->addGroupRule("motion_votes[$index][$sub_index]", array("aye[$index][$sub_index]" => array(array(get_string('numeric_only', 'chairman'), 'numeric', null, 'client', false, true)),
                            "nay[$index][$sub_index]" => array(array(get_string('numeric_only', 'chairman'), 'numeric', null, 'client', false, true)),
                            "abs[$index][$sub_index]" => array(array(get_string('numeric_only', 'chairman'), 'numeric', null, 'client', false, true))
                        ));

//---------END DEFAULTS---------------------------------------------------------

                        $sub_index++;
                    }
                }//end if any motions exist
//-------DEFAULT TOPIC VALUEs---------------------------------------------------
//Delete the \r\n of the note to avoid problem on the display in the WYSIWYG
                $topic->notes = str_replace("\r\n", "", $topic->notes);
                $toform->topic_notes[$index] = $topic->notes;

                if (isset($topic->status)) {
                    $toform->topic_status[$index] = $topic->status;
                } else {
                    $toform->topic_status[$index] = 'open';
                }

//$toform->follow_up[$index] = $topic->follow_up;
//---------END DEFAULTS---------------------------------------------------------
//-----NEW MOTION---------------------------------------------------------------
//------------------------------------------------------------------------------

                $proposing_choices = $chairmanmembers;
                $supporting_choices = $chairmanmembers;

                $proposing_choices['-1'] = get_string('proposedby', 'chairman');
                $supporting_choices['-1'] = get_string('supportedby', 'chairman');
//$mform->addElement('static', "", "", "----------------------------------");

                $mform->addElement('html', "<div class='fitem'>");
                $mform->addElement('html', "<div class='felement' id='motion_new_accordian_$index'><h3>" . get_string('new_motion', 'chairman') . "</h3>");
                $mform->addElement('html', "<div id='motion_new_header_$index'>");

//$mform->addElement('text', "proposition_new[$index]", get_string('motion_proposal', 'chairman'), array('size'=>'50'));
                $mform->addElement('htmleditor', "proposition_new[$index]", '&nbsp;', array('cols' => '100%'));


                $member_support = array();
                $member_support[] = $mform->createElement('select', "proposed_new[$index]", '', $proposing_choices, $attributes = null);
                $member_support[] = $mform->createElement('select', "supported_new[$index]", '', $supporting_choices, $attributes = null);
                ;
                $mform->addGroup($member_support, "member_support_new[$index]", get_string('motion_support', 'chairman'), array(' '), false);


                $votes = array();
                $votes[] = $mform->createElement('static', "", "", get_string('topic_vote_aye', 'chairman'));
                $votes[] = $mform->createElement('text', "aye_new[$index]", '', array('size' => '3', 'maxlength' => "3"));
                $mform->setType('aye_new', PARAM_TEXT);
                $votes[] = $mform->createElement('static', "", "", get_string('topic_vote_nay', 'chairman'));
                $votes[] = $mform->createElement('text', "nay_new[$index]", '', array('size' => '3', 'maxlength' => "3"));
                $mform->setType('nay_new', PARAM_TEXT);
                $votes[] = $mform->createElement('static', "", "", get_string('topic_vote_abs', 'chairman'));
                $votes[] = $mform->createElement('text', "abs_new[$index]", '', array('size' => '3', 'maxlength' => "3"));
                $mform->setType('abs_new', PARAM_TEXT);
                $votes[] = $mform->createElement('checkbox', "unanimous_new[$index]", '', " " . get_string('unanimous', 'chairman'));

                $mform->addGroup($votes, "motion_votes_new[$index]", get_string('motion_votes', 'chairman'), array(' '), false);

                $results = array();
                $results[] = $mform->createElement('select', "motion_result_new[$index]", '', $motion_result, $attributes = null);
                $mform->addGroup($results, "result_new[$index]", get_string('motion_outcome', 'chairman'), array(' '), false);


                $mform->addGroupRule("motion_votes_new[$index]", array("aye_new[$index]" => array(array(get_string('numeric_only', 'chairman'), 'numeric', null, 'client', false, true)),
                    "nay_new[$index]" => array(array(get_string('numeric_only', 'chairman'), 'numeric', null, 'client', false, true)),
                    "abs_new[$index]" => array(array(get_string('numeric_only', 'chairman'), 'numeric', null, 'client', false, true))
                ));

                $mform->addElement('html', '</div></div></div><br/>');

                $submit = &$mform->addElement('submit', "add_motion[$index]", get_string('add_update_motions', 'chairman'));
                $submit->setLabel('&nbsp;');

                $mform->setDefault("motion_result_new[$index]", '-1');
                $mform->setDefault("supported_new[$index]", '-1');
                $mform->setDefault("proposed_new[$index]", '-1');



                $index++;
            }//end foreach topic
        }//end topics
//--------HIDDEN ELEMENTS-------------------------------------------------------
        $mform->addElement('hidden', 'event_id', '');
        $mform->setType('event_id', PARAM_TEXT);
        $mform->addElement('hidden', 'selected_tab', '');
        $mform->setType('selected_tab', PARAM_TEXT);
        $mform->addElement('hidden', 'base_url', "$CFG->wwwroot");
        $mform->setType('base_url', PARAM_TEXT);
        $mform->addElement('hidden', 'courseid', "$chairman->course");
        $mform->setType('courseid', PARAM_INT);
//------------------------------------------------------------------------------


        $this->add_action_buttons();

        //Assign our default values to a private variable
        $this->default_toform = $toform;
    }

    /*
     * Returns the default values for the form.
     */

    function getDefault_toform() {
        return $this->default_toform;
    }

    /*
     * Returns An array of topic names with array keys being the index that the topic
     * is on the page. Used for menu sidebar creation.
     */

    function getIndexToNamesArray() {
        return $this->topicNames;
    }

}
