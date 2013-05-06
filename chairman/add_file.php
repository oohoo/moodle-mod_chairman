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
$file = optional_param('file',0,PARAM_INT);
$fileid = optional_param('fileid',0,PARAM_INT);

if($fileobj = $DB->get_record('chairman_files',array('id'=>$fileid))){
    $file = $fileobj->parent;
}

chairman_check($id);
chairman_header($id,'addfile','file.php?id='.$id.'&file='.$file);

if(chairman_isadmin($id)) {

    $submitted = optional_param('submitted',null,PARAM_TEXT);
    $private = optional_param('private',null, PARAM_INT);

    if ($submitted == "yes") {

        $course_mod = get_coursemodule_from_id('chairman', $id);
        $mod = get_context_instance(CONTEXT_MODULE,$course_mod->id);

        if($fileid=='') {
            $now = time();

            if (isset($_FILES['userfile']['name'])) {
                $name = $_FILES['userfile']['name'];
                $filename = $name;

                $fs = get_file_storage();

                $file_record = array('contextid'=>$mod->id,'component'=>'mod_chairman', 'filearea'=>'chairman', 'itemid'=>0, 'filepath'=>'/'.$course_mod->id.'/'.$now.'/',
                        'filename'=>$filename, 'timecreated'=>$now, 'timemodified'=>$now);

                $tmpfile = $_FILES['userfile']['tmp_name'];

                $fs->create_file_from_pathname($file_record,$tmpfile);

                //enter data into database table
                $insert = new object();
                $insert->user_id = $USER->id;
                $insert->name = $filename;
                $insert->parent = $file;
                $insert->private = $private;
                $insert->timemodified = $now;
                $insert->type = 1;
                //$insert->private = $private;
                $insert->chairman_id = $id;

                // print_object($insert);

                if (!$DB->insert_record('chairman_files',$insert)) {
                    echo 'not saved';
                    //print_object($insert);
                } else {
                    echo '<script type="text/javascript">';
                    echo 'window.location.href="'.$CFG->wwwroot.'/mod/chairman/files.php?id='.$id.'&file='.$file.'";';
                    echo '</script>';
                }
            }
        }
        else {
            $insert = new object();
            $insert->id = $fileid;
            $insert->private = $private;
            $insert->user_id = $USER->id;
            //$insert->timemodified = time();

            $DB->update_record('chairman_files', $insert);
            echo '<script type="text/javascript">';
            echo 'window.location.href="'.$CFG->wwwroot.'/mod/chairman/files.php?id='.$id.'&file='.$file.'";';
            echo '</script>';
        }
    }

    if($fileid=='') {
        echo '<div><div class="title">'.get_string('addfile', 'chairman').'</div>';
    }
    else {
        echo '<div><div class="title">'.get_string('editfile', 'chairman').'</div>';
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
        if($link->private==1){
            $private = true;
        }
        $counter++;
    }

    echo '<table width=100% border=0>';

    echo '<form name="uploadform" id="uploadform" action="'.$_SERVER['PHP_SELF'].'" enctype="multipart/form-data" method="post">';
    echo '<input type="hidden" name="submitted" value="yes">';
    echo '<input type="hidden" name="id" value="'.$id.'">';

    echo '<tr><td>'.get_string('file', 'chairman').' : </td>';
    if($fileid=='') {
        echo '<td width="85%"><input type="file" name="userfile"></td></tr>';
        $file_obj->private = 1;
    }
    else {
        $file_obj = $DB->get_record('chairman_files',array('id'=>$fileid));
        echo '<td width="85%">'.$file_obj->name.'</td>';
    }
    echo '<tr><td>'.get_string('private', 'chairman').' : </td>';
    if($private) {
        echo '<td>'.get_string('yes','chairman').'</td>';
        echo '<input type="hidden" name="private" value="1">';
    }
    else {
        echo '<td><select name="private">';
        echo '<option value="0" ';
        if($file_obj->private==0)
            echo 'SELECTED';
        echo '>'.get_string('no', 'chairman').'</option>';
        echo '<option value="1" ';
        if($file_obj->private==1)
            echo 'SELECTED';
        echo '>'.get_string('yes', 'chairman').'</option>';
        echo '</select></td>';
    }
    echo '</tr>';
    echo '<input type="hidden" name="fileid" value="'.$fileid.'">';
    echo '<input type="hidden" name="file" value="'.$file.'">';
    echo '<tr><td><br/></td><td></td></tr>';
    echo '<tr><td></td><td><input type="submit" value="'.get_string('submit','chairman').'">';
    echo '<input type="button" value="'.get_string('cancel', 'chairman').'" onClick="parent.location=\''.$CFG->wwwroot.'/mod/chairman/files.php?id='.$id.'&file='.$file.'\'"></td></tr>';

    echo '</table>';
    echo '</form>';

    echo '<span class="content">';

    echo '</span></div>';

}

chairman_footer();

?>
