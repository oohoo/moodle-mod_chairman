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
function chairman_header($cmid, $pagename, $pagelink) {
    global $PAGE, $OUTPUT, $DB, $CFG, $chairman_name;

    $course_mod = $DB->get_record('course_modules', array('id' => $cmid));
    $chairman = $DB->get_record('chairman', array('id' => $course_mod->instance));
    $context = get_context_instance(CONTEXT_MODULE, $course_mod->id);

    require_course_login($course_mod->course, true, $course_mod);

    $chairman_name = $chairman->name;

    $navlinks = array(
        array('name' => get_string($pagename, 'chairman'), 'link' => $CFG->wwwroot . '/mod/chairman/' . $pagelink, 'type' => 'misc')
    );
    build_navigation($navlinks);

    $page = get_string($pagename, 'chairman');
    $title = $chairman_name . ': ' . $page;

    load_jQuery();
    
    $PAGE->requires->js('/mod/chairman/chairman.js');
    $PAGE->requires->css("/mod/chairman/style.css");
    
    $PAGE->requires->js('/mod/chairman/jquery/plugins/datatables/js/jquery.dataTables.min.js');  
    $PAGE->requires->css('/mod/chairman/jquery/plugins/datatables/css/jquery.dataTables_themeroller.css');

    $PAGE->set_url('/mod/chairman/' . $pagelink);
    $PAGE->set_title($chairman_name);
    $PAGE->set_heading($title);
    $PAGE->set_context($context);

    echo $OUTPUT->header();


    chairman_global_js($cmid);
    chairman_structure($chairman, $pagename, $cmid);
}

function load_jQuery() {
    global $PAGE;
    
    if (moodle_major_version() >= '2.5') {
        $PAGE->requires->jquery();
        $PAGE->requires->jquery_plugin('migrate');
        $PAGE->requires->jquery_plugin('ui');
        $PAGE->requires->jquery_plugin('ui-css');
    } else {
        $PAGE->requires->js("/mod/chairman/jquery/core/jquery-1.9.1.js");
        $PAGE->requires->js("/mod/chairman/jquery/core/jquery-ui.min.js");
        $PAGE->requires->css("/mod/chairman/jquery/core/themes/base/jquery.ui.all.css");
    }
}

/**
 * Generate global language strings that will be used by javascript
 * 
 * @param type $cmid
 */
