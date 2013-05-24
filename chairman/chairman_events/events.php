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
 * Displays the events for the present year, by month
 * 
 */
require_once('../../../config.php');
require_once('../lib.php');
require_once('../lib_chairman.php');
require_once('./EventOutputRenderer.php');

global $PAGE, $DB;

$id = optional_param('id', 0, PARAM_INT);    // Course Module ID

$PAGE->requires->css('/mod/chairman/chairman_events/css/event_style.css');
$PAGE->requires->js('/mod/chairman/chairman_events/js/events.js');

chairman_check($id);

//An ajax request - no output except the result
$is_ajax = optional_param('ajax_request', 0, PARAM_INT);    // Course Module ID
if($is_ajax)
{
    ajax_request($id);
    return;
}

//Normal page load
chairman_header($id, 'events', 'events.php?id=' . $id);

//title
echo '<div><div class="title">' . get_string('events', 'chairman') . '</div>';

//search
echo '<div id="meeting_search_container">';
echo '<input type="text" id="meeting_search" />';
echo '<span id="search_button">'.get_string("search")." </span>";
echo '</div>';

//top add event link
add_event_link($id);

//events display
echo '<div id="events_root">';
echo '<span id="search_error" class="error"/>';
$renderer = new EventOutputRenderer($id);
$renderer->output_current_year(null);

//hidden ajax loading message
echo '<div id="ajax_loading" style="display:none">';
echo '<img src="../img/ajax-loader.gif" style="vertical-align:middle;"/>';
echo '<span>'.get_string("loading",'chairman').'</span>';
echo '</div>';

echo '</div>';

//bottom add event link
add_event_link($id);
echo '</div>';


chairman_footer();


    /**
     * Outputs the event link to the output
     * 
     * @global type $CFG
     * @param type $id
     */
    function add_event_link($id) {
        global $CFG;

        if (chairman_isadmin($id)) {
            echo '<div class="add_link">';
            echo '<a href="' . $CFG->wwwroot . '/mod/chairman/chairman_events/add_event.php?id=' . $id . '"><img src="' . $CFG->wwwroot . '/mod/chairman/pix/switch_plus.gif' . '">' . get_string('addevent', 'chairman') . '</a>';
            echo '</div>';
        }
    }
    
        /**
     * Outputs the event link to the output
     * 
     * @global type $CFG
     * @param type $id
     */
    function ajax_request($id) {
        $search = optional_param('search', null, PARAM_TEXT);

        $renderer = new EventOutputRenderer($id);
        $renderer->output_current_year($search);
    }

?>
