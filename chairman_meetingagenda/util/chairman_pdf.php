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
defined('MOODLE_INTERNAL') || die();

global $CFG;


require_once("$CFG->dirroot/mod/chairman/chairman_meetingagenda/lib.php");
require_once("$CFG->dirroot/lib/pdflib.php");

/**
 * left margin
 */
define('PDF_MARGIN_LEFT', 15);

/**
 * right margin
 */
define('PDF_MARGIN_RIGHT', 15);

/**
 * Class for add Header and footer to the PDF
 */
class chairman_pdf extends tcpdf {

    private $agenda;
    
    /**
     * Sets the agenda object
     * Added this function to avoid having to override the parent constructor to
     * include this parameter. For this reason this function should always be called
     * before the pdf is generated.
     * 
     * @param type $agenda
     */
    public function setAgenda($agenda) {
        $this->agenda = $agenda;
    }
    
    /**
     * Function Header to customise page header
     * @global core_renderer $OUTPUT
     */
    public function Header() {
        global $SESSION;
//
//        $image_file = str_replace('\\', '/', $SESSION->chairman_logo);
//        $this->Image($image_file, PDF_MARGIN_LEFT, 0, 50, 17, '', '', 'T', false, 300, '', false, false, 0, true, false, false);
//
//
//        $test = 'DDD<img border="0" src="http://www.w3schools.com/images/pulpit.jpg" alt="Pulpit Rock"/>';
    }

    /**
     * To customise page footer
     */
    public function Footer() {
        $this->SetY(-15);
        
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, $this->agenda->footer . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        
    }

}

/**
 * The class that handles the generation of the chairman pdf for Agenda's and Minutes
 */
class chairman_pdf_creator {

    private $instance;
    private $event;
    private $agenda;
    private $default_toform;
    private $pdf;
    private $title;
    private $event_name;
    private $includeMinutes;

    private $topic_statuses;
    private $motion_result;
    
    /**
     * General Constructor
     * 
     * 
     * @global moodle_database $DB
     * @param int $event_id Record id in chairman event
     * @param int $agenda_id Record id in chairman agenda
     * @param int $cmid course module for the current chairman
     */
    function __construct($event_id, $agenda_id, $cmid, $includeMinutes=1) {
        global $DB, $CFG;

        $this->event = $DB->get_record('chairman_events', array('id' => $event_id));
        $this->agenda = $DB->get_record('chairman_agenda', array('id' => $agenda_id));
        $this->cm = get_coursemodule_from_id('chairman', $cmid);
        $this->chairman = $DB->get_record('chairman', array('id' => $this->cm->instance));;
        $this->context = context_module::instance($cmid);
        $this->includeMinutes = $includeMinutes;
        
        $this->topic_statuses = array('open' => get_string('topic_open', 'chairman'),
                'in_progress' => get_string('topic_inprogress', 'chairman'),
                'closed' => get_string('topic_closed', 'chairman'));
        
        $this->motion_result =  array('-1' => '-----',
                '1' => get_string('motion_accepted', 'chairman'),
                '0' => get_string('motion_rejected', 'chairman'));
        
    }

    /**
     * Function for print the meeting PDF
     * @global type $DB
     * @global type $CFG
     * @param boolean $minutesIncluded If true, print only the agenda, otherwise print all data with minutes, participants, etc.
     */
    public function create_pdf($includeMinutes) {
       
        $toform = new stdClass();
        
        if($includeMinutes === null)
            $includeMinutes = $this->includeMinutes;
        
        $page = '';
        $this->pdf_header($page);
        $this->generate_participants($includeMinutes, $page, $toform);
        $this->pdf_topics($includeMinutes, $page, $toform);
        $this->pdf_footer($page);
        
        $this->generate_pdf_object($page, $toform, $includeMinutes);
        
    }
    
    /**
     * Generates the header for the agenda/minutes pdf.
     * This includes the addition of the logo & general meeting information
     * 
     * @param string $page
     */
    private function pdf_header(&$page) {
       $page.= "<table>"; 
       $page.= "<tr>";  
        
       $page.= "<td>";
       $page.= '<img src="'.chairman_get_logo_url($this->cm->id).'"/>';
       $page.= "</td>";
       
       $page.= "<td>";
       $page.= $this->get_event_table();
       $page.= "</td>";
        
       $page.= "</tr>";  
       $page.= "</table>"; 
    }
    