function chairman_global_js($cmid) {
    global $CFG;
    echo '<script>';
    echo 'var php_strings = new Array();';
    echo 'php_strings = new Array();';
    echo 'php_strings["addlink"] = "' . get_string('addlink', 'chairman') . '";';
    echo 'php_strings["emptyname"] = "' . get_string('emptyname', 'chairman') . '";';
    echo 'php_strings["emptylink"] = "' . get_string('emptylink', 'chairman') . '";';
    echo 'php_strings["form_info_default"] = "' . get_string('form_info_default', 'chairman') . '";';
    echo 'php_strings["cancel"] = "' . get_string('cancel') . '";';
    echo 'php_strings["export"] = "' . get_string('export','chairman') . '";';
    echo 'php_strings["id"] = "' . $cmid . '";';
    echo 'php_strings["remove_link"] = "' . get_string('remove_link', 'chairman') . '";';
    echo 'php_strings["link_ajax_sending"] = "' . get_string('link_ajax_sending', 'chairman') . '";';
    echo 'php_strings["link_ajax_failed"] = "' . get_string('link_ajax_failed', 'chairman') . '";';
    echo "php_strings['ajax_url'] = '$CFG->wwwroot/mod/chairman/link_controller.php';";
    echo "php_strings['wwwroot']  = '$CFG->wwwroot';";
    echo 'php_strings["itemsSelected_nil"] = "' . get_string('itemsSelected_nil', 'chairman') . '";';
    echo 'php_strings["itemsSelected"] = "' . get_string('itemsSelected', 'chairman') . '";';
    echo 'php_strings["itemsSelected_plural"] = "' . get_string('itemsSelected_plural', 'chairman') . '";';
    echo 'php_strings["itemsAvailable_nil"] = "' . get_string('itemsAvailable_nil', 'chairman') . '";';
    echo 'php_strings["itemsAvailable"] = "' . get_string('itemsAvailable', 'chairman') . '";';
    echo 'php_strings["itemsAvailable_plural"] = "' . get_string('itemsAvailable_plural', 'chairman') . '";';
    echo 'php_strings["itemsFiltered_nil"] = "' . get_string('itemsFiltered_nil', 'chairman') . '";';
    echo 'php_strings["itemsFiltered"] = "' . get_string('itemsFiltered', 'chairman') . '";';
    echo 'php_strings["itemsFiltered_plural"] = "' . get_string('itemsFiltered_plural', 'chairman') . '";';
    echo 'php_strings["selectAll"] = "' . get_string('selectAll', 'chairman') . '";';
    echo 'php_strings["deselectAll"] = "' . get_string('deselectAll', 'chairman') . '";';
    echo 'php_strings["search"] = "' . get_string('search', 'chairman') . '";';
    echo 'php_strings["event_search_error"] = "' . get_string('event_search_error', 'chairman') . '";';
    echo 'php_strings["export_pdf_email"] = "' . get_string('export_pdf_email', 'chairman') . '";';
    echo "php_strings['ajax_event_search_url'] = '$CFG->wwwroot/mod/chairman/chairman_events/events.php';";
    echo 'php_strings["export_pdf_email_public"] = "' . get_string('export_pdf_email_public', 'chairman') . '";';
    echo 'php_strings["export_pdf_email_public_warning"] = "' . get_string('export_pdf_email_public_warning', 'chairman') . '";';
    echo 'php_strings["export_pdf_email_private"] = "' . get_string('export_pdf_email_private', 'chairman') . '";';
    
    echo "php_strings['select2_no_matches'] = '" . get_string('select2_no_matches', 'chairman') . "';";
    echo "php_strings['select2_plural_extension'] = '" . get_string('select2_plural_extension', 'chairman') . "';";
    echo "php_strings['select2_enter'] = '" . get_string('select2_enter', 'chairman') . "';";
    echo "php_strings['select2_additional_chars'] = '" . get_string('select2_additional_chars', 'chairman') . "';";
    echo "php_strings['select2_remove_chars'] = '" . get_string('select2_remove_chars', 'chairman') . "';";
    echo "php_strings['select2_chars'] = '" . get_string('select2_chars', 'chairman') . "';";
    echo "php_strings['select2_only_select'] = '" . get_string('select2_only_select', 'chairman') . "';";
    echo "php_strings['select2_item'] = '" . get_string('select2_item', 'chairman') . "';";
    echo "php_strings['select2_loading_more'] = '" . get_string('select2_loading_more', 'chairman') . "';";
    echo "php_strings['select2_searching'] = '" . get_string('select2_searching', 'chairman') . "';";
    
    echo "php_strings['search_moodle_users'] = '" . get_string('search_moodle_users', 'chairman') . "';";
    echo "php_strings['no_guests'] = '" . get_string('no_guests', 'chairman') . "';";
    
    echo "php_strings['agenda_archive_topic_title'] = '" . get_string('agenda_archive_topic_title', 'chairman') . "';";
    echo "php_strings['agenda_archive_motion_title'] = '" . get_string('agenda_archive_motion_title', 'chairman') . "';";
    
    //data tables
    echo "php_strings['sEmptyTable'] = '" . get_string('sEmptyTable', 'chairman') . "';";
    echo "php_strings['sInfo'] = '" . get_string('sInfo', 'chairman') . "';";
    echo "php_strings['sInfoEmpty'] = '" . get_string('sInfoEmpty', 'chairman') . "';";
    echo "php_strings['sInfoFiltered'] = '" . get_string('sInfoFiltered', 'chairman') . "';";
    echo "php_strings['sInfoPostFix'] = '" . get_string('sInfoPostFix', 'chairman') . "';";
    echo "php_strings['sInfoThousands'] = '" . get_string('sInfoThousands', 'chairman') . "';";
    echo "php_strings['sLengthMenu'] = '" . get_string('sLengthMenu', 'chairman') . "';";
    echo "php_strings['sLoadingRecords'] = '" . get_string('sLoadingRecords', 'chairman') . "';";
    echo "php_strings['sProcessing'] = '" . get_string('sProcessing', 'chairman') . "';";
    echo "php_strings['sSearch'] = '" . get_string('sSearch', 'chairman') . "';";
    echo "php_strings['sZeroRecords'] = '" . get_string('sZeroRecords', 'chairman') . "';";
    echo "php_strings['sFirst'] = '" . get_string('sFirst', 'chairman') . "';";
    echo "php_strings['sLast'] = '" . get_string('sLast', 'chairman') . "';";
    echo "php_strings['sNext'] = '" . get_string('sNext', 'chairman') . "';";
    echo "php_strings['sPrevious'] = '" . get_string('sPrevious', 'chairman') . "';";
    echo "php_strings['sSortAscending'] = '" . get_string('sSortAscending', 'chairman') . "';";
    echo "php_strings['sSortDescending'] = '" . get_string('sSortDescending', 'chairman') . "';";
    
    echo '</script>';
}

