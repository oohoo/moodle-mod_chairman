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
 * Handles the output of the event/meetings. Each year of events, seperated by month,
 * can be output directly. 
 *
 */
class EventOutputRenderer {
    /*
     * cmid
     */

    private $id;

    /**
     * General Constructor
     * @param int $id cmid
     */
    public function __construct($id) {
        $this->id = $id;
    }

    /**
     * Output all the events for the current year, seperated by month.
     * 
     * @param string $search A search criteria to filter events based on name/description.
     */
    public function output_current_year($search) {
        return  $this->output_year(time(), new DateTime(), $search, false);
    }

    /**
     * 
     * Outputs all events grouped by year and then month.
     * The current year's event's are skipped.
     * 
     * @param string @search A search criteria to filter events
     */
    public function output_archive($search) {
        $self = $this;
        $this->output_yearly_itteration($search, true, function($date, $search) use ($self) {
                    echo '<h3>' . chairman_get_month($date->format('m')) . " " . ($date->format('Y') - 1) . " - " .
                    chairman_get_month($date->format('m')) . " " . ($date->format('Y')) . '</h3>';
                    echo '<div>';
                    $self->output_year($date->getTimestamp(), clone $date, $search, false);
                    echo '</div>';
                });
    }

    /**
     * Outputs all events based on year year - grouped only by month.
     * 
     */
    public function output_year_events() {

        $self = $this;
        $this->output_yearly_itteration(null, false, function($date, $search) use ($self) {
                    global $CFG;

                    $records = $self->get_year_event_records($date, null);

                    echo '<h3>' . chairman_get_month($date->format('m')) . " " . ($date->format('Y') - 1) . " - " .
                    chairman_get_month($date->format('m')) . " " . ($date->format('Y')) . '</h3>';
                    echo '<div>';

                    echo "<ul>";
                    foreach ($records as $record) {
                        $url = "$CFG->wwwroot/mod/chairman/chairman_meetingagenda/view.php?event_id=" . $record->id . "&selected_tab=" . 1;
                        print '<li class="ui-state-default ui-corner-all"><a href="' . $url . '" >' . toMonth($record->month) . " " . $record->day . ", " . $record->year . '</a></li>';
                    }
                    echo "</ul>";

                    echo '</div>';
                });
    }


    /**
     * Functions that itterates through all the events from the current date,
     * to the events with the oldest date.
     * 
     * The function requires a display lamdba (anon function) to determine how
     * to display each year's events.
     * 
     * @global moodle_database $DB
     * @param string $search
     * @param bool $skip_first
     * @param function $display_function
     */
    public function output_yearly_itteration($search, $skip_first, $display_function) {
        global $DB;

        $date_time = new DateTime();

        $min_year = getMinEventYear($this->id);


        if ($skip_first){
            list($itteration_date) = chairman_get_year_definition($this->id);
            $itteration_date->add(new DateInterval("P1Y"));
        }
        else
        {
            list($a, $b, $itteration_date) = chairman_get_year_definition($this->id);
            $itteration_date->add(new DateInterval("P1Y"));
        }
        
        
        $end_year = new DateTime();
        $end_year->setDate($min_year, $date_time->format('m'), $date_time->format('d'));

        $interval = $itteration_date->diff($end_year, false);

        echo '<div id="archive_container" class="events_container">';
        while (($interval->invert === 1) ||
        ($interval->invert === 0 && ($interval->y === 0))) {

            $count = $this->get_year_event_records_count($itteration_date, $search);

            //echo $itteration_date->format("Y") . " " . $itteration_date->format("m") . "</br>";

            if ($count == 0) {
                $interval = $itteration_date->sub(new DateInterval("P1Y"))->diff($end_year, false);
                continue;
            }

            $display_function($itteration_date, $search);

            $interval = $itteration_date->sub(new DateInterval("P1Y"))->diff($end_year, false);
        }

        echo '</div>';
    }

