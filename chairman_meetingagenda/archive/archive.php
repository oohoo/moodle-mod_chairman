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
 * Displays an archive of all topics & motions for a given chairman module.
 * Archives are searchable on their title, desc, notes, files, etc.
 * 
 * The archives can be explicitly filtered by year.
 *  
 */
require_once("$CFG->dirroot/mod/chairman/chairman_meetingagenda/lib.php");

//inherited from parent page
global $PAGE, $chairman_id;


//include page specific js
$PAGE->requires->js('/mod/chairman/chairman_meetingagenda/archive/js/archive.js');

//include page specific css
echo "<link rel='stylesheet' type='text/css' href='$CFG->wwwroot/mod/chairman/chairman_meetingagenda/archive/css/archive.css' />";


//possible topic status:
$TOPIC_STATUS = array('open' => get_string('topic_open', 'chairman'),
    'in_progress' => get_string('topic_inprogress', 'chairman'),
    'closed' => get_string('topic_closed', 'chairman'));

//output archive
build_agenda_archive($chairman_id);

/**
 * Builds and outputs the archive page.
 * This includes preparing the data, and outputting the topics and motions tables.
 * 
 * @param int $chairman_id course module id
 */
function build_agenda_archive($chairman_id) {

    //prepare data for output
    $data = build_yearly_agenda_archive_data($chairman_id);

    //generate and output topics table
    build_agenda_topics_archive($chairman_id, $data);

    //generate and output motions table
    build_agenda_motions_archive($chairman_id, $data);
}

/**
 * Builds and outputs the topics table, and it's year filter.
 * 
 * @global Object $CFG
 * @global array $TOPIC_STATUS The meaning of each of the possible topic statuses
 * @param int $chairman_id course module id
 * @param array $data A multinested array containing the topic data: $data[YEAR]['topics']
 */
function build_agenda_topics_archive($chairman_id, $data) {
    global $CFG, $TOPIC_STATUS, $DB;

    //get filesystem for retrieving filenames for each topic
    $fs = get_file_storage();

    //output the year filter based on which years topics are avaliable
    output_year_selector('agenda_archive_topics_year_filter', 'agenda_archive_year_filter', $chairman_id, $data, 'topics');

    //output table with unique id & class that is shared with motions table
    echo '<table id="agenda_archive_topics" class="archive_table">';
    echo '<thead><tr>'; //headers
    echo '<th>' . get_string('agenda_archive_event', 'chairman') . '</th>'; //event
    echo '<th>' . get_string('agenda_archive_title', 'chairman') . '</th>'; //title
    echo '<th>' . get_string('agenda_archive_date', 'chairman') . '</th>'; //date
    echo '<th>' . get_string('agenda_archive_status', 'chairman') . '</th>'; //status
    echo '<th>' . get_string('agenda_archive_desc', 'chairman') . '</th>'; //desc
    echo '<th>' . get_string('agenda_archive_notes', 'chairman') . '</th>'; //notes   
    echo '<th>' . get_string('agenda_archive_files', 'chairman') . '</th>'; //filenames
    echo '<th></th>'; //year - hidden
    echo '<th></th>'; //month - hidden
    echo '<th></th>'; //day - hidden
    echo '</tr></thead>';

    //actual body of table
    echo '<tbody>';

    //itterate through each year of data and output topics
    foreach ($data as $yeardata) {
        //get topics for this year
        $topics = $yeardata['topics'];

        //itterate through all topics
        foreach ($topics as $topic) {
            //get context
            $context = get_context_instance(CONTEXT_MODULE, $chairman_id);
            //grab all files for this topic
            $files = $fs->get_area_files($context->id, 'mod_chairman', 'attachment', $topic->filename);

            //put all files into the field
            //that way they are searchable by the table search
            $filenames = '';
            foreach ($files as $file) { //itterate through each file
                $filename = $file->get_filename(); //get filename
                if ($filename === "." || $filename === "..")
                    continue; //ignore current file
                $filenames .= $filename . "<br>";  //seperate by html newline
            }

            //create a display date using month, day, year
            $display_date = chairman_get_month($topic->month) . " " . $topic->day . ", " . $topic->year;
            //create display text based on status
            $status = $TOPIC_STATUS[$topic->status];
            //link to agenda containing topic
            $link_agenda = "$CFG->wwwroot/mod/chairman/chairman_meetingagenda/view.php?event_id=" . $topic->eid . "&selected_tab=" . 3;
            $link_event = "$CFG->wwwroot/mod/chairman/chairman_events/edit_event.php?id=$chairman_id&event_id=" . $topic->eid;
            //create row
            echo '<tr>';

            //title with link to agenda
            echo '<td><a sort="' . $topic->summary . '" href="' . $link_event . '">' . $topic->summary . '</a></td>'; //summary from event
            echo '<td><a sort="' . $topic->title . '" href="' . $link_agenda . '">' . $topic->title . '</a></td>'; //title
            echo '<td>' . $display_date . '</td>'; //date
            echo '<td>' . $status . '</td>'; //status
            echo '<td>' . $topic->description . '</td>'; //desc
            echo '<td>' . $topic->notes . '</td>'; //notes
            echo '<td>' . $filenames . '</td>'; //filenames
            //hidden fields
            echo '<td>' . $topic->year . '</td>'; //year
            echo '<td>' . $topic->month . '</td>'; //month
            echo '<td>' . $topic->day . '</td>'; //day

            echo '</tr>';
        }
    }

    //end table
    echo '</tbody>';
    echo '</table>';
    $cm = get_coursemodule_from_id('chairman', $chairman_id);
    $chairman = $DB->get_record('chairman', array('id' => $cm->instance));
    add_to_log($cm->course, 'chairman', 'view', '', get_string('collaps_menu_agenda_archives', 'chairman') . ' - ' .  $chairman->name , $cm->id);
}