    /**
     * Generates a table containing all of the standard meeting information
     * 
     * @param string $page The var containing the string html that is used to generate the pdf.
     */
    private function get_event_table() {
        $table = '<table align="right">';
        $this->conditionally_add_single_row($table, $this->chairman->name);
        $this->conditionally_add_single_row($table, $this->event->summary);
        $this->conditionally_add_single_row($table, $this->get_date());
        $this->conditionally_add_single_row($table, find_event_duration($this->event));
        $this->conditionally_add_single_row($table, $this->agenda->location); 
         
      $table .= "</table>";
      
      return $table;
    }
    
    public function get_date() {
         global $CFG;
         
        $date = new DateTime($this->event->year . "-" . $this->event->month . "-" . $this->event->day);
        //Change the local just for get the date in the format for this language
        setlocale(LC_TIME, $CFG->lang);
        $dateString = utf8_encode(strftime(get_string('format_date', 'chairman'), $date->getTimestamp()));
        $time = $this->event->starthour . get_string('separatorhourminute', 'chairman') . ZeroPaddingTime($this->event->startminutes) . "-" . $this->event->endhour . get_string('separatorhourminute', 'chairman') . ZeroPaddingTime($this->event->endminutes);
    
        return "$dateString $time";
        
    }
    
    private function conditionally_add_single_row(&$table, $data) {
        if(!empty($data))
          $table.=   "<tr><td>" .$data. '</td></tr>';
    }
    
    /**
     * A message that occurs at the end of the agenda/minutes.
     * 
     * Note: The small text footer, including page number is generated in chairman_pdf::Footer()
     * 
     * @param string $page The var containing the string html that is used to generate the pdf.
     */
    private function pdf_footer(&$page) {
        
        //generate single cell table with border
        $page .= '<table border="1" align="center">';
        $page .= '<tr><td>';
        
        $page .= $this->agenda->message;
        
        $page .= '</td></tr>';
        $page .= '</table>';
        
    }
    
    /**
     * 
     * Generates the table containing the information on participants and attendance of the meeting.
     * This is only called for a minutes pdf.
     * 
     * @param type $includeMinutes Should the additional elements that are specific to the minutes be included
     * @param string $page The var containing the string html that is used to generate the pdf.
     * @param object $toform A var where data can be added and persisted throughout the html generation
     */
    private function generate_participants($includeMinutes, &$page, $toform) {
        if(!$includeMinutes) return;//if minutes are not included - do nothing
        
        $page .= "<br/><br/><hr/>";//spacer
        
        //header
        $page .= "<h2>" . get_string('participants_header','chairman') . "</h2>";
        
        //attendance table
        $this->generate_attendance_table($page);
        
        //guest table
        $this->generate_guests_table($page);
        
    }
    
    /**
     * Generates a table containing all of the guest information for a meetings.
     * This is specific to guests that are not within moodle and are explicitly added by name and email.
     * 
     * @global moodle_database $DB
     * @param string $page The var containing the string html that is used to generate the pdf.
     */
    private function generate_guests_table(&$page) {
        global $DB;
        
        //spacer
        $page .= '<h2/>';
        
        $page .= '<table align="left">';
        
        //moodle guests title
        $page .= "<tr>";
            $page .= '<td colspan="2" ><h3><u>' . get_string('moodle_guests','chairman') . "</u></h3></td>";
        $page .= "</tr>";
        
        //moodle guests labels
        $page .= "<tr>";
            $page .= '<td><h3>' . get_string('name','chairman') . "</h3></td>";
            $page .= '<td><h3>' . get_string('email') . "</h3></td>";
        $page .= "</tr>";
        
        //moodle exclusive guests
        $moodle_guests = $DB->get_records_sql('SELECT * FROM {chairman_agenda_guests} WHERE chairman_agenda = ? AND moodleid IS NOT NULL ', array($this->agenda->id));
        
        //moodle guests
        $page .= $this->generate_moodle_guests_table_rows($moodle_guests) . '<br/>';
        
        //guests title
        $page .= "<tr>";
            $page .= '<td align="left" colspan=2><h3><u>' . get_string('guests','chairman') . "</u></h3></td>";
        $page .= "</tr>";
        
         // guests labels
        $page .= "<tr>";
            $page .= '<td><h3>' . get_string('name','chairman') . "</h3></td>";
            $page .= '<td><h3>' . get_string('email') . "</h3></td>";
        $page .= "</tr>";
        
        //non-moodle guests
        $guests = $DB->get_records_sql('SELECT * FROM {chairman_agenda_guests} WHERE chairman_agenda = ? AND moodleid IS NULL ', array($this->agenda->id));
        
        //guests table
        $page .= $this->generate_guests_table_rows($guests);
        
        
        $page .= "</table>";
        
    }
    