/**
 * Generate the overall structure of chairman module. This includes the
 * navigation menu, and content area.
 * 
 * @param type $chairman
 * @param type $cmid
 */
function chairman_structure($chairman, $pagename, $cmid) {
    echo "<div id='chairman_root' class='ui-state-default ui-corner-all chairman_container'>";
    echo "<div id='chairman_root_container' class='chairman_container'>";
    echo "<h1>$chairman->name</h1>";


    echo "<div id='chairman_nav_root' class='chairman_container ui-widget-header ui-corner-all chairman_nav_container'>";
    chairman_menu($chairman, $pagename, $cmid);
    chairman_links($chairman, $cmid);
    echo "</div>";

    chairman_main();
}

/**
 * Display the footer details for the chairman module.
 * 
 * @global type $OUTPUT
 */
function chairman_footer() {
    global $OUTPUT;

    chairman_basic_footer();

    echo $OUTPUT->footer();
}

/**
 * Display the footer details for the chairman module.
 * Doesn't actually call the output. This can be used before a redirect.
 * 
 * @global type $OUTPUT
 */
function chairman_basic_footer() {

     echo "</div>";
    echo "</div>"; //end main area
    echo "</div>"; //end root container
    echo "</div>"; //end root

    add_link_dialogs();
}

/**
 * Generate and output the overall menu for the application.
 * 
 * @global type $CFG
 * @global moodle_database $DB
 * @param type $chairman
 * @param type $id
 */