    /**
     * Output a specific year of events based on the unix time timestamp & provided date.
     * The events are seperated by month, and can be filtered by a provided search string.
     * The search string is compared against the name or description.
     * 
     * 
     * @param int $unixnow
     * @param DateTime $itteration_date
     * @param string $search
     */
    public function output_year($unixnow, $itteration_date, $search, $force_first = true) {
        echo '<div class="events_container">';

        $tab_counter = 0;
        $active_tab = 0;
        list($start_date) = chairman_get_year_definition($this->id, $itteration_date);
        $itteration_date->add(new DateInterval("P1Y"));
        
        $datetime = new DateTime();
        
        $records = $this->get_month_event_records($itteration_date, $search);

        if (!empty($records) || $force_first) {
            echo '<h3>' . chairman_get_month($itteration_date->format('m')) . ' ' . $itteration_date->format('Y') . '</h3>';
            echo '<div>';
            $this->chairman_print_events($unixnow, $records);
            echo "</div>";
        }

        $itteration_date->sub(new DateInterval("P1M"));
        $interval = $itteration_date->diff($start_date, false);

        while ($interval && (($interval->invert === 1) || ($interval->invert === 0 && $interval->m === 0 && $interval->y === 0 && $interval->d === 0))) {
            //echo chairman_get_month($itteration_date->format('m'))."<br/>";

            $records = $this->get_month_event_records($itteration_date, $search);
            if (empty($records)) {
                $interval = $itteration_date->sub(new DateInterval("P1M"))->diff($start_date, false);
                continue;
            }

             $tab_counter++;
            if(($datetime->format('m') == $itteration_date->format('m')) && 
                        $datetime->format('Y') == $itteration_date->format('Y'))
            {
                $active_tab = $tab_counter;
                echo "<!-- TESTESTEST ".$datetime->format('Y')."  ".$datetime->format('m')."-->";
            }
            
            echo '<h3>' . chairman_get_month($itteration_date->format('m')) . ' ' . $itteration_date->format('Y') . '</h3>';
            echo '<div>';
            $this->chairman_print_events($unixnow, $records);
            echo '</div>';

            $interval = $itteration_date->sub(new DateInterval("P1M"))->diff($start_date, false);
        }
        echo '<input type="hidden" id="current_active_month" value="'.$active_tab.'"/>';
        echo '</div>'; //container
    }

