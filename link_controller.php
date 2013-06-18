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
 * Ajax controller for adding or removing external links from a chairman module,
 * via an ajax call.
 */
require_once('../../config.php');
global $CFG;

require_once("$CFG->dirroot/mod/chairman/lib_chairman.php");

$id = required_param('id', PARAM_INT);
$type = required_param('type', PARAM_TEXT);

chairman_check($id);

//check the user is admin for the current chairman
if (!chairman_isadmin($id)) {
    header(get_string("unauthorized_link", 'chairman'), true, 401);
    return;
}

if ($type == 'add')
    echo add_external_link($id);
else if ($type == 'delete')
    remove_external_link();
else
    header(get_string("unknown_ajax_type_link", 'chairman'), true, 501);

/**
 * Inserts the provided link, and returns the inserted id.
 * 
 * @global moodle_database $DB
 * @param type $name
 * @param type $link
 * @param type $id
 */
function add_external_link($id) {
    global $DB;

    $name = required_param('name', PARAM_TEXT);
    $link = required_param('link', PARAM_URL);

    $record = new stdClass();
    $record->name = $name;
    $record->link = $link;
    $record->chairman_id = $id;

    $id = $DB->insert_record('chairman_links', $record);
    return $id;
}

/**
 * Removes a provided link for the chairman module based on the provided link id.
 * 
 * @global moodle_database $DB
 * @param type $name
 * @param type $link
 * @param type $id
 */
function remove_external_link() {
    global $DB;

    $link_id = required_param('link_id', PARAM_INT);

    $DB->delete_records('chairman_links', array('id' => $link_id));
}

?>
