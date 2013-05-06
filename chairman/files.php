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
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->dirroot.'/lib/uploadlib.php');
echo '<link rel="stylesheet" type="text/css" href="style.php">';

$id = optional_param('id',0,PARAM_INT);    // Course Module ID
$file = optional_param('file',0,PARAM_INT); //File id (usually a folder)

chairman_check($id);
chairman_header($id,'files','files.php?id='.$id.'&file='.$file);

$course_mod = get_coursemodule_from_id('chairman', $id);
$context = get_context_instance(CONTEXT_MODULE,$course_mod->id);

$folder = '';
if($file!=0) {
    $folderobj = $DB->get_record('chairman_files',array('id'=>$file));
    $folder = '-'.$folderobj->name;
}
else {
    $folder = '-'.get_string('root','chairman');
}

echo '<div><div class="title">';
echo get_string('files', 'chairman').$folder;
echo '</div>';

echo '<span class="content">';

//Build breadcrumb trail recursively
$breadcrumb = breadcrumb($file);
//print_object($breadcrumb);
$length = sizeof($breadcrumb);
$counter = 1;
foreach($breadcrumb as $link) {
    echo '<a href="'.$link->url.'">'.$link->name.'</a>';
    if($counter!=$length) {
        //Print arrow
        echo '&nbsp;&nbsp;';
        echo '<img src="'.$CFG->wwwroot.'/pix/t/collapsed.png">';
        echo '&nbsp;&nbsp;';
    }
    $counter++;
}

$files = $DB->get_records('chairman_files', array('chairman_id'=>$id,'parent'=>$file),'type ASC, name ASC');

$PRIVATE = 0;
if(chairman_isMember($id) || chairman_isadmin($id)) {
    $PRIVATE = 1;
}

foreach($files as $fileobj) {
    //private files and folders
    if($fileobj->private==1 && $PRIVATE==1) {
        if($fileobj->type==0) {
            print_folder($fileobj);
        }
        else if($fileobj->type==1) {
            print_file($fileobj);
        }
    }
    else if($fileobj->private==0) {
        if($fileobj->type==0) {
            print_folder($fileobj);
        }
        else if($fileobj->type==1) {
            print_file($fileobj);
        }
    }
}

//echo '<a href="'.$CFG->wwwroot.'/mod/chairman/files.php?id='.$id.'"><img id="back" src="'.$CFG->wwwroot.'/mod/chairman/pix/up.png" title="'.get_string('goback','chairman').'"></a>';

echo '</span></div>';

if($PRIVATE==1) {
    echo '<div class="add">';
    echo '<br/>';
    if (chairman_isadmin($id)){
    echo '<a href="'.$CFG->wwwroot.'/mod/chairman/add_folder.php?id='.$id.'&file='.$file.'"><img src="'.$CFG->wwwroot.'/mod/chairman/pix/switch_plus.gif'.'">'.get_string('addfolder', 'chairman').'</a><br/>';
    echo '<a href="'.$CFG->wwwroot.'/mod/chairman/add_file.php?id='.$id.'&file='.$file.'"><img src="'.$CFG->wwwroot.'/mod/chairman/pix/switch_plus.gif'.'">'.get_string('addfile', 'chairman').'</a>';
    }
    echo '</div>';
}

chairman_footer();





