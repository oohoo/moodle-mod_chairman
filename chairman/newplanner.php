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

$id = required_param('id',PARAM_INT);    // Course Module ID
$planner = optional_param('planner',0,PARAM_INT);

if($planner != 0){
    $plannerobj = $DB->get_record('chairman_planner', array('id'=>$planner));
}

//get dependencies
$PAGE->requires->yui2_lib('fonts-min');
$PAGE->requires->yui2_lib('button');
$PAGE->requires->yui2_lib('container');
$PAGE->requires->yui2_lib('calendar');
$PAGE->requires->yui2_lib('yahoo-dom-event');
$PAGE->requires->yui2_lib('dragdrop-min');
$PAGE->requires->yui2_lib('element-min');
$PAGE->requires->yui2_lib('button-min');
$PAGE->requires->yui2_lib('container-min');
$PAGE->requires->yui2_lib('calendar-min');
$PAGE->requires->yui2_lib('yui_yahoo');
$PAGE->requires->yui2_lib('yui_event');
$PAGE->requires->js('/mod/chairman/planner.js');


//require_js(array('yui_yahoo', 'yui_event'));


// print header
chairman_check($id);
chairman_header($id,'newplanner','newplanner.php?id='.$id);

echo '<script type="text/javascript">
    YAHOO.util.Event.onDOMReady(function(){
    	//alert(\'ok\');

        var Event = YAHOO.util.Event,
            Dom = YAHOO.util.Dom,
            dialog,
            calendar;

        var showBtn = Dom.get("show");

        Event.on(showBtn, "click", function() {

            // Lazy Dialog Creation - Wait to create the Dialog, and setup document click listeners, until the first time the button is clicked.
            if (!dialog) {

                // Hide Calendar if we click anywhere in the document other than the calendar
                Event.on(document, "click", function(e) {
                    var el = Event.getTarget(e);
                    var dialogEl = dialog.element;
                    if (el != dialogEl && !Dom.isAncestor(dialogEl, el) && el != showBtn && !Dom.isAncestor(showBtn, el)) {
                        dialog.hide();
                    }
                });

                function resetHandler() {
                    // Reset the current calendar page to the select date, or
                    // to today if nothing is selected.
                    var selDates = calendar.getSelectedDates();
                    var resetDate;

                    if (selDates.length > 0) {
                        resetDate = selDates[0];
                    } else {
                        resetDate = calendar.today;
                    }

                    calendar.cfg.setProperty("pagedate", resetDate);
                    calendar.render();
                }

                function closeHandler() {
                    dialog.hide();
                }

                dialog = new YAHOO.widget.Dialog("container", {
                    visible:false,
                    context:["show", "tl", "bl"],
                    buttons:[ {text:"Reset", handler: resetHandler, isDefault:true}, {text:"Close", handler: closeHandler}],
                    draggable:false,
                    close:true
                });
                dialog.setHeader(\'Pick A Date\');
                dialog.setBody(\'<div id="cal"></div>\');
                dialog.render(document.body);

                dialog.showEvent.subscribe(function() {
                    if (YAHOO.env.ua.ie) {
                        // Since we\'re hiding the table using yui-overlay-hidden, we
                        // want to let the dialog know that the content size has changed, when
                        // shown
                        dialog.fireEvent("changeContent");
                    }
                });
            }

            // Lazy Calendar Creation - Wait to create the Calendar until the first time the button is clicked.
            if (!calendar) {

                calendar = new YAHOO.widget.Calendar("cal", {
                    iframe:false,          // Turn iframe off, since container has iframe support.
                    hide_blank_weeks:true  // Enable, to demonstrate how we handle changing height, using changeContent
                });
                calendar.render();

                calendar.selectEvent.subscribe(function() {
                    if (calendar.getSelectedDates().length > 0) {

                        var selDate = calendar.getSelectedDates()[0];

                        // Pretty Date Output, using Calendar\'s Locale values: Friday, 8 February 2008
                        var day = selDate.getDate();
                        var month = selDate.getMonth()+1;
                        var year = selDate.getFullYear();

                        Dom.get("date").value = day + "/" + month + "/" + year;
                    } else {
                        Dom.get("date").value = "";
                    }
                    dialog.hide();
                });

                calendar.renderEvent.subscribe(function() {
                    // Tell Dialog it\'s contents have changed, which allows
                    // container to redraw the underlay (for IE6/Safari2)
                    dialog.fireEvent("changeContent");
                });
            }

            var seldate = calendar.getSelectedDates();

            if (seldate.length > 0) {
                // Set the pagedate to show the selected date if it exists
                calendar.cfg.setProperty("pagedate", seldate[0]);
                calendar.render();
            }

            dialog.show();
        });
    });
</script>
';

//content
echo '<div class="title">'.get_string('newplanner','chairman').'</div>';

echo '<form action="'.$CFG->wwwroot.'/mod/chairman/new_planner_script.php?id='.$id.'" method="POST" name="newplanner">';
echo '<input type="hidden" name="id" value="'.$id.'">';
if($planner != 0){
    echo '<input type="hidden" name="edit" value="'.$planner.'">';
}
echo '<table width=100% border=0>';
echo '<tr>';
echo '<td>'.get_string('name').':</td>';
echo '<td><input type="text" name="name" id="name" value="';
if(isset($plannerobj->name)){
    echo $plannerobj->name;
}
echo '" size="60"><br/><span id="nameerror" style="font-size:10px;color:red;"></span></td>';
echo '</tr>';
echo '<tr>';
echo '<td valign="top">'.get_string('details','chairman').':</td>';
echo '<td><textarea name="description" cols="50" rows="3">';
if(isset($plannerobj->description)){
    echo $plannerobj->description;
}
echo '</textarea></td>';
echo '</tr>';
echo '<tr>';
echo '<td valign="top">'.get_string('members','chairman').':</td>';
echo '<td>';
$members = $DB->get_records('chairman_members', array('chairman_id'=>$id));
echo '<table cellspacing="0" cellpadding="0" border="1">';
foreach($members as $member){
    if($planner != 0){
        $member_obj = $DB->get_record('chairman_planner_users',array('planner_id'=>$planner,'chairman_member_id'=>$member->id));
    }
    echo '<tr>';
    $userobj = $DB->get_record('user', array('id'=>$member->user_id));
    echo '<td>'.$userobj->firstname.' '.$userobj->lastname.'</td>';
    echo '<td><select name="rule['.$member->id.']">';
    echo '<option value="0" ';
    if(isset($member_obj->rule) && $member_obj->rule==0){ echo 'SELECTED'; }
    echo '>'.get_string('optional','chairman').'</option>';
    echo '<option value="1" ';
    if(isset($member_obj->rule) && $member_obj->rule==1){ echo 'SELECTED'; }
    echo '>'.get_string('required','chairman').'</option>';
    echo '</select></td>';
    echo '</tr>';
}
echo '</table>';
echo '</td>';
echo '<tr>';
echo '<td valign="top">'.get_string('timezone_used','mod_chairman').'</td>';
//Timezone
require_once($CFG->dirroot.'/calendar/lib.php');
$timezones = get_list_of_timezones();
//get user timezone
if ($plannerobj->timezone == '99'){
    $current = $USER->timezone;
	if ($current == '99') {
		$current = $CFG->timezone;
	}
} else {
    $current = $plannerobj->timezone;
}
echo '<td>'.html_writer::select($timezones, "timezone", $current, array('99'=>get_string("serverlocaltime"))).'</td>';
echo '</tr>';
echo '<tr>';
echo '<td valign="top">'.get_string('dates','chairman').':</td>';
echo '<td>';
echo '<table><tr>';
echo '<td style="border:1px solid black;" valign="top">';
echo '<b>'.get_string('day','chairman').'</b><br/>';
echo '<div class="datefield"><input type="text" id="date" name="date" size="8" style="padding:3px;vertical-align:bottom;" />';
echo '<button type="button" id="show" title="Show Calendar" style="vertical-align:bottom;"><img src="calbtn.gif" width="18" height="18" alt="Calendar" ></button></div>';
echo '<br/><b>'.get_string('from','chairman').'</b><br/>';
echo render_timepicker('from',time()).'<br/>';
echo '<br/><b>'.get_string('to','chairman').'</b><br/>';
echo render_timepicker('to',(time()+(60*60)));
echo '</td>';
echo '<td><button type="button" onclick="planner_add_date();"><img src="right.gif"></button><br/><button type="button" onclick="planner_remove_date();"><img src="garbage.gif"></button></td>';
echo '<td style="border:1px solid black;"><div id="listerror" style="font-size:10px;color:red;"></div><select name="list[]" id="list" multiple="multiple" style="width:300px;height:150px;">';
if($planner != 0){
    $dates_obj = $DB->get_records('chairman_planner_dates',array('planner_id'=>$planner));
    foreach($dates_obj as $date_obj){
        $value = date('d/n/Y@H:i@',$date_obj->from_time).date('H:i',$date_obj->to_time);
        $text = date('d/n/Y H:i - ',$date_obj->from_time).date('H:i',$date_obj->to_time);
        echo '<option value="'.$value.'">'.$text.'</option>';
    }
}
echo '</select></td>';
echo '</tr></table>';
echo '</td>';
echo '</tr>';
//Notifications
    echo '<tr><td valign="top" colspan="2">'.get_string('sendnotificationnow', 'chairman').' : ';
    echo '<input type="checkbox" name="notify_now" value="1">';
    echo '</td></tr>';
echo '<tr>';
echo '<td></td>';
echo '<td><input type="button" value="'.get_string('submit','chairman').'" onclick="planner_submit()"/>';
echo '<input type="button" value="'.get_string('back','chairman').'" onclick="window.location.href=\''.$CFG->wwwroot.'/mod/chairman/planner.php?id='.$id.'\';" />';
echo '</td></tr>';
echo '</table></form>';

//footer
chairman_footer();

?>
