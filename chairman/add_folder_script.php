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
require_once('lib.php');
require_once('lib_chairman.php');
echo '<link rel="stylesheet" type="text/css" href="style.php">';

$id = optional_param('id',0,PARAM_INT);    // Course Module ID

$name = optional_param('name',0,PARAM_RAW);
$private = optional_param('private',0,PARAM_INT);
$parent = optional_param('file',0,PARAM_INT);
$fileid = optional_param('fileid',0,PARAM_INT);

chairman_check($id);
if($fileid==''){
    $title = 'addfolder';
}
else {
    $title = 'editfolder';
}
chairman_header($id,$title,'file.php?id='.$id.'&file='.$parent);

echo '<div><div class="title">'.get_string('addfolder', 'chairman').'</div>';

if($fileid=='')
    echo get_string('addingfolderpleasewait', 'chairman');
else
    echo get_string('editingfolderpleasewait','chairman');

echo '<span class="content">';

echo '</span></div>';

chairman_footer();

if($fileid=='') {

    $new_folder = new stdClass();
    $new_folder->chairman_id = $id;
    $new_folder->name = $name;
    $new_folder->parent = $parent;
    $new_folder->private = $private;
    $new_folder->type = 0;
    $new_folder->user_id = $USER->id;
    $new_folder->timemodified = time();

    $DB->insert_record('chairman_files', $new_folder);
}
else {
    $folder = new stdClass();
    $folder->id = $fileid;
    $folder->name = $name;
    $folder->parent = $parent;
    $folder->private = $private;

    $DB->update_record('chairman_files', $folder);
}

echo '<script type="text/javascript">';
echo 'window.location.href="'.$CFG->wwwroot.'/mod/chairman/files.php?id='.$id.'&file='.$parent.'";';
echo '</script>';

?>