function chairman_menu($chairman, $pagename, $id) {
    global $CFG, $DB, $USER;
    
    $select = "chairman_id = ? and " . $DB->sql_compare_text('page_code') . " = ?";
    $menu_state = $DB->get_record_select('chairman_menu_state', $select, array($chairman->id, $pagename));
    $member = $DB->get_record('chairman_members', array('chairman_id' => $id, 'user_id' => $USER->id));
    
    $state = 0;
    if ($menu_state && $menu_state->state == 1)
        $state = 1;

    echo "<script>var menu_state_default = $state;</script>";


    //collapsable icon
    echo "<div id='chairman_menu_collapse_root' class='chairman_container chairman_menu_container'>";
    echo "<span id='menu_title' class='nav_title'>" . get_string("navigation", 'chairman') . "</span>";
    echo "<div id='chairman_menu_collapse_button' class='ui-state-default ui-corner-all'>";
    echo "<span id='chairman_menu_collapse' class='ui-icon ui-icon-arrowthickstop-1-w'/>";
    echo "</div>";
    echo "</div>";

    echo "<div id='chairman_menu_container' class='chairman_container chairman_menu_container'>";

    echo "<ul id='chairman_menu' class='chairman_menu'>";

    echo '<li><a href="' . $CFG->wwwroot . '/mod/chairman/view.php?id=' . $id . '">' . get_string('members', 'chairman') . '</a></li>';
    echo '<li><a href="' . $CFG->wwwroot . '/mod/chairman/chairman_planner/planner.php?id=' . $id . '">' . get_string('planner', 'chairman') . '</a></li>';
    echo '<li><a href="' . $CFG->wwwroot . '/mod/chairman/chairman_events/events.php?id=' . $id . '">' . get_string('events', 'chairman') . '</a></li>';
    echo '<li><a href="' . $CFG->wwwroot . '/mod/chairman/chairman_meetingagenda/viewer.php?chairman_id=' . $id . '">' . get_string('agendas', 'chairman') . '</a></li>';
    echo '<li><a href="' . $CFG->wwwroot . '/mod/chairman/chairman_filesystem/file_form.php?id=' . $id . '">' . get_string('files', 'chairman') . '</a></li>';

    if ($chairman->use_forum == 1) {

        echo '<li><a href="' . $CFG->wwwroot . '/mod/forum/view.php?id=' . $chairman->forum . '" target="_blank">' . get_string('menu_forum', 'chairman') . '</a></li>';
    }
    if ($chairman->use_wiki == 1) {

        echo '<li><a href="' . $CFG->wwwroot . '/mod/wiki/view.php?id=' . $chairman->wiki . '" target="_blank">' . get_string('menu_wiki', 'chairman') . '</a></li>';
    }
    if (isset($chairman->use_questionnaire) && $chairman->use_questionnaire == 1) {

        echo '<li><a href="' . $CFG->wwwroot . '/mod/questionnaire/view.php?id=' . $chairman->questionnaire . '" target="_blank">' . get_string('menu_questionnaire', 'chairman') . '</a></li>';
    }
    $role_id = '';
    if (isset($member->role_id)) {
        $role_id = $member->role_id;
    }
    if (($role_id == 1) || ($role_id == 2) || ($role_id == 4) || (chairman_isadmin($USER->id))) {
       
        $cm = get_coursemodule_from_id('chairman', $id);
        echo '<li><a href="' . $CFG->wwwroot . '/report/log/index.php?chooselog=1&id=' . $cm->course . '&modid=' . $cm->id . '">' . get_string('view_logs', 'chairman') . '</a></li>';
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
function chairman_links($chairman, $cmid) {
    global $USER, $DB;
    //if links exist

    $link_records = $DB->get_records('chairman_links', array("chairman_id" => $cmid), "name ASC");

    echo "<div id='chairman_links_container' class='chairman_container chairman_links_container'>";

    echo "<span id='link_title' class='nav_title'>" . get_string("external_link_label", 'chairman') . "</span>";

    echo "<ul id='chairman_links' class='chairman_menu'>";

    foreach ($link_records as $link) {
        $URL = $link->link;

        //For case when user only adds: www.asdf.com
        if (!(strpos($URL, 'http') === 0)) {
            $URL = "http://" . $URL;
        }
        
        
        $remove_icon = '';
        if(chairman_isadmin($USER->id))
            $remove_icon = "<span style='float:left' name='delete_link_" . $link->id . "' class='ui-icon ui-icon-minusthick'/>";
        
        
        echo "<li id='link_$link->id'><a target='_blank' href='$URL'>$link->name$remove_icon</a></li>";
    }


    if(chairman_isadmin($USER->id))
        echo '<li><a id="chairman_add_link" href="javascript:void(0)"/>' . get_string('new_external_link', 'chairman') . '<span class="ui-icon ui-icon-plusthick"/></a></li>';

    echo "</ul>";

    echo "</div>";
}

/**
 * Generate and output the start of the main content area.
 * 
 */
function chairman_main() {
    echo "<div id='chairman_main_container' class='chairman_container chairman_main_container'>";
    echo "<div id='chairman_main' class='chairman_container chairman_main_container ui-widget-content ui-corner-all'>";
}

/**
 * Generates and outputs the required dialogs for adding and removing dialogs.
 * 
 */
function add_link_dialogs() {

    echo "<div id='link_dialog_form' title='" . get_string('addlinklabel', 'chairman') . "'>";
    echo "<p class='form_information'>" . get_string('form_info_default', 'chairman') . "</p>";
    echo "<form>";
    echo "<fieldset>";
    echo "<label id='chairman_link_name_label' for='chairman_link_name'>" . get_string('linknamelabel', 'chairman') . "</label>";
    echo "<input type='text' name='chairman_link_name' id='chairman_link_name' class='text ui-widget-content ui-corner-all' />";
    echo "<label id='chairman_link_label' for='chairman_link'>" . get_string('linklabel', 'chairman') . "</label>";
    echo "<input type='text' name='chairman_link' id='chairman_link' value='' class='text ui-widget-content ui-corner-all' />";
    echo "</fieldset>";
    echo "</form>";
    echo "</div>";

    echo '<div id="link_delete_confirm" title="' . get_string('delete_link', 'chairman') . '">';
    echo '<p><span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>' . get_string('delete_link_msg', 'chairman') . '</p>';
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


function chairman_ajax_check($id) {  
    chairman_check($id, $silent = true, $redirect = false);
}

function chairman_check($id, $silent = false, $redirect = true) {
    global $USER, $DB, $CFG, $SESSION;

    if($silent)
        $error = function($message) { throw new Exception(); };
    else
        $error = function($message) { print_error($message); };
    
    // checks
    if ($id) {
        if (!$cm = get_coursemodule_from_id('chairman', $id)) {
            $error("Course Module ID was incorrect");
        }

        if (!$course = $DB->get_record("course", array("id" => $cm->course))) {
            $error("Course is misconfigured");
        }

        if (!$chairman = $DB->get_record("chairman", array("id" => $cm->instance))) {
            $error("Course module is incorrect");
        }
    } else {
        if (!$chairman = $DB->get_record("chairman", array("id" => $l))) {
            $error("Course module is incorrect");
        }
        if (!$course = $DB->get_record("course", array("id" => $chairman->course))) {
            $error("Course is misconfigured");
        }
        if (!$cm = get_coursemodule_from_instance("chairman", $chairman->id, $course->id)) {
            $error("Course Module ID was incorrect");
        }
    }

    require_course_login($course, true, $cm, !$redirect);

    //context needed for access rights
    $context = get_context_instance(CONTEXT_USER, $USER->id);

    global $cm;

    //If not a member, get out
    if ($chairman->secured == 1) {
        if ((!chairman_isMember($id)) AND (!chairman_isadmin($id))) {
           if($redirect)
            redirect($CFG->wwwroot . '/course/view.php?id=' . $course->id, get_string('not_member', 'mod_chairman'), 10);
        else 
            $error("");
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

            echo ' <input type="image" title="'.get_string('create_meeting', 'chairman').'" src="../pix/create_meeting.png" />';
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
 *     
 *    If moodle's timezone is set to server default it becomes 99 - which is invalid, and no origin is provided - returns false
 *    If the remote or origin timezone is 99, it returns false. 
 *    If remote or origin cannot be resolved to a valid timezone - it returns false - ex: 99999, asdf, or -5.5, etc
 * 
 *    The function accepts: -UTC parameters such as 9.0, -5.0, 0, etc. 
 *                          -Timezone inputs(IF MOODLE HAS THEM DOWNLOADED): ex: America/Edmonton
 * 
 *    @param $remote_tz The reference point for the meeting. AKA: The meeting is held at 8:00 UTF-8.
 *    @param $origin_tz; The user's timezone or moodle's server's timezone if not set.
 *    @return int offset in seconds or false on failure (moodle timezone not set or remote not set)
 */
function chairman_get_timezone_offset($remote_tz, $origin_tz = null) {
    global $DB;
    
    if ($origin_tz === null || $origin_tz == 99) {
        $timezone = $DB->get_record('config', array('name'=>'timezone'));
        $origin_tz = $timezone->value;
    }
    
    //if either the remote or origin is 99, we cannot do offset
    if($remote_tz == 99 || $origin_tz == 99) return false;
    
    if(is_numeric($remote_tz)) {
        $remote_timezonename = timezone_name_from_abbr(null, $remote_tz * 3600, true);
        if($remote_timezonename === false) $remote_timezonename = timezone_name_from_abbr(null, $remote_tz * 3600, false);
        
        
    if ($remote_timezonename === false)
        return false;
    
    } else {
       $remote_timezonename = $remote_tz;
    }
    
    if(is_numeric($origin_tz)) {
        
        $origin_timezonename = timezone_name_from_abbr(null, $origin_tz * 3600, true);
        if($origin_timezonename === false) $origin_timezonename = timezone_name_from_abbr(null, $origin_tz * 3600, false);
        
        if ($origin_timezonename === false)
            return false;
    } else {
        $origin_timezonename = $origin_tz;
    }
    
    try{
    $origin_dtz = new DateTimeZone($origin_timezonename);
    $remote_dtz = new DateTimeZone($remote_timezonename);
    $origin_dt = new DateTime("now", $origin_dtz);
    $remote_dt = new DateTime("now", $remote_dtz);
    } catch(Exception $e)
    {
        return false;
    }
    
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

/**
 * Determines  the start / end dates for the current year as specified by start
 * month & day of each year. (To accomidate for academic or custom year definitions.
 */
function chairman_get_year_definition($cmid, $now=null) {
    
    global $DB;
    
    if(!$now)
        $now = new DateTime();
    
    $cm = get_coursemodule_from_id('chairman', $cmid);
    $chairman = $DB->get_record('chairman', array("id"=>$cm->instance));
    
    $start_time = mktime(0, 0, 0, $chairman->start_month_of_year, 01, $now->format("Y"));
    $start_year = new DateTime();
    $start_year->setTimestamp($start_time);

    $interval = $now->diff($start_year, false);
    
    //If current date is less than start date then subtract a year for the start date
    //invert specifies if its a negative comparision (before now) && d > 0 ensures that if
    //start day is today - it restarts
    if (!$interval || $interval->invert === 0 || ($interval->y == 1 && $interval->m === 0 && $interval->d === 0) )
        $start_year->sub(new DateInterval("P1Y"));

    $end_year = new DateTime();
    $end_year->setTimestamp($start_year->getTimestamp());
    $end_year->add(new DateInterval("P1Y"));

    return array($start_year, $now, $end_year);
}

/**
 * Returns the earliest year that a meeting, and therefore angenda can exist.
 * 
 * @param int $chairman_id
 */
function getMinEventYear($chairman_id) {
    global $DB;
    
    $date_time = new DateTime();

    $sql = "SELECT MIN(year) as year from {chairman_events} " .
            "WHERE chairman_id=? ";

    $min_years_events = $DB->get_records_sql($sql, array($chairman_id));

    if (empty($min_years_events))
        $min_year = $date_time->format('Y');
    else {
        $years = array_keys($min_years_events);
        $min_year = $years[0];
    }

    return $min_year;
}

/**
 * Based on a given month, where jan = 1, feb = 2, ...
 * This function will return the string representation of that month.
 * 
 * @param int $month_num
 * @return string string representation of month
 */
function chairman_get_month($month_num) {
    switch ($month_num) {
        case 1: return get_string('january', 'chairman');
        case 2: return get_string('february', 'chairman');
        case 3: return get_string('march', 'chairman');
        case 4: return get_string('april', 'chairman');
        case 5: return get_string('may', 'chairman');
        case 6: return get_string('june', 'chairman');
        case 7: return get_string('july', 'chairman');
        case 8: return get_string('august', 'chairman');
        case 9: return get_string('september', 'chairman');
        case 10: return get_string('october', 'chairman');
        case 11: return get_string('november', 'chairman');
        case 12: return get_string('december', 'chairman');

        default: return "";
    }
}

/**
 * Retrieves the logo file for a particular chairman module
 * 
 * @param int $cmid course module id
 * @return A file record of the chairman logo, or the default if not avaliable
 */
function chairman_get_logo_file($cmid) {
    global $CFG;
    
    //get filesystem
    $fs = get_file_storage();
    
    //get context
    $context = context_module::instance($cmid);
    
    //get all area files in chairman logo for this context
    //there should be at MOST: current directory "." and a logo
    $files = $fs->get_area_files($context->id, 'mod_chairman', 'chairman_logo');
    
    //attempt to find logo, and if one is present - return that file
    foreach ($files as $file) {
        if($file->get_filename() != ".") {//do not return current directory
            return $file;//found logo
        }
    }
    
    
    //NO LOGO AVALIABLE - use or load default
    //default file
    $file = $fs->get_file($context->id, 'mod_chairman', 'chairman_logo_default', 0, '/', 'default_logo.jpeg');
    
    //see if default logo has already been loaded before
    if($file) {
         return $file;//default logo found - use it
    }
    
    //default logo has never been used
    //we are going to add it to its own filearea
    //
    //This is done to allow chairman to handle user uploaded logos and the default uploaded logos to be
    //treated in the same way
    $record = new stdClass();
        $record->contextid = $context->id;
        $record->component = 'mod_chairman';
        $record->filearea = 'chairman_logo_default';
        $record->itemid = 0;
        $record->filename = 'default_logo.jpeg';
        $record->filepath = '/';
    
    //create default logo file from a file in chairman imgs
    $default_file = $fs->create_file_from_pathname($record, "$CFG->dirroot/mod/chairman/img/default_logo.jpeg");
    
    //return default
    return $default_file;
}

/**
 * 
 * @param int $cmid course module id
 * @return A file record of the chairman logo, or the default if not avaliable
 */
function chairman_get_logo_url($cmid) {   
    $logo = chairman_get_logo_file($cmid);
    
   return moodle_url::make_pluginfile_url($logo->get_contextid(), $logo->get_component(), $logo->get_filearea(), $logo->get_itemid(), $logo->get_filepath(), $logo->get_filename());
}

?>