    /**
     * Generates a single row for the guests table. This is for non-moodle guests only
     * 
     * @param array $guests array of guest records
     * @return string the resultant row of the guest table as a string
     */    
    private function generate_guests_table_rows($guests) {
        $result = '';
        
        foreach ($guests as $guest) {
           $result .= '<tr>';
            $result .= '<td>' . $guest->firstname . " " . $guest->lastname . '</td>';
            $result .= '<td>' . $guest->email . '</td>';
           $result .= '</tr>';
        }
        
        return $result;
    }
    
    /**
     * Generates the guest table as a string(only moodle guests)
     * 
     * @global moodle_database $DB
     * @param array $moodle_guests array of moodle guest records
     * @return string html table of guests
     */
    private function generate_moodle_guests_table_rows($moodle_guests) {
        global $DB;
        $result = '';
        
        //for each moodle guest
        foreach ($moodle_guests as $moodle_guest) {
           
            //get their user info
            $user = $DB->get_record('user', array('id'=>$moodle_guest->moodleid));
           
            //create table
           $result .= '<tr>';
            $result .= '<td>' . $user->firstname . " " . $user->lastname . '</td>';
            $result .= '<td>' . $user->email . '</td>';
           $result .= '</tr>';
        }
        
        return $result;
    }
    
    /**
     * Generates the attendance table for the given meeting.
     * 
     * @global moodle_database $DB
     * @param string $page The var containing the string html that is used to generate the pdf.
     */
    private function generate_attendance_table(&$page) {
        global $DB;
        
        //create table
        $page .= '<table align="left"  border="1">';
        
        //table headers
        $page .= "<tr>";
            $page .= '<th><h3 align="center" border=1>' . get_string('agenda_present','chairman') . "</h3></th>";
            $page .= '<th><h3 align="center">' . get_string('agenda_absent','chairman') . "</h3></th>";
            $page .= '<th><h3 align="center">' . get_string('agenda_uabsent','chairman') . "</h3></th>";
        $page .= "</tr>";
        
        //get present, absent, and unexcused members
        $present = $DB->get_records('chairman_agenda_attendance', array('chairman_agenda'=>$this->agenda->id, 'absent'=> 0));
        $absent = $DB->get_records('chairman_agenda_attendance', array('chairman_agenda'=>$this->agenda->id, 'absent'=> 1));
        $unexabsent = $DB->get_records('chairman_agenda_attendance', array('chairman_agenda'=>$this->agenda->id, 'unexcused_absence'=> 1));
        
        //create a list of users under each of the categories
        $page .= "<tr>";
            $page .= '<td align="center">' . $this->print_names_list($present) . "</td>";
            $page .= '<td align="center">' . $this->print_names_list($absent) . "</td>";
            $page .= '<td align="center">' . $this->print_names_list($unexabsent) . "</td>";
        $page .= "</tr>";
        
        $page .= "</table>";
        
    }
    
    /**
     * Generates a ul based list of provided agenda members(full names)
     * 
     * @param array $agenda_members array of agenda member records
     * @return string the ul list of member names
     */
    private function print_names_list($agenda_members) {
        $result = '';
        
        $result .= '<ul>';//create inital list
        
        //for each member add their full name as a list element
        foreach($agenda_members as $agenda_member) {
            $result .= '<li>' . getUserNameFromAgendaMemberID($agenda_member->chairman_members) . "</li>";
        }
        
        $result .= '</ul>';//end list
        
        return $result;
    }
    
