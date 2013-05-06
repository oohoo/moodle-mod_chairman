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
require_once("$CFG->dirroot/mod/chairman/meetingagenda/lib.php");
require_once("$CFG->dirroot/mod/chairman/meetingagenda/rooms_avaliable_form.php");

class mod_chairman_agenda_form extends moodleform {

private $_instance;
public $repeat_count;
private $agenda_id;

function __construct($itterations,$agenda_id = null){
$this->repeat_count = $itterations;
$this->agenda_id = $agenda_id;
parent::__construct();
}


function definition() {
$mform =& $this->_form;

global $DB;

$agenda_id = $this->agenda_id;
		
		

//---------GENERAL AGENDA INFORMATION-------------------------------------------
//------------------------------------------------------------------------------
$mform->addElement('header', 'mod-committee-general', get_string('general', 'form'));
$mform->addElement('static', 'committee', get_string('committee_agenda', 'chairman'),"");
$mform->addElement('static', 'date', get_string('date_agenda', 'chairman'),"");
$mform->addElement('static', 'time', get_string('time_agenda', 'chairman'),"");
$mform->addElement('static', 'duration', get_string('duration_agenda', 'chairman'),"");


$mform->addElement('text', 'location', get_string('location_agenda', 'chairman'),"");
$mform->setType('location', PARAM_TEXT);

$agenda = $DB->get_record('chairman_agenda', array('id' => $agenda_id), '*', $ignoremultiple = false);

if($agenda){
$event_record = $DB->get_record('chairman_events', array('id' => $agenda->chairman_events_id), '*', $ignoremultiple = false);

//----CHECK FOR SCHEDULER PLUGIN -----------------------------------------------
 $dbman = $DB->get_manager();
$table = new xmldb_table('roomscheduler_reservations');
$scheduler_plugin_installed = $dbman->table_exists($table);

if ($scheduler_plugin_installed && $event_record->room_reservation_id > 0) {   //plugin exists
 $mform->addElement('static','scheduler_element',get_string('room_scheduler','chairman'), '<div id="booked_location"></div>');
}
 //----------------------------------------------------------------------------



 
if(isset($event_record)){
 conditionally_add_static($mform, $event_record->summary, 'summary', get_string('summary_agenda', 'chairman'));
conditionally_add_static($mform, $event_record->description, 'description', get_string('desc_agenda', 'chairman'));

}

}


//---------AGENDA Description And Summary---------------------------------------
//------------------------------------------------------------------------------
$mform->addElement('static', 'edit_url','');

//---------Agenda Topics--------------------------------------------------------
//------------------------------------------------------------------------------
$mform->addElement('header', 'mod-committee-create_topics', get_string('create_topics', 'chairman'));
$repeatno = $this->repeat_count;

//-----Repeating Topic Elements-------------------------------------------------
$repeatarray=array();
    $repeatarray[] = $mform->createElement('text', 'topic_title', get_string('title_agenda', 'chairman'),array('size'=>'80'));
    $mform->setType('topic_title', PARAM_TEXT);
    $repeatarray[] = $mform->createElement('text', 'duration_topic', get_string('duration_agenda', 'chairman'),"");
    $mform->setType('duration_topic', PARAM_TEXT);
    $repeatarray[] = $mform->createElement('textarea', 'topic_description', get_string('desc_agenda', 'chairman'), 'wrap="virtual" rows="5" cols="80"');
    $repeatarray[] = $mform->createElement('filemanager', 'attachments', get_string('attachments', 'chairman'), null,array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 10, 'accepted_types' => array('*')) );
    $submit = &$mform->createElement('submit', 'remove_topic', get_string('remove_topic','chairman'));
    $submit->setLabel('&nbsp;');
    $repeatarray[]=$submit;
    $repeatarray[] = $mform->createElement('hidden', 'topic_id', '');
    $mform->setType('topic_id', PARAM_TEXT);
    //$repeatarray[] = $mform->createElement('static', 'spacer_', '',"----------------------------------------------------------------------------");
    $repeateloptions = array();
    $this->repeat_elements($repeatarray, $repeatno, $repeateloptions, 'option_repeats','option_add_fields', 1, get_string('add_topic','chairman'));

//-----Conditional Rules--------------------------------------------------------
 $mform->disabledIf('option_add_fields', 'created', 'eq', 'no');
 $mform->disabledIf('submitbutton', 'is_editable', 'eq', 'no');
 $mform->disabledIf('option_add_fields', 'is_editable', 'eq', 'no');
 
 for($i=0;$i<$repeatno;$i++){
 $mform->disabledIf('topic_title['.$i.']', 'is_editable', 'eq', 'no');
 $mform->disabledIf('duration_topic['.$i.']', 'is_editable', 'eq', 'no');
 $mform->disabledIf('topic_description['.$i.']', 'is_editable', 'eq', 'no');
 $mform->disabledIf('userfile['.$i.']', 'is_editable', 'eq', 'no');
 $mform->registerNoSubmitButton('remove_topic['.$i.']');
 $mform->addRule('topic_title['.$i.']', null, 'required', null, 'client');

 }

 //----Remove Buttons-----------------------------------------------------------
$mform->registerNoSubmitButton('remove_topic');
$mform->registerNoSubmitButton('remove_agenda');
$mform->registerNoSubmitButton('remove_agenda');


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




$mform->addElement('html', '<br>');

//Add send email to members
$mform->addElement('checkbox','notify_now', get_string('sendnotificationnow', 'chairman'));
$mform->addHelpButton('notify_now','sendnotificationnow','chairman');
$mform->addElement('html', '<br>');
//$this->add_action_buttons();
$buttonarray=array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        $buttonarray[] =& $mform->createElement('submit', 'complete_agenda', get_string('complete_agenda','chairman'));

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);


//-----Conditional Rules--------------------------------------------------------
$mform->disabledIf('remove_agenda', 'is_editable', 'eq', 'no');
$mform->disabledIf('remove_agenda', 'created', 'eq', 'no');
$mform->registerNoSubmitButton('remove_agenda');

$mform->disabledIf('complete_agenda', 'is_editable', 'eq', 'no');
$mform->disabledIf('complete_agenda', 'created', 'eq', 'no');

//------------Remove Agenda-----------------------------------------------------

$mform->addElement('html', '<br><br><br><br>');
$buttonarray=array();
$buttonarray[] =& $mform->createElement('submit', 'remove_agenda', get_string('remove_agenda','chairman'));

$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);


}


}