/**
 * Builds the motion table for the archive
 * 
 * @param int $chairman_id course module id
 * @param array $data A multinested array containing the topic data: $data[YEAR]['motions']
 */
function build_agenda_motions_archive($chairman_id, $data) {
    global $CFG;

    //output the year filter based on which years topics are avaliable
    output_year_selector('agenda_archive_motions_year_filter', 'agenda_archive_year_filter', $chairman_id, $data, 'motions');


    //output table with unique id & class that is shared with topics table
    echo '<table id="agenda_archive_motions" class="archive_table">';
    echo '<thead><tr>'; //headers
    echo '<th>' . get_string('agenda_archive_event', 'chairman') . '</th>'; //event
    echo '<th>' . get_string('agenda_archive_topic', 'chairman') . '</th>'; //topic
    echo '<th>' . get_string('agenda_archive_motion', 'chairman') . '</th>'; //motion
    echo '<th>' . get_string('agenda_archive_date', 'chairman') . '</th>'; //date
    echo '<th>' . get_string('agenda_archive_motionby', 'chairman') . '</th>'; //motionby
    echo '<th>' . get_string('agenda_archive_secby', 'chairman') . '</th>'; //seconded by
    echo '<th>' . get_string('agenda_archive_carried', 'chairman') . '</th>'; //carried
    echo '<th>' . get_string('agenda_archive_unanimous', 'chairman') . '</th>'; //unanimous
    echo '<th>' . get_string('agenda_archive_yea', 'chairman') . '</th>'; //yes
    echo '<th>' . get_string('agenda_archive_nay', 'chairman') . '</th>'; //no
    echo '<th>' . get_string('agenda_archive_abs', 'chairman') . '</th>'; //dont care
    echo '<th></th>'; //year - hidden
    echo '<th></th>'; //month - hidden
    echo '<th></th>'; //day - hidden
    echo '</tr></thead>';

    //actual body of table
    echo '<tbody>';

    //itterate through each year of data and output topics
    foreach ($data as $yeardata) {
        //get motions for this year
        $motions = $yeardata['motions'];

        //itterate through all motions
        foreach ($motions as $motion) {

            //create a display date using month, day, year
            $display_date = chairman_get_month($motion->month) . " " . $motion->day . ", " . $motion->year;

            //get member name for motionby and secondedby
            $motionby = get_agenda_member_name($motion->motionby);
            $secondedby = get_agenda_member_name($motion->secondedby);

            //display if carried
            $carried = ($motion->carried == 1) ? get_string('yes') : get_string('no');

            //display if unanimous
            $unanimous = ($motion->unanimous == 1) ? get_string('yes') : get_string('no');

            //link to agenda containing topic
            $link_agenda = "$CFG->wwwroot/mod/chairman/chairman_meetingagenda/view.php?event_id=" . $motion->eid . "&selected_tab=" . 3;
            $link_event = "$CFG->wwwroot/mod/chairman/chairman_events/edit_event.php?id=$chairman_id&event_id=" . $motion->eid;
            //create row
            echo '<tr>';

            //title with link to agenda
            echo '<td><a sort="' . $motion->event . '" href="' . $link_event . '">' . $motion->event . '</a></td>'; //summary from event
            echo '<td><a sort="' . $motion->topic . '" href="' . $link_agenda . '">' . $motion->topic . '</a></td>'; //topic
            echo '<td>' . $motion->motion . '</td>'; //motion
            echo '<td>' . $display_date . '</td>'; //date
            echo '<td>' . $motionby . '</td>'; //who started the motion
            echo '<td>' . $secondedby . '</td>'; //who seconded
            echo '<td>' . $carried . '</td>'; //did it get carried
            echo '<td>' . $unanimous . '</td>'; //was it unanimous
            echo '<td>' . $motion->yea . '</td>'; //in favour count
            echo '<td>' . $motion->nay . '</td>'; //against count
            echo '<td>' . $motion->abstained . '</td>'; //didn't vote
            //hidden fields
            echo '<td>' . $motion->year . '</td>'; //year
            echo '<td>' . $motion->month . '</td>'; //month
            echo '<td>' . $motion->day . '</td>'; //day

            echo '</tr>';
        }
    }

    //end table
    echo '</tbody>';
    echo '</table>';
}