    /**
     * Retrieve all events for a particular moth and year based on the given
     * DateTime. The events are further filtered by the search parameter against the
     * name & description of the events.
     * 
     * 
     * @global moodle_database $DB
     * @param DateTime $date
     * @param string $search
     */
    public function get_month_event_records($date, $search) {
        global $DB;
        //$records = $DB->get_records("chairman_events", array('chairman_id' => $this->id, 'month' => $itteration_date->format('m'), 'year' => $itteration_date->format('Y')), 'stamp_start DESC');

        $clean_search = trim(clean_param($search, PARAM_TEXT));

        $params = array();
        $sql = "SELECT * FROM {chairman_events} " .
                "WHERE chairman_id=? and month=? and year=? ";

        array_push($params, $this->id, $date->format('m'), $date->format('Y'));

        if ($clean_search && !empty($clean_search)) {
            $clean_search = "%" . $clean_search . "%";
            $sql.= "and (summary LIKE ? or description LIKE ?) ";
            array_push($params, $clean_search, $clean_search);
        }

        $sql.= " ORDER BY stamp_start DESC ";

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Returns the number of events that have occured in a given year. The date
     * provided is seen as the start of the last day in the year.
     * 
     * Ex: date Jan 2013 ->
     * 
     *  Jan 2012 -> Jan 2013
     * 
     * @global moodle_database $DB
     * @param Datetime $date
     * @param string $search
     */
    private function get_year_event_records_count($date, $search) {
        global $DB;

        //get sql in first position, params in second
        $sql_query = $this->get_yearly_sql($date, $search, true);

        return $DB->count_records_sql($sql_query[0], $sql_query[1]);
    }

    /**
     * This function builds the sql query needed to retrieve either the events in
     * a year, or the number of events that occured in a year.
     *
     * Returns the number of events that have occured in a given year. The date
     * provided is seen as the start of the last day in the year.
     * 
     * Ex: date Jan 2013 ->
     * 
     *  Jan 2012 -> Jan 2013
     *
     * @param Datetime $date
     * @param string $search
     * @param bool $is_count
     */
    private function get_yearly_sql($date, $search, $is_count = false) {
        $clean_search = trim(clean_param($search, PARAM_TEXT));

        $fields = ($is_count ? "count(DISTINCT id)" : "*");

        $params = array();
        $sql = "SELECT $fields FROM {chairman_events} " .
                "WHERE chairman_id=? and ((year=? and month>=?) or (year=? and month<=?) )";
        $sql.= " ORDER BY stamp_start DESC ";

        array_push($params, $this->id, $date->format('Y') - 1, $date->format('m'), $date->format('Y'), $date->format('m'));

        if ($clean_search && !empty($clean_search)) {
            $clean_search = "%" . $clean_search . "%";
            $sql.= "and (summary LIKE ? or description LIKE ?) ";
            array_push($params, $clean_search, $clean_search);
        }

        return array($sql, $params);
    }

    /**
     * Retrieves an array of records for a given year that is based on the given
     * Datetime object.
     * 
     * @global moodle_database $DB
     * @param Datetime $date
     * @param string $search
     */
    public function get_year_event_records($date, $search) {
        global $DB;

        //get sql in first position, params in second
        $sql_query = $this->get_yearly_sql($date, $search);

        return $DB->get_records_sql($sql_query[0], $sql_query[1]);
    }

    /**
     * Prints out a given array of event records
     * 
     * @global moodle_database $DB
     * @param type $id
     * @param type $month
     * @param type $year
     */
    private function chairman_print_events($now, $event_records) {
        global $USER, $CFG;
        $nextevent = 0;

        if (!$event_records)
            return;

        foreach ($event_records as $event) {
            //needed to calculate the proper timestamp values for thebackground color
            $eventstart = $event->day . '-' . $event->month . '-' . $event->year . ' ' . $event->starthour . ':' . $event->startminutes;
            $eventtimestamp = strtotime($eventstart);

            //Find out if event is the next one in line. If so change background color
            if (($eventtimestamp >= $now) && ($nextevent == 0)) {
                $eventstyle = 'style="background-color:#FFFFC7"';
                $nextevent = 1;
            }
            else
                $eventstyle = '';

            echo '<div class="file" ' . $eventstyle . '>';
            echo '<table><tr><td>';
            echo '<a href="' . $CFG->wwwroot . '/mod/chairman/chairman_events/export_event.php?event_id=' . $event->id . '"><img id="icon" src="' . $CFG->wwwroot . '/mod/chairman/pix/cal.png"></a>';
            echo '</td>';
            echo '<td style="padding:6px;">';
            echo '<b>' . get_string('summary', 'chairman') . ' : </b>';
            echo $event->summary;
            if (chairman_isadmin($this->id)) {
                echo ' - <a href="' . $CFG->wwwroot . '/mod/chairman/chairman_events/delete_event_script.php?id=' . $this->id . '&event_id=' . $event->id . '" onClick="return confirm(\'' . get_string('deleteeventquestion', 'chairman') . '\');"><img src="' . $CFG->wwwroot . '/mod/chairman/pix/delete.gif"></a>';
                echo '<a href="edit_event.php?id=' . $this->id . '&event_id=' . $event->id . '"><img src="' . $CFG->wwwroot . '/mod/chairman/pix/edit.gif"></a>';
            }


            //Timezone adjustments
            //convert string into timestamp
            $event_start_str = chairman_convert_strdate_time($event->year, $event->day, $event->month, $event->starthour, $event->startminutes);
            $event_end_str = chairman_convert_strdate_time($event->year, $event->day, $event->month, $event->endhour, $event->endminutes);
            //echo "Event year = $event->year-$event->day-$event->month event start str = $event_start_str";


            $user_timezone = $USER->timezone;
            if ($user_timezone == '99') {
                $region_tz = $CFG->timezone;
            } else {
                $region_tz = $USER->timezone;
            }

            $offset = chairman_get_timezone_offset($event->timezone, $region_tz);

            //Calculate offset
            $local_user_starttime = $event_start_str + $offset;
            $local_user_endtime = $event_end_str + $offset;
            //Convert timestamp back to string
            $event->day = date('d', $local_user_starttime);
            $event->month = date('m', $local_user_starttime);
            $event->year = date('Y', $local_user_starttime);
            $event->starthour = date('H', $local_user_starttime);
            $event->startminutes = date('i', $local_user_starttime);
            $event->endhour = date('H', $local_user_endtime);
            $event->endminutes = date('i', $local_user_endtime);

            echo '<br/>';
            echo '<b>' . get_string('date', 'chairman') . ' : </b>';
            echo $event->day . ' ';

            echo chairman_get_month($event->month);

            echo ', ' . $event->year . '<br/>';

            echo '<b>' . get_string('starttime', 'chairman') . ' : </b>';
            echo $event->starthour . ':';
            echo $event->startminutes . '<br/>';

            echo '<b>' . get_string('endtime', 'chairman') . ' : </b>';

            echo $event->endhour . ':';
            echo $event->endminutes . '<br/>';

            echo '<b>' . get_string('description', 'chairman') . ' : </b>';
            echo $event->description . '<br/>';

            echo '<br/>';
            echo '<a href="' . $CFG->wwwroot . '/mod/chairman/chairman_events/add_event_moodle_cal.php?event_id=' . $event->id . '">' . get_string('addtomoodlecalendar', 'chairman') . '</a></br>';
            echo '<a href="' . $CFG->wwwroot . '/mod/chairman/chairman_events/export_event.php?event_id=' . $event->id . '">' . get_string('addtocalendar', 'chairman') . '</a></br>';
            echo '<a href="' . $CFG->wwwroot . '/mod/chairman/chairman_meetingagenda/view.php?event_id=' . $event->id . '">' . get_string('meeting_agenda', 'chairman') . '</a>';
            echo '</td></tr></table>';
            echo '</div>';
        }
    }

}

?>
