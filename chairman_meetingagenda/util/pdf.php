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

/* The class to represent an PDF version of the agenda.
 *
 *
 * @package   Agenda/Meeting Extension to Committee Module
 * @copyright 2011 Dustin Durand
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/mod/chairman/chairman_meetingagenda/lib.php");
require_once("$CFG->dirroot/lib/tcpdf/tcpdf.php");

/**
 * Class for add Header and footer to the PDF
 */
class Pdf_custom extends TCPDF
{

    /**
     * Function Header to customise page header
     * @global core_renderer $OUTPUT
     */
    public function Header()
    {
        global $OUTPUT, $CFG, $SESSION;
        
        // Logo - Get the logo from the theme folder
        $lang = (isset($SESSION->lang)?$SESSION->lang:$CFG->lang);
        //$image_file = $OUTPUT->pix_url('logo_' . $lang, 'mod_chairman');
		
	$image_file = "$SESSION->chairman_logo";
        $image_file = str_replace('\\','/', $image_file);
        
        $this->Image($image_file, PDF_MARGIN_LEFT, 0, 50, 17, '', '', 'T', false, 300, '', false, false, 0, true, false, false);
		
        $this->SetY(17);
        // Set font
        $this->SetFont('helvetica', '', 10);
        // Title
        $this->WriteHTML($this->title);
    }

    /**
     * To customise page footer
     */
    public function Footer()
    {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, get_string('page', 'chairman') . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }

}

/**
 * 
 */
class pdf_creator
{

    private $instance;
    private $event_id;
    private $agenda_id;
    private $chairman_id;
    private $default_toform;
    private $pdf;
    private $title;
    private $event_name;
    private $plain_pdf;
    

    function __construct($event_id, $agenda_id, $chairman_id, $cm)
    {
        /**
         * @TODO Fix Naming in this file
         * NAMING IS REALLY MESSED UP HERE
         * chairman_id = cm->id
         * 
         * I think cm is actually the chairman id too?!...
         */
        
        $this->event_id = $event_id;
        $this->agenda_id = $agenda_id;
        $this->chairman_id = $chairman_id;
        $this->instance = $cm;
    }

