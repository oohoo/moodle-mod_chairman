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
 * Generate the standard header for the chairman module.
 * 
 * @global type $PAGE
 * @global type $OUTPUT
 * @global moodle_database $DB
 * @global type $CFG
 * @param type $title
 * @param type $pagename
 * @param type $pagelink
 * @param type $cmid
 */
function chairman_header($cmid,$pagename,$pagelink)
{
    global $PAGE, $OUTPUT, $DB, $CFG;
    
    $course_mod = $DB->get_record('course_modules', array('id'=>$cmid));
    $chairman = $DB->get_record('chairman', array('id'=>$course_mod->instance));
    $context = get_context_instance(CONTEXT_MODULE, $course_mod->id);
    
    require_course_login($course_mod->course, true, $course_mod);
    
    $chairman_name = $chairman->name;
    
    $navlinks = array(
            array('name' => get_string($pagename,'chairman'), 'link' => $CFG->wwwroot.'/mod/chairman/'.$pagelink, 'type' => 'misc')
    );
    build_navigation($navlinks);
    
    $page = get_string($pagename, 'chairman');
    $title = $chairman_name . ': ' . $page;
    
    $PAGE->requires->jquery();
    $PAGE->requires->jquery_plugin('ui');
    $PAGE->requires->jquery_plugin('ui-css');
    $PAGE->requires->js('/mod/chairman/chairman.js');
    $PAGE->requires->css("/mod/chairman/style.css");
    
    $PAGE->set_url('/mod/chairman/'.$pagelink);
    $PAGE->set_title($chairman_name);
    $PAGE->set_heading($title);
    $PAGE->set_context($context);
    
    echo $OUTPUT->header();
    
    
    chairman_global_js($cmid);
    chairman_structure($chairman, $cmid);
    
}

/**
 * Generate global language strings that will be used by javascript
 * 
 * @param type $cmid
 */
function chairman_global_js($cmid)
{
    echo '<script>';
    echo 'var php_strings = new Array();';
    echo 'php_strings = new Array();';
    echo 'php_strings["addlink"] = "'.get_string('addlink','chairman').'";';
    echo 'php_strings["emptyname"] = "'.get_string('emptyname','chairman').'";';
    echo 'php_strings["emptylink"] = "'.get_string('emptylink','chairman').'";';
    echo 'php_strings["form_info_default"] = "'.get_string('form_info_default','chairman').'";';
    echo 'php_strings["cancel"] = "'.get_string('cancel').'";';
    echo 'php_strings["id"] = "'.$cmid.'";';
    echo 'php_strings["remove_link"] = "'.get_string('remove_link','chairman').'";';
    echo 'php_strings["link_ajax_sending"] = "'.get_string('link_ajax_sending','chairman').'";';
    echo 'php_strings["link_ajax_failed"] = "'.get_string('link_ajax_failed','chairman').'";';
    
    
    
    echo '</script>';
    
}

/**
 * Generate the overall structure of chairman module. This includes the
 * navigation menu, and content area.
 * 
 * @param type $chairman
 * @param type $cmid
 */
function chairman_structure($chairman, $cmid)
{
    echo "<div id='chairman_root' class='chairman_container'>";
    echo "<div id='chairman_root_container' class='chairman_container'>";
    echo "<h1>$chairman->name</h1>";
    
    
    echo "<div id='chairman_nav_root' class='chairman_container chairman_nav_container'>";
    chairman_menu($chairman, $cmid);
    chairman_links($chairman, $cmid);
    echo "</div>";
    
    chairman_main();
    
}

/**
 * Display the footer details for the chairman module.
 * 
 * @global type $OUTPUT
 */
function chairman_footer()
{
    global $OUTPUT;
    
    echo "</div>";//end main area
    echo "</div>";//end root container
    echo "</div>";//end root
    
    add_link_dialogs();
    
    echo $OUTPUT->footer();
}

