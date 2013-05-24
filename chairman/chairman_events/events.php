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

global $PAGE, $DB;

$id = optional_param('id', 0, PARAM_INT);    // Course Module ID

$PAGE->requires->css('/mod/chairman/chairman_events/css/event_style.css');
$PAGE->requires->js('/mod/chairman/chairman_events/js/events.js');

chairman_check($id);
chairman_header($id, 'events', 'events.php?id=' . $id);

echo '<div><div class="title">' . get_string('events', 'chairman') . '</div>';
add_event_link($id);
echo '<div id="events_root">';
echo '<div id="events_container">';

list($start_date, $now, $end_date) = chairman_get_year_definition();
$unixnow = time();
$itteration_date = new DateTime();

//always display current month
echo '<h3>' . chairman_get_month($itteration_date->format('m')) . ' ' . $itteration_date->format('Y') . '</h3>';

echo '<div>';
$records = $DB->get_records("chairman_events", array('chairman_id' => $id, 'month' => $itteration_date->format('m'), 'year' => $itteration_date->format('Y')), 'stamp_start DESC');
chairman_print_events($id, $unixnow, $records);
echo "</div>";

$itteration_date->sub(new DateInterval("P1M"));
$interval = $itteration_date->diff($start_date, false);

while (($interval->invert === 1) ||
 ($interval->invert === 0 && $interval->d == 0 && ($interval->y === 0 || $interval->y === 1 && $interval->m === 1))) {

    $records = $DB->get_records("chairman_events", array('chairman_id' => $id, 'month' => $itteration_date->format('m'), 'year' => $itteration_date->format('Y')), 'stamp_start DESC');
    if (empty($records)) {
        $interval = $itteration_date->sub(new DateInterval("P1M"))->diff($start_date, false);
        continue;
    }

    echo '<h3>' . chairman_get_month($itteration_date->format('m')) . ' ' . $itteration_date->format('Y') . '</h3>';
    echo '<div>';
    chairman_print_events($id, $unixnow, $records);
    echo '</div>';

    $interval = $itteration_date->sub(new DateInterval("P1M"))->diff($start_date, false);
}

echo '</div>'; //container
echo '</div>'; //root

echo '</div>';

add_event_link($id);

chairman_footer();

function add_event_link($id) {
    global $CFG;

    if (chairman_isadmin($id)) {
        echo '<div class="add_link">';
        echo '<a href="' . $CFG->wwwroot . '/mod/chairman/chairman_events/add_event.php?id=' . $id . '"><img src="' . $CFG->wwwroot . '/mod/chairman/pix/switch_plus.gif' . '">' . get_string('addevent', 'chairman') . '</a>';
        echo '</div>';
    }
}

?>
