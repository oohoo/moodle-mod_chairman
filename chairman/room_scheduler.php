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

require_once('../../config.php');

global $DB,$CFG;
require_once($CFG->dirroot.'/mod/chairman/meetingagenda/rooms_avaliable_form.php');


$id = required_param('id', PARAM_INT);
//----CHECK FOR SCHEDULER PLUGIN -----------------------------------------------
 $dbman = $DB->get_manager();
$table = new xmldb_table('roomscheduler_reservations');
$scheduler_plugin_installed = $dbman->table_exists($table);

$cm = get_coursemodule_from_id('chairman', $id);
$context = get_context_instance(CONTEXT_COURSE, $cm->course);

if ($scheduler_plugin_installed && has_capability('block/roomscheduler:reserve', $context)) {   //plugin exists
global $DB;

$cm = get_coursemodule_from_id('chairman', $id);
$chairman = $DB->get_record("chairman", array("id"=>$cm->instance));

$scheduler_form = new rooms_avaliable_form();
$scheduler_form->initalize_popup_newEvent($chairman->course, $chairman->name);
echo $scheduler_form;

echo '12345';
print '123445';

}
?>