/**
 * Generate and output the overall menu for the application.
 * 
 * @global type $CFG
 * @param type $chairman
 * @param type $id
 */
function chairman_menu($chairman, $id)
{
    global $CFG;
    
    
    //collapsable icon
    echo "<div id='chairman_menu_collapse_root' class='chairman_container chairman_menu_container'>";
    echo "<span id='menu_title' class='nav_title'>".get_string("navigation", 'chairman')."</span>";
    echo "<div id='chairman_menu_collapse_button' class='ui-state-default ui-corner-all'>";
    echo "<span id='chairman_menu_collapse' class='ui-icon ui-icon-arrowthickstop-1-w'/>";
    echo "</div>";
    echo "</div>";
    
    echo "<div id='chairman_menu_container' class='chairman_container chairman_menu_container'>";
    
    echo "<ul id='chairman_menu'>";
    
    echo '<li><a href="'.$CFG->wwwroot.'/mod/chairman/view.php?id='.$id.'">'.get_string('members', 'chairman').'</a></li>';
    echo '<li><a href="'.$CFG->wwwroot.'/mod/chairman/chairman_planner/planner.php?id='.$id.'">'.get_string('planner', 'chairman').'</a></li>';
    echo '<li><a href="'.$CFG->wwwroot.'/mod/chairman/chairman_events/events.php?id='.$id.'">'.get_string('events', 'chairman').'</a></li>';
    echo '<li><a href="'.$CFG->wwwroot.'/mod/chairman/chairman_meetingagenda/viewer.php?chairman_id='.$id.'">'.get_string('agendas', 'chairman').'</a></li>';
    echo '<li><a href="'.$CFG->wwwroot.'/mod/chairman/chairman_filesystem/file_form.php?id='.$id.'">'.get_string('files', 'chairman').'</a></li>';
    
    if ($chairman->use_forum == 1) {

        echo '<li><a href="'.$CFG->wwwroot.'/mod/forum/view.php?id='.$chairman->forum.'" target="_blank">'.get_string('menu_forum', 'chairman').'</a></li>';

    }
    if ($chairman->use_wiki == 1) {

        echo '<li><a href="'.$CFG->wwwroot.'/mod/wiki/view.php?id='.$chairman->wiki.'" target="_blank">'.get_string('menu_wiki', 'chairman').'</a></li>';

    }
    if (isset($chairman->use_questionnaire) && $chairman->use_questionnaire == 1) {

        echo '<li><a href="'.$CFG->wwwroot.'/mod/questionnaire/view.php?id='.$chairman->questionnaire.'" target="_blank">'.get_string('menu_questionnaire', 'chairman').'</a></li>';

    }
    
    echo "</ul>";
    
    echo "</div>";
    
}

/**
 * Generate the external links for the current chairman module.
 * 
 * @global type $USER
 * @global moodle_database $DB
 * @param type $chairman
 * @param type $cmid
 */
function chairman_links($chairman, $cmid)
{
    global $USER, $DB;
    //if links exist
    
    $link_records = $DB->get_records('chairman_links', array("chairman_id"=>$cmid), "name ASC");
    
    echo "<div id='chairman_links_container' class='chairman_container chairman_links_container'>";
    
    echo "<span id='link_title' class='nav_title'>".get_string("external_link_label", 'chairman')."</span>";
    
    echo "<ul id='chairman_links'>";
    
    foreach ($link_records as $link)
    {
     $URL = $link->link;
    
    //For case when user only adds: www.asdf.com
    if (!(strpos($URL, 'http') === 0)) {
      $URL = "http://". $URL; 
    }
     
        echo "<li id='link_$link->id'><a target='_blank' href='$URL'>$link->name<span style='float:left' name='delete_link_".$link->id."' class='ui-icon ui-icon-minusthick'/></a></li>";
    }
    
    
    //if admin
    echo '<li><a id="chairman_add_link" href="javascript:void(0)"/>'.get_string('new_external_link','chairman' ).'<span class="ui-icon ui-icon-plusthick"/></a></li>';
    
    echo "</ul>";
    
    echo "</div>";
    
}

