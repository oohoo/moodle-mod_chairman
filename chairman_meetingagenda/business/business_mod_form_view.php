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
        global $DB, $CFG;

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
        conditionally_add_static($mform, $agenda->message, 'message', get_string('agenda_post_message', 'chairman'));
        conditionally_add_static($mform, $agenda->footer, 'footer', get_string('agenda_post_footer', 'chairman'));

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
        $chairmanMemberRecords = $DB->get_records('chairman_agenda_members', array('chairman_id' => $chairman_id, 'agenda_id' => $agenda_id), '', '*', $ignoremultiple = false);

//--------Comittee Members------------------------------------------------------
        if ($chairmanMemberRecords) {//If any committee members present
            generate_participants_multiselect($mform, $agenda_id, $chairmanMemberRecords);
        }
        
        $chairmanmembers = convert_members_to_select_options($chairmanMemberRecords);

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

//----------ADD New Moodle MEMBERS----------------------------------------------
//------------------------------------------------------------------------------
        $mform->addElement('static', "", '', "");


//-----------GUESTS-------------------------------------------------------------

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
                '1' => '<font color="#4AA02C">' . get_string('motion_accepted', 'chairman') . '</font>',
                '0' => get_string('motion_rejected', 'chairman'));
            $index = 1;
            foreach ($topics as $key => $topic) {

                $this->topicNames[$index] = $topic->title;
                $mform->addElement('html', "<a name=\"topic_$index\"></a>");
                $mform->addElement('header', 'mod-committee-topic_header', get_string('topics_header', 'chairman') . " " . $index);
                $mform->addElement('static', "", "", "<h3>$topic->title</h3>");

                $mform->addElement('text', 'topic_header_' . $index, '', array('class'=>"topic_header_text", 'style'=>"display:none"));
                $mform->setType('topic_header_'. $index, PARAM_TEXT);
                
                $header_id = 'topic_header_'.$index;
                $toform->$header_id = $topic->topic_header;
                
                //NOTES FOR TOPIC && DEFAULT VALUE FOR NOTES------------------------------------
                $mform->addElement('html', '</br>');

                $notes_htmlformated = format_text($topic->notes, $format = FORMAT_MOODLE, $options = NULL, $courseid_do_not_use = NULL);

                $mform->addElement('static', "topic_status_" . $index, get_string('topics_notes', 'chairman'), $notes_htmlformated);

                //------------------------------------------------------------------------------
                //STATUS OF TOPIC
                $mform->addElement('static', "topic_status[$index]", get_string('topic_status', 'chairman'), '');
                
                //Presentedby fields (text and member id)
                conditionally_add_static($mform, get_presentedby_static_value($topic), "presentedby[$index]", get_string('agenda_presentedby', 'chairman'));
                
                
                $mform->addElement('hidden', "topic_ids[$index]", $topic->id);
                $mform->setType('topic_ids', PARAM_INT);
                
                //$mform->addElement('static', "follow_up[$index]", get_string('topic_followup', 'chairman'), '');
                //-------FILE MANAGER -- VIEW ONLY----------------------------------------------
                $mform->addElement('filemanager', "attachments[" . $index . "]", get_string('attachments', 'chairman'), null, array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 10, 'accepted_types' => array('*')));

                $context = get_context_instance(CONTEXT_MODULE, $this->chairman_id);
                $draftitemid = file_get_submitted_draft_itemid('attachments[' . $index . "]");
                file_prepare_draft_area($draftitemid, $context->id, 'mod_chairman', 'attachment', $topic->filename, array('subdirs' => 0, 'maxfiles' => 50));
                $toform->attachments[$index] = $draftitemid;