/**
 * Builds a data object containing all of the topics and motions by year.
 * The data generated is an nested array of the format:
 * 
 * $data[YEAR][TYPE]
 * TYPE: topics or motions
 * 
 * @global moodle_database $DB
 * @param int $chairman_id course module
 * @return array
 */
function build_yearly_agenda_archive_data($chairman_id) {
    global $DB;

    //top level of return data
    $data = array();

    //create date
    $date_time = new DateTime();

    //get the first event ever created for this chairman
    $min_year = getMinEventYear($chairman_id);
    //get year definition
    list($a, $b, $itteration_date) = chairman_get_year_definition($chairman_id);

    //figure out the end year - aka the earliest year an event occured
    $end_year = new DateTime();
    $end_year->setDate($min_year, $date_time->format('m'), $date_time->format('d'));

    //we use interval to determine if our current itteration date is before the earliest event date
    $interval = $itteration_date->diff($end_year, false);

    //itterate each year until before the first event
    while (($interval->invert === 1) ||
    ($interval->invert === 0 && ($interval->y === 0))) {

        //all topics sql with event info
        $sql = "SELECT DISTINCT t.*, e.day, e.month, e.year, e.summary, e.id as EID FROM {chairman_agenda} a, {chairman_agenda_topics} t, {chairman_events} e " .
                "WHERE t.chairman_agenda = a.id AND e.id = a.chairman_events_id AND e.chairman_id = a.chairman_id " .
                "AND a.chairman_id = $chairman_id and ((e.year=? and e.month>=?) or (e.year=? and e.month<=?)) " .
                "ORDER BY e.year DESC, e.month DESC, e.day DESC";

        //get topics
        $topics = $DB->get_records_sql($sql, array($itteration_date->format('Y') - 1, $itteration_date->format('m'), $itteration_date->format('Y'), $itteration_date->format('m')));

        //if not topics, skip this year
        if (count($topics) === 0) {
            $interval = $itteration_date->sub(new DateInterval("P1Y"))->diff($end_year, false);
            continue;
        }

        //we are grabbing the motions for all topics to avoid an extra itteration
        $motions = array();
        foreach ($topics as $topic) {
            //grab motion for current topic
            $topic_motions = $DB->get_records('chairman_agenda_motions', array('chairman_agenda_topics' => $topic->id));

            //add motions to array containing all motions for this year
            foreach ($topic_motions as $topic_motion) {
                $topic_motion->event = $topic->summary;
                $topic_motion->eid = $topic->eid;
                $topic_motion->topic = $topic->title;
                $topic_motion->topic_id = $topic->id;
                $topic_motion->day = $topic->day;
                $topic_motion->month = $topic->month;
                $topic_motion->year = $topic->year;
                array_push($motions, $topic_motion);
            }
        }

        //add topics and motions to return data
        $data[$itteration_date->format('Y')]['topics'] = $topics;
        $data[$itteration_date->format('Y')]['motions'] = $motions;

        //decrease by 1 year
        $interval = $itteration_date->sub(new DateInterval("P1Y"))->diff($end_year, false);
    }


    return $data;
}