/**
 * Generate and output the start of the main content area.
 * 
 */
function chairman_main()
{
    echo "<div id='chairman_main_container' class='chairman_container chairman_main_container'>";
    echo "<div id='chairman_main' class='chairman_container chairman_main_container'>";
}

/**
 * Generates and outputs the required dialogs for adding and removing dialogs.
 * 
 */
function add_link_dialogs()
{
    
  echo "<div id='link_dialog_form' title='".get_string('addlinklabel','chairman')."'>";
  echo "<p class='form_information'>".get_string('form_info_default','chairman')."</p>";
  echo "<form>";
  echo "<fieldset>";
  echo "<label id='chairman_link_name_label' for='chairman_link_name'>".get_string('linknamelabel','chairman')."</label>";
  echo "<input type='text' name='chairman_link_name' id='chairman_link_name' class='text ui-widget-content ui-corner-all' />";
  echo "<label id='chairman_link_label' for='chairman_link'>".get_string('linklabel','chairman')."</label>";
  echo "<input type='text' name='chairman_link' id='chairman_link' value='' class='text ui-widget-content ui-corner-all' />";
  echo "</fieldset>";
  echo "</form>";
  echo "</div>";
    
  echo '<div id="link_delete_confirm" title="'.get_string('delete_link','chairman').'">';
  echo '<p><span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>'.get_string('delete_link_msg','chairman').'</p>';
  echo '</div>';
  
}


/**
 * Gets Firstname Lastname and email of chairman members.
 *
 * @global $DB
 * @param id The chairman id
 * @return array of users
 */
function get_chairman_members($id) {
    global $DB;
    $membersql = "SELECT {user}.id, {user}.firstname, {user}.lastname, {user}.email
                  FROM {user} INNER JOIN {chairman_members} ON {user}.id = {chairman_members}.user_id
                  WHERE {chairman_members}.chairman_id = $id";


    $members = $DB->get_records_sql($membersql);

    return $members;
}

function chairman_check($id) {
    global $USER, $DB, $CFG, $SESSION;

    // checks
    if ($id) {
        if (!$cm = get_coursemodule_from_id('chairman', $id)) {
            print_error("Course Module ID was incorrect");
        }

        if (!$course = $DB->get_record("course", array("id" => $cm->course))) {
            print_error("Course is misconfigured");
        }

        if (!$chairman = $DB->get_record("chairman", array("id" => $cm->instance))) {
            print_error("Course module is incorrect");
        }
    } else {
        if (!$chairman = $DB->get_record("chairman", array("id" => $l))) {
            print_error("Course module is incorrect");
        }
        if (!$course = $DB->get_record("course", array("id" => $chairman->course))) {
            print_error("Course is misconfigured");
        }
        if (!$cm = get_coursemodule_from_instance("chairman", $chairman->id, $course->id)) {
            print_error("Course Module ID was incorrect");
        }
    }

    require_course_login($course, true, $cm);

    //context needed for access rights
    $context = get_context_instance(CONTEXT_USER, $USER->id);

    global $cm;
    //Set session logo
    if (!empty($chairman->logo)) {
        $SESSION->chairman_logo = "$CFG->dirroot/mod/chairman/img/logos/$chairman->logo";
    } else {
        $SESSION->chairman_logo = "$CFG->dirroot/mod/chairman/img/blank.jpg";
    }
    //If not a member, get out
    if ($chairman->secured == 1) {
        if ((!chairman_isMember($id)) AND (!chairman_isadmin($id))) {
            redirect($CFG->wwwroot . '/course/view.php?id=' . $course->id, get_string('not_member', 'mod_chairman'), 10);
        }
    }
}

