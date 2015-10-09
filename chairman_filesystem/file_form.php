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
 * This file allows a vistors to view public chairman files, members to view public & private
 * chairman files, and for the admin of the chairman to view and edit public/private chairman files.
 * 
 */

require_once('../../../config.php');
require_once('../lib.php');
require_once('../lib_chairman.php');
require_once('files_edit_form.php');

$id = required_param('id', PARAM_INT);    // chairman id

chairman_check($id);
chairman_header($id, 'filesview', 'chairman_filesystem/file_form.php?id=' . $id);

$COURSE_MODULE = get_coursemodule_from_id('chairman', $id);
$CONTEXT = get_context_instance(CONTEXT_MODULE, $COURSE_MODULE->id);
$PRIVATE = 0;
$ADMIN = 0;

//Check permissions of users
if(chairman_isMember($id) || chairman_isadmin($id)) {
    $PRIVATE = 1;
}

//check the user is admin for the current chairman
if(chairman_isadmin($id)) {
    $ADMIN = 1;
}

//generate form
$mform = new files_edit_form($PRIVATE, $ADMIN, $COURSE_MODULE->id); //name of the form you defined in file above.


if ($mform->is_cancelled()) {
    
    chairman_basic_footer();
    redirect("$CFG->wwwroot/mod/chairman/chairman_filesystem/file_form.php?id=$id", null, 0);
    
} else if ($data = $mform->get_data()) {

    //only save if they have admin level access to this committee
    if($ADMIN)
    {
        file_save_draft_area_files($data->private_files, $CONTEXT->id, 'mod_chairman', 'chairman_private',
                   0, array('subdirs' => 1, 'maxfiles' => 500));
        
        file_save_draft_area_files($data->public_files, $CONTEXT->id, 'mod_chairman', 'chairman',
                   0, array('subdirs' => 1, 'maxfiles' => 500));
    }
    //Add to logs
    add_to_log($COURSE_MODULE->course, 'chairman', 'add', '', get_string('save_pdf_local', 'chairman'), $id);
    chairman_basic_footer();
    //redirect back to page
    redirect("$CFG->wwwroot/mod/chairman/chairman_filesystem/file_form.php?id=$id", null, 0);
    
} else {

    //Display Form
    add_to_log($COURSE_MODULE->course, 'chairman', 'view', '', get_string('agenda_archive_files', 'chairman'), $id);
    $toform = new stdclass();

    
    //Display private files if a member or admin
    if($PRIVATE)
    {
        $draftitemid = file_get_submitted_draft_itemid("private_files");
        file_prepare_draft_area($draftitemid, $CONTEXT->id, 'mod_chairman', 'chairman_private', 0, array('subdirs' => 1, 'maxfiles' => 200));
        $toform->private_files = $draftitemid;
    }
    
    //Display files for everyone who can view the page
    $draftitemid = file_get_submitted_draft_itemid('public_files');
    file_prepare_draft_area($draftitemid, $CONTEXT->id, 'mod_chairman', 'chairman', 0, array('subdirs' => 1, 'maxfiles' => 200));
    $toform->public_files = $draftitemid;
    
    //set data & display
    $mform->set_data($toform);
    $mform->display();
    chairman_footer();
}
?>