    /**
     * A function that coordinates the creation of the topics for the agenda/minutes
     * 
     * @param boolean $includeMinutes On true, all topic info is provided. On false only the basics for agenda are provided.
     * @global moodle_database $DB
     * @param string $page The var containing the string html that is used to generate the pdf.
     */
    private function pdf_topics($includeMinutes, &$page) {
       
        $page .= "<hr/>";
       
        $page .= "<h2>" . get_string('create_topics','chairman') . "</h2>";
        
       //topics table
       $page .= "<table>";
       
       //display rows
       $this->topic_table_rows($page, $includeMinutes);
       
       $page .= "</table>";
        
    }

    /**
     * Generates the output for each row of the topic table
     * 
     * 
     * @global moodle_database $DB
     * @param string $page The var containing the string html that is used to generate the pdf.
     * @param boolean $includeMinutes On true, all topic info is provided. On false only the basics for agenda are provided.
     */
    private function topic_table_rows(&$page, $includeMinutes) {
        global $DB;
        
        //retrieve topics in specific order
        $topics = $DB->get_records('chairman_agenda_topics', array('chairman_agenda'=>$this->agenda->id), 'topic_order ASC');
        
        $index = 1;//index for labeling
        $previous_header = '';//header for the last topic
        
        //for each topic
        foreach($topics as $topic) {
            
            //add header if it's different than the previous one
            $page .= $this->conditional_topic_header($topic, $previous_header);
            
            //create row for topic data
            $page .= '<tr>';
            
                //display num and topic name
                $page .= '<td>';
                    $page .= '<h3>' . $index . ". " . $topic->title . '</h3>';
                    //output topics details
                    $page .= $this->generate_topics_details_table($topic, $index, $includeMinutes);
                $page .= '</td>';
                
                //display presented if there is one
                $page .= '<td align="right" valign="top">';
                    $page .= get_presentedby_static_value($topic);
                $page .= '</td>';
                
            //end topic row
            $page .= '</tr>';
            //add spacer
            $page .= '<tr><td colspan="2"><br/></td></tr>';
            
          //inc display num
          $index++;  
        }
        
    }
    
    /**
     * Adds a header for a topic if the parameter (previous header) is different
     * than the current topic header.
     * 
     * If the current header is empty, the previous header will be reset but no header
     * will be output.
     * 
     * ex: (A A '' A) Will create 2 A headers A 1 2 ->  3 ->  A 4
     * 
     * @param record $topic The current topic to add a dynamic header for
     * @param string $previous The header of the previous topic
     * @return string The header string that should be added to the table before the topic.
     */
    private function conditional_topic_header($topic, &$previous) {
        $result = '';
        
        //set header
        $header = $topic->topic_header;

        //if header is empty or the same as the previous one
        //set header to nothing
        if (empty($header) || $header == $previous)
            $header = '';
        
        //set presenter label to nothing
        $presenter_label = '';
        
        //get value of presenter field
        $presenter = get_presentedby_static_value($topic);

        //if presenter field is not empty then add a value to the header
        if (!empty($presenter))
            $presenter_label = get_string('presenter', 'chairman');

        //create output for header/presenter row
        $result .= "<tr>";
        $result .= "<th><h3><u><i>" . $header . "</i></u></h3></th>"; //topics header
        $result .= '<th align="right"><h3><u>' . $presenter_label . "</u></h3></th>";  //presenter header
        $result .= "</tr>";

        //update previous to the current header
        $previous = $topic->topic_header;

        return $result;
    }
    
    /**
     * Generates the html string containing all the topic details for the pdf
     * 
     * @param Object $topic The topic record that the details will be generated for
     * @param int $index The current position of the topic
     * @param boolean $includeMinutes Whether to include minutes exclusive topic information
     * @return string An string containing html of all the information for the current topic
     */
    private function generate_topics_details_table($topic, $index, $includeMinutes) {
        $result = '<table>';

        //topic description added if it has content
        $result .= $this->conditionally_add_topic_detail_row(get_string('desc_agenda_c', 'chairman'), $topic->description);
        
        //duration added if its not empty
        $result .= $this->conditionally_add_topic_detail_row(get_string('duration_agenda', 'chairman'), $topic->duration);
        
        //add filenames if its not empty
        $filenames = $this->get_topic_filenames($topic);
        $result .= $this->conditionally_add_topic_detail_row(get_string('filenames_agenda_c', 'chairman'), $filenames);
        
        //minutes exclusive details
        if($includeMinutes) {
            //add topic meeting notes
            $result .= $this->conditionally_add_topic_detail_row(get_string('topics_notes', 'chairman'), $topic->notes);
            
            //current topic status
            $result .= $this->conditionally_add_topic_detail_row(get_string('topic_status', 'chairman'), $this->topic_statuses[$topic->status]);
            
            //add any motions added for a topic
            $result .= $this->add_topic_motions($topic, $index);
        }
        
        
        $result .= '<tr><td colspan="3"></td></tr>';
        $result .= '</table>';
        
        
        return $result;
    }
    