function chairman_isadmin($id) {
    global $DB, $USER, $PAGE;
    $instance = $PAGE->course->id;
    $context = get_context_instance(CONTEXT_COURSE, $instance);
    if ($DB->get_record('chairman_members', array('chairman_id' => $id, 'role_id' => 2, 'user_id' => $USER->id)) || $DB->get_record('chairman_members', array('chairman_id' => $id, 'role_id' => 1, 'user_id' => $USER->id)) || $DB->get_record('chairman_members', array('chairman_id' => $id, 'role_id' => 4, 'user_id' => $USER->id)) || has_capability('moodle/course:update', $context) || is_siteadmin()) {
        return true;
    } else {
        return false;
    }
}

/*
 * Given an chairman id check to determine if the user is a member of the committee.
 * Does not check for just committee role of member, but any role of member, president, Vpresident, and admin.
 *
 */

function chairman_isMember($id) {
    global $DB, $USER;

    if ($DB->get_record('chairman_members', array('chairman_id' => $id, 'role_id' => 2, 'user_id' => $USER->id)) || $DB->get_record('chairman_members', array('chairman_id' => $id, 'role_id' => 1, 'user_id' => $USER->id)) || $DB->get_record('chairman_members', array('chairman_id' => $id, 'role_id' => 3, 'user_id' => $USER->id)) || $DB->get_record('chairman_members', array('chairman_id' => $id, 'role_id' => 4, 'user_id' => $USER->id))) {

        return true;
    } else {
        return false;
    }
}

/**
 * Returns markup for dropdown for time selection.
 *
 * @param string $name name and id to give our form element
 * @param int $time timestamp to match pre-selected value
 *
 * return string with HTML markup ready for output
 */
function render_timepicker($name, $time) {
    $hourtime = date('G', $time);
    $minutetime = date('i', $time);
    if ($minutetime < 10) {
        $minutetime = 10;
    } else if ($minutetime >= 50) {
        $hourtime += 1;
        $minutetime = 0;
    } else {
        $minutetime = ceil($minutetime / 10) * 10;
    }

    $string = '<select name="' . $name . '_time" id="' . $name . '_time">';
    for ($hour = 0; $hour <= 23; $hour++) {
        for ($minutes = 0; $minutes < 60; $minutes+=10) {
            if ($minutes == 0) {
                $output = '00';
            } else {
                $output = $minutes;
            }
            $string .= '<option value="' . $hour . ':' . $output . '" ';
            if ($hourtime == $hour && $minutetime == $minutes) {
                $string .= 'SELECTED';
            }
            $string .= '>' . $hour . ':' . $output . '</option>';
        }
    }
    $string .= '</select>';

    return $string;
}

/*
 * Function to display the planner results as a table
 *
 * @param int $planner_id The database id for the planner.
 */

