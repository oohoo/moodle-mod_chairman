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
 * @copyright 2010 Raymond Wainman, Patrick Thibaudeau (Campus St. Jean, University of Alberta)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
require_once('../lib.php');
require_once('../lib_chairman.php');
echo '<link rel="stylesheet" type="text/css" href="../style.php">';

$id = optional_param('id',0,PARAM_INT);    // Course Module ID

$folder_id = optional_param('fileid',0,PARAM_INT);

$folderobj = $DB->get_record('chairman_files',array('id'=>$folder_id));

chairman_check($id);
chairman_header($id,'deletefolder','files.php?id='.$id.'&file='.$folder_id);

echo '<div><div class="title">'.get_string('deletefolder', 'chairman').'</div>';

echo get_string('deletingfolder', 'chairman');

echo '<span class="content">';

$success = false;

if($DB->get_records('chairman_files',array('parent'=>$folder_id))) {
    echo '<br/><br/>';
    echo get_string('deletefoldererror','chairman');
    echo '<br/><br/>';
    echo '<a href="'.$CFG->wwwroot.'/mod/chairman/chairman_filesystem/files.php?id='.$id.'&file='.$folderobj->parent.'">'.get_string('back','chairman').'</a>';
}
else {
    $success = true;
    $DB->delete_records('chairman_files', array('id'=>$folder_id));
}

echo '</span></div>';

chairman_footer();

if($success){
    echo '<script type="text/javascript">';
    echo 'window.location.href="'.$CFG->wwwroot.'/mod/chairman/chairman_filesystem/files.php?id='.$id.'&file='.$folderobj->parent.'";';
    echo '</script>';
}
?>
