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
require_once('../../../config.php');
require_once('../lib.php');
require_once('../lib_chairman.php');
echo '<link rel="stylesheet" type="text/css" href="../style.php">';

//If new
$id = optional_param('id',0,PARAM_INT);    // Course Module ID
$file = optional_param('file',0,PARAM_INT); // parent

//If editing
$fileid = optional_param('fileid',0,PARAM_INT); // if editing
if($fileobj = $DB->get_record('chairman_files',array('id'=>$fileid))) {
    $file = $fileobj->parent;
}
else {
	$fileobj = new object();
    $fileobj->parent = 0;
}

chairman_check($id);
if($fileid=='') {
    $title = 'addfolder';
}
else {
    $title = 'editfolder';
}
chairman_header($id,$title,'files.php?id='.$id.'&file='.$file);

if(chairman_isadmin($id)) {

    if($fileid=='') {
        echo '<div><div class="title">'.get_string('addfolder', 'chairman').'</div>';
    }
    else {
        echo '<div><div class="title">'.get_string('editfolder', 'chairman').'</div>';
    }

    //Build breadcrumb trail recursively
    $breadcrumb = breadcrumb($file);
    //print_object($breadcrumb);
    $length = sizeof($breadcrumb);
    $counter = 1;
    $private = false;
    foreach($breadcrumb as $link) {
        echo '<a href="'.$link->url.'">'.$link->name.'</a>';
        if($counter!=$length) {
            //Print arrow
            echo '&nbsp;&nbsp;';
            echo '<img src="'.$CFG->wwwroot.'/pix/t/collapsed.png">';
            echo '&nbsp;&nbsp;';
        }
        if($link->private==1) {
            $private = true;
        }
        $counter++;
    }

    echo '<form action="'.$CFG->wwwroot.'/mod/chairman/chairman_filesystem/add_folder_script.php?id='.$id.'" method="POST" name="newfolder">';
    echo '<table width=100% border=0>';

    $fold = new object();
    
    if($fileid!='') {
        $fold = $DB->get_record('chairman_files', array('id'=>$fileid));
        $file = $fold->parent;
    }
    else {
        $fold->name = '';
        $fold->private = 1;
    }

    echo '<tr><td>'.get_string('name', 'chairman').' : </td>';
    echo '<td width=85%><input type="text" name="name" value="'.$fold->name.'">';
    echo '</td></tr>';
    echo '<tr><td>'.get_string('private', 'chairman').' : </td>';
    if($private) {
        echo '<td>'.get_string('yes','chairman').'</td>';
        echo '<input type="hidden" name="private" value="1">';
    }
    else {
        echo '<td><select name="private">';
        echo '<option value="1" ';
        if($fold->private==1)
            echo 'SELECTED';
        echo '>'.get_string('yes', 'chairman').'</option>';
        echo '<option value="0" ';
        if($fold->private==0)
            echo 'SELECTED';
        echo '>'.get_string('no', 'chairman').'</option>';
        echo '</select></td>';
    }
    echo '</tr>';
    echo '<tr><td><br/></td><td></td></tr>';
    echo '<tr>';
    echo '<input type="hidden" name="file" value="'.$file.'">';
    echo '<input type="hidden" name="fileid" value="'.$fileid.'">';
    echo '<td></td><td><input type="submit" value="'.get_string('submit', 'chairman').'">';
    echo '<input type="button" value="'.get_string('cancel', 'chairman').'" onClick="window.location=\''.$CFG->wwwroot.'/mod/chairman/chairman_filesystem/files.php?id='.$id.'&file='.$file.'\'"></td>';
    echo '</tr>';
    echo '</table>';
    echo '</form>';

    echo '<span class="content">';

    echo '</span></div>';

}

chairman_footer();

?>
