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
 * @package   chairman
 * @copyright 2011 Raymond Wainman, Dustin Durand, Patrick Thibaudeau (Campus St. Jean, University of Alberta)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require_once('lib.php');
require_once('meetingagenda/lib.php');
require_once('lib_chairman.php');
echo '<link rel="stylesheet" type="text/css" href="style.php">';
print '<script type="text/javascript" src="'.$CFG->wwwroot.'/mod/chairman/meetingagenda/rooms_available.js"></script>';


$id = optional_param('id', 0, PARAM_INT);    // Course Module ID

$event_id = optional_param('event_id', 0, PARAM_INT);

chairman_check($id);
chairman_header($id, 'deleteevent', 'events.php?id=' . $id);

echo '<div><div class="title">' . get_string('deleteevent', 'chairman') . '</div>';

echo get_string('deletingevent', 'chairman');

echo '<span class="content">';

echo '</span></div>';

chairman_footer();


//---DELETE AGENDA FOR EVENT----------------------------------------------------
//DO NOT DELETE AGENDA
/*
if ($event_id) {
    $agenda = $DB->get_record('chairman_agenda', array('chairman_events_id' => $event_id), '*', $ignoremultiple = false);
    if ($agenda) {
        $chairman_id = $agenda->chairman_id;
        $event_id = $agenda->chairman_events_id;
        $agenda_id = $agenda->id;

        $cm = get_coursemodule_from_id('chairman', $chairman_id); //get course module
//Delete all files within the instace of this module for agenda
        $fs = get_file_storage();
        $files = $fs->get_area_files($cm->instance, 'mod_chairman', 'attachment');
        foreach ($files as $f) {
            $f->delete();
        }

        $DB->delete_records('chairman_agenda_topics', array('chairman_agenda' => $agenda_id));
        $DB->delete_records('chairman_agenda_guests', array('chairman_agenda' => $agenda_id));
        $DB->delete_records('chairman_agenda_motions', array('chairman_agenda' => $agenda_id));
        $DB->delete_records('chairman_agenda_attendance', array('chairman_agenda' => $agenda_id));
        $DB->delete_records('chairman_agenda_members', array('agenda_id' => $agenda_id));
        $DB->delete_records('chairman_agenda', array('id' => $agenda_id));


    }
    $event = $DB->get_record('chairman_events', array('id' => $event_id), '*', $ignoremultiple = false);
    if($event && $event->room_reservation_id > 0){
        $dbman = $DB->get_manager();
$table = new xmldb_table('roomscheduler_reservations');
$scheduler_plugin_installed = $dbman->table_exists($table);

if ($scheduler_plugin_installed) {   //plugin exists
    js_function('room_scheduler_delete',$event->room_reservation_id);
}
}

}
*/

//-----------------------------------------------------------------------------
//Delete Event itself
$DB->delete_records('chairman_events', array('id' => $event_id));

echo '<script type="text/javascript">';
echo 'window.location.href="' . $CFG->wwwroot . '/mod/chairman/events.php?id=' . $id . '";';
echo '</script>';
?>
