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
 * Sidebar for the topics by year.
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package   Agenda/Meeting Extension to Committee Module
 * @copyright 2011 Dustin Durand
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("$CFG->dirroot/mod/chairman/chairman_meetingagenda/business/css/business_menu_style.css");
require_once("$CFG->dirroot/mod/chairman/chairman_meetingagenda/lib.php");

//-----TOPIC NAMES--------------------------------------------------------------

print '<div class="business_side_menu">';
print '<h3 class="headerbar">'.get_string('menu_topics','chairman').'</h3><ul>';

//Get minimum and Max Year for events
$sql = "SELECT min(e.year) as minyear, max(e.year) as maxyear from {chairman_events} e WHERE e.chairman_id = $chairman_id";
$record =  $DB->get_record_sql($sql, array());
$start_year = $record->minyear;
$end_year = $record->maxyear;

//make a achor link for each year inbetween the max and min
for($year=$start_year;$year<=$end_year;$year++){
 print '<li><a href="'."#year_$year".'">'."$year".'</a></li>';
}

print '</ul>'; //end list


print '</div>'; //end div

?>