    /**
     * Generates an html string containing all the motion information for a given topic
     * 
     * @global moodle_database $DB
     * @param object $topic The current topic to output motion information for
     * @param int $index The position of the current topic
     * @return string An html string containing all the motions for a given topic
     */
    private function add_topic_motions($topic, $index) {
        global $DB;
        
        $motions = $DB->get_records('chairman_agenda_motions', array('chairman_agenda_topics'=>$topic->id));
        
        $result = '<tr><td colspan="3"></td></tr>';//spacer
        
        //add motions title if motions are present
        $title = '';
        if(count($motions) > 0)
            $title = get_string('motions', 'chairman');
        
        
        //add indentation & motions label
        $result .= '<tr><td width="20px"></td><td colspan="2"><h4><u>'.$title.'</u></h4></td></tr>';
        $result .= '<tr>';
        
        //add addition indent
        $result .= '<td width="30px"> </td>';//spacer
       
        //create cell for motions content
        $result .= '<td colspan="2" width="100%">';
        
        //motions table
        $result .= '<table><tbody>';
        $subindex = 1;//subindex label
        foreach($motions as $motion) {
            
           //label for current motion
           $result .= '<tr><td colspan="2"><h4>'. get_string('motion', 'chairman') . " " . $index.'.'.$subindex .'</h4></td></tr>';  
           
           //add motion description if it contains content
           $result .= $this->add_motion_detail_row(get_string('description_c','chairman'), $motion->motion);
           
           //spacer
          $result .= '<tr><td colspan="2"></td></tr>';
           
          //add the row containing the support information for a motion
           $support = $this->generate_motion_support($motion);
           $result .= $this->add_motion_detail_row(get_string('motion_support','chairman'), $support);
           
           //spacer
           $result .= '<tr><td colspan="2"></td></tr>';
           
           //add row containing the votes information for a motion
           $votes = $this->generate_motion_votes($motion);
           $result .= $this->add_motion_detail_row(get_string('voting','chairman'), $votes);
           
           //spacer
           $result .= '<tr><td colspan="2"></td></tr>';
           
           
           //determine correct string for the carried row
           $carried = '';
           if($motion->carried == 1)
               $carried = get_string('motion_accepted','chairman');
           elseif($motion->carried == 0)
                $carried = get_string('motion_rejected','chairman');
           
           //add row containing information on whether a motion has passed (carried)
           $result .= $this->add_motion_detail_row(get_string('agenda_archive_carried','chairman'), $carried);
           
           //spacer
           $result .= '<tr><td colspan="2"></td></tr>';
           
           $subindex++;
           
           
        }

            
            //output spacer
            $result .= '<tr><td> </td><td width="100%"> </td></tr>';
            $result .= "</tbody></table>";//end motion table
        
        //end motions row
        $result .= '</td>';
        $result .= '</tr>';
        
        return $result;
    }
    