function display_planner_results($planner_id, $chairman_id) {

    global $DB, $USER, $CFG;

    $planner = $DB->get_record('chairman_planner', array('id' => $planner_id));



//content
    echo '<table class="generaltable" style="margin-left:0;">';
    echo '<tr>';
    $dates = $DB->get_records('chairman_planner_dates', array('planner_id' => $planner->id), 'to_time ASC');
    $count = 0;
    $date_col = array();        //Keep track of what id is which column
    $date_col_count = array();  //Keep track of how many people can make this date
    $date_flag = array();       //If a required person cannot make it, flag it here

    foreach ($dates as $date) {
        //Timezone adjustment
        $user_timezone = $USER->timezone;
        if ($user_timezone == '99') {
            $region_tz = $CFG->timezone;
        } else {
            $region_tz = $USER->timezone;
        }
        $offset = chairman_get_timezone_offset($planner->timezone, $region_tz);
        //Calculate offset
        $local_user_time_from = $date->from_time + $offset;
        $local_user_time_to = $date->to_time + $offset;

        $date->from_time = $local_user_time_from;
        $date->to_time = $local_user_time_to;

        echo '<th class="header">' . strftime('%a %d %B, %Y', $date->from_time) . '<br/>';
        echo '<span style="font-size:10px;font-weight:normal;">' . date('H:i', $date->from_time) . ' - ' . date('H:i', $date->to_time) . '</span>';
        echo '</th>';
        $date_col[$count] = $date->id;
        $date_flag[$count] = false; //Initialise
        $date_col_count[$count] = 0;    //Initialise
        $count++;
    }
    echo '</tr>';

    $members = $DB->get_records('chairman_planner_users', array('planner_id' => $planner->id));
    $numberofmembers = $DB->count_records('chairman_planner_users', array('planner_id' => $planner->id));
    foreach ($members as $member) {
        echo '<tr>';
        $memberobj = $DB->get_record('chairman_members', array('id' => $member->chairman_member_id));
        $userobj = $DB->get_record('user', array('id' => $memberobj->user_id));
        if ($member->rule == 1) {
            $style = 'font-weight:bold;';
        } else {
            $style = '';
        }

        for ($i = 0; $i < $count; $i++) {

            if ($DB->get_record('chairman_planner_response', array('planner_user_id' => $member->id, 'planner_date_id' => $date_col[$i]))) {
                $date_col_count[$i]++;
            } else if ($member->rule == 1) {
                $date_flag[$i] = true;
            }
        }
        echo '</tr>';
    }

    echo '<tr>';

    for ($i = 0; $i < $count; $i++) {
        if ($date_flag[$i]) {
            $background = 'red';
            $percentage = '0';
        } else {
            $brilliance = ($date_col_count[$i]) / ($numberofmembers);
            $background = 'rgba(33,204,33,' . $brilliance . ')';
            $percentage = number_format($brilliance * 100, 0);
        }
        echo '<td class="cell" style="font-size:10px;height:20px;background-color:' . $background . ';">' .
        '<center><form method="post" action="planner_to_event_script.php">' . $percentage . '%';


        if (chairman_isadmin($chairman_id) && $percentage > 0) {

            echo ' <input type="image" src="../pix/accept.png" />';
            echo '<input type="hidden" name="date_id" value ="' . $date_col[$i] . '"/>';
            echo '<input type="hidden" name="chairman_id" value ="' . $chairman_id . '"/>';
            echo '</form>';
        }

        echo '</center></td>';
    }
    echo '</tr>';



    echo '</table>';
}

/**    Returns the offset from the origin timezone to the remote timezone, in seconds.
 *    @param $remote_tz;
 *    @param $origin_tz; If null the servers current timezone is used as the origin.
 *    @return int;
 */
function chairman_get_timezone_offset($remote_tz, $origin_tz = null) {
    if ($origin_tz === null) {
        if (!is_string($origin_tz = date_default_timezone_get())) {
            return false; // A UTC timestamp was returned -- bail out!
        }
    }
    $offset = 3600;
    $remote_timezonename = timezone_name_from_abbr("", $remote_tz * $offset, false);
    $origin_timezonename = timezone_name_from_abbr("", $origin_tz * $offset, false);

    if ($remote_timezonename === false || $origin_timezonename === false)
        return false;

    $origin_dtz = new DateTimeZone($origin_timezonename);
    $remote_dtz = new DateTimeZone($remote_timezonename);
    $origin_dt = new DateTime("now", $origin_dtz);
    $remote_dt = new DateTime("now", $remote_dtz);
    $offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);
    return $offset;
}

function chairman_convert_strdate_time($year, $day, $month, $hour, $minute) {
    if ($minute < 10) {
        $minute = '0' . $minute;
    }
    if ($hour < 10) {
        $hour = '0' . $hour;
    }
    if ($month < 10) {
        $month = '0' . $month;
    }
    if ($day < 10) {
        $day = '0' . $day;
    }
    $date_string = "$year-$month-$day" . " " . "$hour:$minute:00";
    $date = strtotime($date_string);
    return $date;
}

?>