    /**
     * Function for print the meeting PDF
     * @global type $DB
     * @global type $CFG
     * @param boolean $plain_pdf If true, print only the agenda, otherwise print all data with minutes, participants, etc.
     */
    function create_pdf($plain_pdf)
    {
        global $DB, $CFG;

        //----Prepare data --------------------------------------------------------------
        //-- variable convience--

        $event_id = $this->event_id;
        $agenda_id = $this->agenda_id;
        $instance = $this->instance;
        $chairman_id = $this->chairman_id;
        $context = context_module::instance($chairman_id);
        
        if($plain_pdf!= null)
            $this->plain_pdf = $plain_pdf;
        else
            $plain_pdf =  $this->plain_pdf;

        $event_record = $DB->get_record('chairman_events', array('id' => $event_id), '*', $ignoremultiple = false);
        
        $this->event_name = $this->get_event_name();
        
        $agenda = $DB->get_record('chairman_agenda', array('id' => $agenda_id), '*', $ignoremultiple = false);
        $chairman = $DB->get_record("chairman", array("id" => $instance)); // get chairman record for our instance
        //Date
        $date = new DateTime($event_record->year . "-" . $event_record->month . "-" . $event_record->day);
        //Change the local just for get the date in the format for this language
        setlocale(LC_TIME, $CFG->lang);
        $dateString = utf8_encode(strftime(get_string('format_date', 'chairman'), $date->getTimestamp()));
        $time = $event_record->starthour . get_string('separatorhourminute', 'chairman') . ZeroPaddingTime($event_record->startminutes) . "-" . $event_record->endhour . get_string('separatorhourminute', 'chairman') . ZeroPaddingTime($event_record->endminutes);

        $toform = new stdClass();
        $toform->committee = $chairman->name;
        $toform->summary = $event_record->summary;
        $toform->description = $event_record->description;
        $toform->event_id = $event_id;
        $toform->location = $agenda->location;

        if (!$plain_pdf)
        {
            $header_text = get_string('minutes_tab', 'chairman');
        }
        else
        {
            $header_text = get_string('agenda_tab', 'chairman');
        }

        $chairmanmembers = array();

        //-----Participants--------------------------------------------------------------
        //Only if plain_pdf is false
        if (!$plain_pdf)
        {
            //Get the committee members
            $commityRecords = $DB->get_records('chairman_agenda_members', array('chairman_id' => $chairman_id, 'agenda_id' => $agenda_id), '', '*', $ignoremultiple = false);

            //--------Committee Members------------------------------------------------------
            $committeemembers = array('present' => array(), 'absent' => array(), 'uabsent' => array()); //Used to store commitee members in an array
            //Used for topics and motions
            $chairmanmembers = array();

            $FORUM_TYPES = array(
                '0' => get_string('agenda_present', 'chairman'),
                '1' => get_string('agenda_absent', 'chairman'),
                '2' => get_string('agenda_uabsent', 'chairman'));

            //If return data
            if ($commityRecords)
            {
                $index = 0;
                $count_label = 0;
                foreach ($commityRecords as $member)
                {
                    $count_label = $index + 1;

                    //Get Real name from moodle
                    $name = $this->getUserName($member->user_id);

                    $chairmanmembers[$member->id] = $name;

                    //Get the presence of the user
                    $attendance = $DB->get_record('chairman_agenda_attendance', array('chairman_agenda' => $agenda_id, 'chairman_members' => $member->id), '*', $ignoremultiple = false);


                    //Check the presence of the user
                    if ($attendance)
                    {

                        $toform->participant_status_notes[$index] = "";

                        $aMember = array('name' => $name, 'note' => $attendance->notes, 'member' => $member, 'attendance' => $attendance);

                        //If present
                        if (isset($attendance->absent) && $attendance->absent == 0)
                        {
                            $committeemembers['present'][$member->id] = $aMember;
                        }
                        elseif (isset($attendance->absent) && $attendance->absent == 1)
                        {
                            $committeemembers['absent'][$member->id] = $aMember;
                        }
                        else
                        {
                            $committeemembers['uabsent'][$member->id] = $aMember;
                        }
                    }

                    $index++;
                }
            }

            // --------------Moodle Users ---------------------------------------

            $sql = "SELECT * FROM {chairman_agenda_guests} WHERE chairman_agenda = ? AND moodleid IS NOT NULL";
            $moodlemembersagenda = $DB->get_records_sql($sql, array($agenda_id), $limitfrom = 0, $limitnum = 0);
            $moodlemembers = array();
            foreach ($moodlemembersagenda as $member)
            {
                $moodlemembers[] = $this->getUserName($member->moodleid);
            }

            // --------------Guest Users ---------------------------------------
            $sql = "SELECT * FROM {chairman_agenda_guests} WHERE chairman_agenda = ? AND moodleid IS NULL";
            $guests = $DB->get_records_sql($sql, array($agenda_id), $limitfrom = 0, $limitnum = 0);
        }

        // -------------- Topics ------------------------------------------------
        $topics = $DB->get_records('chairman_agenda_topics', array('chairman_agenda' => $agenda_id), $sort = 'topic_order ASC, timecreated ASC', '*', $ignoremultiple = false);



        //----Generate PDF --------------------------------------------------------------
        //INIT PDF
        // Needed to put the page format 'LETTER' in parameter for other countries
        $pdf = new Pdf_custom(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'UTF-8', false);

        //Set meta data
        $pdf->SetCreator('Moodle - Committee');
        $pdf->SetAuthor('Moodle - Committee');
        $pdf->SetSubject($header_text . ' - ' . $chairman->name);
        $pdf->SetKeywords($chairman->name);

        // PDF SETTINGS
        $pdf->SetAutoPageBreak(TRUE, 15);

        // Set Protection
        //$pdf->SetProtection(array('print', 'copy'));
        //
        // Set margins
        $margintop = 30;
        $pdf->SetMargins(PDF_MARGIN_LEFT, $margintop, PDF_MARGIN_RIGHT);

        $pdf->SetFooterMargin(15);

        // Set display mode 100%
        $pdf->SetDisplayMode(100, 'OneColumn');



        // PDF TITLE ---------------------------------------------------------------------
        // Set the header 
        $pdf->SetTitle("<h4 ALIGN=center>$header_text</h3>
                        <h3 ALIGN=center>$chairman->name</h2>");


        // Add the first page
        $pdf->AddPage();



        // PDF DATA ---------------------------------------------------------------------
        //Default font
        $pdf->SetFont('helvetica', 'B', 10);

        //Summary
        if (isset($event_record->summary) && $event_record->summary)
        {
            $pdf->Write($h = 0, $event_record->summary, $link = '', $fill = 0, $align = 'L', $ln = true, $stretch = 0, $firstline = false, $firstblock = false, $maxh = 0);
        }

        $pdf->SetFont('helvetica', '', 10);

        //Date
        $pdf->Write($h = 0, $dateString . ', ' . $time, $link = '', $fill = 0, $align = 'L', $ln = true, $stretch = 0, $firstline = false, $firstblock = false, $maxh = 0);

        //Location 
        if (isset($agenda->location) && $agenda->location)
        {
            $pdf->Write($h = 0, $agenda->location, $link = '', $fill = 0, $align = 'L', $ln = true, $stretch = 0, $firstline = false, $firstblock = false, $maxh = 0);
        }

        //Separation
        $pdf->Cell(0, 0, '', 'B', 1, 'L', 0, '', 0, true);
        $pdf->Cell(0, 0, '', '', 1, 'L', 0, '', 0, true);

        //-----------Participants-------------------------------------------------------
        //Participant are only show if $plain_pdf is false
        if (!$plain_pdf && $commityRecords)
        {

            // --------------Committee members --------------

            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Write($h = 0, get_string('participants', 'chairman'), $link = '', $fill = 0, $align = 'L', $ln = true, $stretch = 0, $firstline = false, $firstblock = false, $maxh = 0);
            $pdf->SetFont('helvetica', '', 10);

            //Modify the margin
            $pdf->SetMargins(PDF_MARGIN_LEFT + 5, $margintop);
            $pdf->ln(0);

            //Check if there is at least one present
            if (!empty($committeemembers['present']))
            {
                $names = array();
                //Insert the presents
                foreach ($committeemembers['present'] as $member)
                {
                    $names[] = $member['name'];
                }
                $pdf->WriteHTML('<B>' . get_string('agenda_presents', 'chairman') . ' </B>' . implode(', ', $names));
            }

            //Check if there is at least one absent
            if (!empty($committeemembers['absent']))
            {
                $names = array();
                //Insert the presents
                foreach ($committeemembers['absent'] as $member)
                {
                    $names[] = $member['name'];
                }
                $pdf->WriteHTML('<B>' . get_string('agenda_absents', 'chairman') . ' </B>' . implode(', ', $names));
            }

            //Check if there is at least one unexused absent
            if (!empty($committeemembers['uabsent']))
            {
                $names = array();
                //Insert the presents
                foreach ($committeemembers['uabsent'] as $member)
                {
                    $names[] = $member['name'];
                }
                $pdf->WriteHTML('<B>' . get_string('agenda_uabsents', 'chairman') . ' </B>' . implode(', ', $names));
            }

            // --------------Moodle Users & Guest Users--------------

            if (!empty($moodlemembers) || !empty($guests))
            {
                $names = $moodlemembers;
                //Insert the presents
                foreach ($guests as $guest)
                {
                    $names[] = $guest->firstname . ' ' . $guest->lastname;
                }
                $pdf->WriteHTML('<B>' . get_string("guest_members", 'chairman') . ' </B>' . implode(', ', $names));
            }

            // --------------Guest Users --------------

            if (!empty($guests))
            {
                $names = array();
            }
            $pdf->SetMargins(PDF_MARGIN_LEFT, $margintop);
        }
        //---------TOPICS---------------------------------------------------------------
        //------------------------------------------------------------------------------

        $pdf->ln(5);

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Write($h = 0, get_string('topics', 'chairman'), $link = '', $fill = 0, $align = 'L', $ln = true, $stretch = 0, $firstline = false, $firstblock = false, $maxh = 0);
        $pdf->SetFont('helvetica', '', 10);

        $pdf->ln(1);



        if ($topics)
        { //check if any topics actually exist
            //possible topic status:
            $topic_statuses = array('open' => get_string('topic_open', 'chairman'),
                'in_progress' => get_string('topic_inprogress', 'chairman'),
                'closed' => get_string('topic_closed', 'chairman'));
            //possible motion status
            $motion_result = array('-1' => '-----',
                '1' => get_string('motion_accepted', 'chairman'),
                '0' => get_string('motion_rejected', 'chairman'));
            $index = 1;
            foreach ($topics as $key => $topic)
            {

                $pdf->SetMargins(PDF_MARGIN_LEFT, $margintop);
                $pdf->ln(0);

                $this->topicNames[$index] = $topic->title;

                $pdf->SetFont('helvetica', 'u', 10);
                $pdf->Cell(0, $h = 0, "$index. $topic->title", $border = 0, $ln = 1, $align = '', $fill = false, $link = '', $stretch = 0, $ignore_min_height = false, $calign = 'T', $valign = 'M');
                $pdf->SetFont('helvetica', '', 10);

                $pdf->SetMargins(PDF_MARGIN_LEFT + 5, $margintop);
                $pdf->ln(0);

                if (isset($topic->duration) && !$topic->duration == "")
                {
                    $pdf->WriteHTML('&nbsp;<B>' . get_string('duration_agenda', 'chairman') . "</b> " . $topic->duration);
                }

                $fs = get_file_storage();
                $files = $fs->get_area_files($context->id, 'mod_chairman', 'attachment', $topic->filename);
                $filenames = '';
                
                foreach($files as $file)
                {
                    $filename = $file->get_filename();
                    if($filename == '.') continue;
                    
                    $link = (moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename()));
                    
                    $file_link = '<a href="'.$link.'" dir="ltr">'.$filename.'</a>';
                    
                    $filenames.= $file_link . ", ";
                }
                    
                $filenames_len = strlen($filenames);
                if($filenames_len > 0)
                    $filenames = substr($filenames, 0, $filenames_len - 2);
                
               if (!$filenames == "")
                {
                    $pdf->WriteHTML('&nbsp;<B>' . get_string('filenames_agenda_c', 'chairman') . "</b> " . $filenames);
                }
                

                if (isset($topic->description) && !$topic->description == "")
                {
                    $pdf->WriteHTML('&nbsp;<B>' . get_string('desc_agenda_c', 'chairman') . "</b> " . $topic->description);
                }

                if (!$plain_pdf && isset($topic->notes) && $topic->notes)
                {
                    $pdf->MultiCell(0, 0, $topic->notes, $border = 0, $align = 'J', $fill = false, $ln = 1, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = true, $autopadding = true, $maxh = 0, $valign = 'T', $fitcell = false);
                }

                $toform->follow_up[$index] = $topic->follow_up;


                $pdf->ln(6);


                //------------------------------------------------------------------------------


                $motions = $DB->get_records('chairman_agenda_motions', array('chairman_agenda' => $agenda_id, 'chairman_agenda_topics' => $topic->id), '', '*', $ignoremultiple = false);

                //-----MOTIONS------------------------------------------------------------------
                //------------------------------------------------------------------------------
                if (!$plain_pdf && $motions)
                {

                    $sub_index = 1;
                    foreach ($motions as $key => $motion)
                    {

                        $proposing_choices = $chairmanmembers;
                        $supporting_choices = $chairmanmembers;

                        $proposing_choices['-1'] = get_string('proposedby', 'chairman');
                        $supporting_choices['-1'] = get_string('supportedby', 'chairman');

                        $pdf->WriteHTML('&nbsp;<b>' . get_string('motion', 'chairman') . $index . '.' . $sub_index . ':</b> ' . $motion->motion);

                        $pdf->SetMargins(PDF_MARGIN_LEFT + 8, $margintop);
                        $pdf->ln(1);

                        //-------DEFAULT VALUEs FOR MOTIONS---------------------------------------------
                        $toform->proposition[$index][$sub_index] = $motion->motion;

                        if (isset($motion->motionby))
                        {

                            $pdf->WriteHTML('&nbsp;<B>' . get_string('proposedby', 'chairman') . "</B> " . $proposing_choices[$motion->motionby]);
                        }
                        else
                        {
                            
                        }

                        if (isset($motion->secondedby))
                        {
                            $pdf->WriteHTML('&nbsp;<B>' . get_string('supportedby', 'chairman') . "</B> " . $supporting_choices[$motion->secondedby]);
                        }
                        else
                        {
                            
                        }

                        $result = "";

                        if (isset($motion->carried))
                        {
                            $result = $motion_result[$motion->carried];

                            if (isset($motion->unanimous))
                            {
                                $result .= "(" . get_string('unanimous', 'chairman') . ")";
                            }
                        }

                        if (isset($motion->carried))
                        {
                            $pdf->WriteHTML('&nbsp;<b>' . get_string('motion_outcome', 'chairman') . "</b> " . $result);
                        }
                        else
                        {
                            
                        }




                        $toform->aye[$index][$sub_index] = $motion->yea;
                        $toform->nay[$index][$sub_index] = $motion->nay;
                        $toform->abs[$index][$sub_index] = $motion->abstained;


                        $vote_string = '&nbsp;<B>' . get_string('topic_vote_aye', 'chairman') . $motion->yea;
                        $vote_string .= ' - <B>' . get_string('topic_vote_nay', 'chairman') . $motion->nay;
                        $vote_string .= ' - <B>' . get_string('topic_vote_abs', 'chairman') . $motion->abstained;

                        $pdf->WriteHTML($vote_string);


                        //---------END DEFAULTS---------------------------------------------------------

                        $sub_index++;
                        $pdf->ln(3);
                    }
                }//end if any motions exist
                //-------DEFAULT TOPIC VALUEs---------------------------------------------------

                $toform->topic_notes[$index] = $topic->notes;

                if (isset($topic->status))
                {
                    $toform->topic_status[$index] = $topic_statuses[$topic->status];
                }
                else
                {
                    $toform->topic_status[$index] = $topic_statuses['open'];
                }

                $toform->follow_up[$index] = $topic->follow_up;
                //---------END DEFAULTS---------------------------------------------------------


                $index++;
                $pdf->ln(5);
            }//end foreach topic
            $pdf->ln(2);
        }//end topics
        //Set default values to private variable
        $this->default_toform = $toform;
        
        
        $this->title = $this->get_title($plain_pdf);
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
    
    public function is_plain_pdf()
    {
        return $this->plain_pdf;
    }
    
        /**
         * This doesn't update the that has already been generated by create_pdf($plain_pdf),
         * but will be used if null is passed in for create_pdf($plain_pdf)
         * 
         * @param type $is_plain_pdf
         */
        public function set_plain_pdf($is_plain_pdf)
    {
        $this->plain_pdf = $is_plain_pdf;
    }
    
     /**
     * Gets the title of the pdf
     */
    public function get_title($plain_pdf) {
        global $DB;
        if (!isset($this->title)) {
            $event_record = $DB->get_record('chairman_events', array('id' => $this->event_id), '*', $ignoremultiple = false);
            $date = new DateTime($event_record->year . "-" . $event_record->month . "-" . $event_record->day);

            if (!$plain_pdf)
                $this->title = get_string('arising_issues', 'chairman') . '-' . $date->format('Y-m-d') . '.pdf';
            else
                $this->title = get_string('agenda', 'chairman') . '-' . $date->format('Y-m-d') . '.pdf';
        }


        return $this->title;
    }
    
         /**
      * Gets the event name the pdf is for
      */
    public function get_event_name()
    {
        global $DB;
        if(!isset($this->event_name))
        {
            $event_record = $DB->get_record('chairman_events', array('id' => $this->event_id), '*', $ignoremultiple = false);
            $this->event_name = $event_record->summary;
        }
    
        return $this->event_name;
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

    /*
     * Converts a given moodle ID into a FirstName LastName String.
     *
     *  @param $int $userID An unique moodle ID for a moodle user.
     */

    function getUserName($userID)
    {
        Global $DB;

        $user = $DB->get_record('user', array('id' => $userID), '*', $ignoremultiple = false);
        $name = null;
        if ($user)
        {
            $name = $user->firstname . " " . $user->lastname;
        }
        return $name;
    }

    /*
     * Returns An array of topic names with array keys being the index that the topic
     * is on the page. Used for menu sidebar creation.
     */

    function getIndexToNamesArray()
    {
        return $this->topicNames;
    }

}