    /**
     * Generates a string containing an html table containing the vote information of a single motion
     * 
     * @param record $motion The record that the vote table will be generated for
     * @return string html table containing the vote information of a single motion
     */
     private function generate_motion_votes($motion) {

         //create table
        $support = '<table><tbody>';

            //all in favour row
            $support .= "<tr>";
            $support .= '<td>' . get_string('topic_vote_aye', 'chairman') . "</td>";
            $support .= '<td width="100%">' . $motion->yea . "</td>";
            $support .= "</tr>";

            //all opposed row
            $support .= "<tr>";
            $support .= '<td>' . get_string('topic_vote_nay', 'chairman') . "</td>";
            $support .= '<td width="100%">' . $motion->nay . "</td>";
            $support .= "</tr>";
            
            //all abstained row
            $support .= "<tr>";
            $support .= '<td>' . get_string('topic_vote_abs', 'chairman') . "</td>";
            $support .= '<td width="100%">' . $motion->abstained . "</td>";
            $support .= "</tr>";
            
            //unanimous row
            $support .= "<tr>";
            $support .= '<td>' . get_string('unanimous', 'chairman') . "</td>";
            $support .= '<td width="100%">' . (($motion->unanimous == 1) ? get_string('yes') : get_string("no")) . "</td>";
            $support .= "</tr>";

        //end vote table
        $support .= "</tbody></table>";

        return $support;
    }
    
    /**
     * Generates a string containing the support information for a single motions
     * 
     * @param object $motion Motion record that the support table will be generated for
     * @return string the support information for a single motions
     */
    private function generate_motion_support($motion) {

        //create table
        $support = '<table><tbody>';

        //if a person has been identifed as being the lead on this motion - add to pdf
        if (is_numeric($motion->motionby)) {
            $support .= "<tr>";
            $support .= '<td width="70px">' . get_string('motion_by', 'chairman') . "</td>";
            $support .= '<td width="100%">' . getUserNameFromAgendaMemberID($motion->motionby) . "</td>";
            $support .= "</tr>";
        }
        
         //if a person has seconded this motion - add to pdf
         if (is_numeric($motion->secondedby)) {
            $support .= "<tr>";
            $support .= '<td width="70px">' . get_string('motion_second', 'chairman') . "</td>";
            $support .= '<td width="100%">' . getUserNameFromAgendaMemberID($motion->secondedby) . "</td>";
            $support .= "</tr>";
        }

        //spacer
        $support .= '<tr><td> </td><td width="100%"> </td></tr>';
        $support .= "</tbody></table>";

        return $support;
    }
    
    /**
     * Returns a row (as a string) to the motion detail table - containing a label and its assocaited
     * data
     * 
     * Note: The width of the label is hardcoded due to a nested table issue in TCPDF.
     * 
     * 
     * @param string $label Label for the motion details
     * @param string $data data for the motion row
     * @return string motion details row
     */
    private function add_motion_detail_row($label, $data) {
        if(empty($data)) return '';
        
        $result = '<tr>';
            $result .= '<td width="70px">' . $label . '</td>';//label
            $result .= '<td width="100%">' . $data . '</td>';//data
        $result .= '</tr>';
        
        return $result;
    }
    
    /**
     * Retrieves all files assocaiated to a particular topic as a comma
     * delimited list.
     * 
     * @param string $topic The topic to retrieve the filename for.
     * @return string comma delimited list of all filenames for a topic
     */
    private function get_topic_filenames($topic) {
        $fs = get_file_storage();//get file system
        
        //get all files for that topic
        $files = $fs->get_area_files($this->context->id, 'mod_chairman', 'attachment', $topic->filename);
        $filenames = '';

        //itterate through all files
        foreach ($files as $file) {
            $filename = $file->get_filename();
            if ($filename == '.')//if current position - ignore
                continue;

            //create moodle link to file
            $link = (moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename()));

            //create html link
            $file_link = '<a href="' . $link . '" dir="ltr">' . $filename . '</a>';

            //add to list of files
            $filenames.= $file_link . ", ";
        }

        //get length of generated string
        $filenames_len = strlen($filenames);
        
        //if we have added anything to this string, we need to remove the extra ", "
        //at the end of the string
        if ($filenames_len > 0)
            $filenames = substr($filenames, 0, $filenames_len - 2);//remove redundent ", "
        
