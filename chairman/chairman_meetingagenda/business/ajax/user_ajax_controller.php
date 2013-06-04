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
 * A backend that allows an ajax client to search and display users through ajax calls.
 * 
 * The controller always returns JSON data, and an empty json on failure.
 * 
 */

//ERRORS ARE SUPRESSED - Change if development required or issues arrise
error_reporting(0);

require_once('../../../../../config.php');
require_once('../../../lib.php');
require_once('../../../lib_chairman.php');

global $CFG, $PAGE, $OUTPUT;

/**
 * Object for searching through moodle users with paging
 */
require_once("$CFG->dirroot/mod/chairman/chairman_meetingagenda/util/moodle_user_selector.php");

$id = optional_param('id',0,PARAM_INT);//cmid
$page_size = optional_param('page_size',10,PARAM_INT);//# users per display page
$current_page = optional_param('current_page',1,PARAM_INT);//current page for paging
$search = optional_param('search',"",PARAM_TEXT);//search text
$type = optional_param('type',"text",PARAM_TEXT);//search text


//course module
$cm = get_coursemodule_from_id('chairman', $id);

//Always returning json
header('Content-type: application/json');


// On failure we will always return an empty json element
    try{
    chairman_ajax_check($id);
    } catch (Exception $e) 
    {
        $empty = array();
        echo empty_json();
    }

//Create the object to search through users with paging
$moodle_user_selector = new moodle_user_selector($cm, $page_size);

if($type == "text")
    echo $moodle_user_selector->get_users_as_json($search, $current_page);
elseif($type == "idlist")
    echo $moodle_user_selector->get_users_as_json_by_id ($search);
else
    empty_json();

//get all matching users

function empty_json()
{
    $empty = array();
    return json_encode($empty);
}
    
?>