function print_folder($fold) {
    global $id,$CFG,$PRIVATE;

    echo '<div class="file">';
    echo '<table>';
    echo '<tr>';
    echo '<td id="image_cell">';
    echo '<a href="'.$CFG->wwwroot.'/mod/chairman/files.php?id='.$id.'&file='.$fold->id.'"><img id="icon" src="'.$CFG->wwwroot.'/mod/chairman/pix/folder.png"></a>';
    echo '</td>';
    echo '<td>';
    echo '<b>'.$fold->name.'</b>';
    if($PRIVATE==1) {
        if (chairman_isadmin($id)){
        echo ' - <a href="'.$CFG->wwwroot.'/mod/chairman/delete_folder_script.php?id='.$id.'&fileid='.$fold->id.'" onClick="return confirm(\''.get_string('deletefolderquestion', 'chairman').'\');"><img src="'.$CFG->wwwroot.'/mod/chairman/pix/delete.gif"></a>';
        echo '<a href="'.$CFG->wwwroot.'/mod/chairman/add_folder.php?id='.$id.'&fileid='.$fold->id.'"><img src="'.$CFG->wwwroot.'/mod/chairman/pix/edit.gif"></a>';
        }

    }
    if($PRIVATE==1) {
        echo '<br/>(';
        if($fold->private==0) {
            echo get_string('public','chairman');
        }
        else {
            echo get_string('private','chairman');
        }
        echo ')';
    }
    echo '<br/><br/>';
    //echo '<a href="'.$CFG->wwwroot.'/mod/chairman/files.php?id='.$id.'&file='.$fold->id.'">'.get_string('openfolder','chairman').'</a>';
    echo '</td></tr></table>';
    echo '</div>';
}

function print_file($file) {
    global $CFG,$PRIVATE,$course_mod,$context,$id,$DB;

    echo '<div class="file">';
    echo '<table>';
    echo '<tr>';
    //Filetype logo
    echo '<td>';
    //$file_path = get_file_url($course_mod->course.'/chairman/'.$id.'/'.$file->timemodified.'/'.$file->filename);
    $file_path = file_encode_url($CFG->wwwroot.'/pluginfile.php', '/'.$context->id.'/mod_chairman/chairman/'.$course_mod->id.'/'.$file->timemodified.'/'.$file->name);

    echo '<a href="'.$file_path.'">';
    if(strpos($file->name, '.doc') || strpos($file->name, '.docx')) {
        //Word logo
        echo '<img id="icon" src="'.$CFG->wwwroot.'/mod/chairman/pix/word.png">';
    }
    else if(strpos($file->name, '.pdf')) {
        //PDF logo
        echo '<img id="icon" src="'.$CFG->wwwroot.'/mod/chairman/pix/pdf.png">';
    }
    else if(strpos($file->name, '.xls') || strpos($file->name, '.xlsx')) {
        //Excel logo
        echo '<img id="icon" src="'.$CFG->wwwroot.'/mod/chairman/pix/excel.png">';
    }
    else if(strpos($file->name, '.ppt') || strpos($file->name, '.pptx')) {
        //Powerpoint logo
        echo '<img id="icon" src="'.$CFG->wwwroot.'/mod/chairman/pix/ppt.png">';
    }
    else {
        //Generic Logo
        echo '<img id="icon" src="'.$CFG->wwwroot.'/mod/chairman/pix/blank.png">';
    }
    echo '</td>';

    //File download link
    echo '<td>';
    echo '</a>';

    echo '<b>'.$file->name.'</b>';
    if($PRIVATE==1) {
        if (chairman_isadmin($id)){
        echo ' - <a href="'.$CFG->wwwroot.'/mod/chairman/delete_file_script.php?id='.$id.'&file_id='.$file->id.'&file='.$file->parent.'" onClick="return confirm(\''.get_string('deletefilequestion', 'chairman').'\');"><img src="'.$CFG->wwwroot.'/mod/chairman/pix/delete.gif"></a>';
        echo '<a href="'.$CFG->wwwroot.'/mod/chairman/add_file.php?id='.$id.'&fileid='.$file->id.'"><img src="'.$CFG->wwwroot.'/mod/chairman/pix/edit.gif"></a>';
        }
        echo '<br/>';
        if($file->private==0) {
            echo '('.get_string('public','chairman').')';
        }
        else if($file->private==1) {
            echo '('.get_string('private','chairman').')';
        }
    }
    echo '<br/>';
    $user_name = $DB->get_record('user', array('id'=>$file->user_id));
    echo get_string('uploadedon', 'chairman').' '.date("m-d-y", $file->timemodified).' '.get_string('by', 'chairman').' '.$user_name->firstname.' '.$user_name->lastname.'<br/><br/>';

    echo '<a href="'.$file_path.'">'.get_string('download', 'chairman').'</a>';

    echo '</td>';
    echo '</tr>';
    echo '</table>';
    echo '</div>';
}

?>