/**
 * Generates a year selector based on the $data provided and the def of a year for
 * this chairman.
 * 
 * @param string $output_id id to give to the selector created
 * @param string $output_class class to give to the selector created
 * @param int $chairman_id course module id
 * @param array $data A multinested array containing the topic data: $data[YEAR][TYPE]
 * @param string $type motions or topics
 */
function output_year_selector($output_id, $output_class, $chairman_id, $data, $type) {

    $date_time = new DateTime();

    //grab first event date
    $min_year = getMinEventYear($chairman_id);
    list($a, $b, $itteration_date) = chairman_get_year_definition($chairman_id);

    $end_year = new DateTime();
    $end_year->setDate($min_year, $date_time->format('m'), $date_time->format('d'));

    $interval = $itteration_date->diff($end_year, false);

    //generate year filter select
    echo "<div id='{$output_id}_wrapper' class='{$output_class}_wrapper'>";
    echo "<label>";
    echo get_string('agenda_archive_year_filter', 'chairman');
    echo "<select id='$output_id' class='$output_class'>";

    //default all years option
    echo "<option value=''>" . get_string('any') . "</option>";

    //itterate through each year(until before first event date) and create option
    while (($interval->invert === 1) ||
    ($interval->invert === 0 && ($interval->y === 0))) {

        $year = $itteration_date->format('Y');
        $month = $itteration_date->format('m');
        $month_full = chairman_get_month($month);

        //if not dates for the year, skip it in selector
        if (!isset($data[$year][$type])) {
            $interval = $itteration_date->sub(new DateInterval("P1Y"))->diff($end_year, false);
            continue;
        }

        //create display for year range
        $display = $month_full . " " . ($year - 1) . " - " . $month_full . " " . $year;
        $value = $month . ';' . $year;

        //output option
        echo "<option value='$value'>$display</option>";

        //decrease by 1 year
        $interval = $itteration_date->sub(new DateInterval("P1Y"))->diff($end_year, false);
    }

    echo "</select>";
    echo "</label>";
    echo "</div>";
}

/**
 * Converts an agenda member id into their full name
 * 
 * @global moodle_database $DB
 * @param int $agenda_member_id id of member from chairman_agenda_members
 * @return string Members full name
 */
function get_agenda_member_name($agenda_member_id) {
    global $DB;

    $member_record = $DB->get_record('chairman_agenda_members', array('id' => $agenda_member_id));
    if (!$member_record)
        return "---";
    return getUserName($member_record->user_id);
}

?>
