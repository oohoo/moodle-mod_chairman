<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

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

$id = optional_param('id',0,PARAM_INT);    // Course Module ID

$course_mod = get_record('course_modules', 'id', $id);
$chairman_instance = get_record('chairman', 'id', $course_mod->instance);

$chairman_name = $chairman_instance->name;

$course_number = get_record('course_modules', 'id', $id);
$course_name = get_record('course', 'id', $course_number->course);

$navlinks = array(
        array(
                'name'=>$course_name->shortname,
                'link'=>$CFG->wwwroot.'/course/view.php?id='.$course_name->id,
                'type'=>'misc'
        ),
        array(
                'name'=>$chairman_name,
                'link'=>$CFG->wwwroot.'/mod/chairman/view.php?id='.$id,
                'type'=>'misc'
        ),

        array(
                'name'=>get_string('about', 'chairman'),
                'link'=>'',
                'type'=>'misc'
        )

);

$nav = build_navigation($navlinks);
print_header($chairman_name, $chairman_name, $nav);

//context needed for access rights
$context = get_context_instance(CONTEXT_USER, $USER->id);

print_content_header();

print_inner_content_header($style_nav);

chairman_printnavbar($id);

print_inner_content_footer();

print_inner_content_header($style_content);

echo '<div><div class="title">'.get_string('description', 'chairman').'</div>';

echo '<span class="content">'.$chairman_instance->description;

echo '</span></div>';

print_inner_content_footer();

echo '<div style="clear:both;"></div>';

print_content_footer();


print_footer();

?>
