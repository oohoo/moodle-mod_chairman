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
/**
 * A script to envoke the creation of the pdf version of the agenda.
 *
 *
 * @package   Agenda/Meeting Extension to Committee Module
 * @copyright 2011 Dustin Durand
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config.php');
require_once('../../lib_chairman.php');
require_once("../lib.php");


$event_id = required_param('event_id', PARAM_INT); // event ID
$plain_pdf = required_param('plain_pdf', PARAM_INT); // event ID

global $DB, $PAGE, $USER;

$agenda = null;

//If Event ID provided
if ($event_id) {
    //Check if agenda created
    $agenda = $DB->get_record('chairman_agenda', array('chairman_events_id' => $event_id), '*', $ignoremultiple = false);
    if ($agenda) {
        $chairman_id = $agenda->chairman_id;
        $event_id = $agenda->chairman_events_id;
        $agenda_id = $agenda->id;

        //No Agenda Created
    } else {

        $chairman_event = $DB->get_record('chairman_events', array('id' => $event_id), '*', $ignoremultiple = false);

        if ($chairman_event) {
            $chairman_id = $chairman_event->chairman_id;
            $event_id = $chairman_event->id;

            //NO EVENT
        } else {
            print_error('You must create an meeting agenda from an event.');
        }
    }

//No EVENT ID
} else {
    print_error('You must create an meeting agenda from an event.');
}



chairman_check($chairman_id);

//Get Course Module Object
$cm = get_coursemodule_from_id('chairman', $chairman_id); //get course module
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

//Get chairman Object
$chairman = $DB->get_record("chairman", array("id" => $cm->instance));

//Get Credentials for this user
if ($current_user_record = $DB->get_record("chairman_members", array("chairman_id" => $chairman_id, "user_id" => $USER->id))) {
    $user_role = $current_user_record->role_id;
} elseif (is_siteadmin($USER)) {
    $user_role = 4; //If site admin => Admin committee profile
}

//Simple cypher for code clarity
$role_cypher = array('1' => 'president', '2' => 'vice', '3' => "member", "4" => 'admin');

//check if user has a valid user role, otherwise give them the credentials of a guest
if (isset($user_role) && ($user_role == '1' || $user_role == '2' || $user_role == '3' || $user_role == '4')) {
    $credentials = $role_cypher[$user_role];
} else {
    $credentials = "guest";
}

if ($credentials == 'president' || $credentials == 'vice' || $credentials == 'admin' || $credentials == 'member') {

    $save = optional_param("local_save", false, PARAM_TEXT);
    $save_security = optional_param("pdf_save_type", "private", PARAM_TEXT);
    $export_type = optional_param("export_pdf_type", "download", PARAM_TEXT);
    $export_link_security = optional_param("export_email_type", "private", PARAM_TEXT);

    require_once("$CFG->dirroot/mod/chairman/chairman_meetingagenda/util/pdf.php");
    $pdf = new pdf_creator($event_id, $agenda_id, $chairman_id, $cm->instance);
    $pdf->create_pdf($plain_pdf);

    //check if want to save
    if ($save)
        save_pdf($credentials, $save_security, $context, $pdf);

    //check if save
    if ($export_type == 'download')
        $pdf->output_force_download();

    //check if want to email
    if ($export_type == 'email')
        send_email($chairman_id, $export_link_security, $context, $pdf);
} else {

    print_error("Access Restricted");
}

function send_email($chairman_id, $export_link_security, $context, $pdf) {
    $fileinfo = generate_pdf_fileinfo($context, $export_link_security, $pdf);

    $fs = get_file_storage();
    $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'], $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

    if ($file) {
        send_email_link($chairman_id, $pdf, $fileinfo);
    } else {
        print_error(get_string('no_pdf_file_to_export', 'chairman'));
    }
}

function send_email_link($chairman_id, $pdf, $fileinfo) {
   
    $url = moodle_url::make_pluginfile_url($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'], $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);
    output_mailto($chairman_id, $pdf, $url);
}

function output_mailto($chairman_id, $pdf, $mailto_body) {
    global $DB;

    $members = $DB->get_records('chairman_members', array('chairman_id' => $chairman_id));
    $mailto_members = "";
    foreach ($members as $member) {
        $muser = $DB->get_record('user', array('id' => $member->user_id));
        $mailto_members .= $muser->email . ";";
    }

    $len = strlen($mailto_members);
    if ($len > 0)
        $mailto_members = substr($mailto_members, 0, $len - 1);

    $mailto_subject = $pdf->get_event_name();


    $mailto = "mailto:" . $mailto_members . "?subject=" . urlencode($mailto_subject) . "&body=" . $mailto_body;

    $return_url = required_param("return_url", PARAM_URL);

    echo "<script>";
    echo "var link = \"" . $mailto . "\";";
    echo "location.href = link;";
    echo "function redirect(){window.location.replace('$return_url');}";
    echo 'window.setTimeout("redirect()",500);';
    echo "</script>";
}

/**
 * Save the pdf to the chairman module's public file system under a folder
 * named after the event/meeting.
 * 
 * @param string $credentials
 * @param object $context
 * @param pdf_creator $pdf
 */
function save_pdf($credentials, $save_security, $context, $pdf) {
    if ($credentials == 'president' || $credentials == 'vice' || $credentials == 'admin') {
        $fs = get_file_storage();

        $fileinfo = generate_pdf_fileinfo($context, $save_security, $pdf);

        $file = file_from_fileinfo($fileinfo);

        if ($file) {
            $file->delete();
        }

        $fs->create_file_from_string($fileinfo, $pdf->get_pdf_string());
    }
}

?>