        return $filenames;
    }
    
    /**
     * Returns a details row (as a string) for the topics details table
     * if the data string is not empty.
     * 
     * @param string $label Label for the detail row to be added
     * @param string $data The data for the given row
     * @return string Empty if data is empty, otherwise an html table row with the given label and data
     */
    private function conditionally_add_topic_detail_row($label, $data) {
        if(empty($data)) return "";//if data is empty - return empty
        
        //add row with indent, label, and data
        $result = '<tr>';
            $result .= '<td width="20px"></td>';
            $result .= '<td >' . $label . '</td>';
            $result .= '<td width="100%">' . $data . '</td>';
        $result .= '</tr>';
        
        return $result;
    }
    
    /**
     * Converts the HTML based output into the pdf
     * 
     * @param string $page html of the entire angeda/minutes for the pdf
     * @param object $toform persistent data object from creation of html
     * @param boolean $includeMinutes whether the pdf includes minutes exclusive content
     */
    private function generate_pdf_object(&$page, $toform, $includeMinutes) {
        //----Generate PDF --------------------------------------------------------------
        //INIT PDF
        //
        //
        // Needed to put the page format 'LETTER' in parameter for other countries
        $pdf = new chairman_pdf($orientation='P', $unit='mm', $format='A4','LETTER', true, 'UTF-8', false);
        $pdf->setAgenda($this->agenda);
        
        //Set meta data
        $pdf->SetCreator('Moodle - Chairman');
        $pdf->SetAuthor('Moodle - Chairman');
        $pdf->SetSubject($this->chairman->name);
        $pdf->SetKeywords($this->chairman->name);

        // PDF SETTINGS
        $pdf->SetAutoPageBreak(TRUE, 15);

        // Set Protection
        //$pdf->SetProtection(array('print', 'copy'));
        //
        // Set margins
        $margintop = 10;
        $pdf->SetMargins(PDF_MARGIN_LEFT, $margintop, PDF_MARGIN_RIGHT);

        $pdf->SetFooterMargin(15);

        // Set display mode 100%
        $pdf->SetDisplayMode(100, 'OneColumn');



        // PDF TITLE ---------------------------------------------------------------------
        // Set the header 
        $pdf->SetTitle("<h4 ALIGN=center>''</h4>
                        <h3 ALIGN=center>".$this->chairman->name."</h3>");


        // Add the first page
        $pdf->AddPage();



        // PDF DATA ---------------------------------------------------------------------
        //Default font
        $pdf->SetFont('helvetica', '', 10);
        
        //convert html into pdf
        $pdf->WriteHTML($page);
        
        $this->default_toform = $toform;
        $this->title = $this->get_title($includeMinutes);
        $this->pdf = $pdf;
    }
    
    
    /**
     * Causes the generated pdf to be output as a forced download.
     * Note: No headers should have been started when this function is called.
     * Also: Nothing should be output after this call.
     * 
     */
    public function output_force_download()
    {
        $this->pdf->Output($this->title, 'd');
    }
    
         /**
     * Gets the title of the pdf
     */
    public function get_title($includeMinutes) {

        if (!isset($this->title)) {
            $date = new DateTime($this->event->year . "-" . $this->event->month . "-" . $this->event->day);

            if ($includeMinutes)
                $this->title = get_string('arising_issues', 'chairman') . '-' . $date->format('Y-m-d') . '.pdf';
            else
                $this->title = get_string('agenda', 'chairman') . '-' . $date->format('Y-m-d') . '.pdf';
        }


        return $this->title;
    }
    
    /**
     * Returns true if minutes are included by default
     * @return true on minutes included by default
     */
    public function isMinutesIncluded()
    {
        return $this->includeMinutes;
    }
    
        /**
         * This doesn't update the that has already been generated by create_pdf,
         * but will be used if null is passed in for create_pdf
         * 
         * @param boolean $isMinutesIncluded Default value if none is provided for $minutesIncluded
         */
    public function setMinutesIncluded($isMinutesIncluded)
    {
        $this->includeMinutes = $isMinutesIncluded;
    }
    
    
     /**
      * Gets the event name the pdf is for
      */
    public function get_event_name()
    {
            return $this->event->summary;
    }
    
    /**
     * Returns the generated pdf as a string.
     */
    public function save_pdf_data()
    {
       return $this->pdf->getPDFData();
    }
    
        /**
     * Returns the generated pdf as a string.
     */
    public function get_pdf_string()
    {
       return $this->pdf->Output($this->title, 's');
    }
    
        /**
     * Returns the generated pdf as a email(mime64).
     */
    public function get_pdf_email()
    {
      return $this->pdf->Output($this->title, 'e'); 
    }
    
    /*
     * Returns the default values for the form.
     */

    function getDefault_toform()
    {
        return $this->default_toform;
    }
    
}

?>
