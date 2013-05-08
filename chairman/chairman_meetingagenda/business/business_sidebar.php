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

require_once("$CFG->dirroot/mod/chairman/chairman_meetingagenda/business/css/business_menu_style.css");
require_once("$CFG->dirroot/mod/chairman/chairman_meetingagenda/lib.php");

//START DIV FOR CSS
print '<div class="business_side_menu">';



//-------AGENDA MENU FOR VIEWER-------------------------------------------------
//------------------------------------------------------------------------------

if(isset($is_viewer)){ //set from viewer.php

$sql = "SELECT ce.id, ce.year, ce.year, ce.month, ce.day ".
"FROM {chairman_agenda} ca, {chairman_events} ce ".
"WHERE ce.chairman_id = ? AND ce.id=ca.chairman_events_id ".
"AND ce.chairman_id=ca.chairman_id ".
"ORDER BY year DESC, month DESC, day DESC";

$agendas = $DB->get_records_sql($sql, array($chairman_id,$event_id), $limitfrom=0, $limitnum=0);

print '<h3 class="headerbar">'.get_string('agendas','chairman').'</h3><ul>';

foreach($agendas as $agenda){
    $url = "$CFG->wwwroot/mod/chairman/chairman_meetingagenda/view.php?event_id=" . $agenda->id . "&selected_tab=" . 3;
    print '<li><a href="'.$url.'" >'.toMonth($agenda->month) ." ".$agenda->day.", ".$agenda->year.'</a></li>';
}
}


//-----TOPIC NAMES--------------------------------------------------------------
$topics = $mform->getIndexToNamesArray(); //Get names of topics from data stored in form
//------------------------------------------------------------------------------

//Start html DIV

if(!$topics){
   $topics = array();
}

//-----------TOPIC MENU---------------------------------------------------------
//------------------------------------------------------------------------------

print '<h3 class="headerbar">'.get_string('menu_topics','chairman').'</h3><ul>';

//Create a list of <a> tags that link to anchor points
foreach($topics as $index=>$topic){

    print '<li><div class="chairman_side_li"><a href="'."#topic_$index".'">'."$index. $topic".'</a></div></li>';
    }

//end list
print '</ul>';


//------------PREVIOUS AGENDAS MENU---------------------------------------------
//------------------------------------------------------------------------------

//Check if event is set: viewer.php does not use event_id and therefore we ignore
//this section of menu if its not set
if(isset($event_id)){

$current_event = $DB->get_record('chairman_events', array('id'=>$event_id));



//DOWNLOAD AGENDA LINK -- Replaced with button
//print '<h3 class="headerbar">'."PDF".'</h3><ul>';
//$url = "$CFG->wwwroot/mod/chairman/chairman_meetingagenda/util/pdf_script.php?event_id=" . $event_id;
//print '<li><a href="'.$url.'" target="_blank">'."Download".'</a></li>';


$day = $current_event->day;
$month = $current_event->month;
$year = $current_event->year;
$start_hour = $current_event->starthour;

//PREVIOUS MINUTES UP TO, but not including current agenda (ordered by most recent to oldest)
$sql = "SELECT ce.id, ce.year, ce.year, ce.month, ce.day ".
"FROM {chairman_agenda} ca, {chairman_events} ce ".
"WHERE ce.chairman_id = ? AND ce.id=ca.chairman_events_id AND ca.id IS NOT NULL ".
"AND ca.chairman_events_id <> ? AND ce.chairman_id=ca.chairman_id ".
"AND ((ce.year < $year) OR (ce.year = $year AND ce.month < $month) ".
"OR (ce.year = $year AND ce.month = $month AND ce.day < $day ) ".
"OR (ce.year = $year AND ce.month = $month AND ce.day = $day AND ce.endhour < $start_hour )) ".
"ORDER BY year DESC, month DESC, day DESC";


$agendas = $DB->get_records_sql($sql, array($chairman_id,$event_id), $limitfrom=0, $limitnum=0);

print '<h3 class="headerbar">'.get_string('other_agendas','chairman').'</h3><ul>';

foreach($agendas as $agenda){
$url = "$CFG->wwwroot/mod/chairman/chairman_meetingagenda/view.php?event_id=" . $agenda->id . "&selected_tab=" . $selected_tab;
 print '<li><a href="'.$url.'">'.toMonth($agenda->month) ." ".$agenda->day.", ".$agenda->year.'</a></li>';
}




}
print '</div>';

?>
