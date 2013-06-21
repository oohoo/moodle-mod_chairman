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

class mod_chairman_agenda_form_view extends moodleform {

    public $repeat_count; //Count of topics
    private $agenda_id;

//Constructor for extended moodleform
    function __construct($itterations, $agenda_id = null) {
        $this->repeat_count = $itterations;
        $this->agenda_id = $agenda_id;
        parent::__construct();
    }

    function definition() {

        global $CFG, $DB;
        $mform = & $this->_form;

        $agenda_id = $this->agenda_id;
        $agenda = $DB->get_record('chairman_agenda', array('id' => $agenda_id), '*', $ignoremultiple = false);

//---------GENERAL AGENDA INFORMATION-------------------------------------------
//------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('static', 'committee', get_string('committee_agenda', 'chairman'), "");
        $mform->addElement('static', 'date', get_string('date_agenda', 'chairman'), "");
        $mform->addElement('static', 'time', get_string('time_agenda', 'chairman'), "");
        $mform->addElement('static', 'duration', get_string('duration_agenda', 'chairman'), "");

//---------AGENDA Description And Summary---------------------------------------
//------------------------------------------------------------------------------

        if ($agenda) {
            conditionally_add_static($mform, $agenda->location, 'location', get_string('location_agenda', 'chairman'));

            $event_record = $DB->get_record('chairman_events', array('id' => $agenda->chairman_events_id), '*', $ignoremultiple = false);

            if (isset($event_record)) {
                conditionally_add_static($mform, $event_record->summary, 'summary', get_string('summary_agenda', 'chairman'));
                conditionally_add_static($mform, $event_record->description, 'description', get_string('desc_agenda', 'chairman'));
            }
            
            conditionally_add_static($mform, $agenda->message, 'message', get_string('agenda_post_message', 'chairman'));
            conditionally_add_static($mform, $agenda->footer, 'footer', get_string('agenda_post_footer', 'chairman'));
            
        }



//---------Agenda Topics--------------------------------------------------------
//------------------------------------------------------------------------------
//-----Repeating Topic Elements-------------------------------------------------
        $repeatno = $this->repeat_count;

        $repeatarray = array();
        $repeatarray[] = $mform->createElement('header', 'mod_committee_create_topics', get_string('create_topic', 'chairman') . ' {no}');
        $repeatarray[] = $mform->createElement('static', 'topic_title', get_string('title_agenda', 'chairman'), "");
        $repeatarray[] = $mform->createElement('static', 'duration_topic', get_string('duration_agenda', 'chairman'), "");
        $repeatarray[] = $mform->createElement('static', 'topic_description', get_string('desc_agenda', 'chairman'));

        $repeatarray[] = $mform->createElement('static', 'presentedby', get_string('agenda_presentedby', 'chairman'));
        
        $repeatarray[] = $mform->createElement('filemanager', 'attachments', get_string('attachments', 'chairman'), null, array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 10, 'accepted_types' => array('*')));

    $header_group = array();
    $header_group[] =& $mform->createElement('text', 'topic_header', '', array('class'=>"topic_header_text", 'style'=>"display:none"));
    $repeatarray[]= $mform->createElement('group', 'topic_header_group', '', $header_group);
    
    $mform->setType('topic_header_group', PARAM_RAW); 
        
// $repeatarray[] = $mform->createElement('static', 'spacer_', '',"----------------------------------------------------------------------------");
        $repeatarray[] = $mform->createElement('static', 'spacer_', '', "");

        $repeateloptions = array(); //No options
        $this->repeat_elements($repeatarray, $repeatno, $repeateloptions, 'option_repeats', 'refresh_page', 0, get_string('refresh_page', 'chairman'));

//Hidden Elements for information transfer between pages------------------------
//------------------------------------------------------------------------------
        $mform->addElement('hidden', 'is_editable', 'no');
        $mform->setType('is_editable', PARAM_RAW);
        $mform->addElement('hidden', 'created', 'no');
        $mform->setType('created', PARAM_RAW);
        $mform->addElement('hidden', 'event_id', '');
        $mform->setType('event_id', PARAM_RAW);
        $mform->addElement('hidden', 'selected_tab', '');
        $mform->setType('selected_tab', PARAM_RAW);
        $mform->addElement('hidden', 'delete_requested', 'no');
        $mform->setType('delete_requested', PARAM_RAW);
    }

}