//------------------------------------------------------------------------------

                $motions = $DB->get_records('chairman_agenda_motions', array('chairman_agenda' => $agenda_id, 'chairman_agenda_topics' => $topic->id), '', '*', $ignoremultiple = false);
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

                        $motion_htmlformated = format_text($motion->motion, $format = FORMAT_MOODLE, $options = NULL, $courseid_do_not_use = NULL);
                        $mform->addElement('static', "proposition[$index][$sub_index]", get_string('motion_proposal', 'chairman'), $motion_htmlformated);

                        $mform->addElement('static', "proposed[$index][$sub_index]", get_string('motion_by', 'chairman'), $proposing_choices, $attributes = null);
                        $mform->addElement('static', "supported[$index][$sub_index]", get_string('motion_second', 'chairman'), $supporting_choices, $attributes = null);
                        ;


                        $votes = array();
                        $votes[] = $mform->createElement('static', "aye_label[$index][$sub_index]", "", "Aye: ");
                        $votes[] = $mform->createElement('static', "aye[$index][$sub_index]", '', array('size' => '1 em', 'maxlength' => "3"));
                        $votes[] = $mform->createElement('static', "nay_label[$index][$sub_index]", "", "Nay: ");
                        $votes[] = $mform->createElement('static', "nay[$index][$sub_index]", '', array('size' => '1 em', 'maxlength' => "3"));
                        $votes[] = $mform->createElement('static', "abs_label[$index][$sub_index]", "", "Abs: ");
                        $votes[] = $mform->createElement('static', "abs[$index][$sub_index]", '', array('size' => '1 em', 'maxlength' => "3"));
                        $votes[] = $mform->createElement('static', "unanimous[$index][$sub_index]", '', "");

                        $mform->addGroup($votes, "motion_votes[$index][$sub_index]", get_string('motion_votes', 'chairman'), array(' '), false);

                        $results = array();
                        $results[] = $mform->createElement('static', "motion_result[$index][$sub_index]", '', $motion_result, $attributes = null);
                        $mform->addGroup($results, "result[$index][$sub_index]", get_string('motion_outcome', 'chairman'), array(' '), false);


                        $mform->addElement('hidden', "motion_ids[$index][$sub_index]", $motion->id);
                        $mform->setType('motion_ids', PARAM_INT);
                        $mform->addElement('html', "</div></div></div>");


//-------DEFAULT VALUEs FOR MOTIONS---------------------------------------------
//$toform->proposition[$index][$sub_index] = $motion->motion;

                        if (isset($motion->motionby)) {
                            $mform->setDefault("proposed[$index][$sub_index]", $proposing_choices[$motion->motionby]);
                        } else {
                            $mform->setDefault("proposed[$index][$sub_index]", "");
                        }

                        if (isset($motion->secondedby)) {
                            $mform->setDefault("supported[$index][$sub_index]", $supporting_choices[$motion->secondedby]);
                        } else {
                            $mform->setDefault("supported[$index][$sub_index]", "");
                        }

                        if (isset($motion->carried)) {
                            $mform->setDefault("motion_result[$index][$sub_index]", $motion_result[$motion->carried]);
                        } else {
                            $mform->setDefault("motion_result[$index][$sub_index]", "");
                        }


                        if (isset($motion->unanimous)) {

                            $toform->unanimous[$index][$sub_index] = "   (" . get_string('unanimous', 'chairman') . ")";
                        }

                        $toform->aye[$index][$sub_index] = $motion->yea;
                        $toform->nay[$index][$sub_index] = $motion->nay;
                        $toform->abs[$index][$sub_index] = $motion->abstained;

//-------------SET Highest vote as bolded---------------------------------------
                        if ($motion->yea > $motion->nay) {
                            if ($motion->yea > $motion->abstained) {
                                $toform->aye[$index][$sub_index] = "<b>" . $toform->aye[$index][$sub_index] . "</b>";
                                $toform->aye_label[$index][$sub_index] = "<b>Aye: </b>";
                            } elseif ($motion->abstained != $motion->yea) {
                                $toform->abs[$index][$sub_index] = "<b>" . $toform->abs[$index][$sub_index] . "</b>";
                                $toform->abs_label[$index][$sub_index] = "<b>Abs: </b>";
                            }
                        } else {
                            if ($motion->nay > $motion->abstained) {
                                $toform->nay[$index][$sub_index] = "<b>" . $toform->nay[$index][$sub_index] . "</b>";
                                $toform->nay_label[$index][$sub_index] = "<b>Nay: </b>";
                            } elseif ($motion->abstained != $motion->nay) {
                                $toform->abs[$index][$sub_index] = "<b>" . $toform->abs[$index][$sub_index] . "</b>";
                                $toform->abs_label[$index][$sub_index] = "<b>Abs: </b>";
                            }
                        }


                        $mform->addGroupRule("motion_votes[$index][$sub_index]", array("aye[$index][$sub_index]" => array(array(get_string('numeric_only', 'chairman'), 'numeric', null, 'client', false, true)),
                            "nay[$index][$sub_index]" => array(array(get_string('numeric_only', 'chairman'), 'numeric', null, 'client', false, true)),
                            "abs[$index][$sub_index]" => array(array(get_string('numeric_only', 'chairman'), 'numeric', null, 'client', false, true))
                        ));

//---------END DEFAULTS---------------------------------------------------------

                        $sub_index++;
                    }
                }//end if any motions exist
//-------DEFAULT TOPIC VALUEs---------------------------------------------------

                $toform->topic_notes[$index] = $topic->notes;

                if (isset($topic->status)) {
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
     * Returns An array of topic names with array keys being the index that the topic
     * is on the page. Used for menu sidebar creation.
     */

    function getIndexToNamesArray() {
        return $this->topicNames;
    }

}
