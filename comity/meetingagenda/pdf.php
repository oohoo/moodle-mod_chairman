<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * The class to represent an PDF version of the agenda.
 *
 *
 * @package   Agenda/Meeting Extension to Committee Module
 * @copyright 2011 Dustin Durand
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/mod/comity/meetingagenda/lib.php");
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
        //$image_file = $OUTPUT->pix_url('logo_' . $lang, 'mod_comity');
		
	$image_file = "$SESSION->comity_logo";
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
        $this->Cell(0, 10, get_string('page', 'comity') . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }

}

class pdf_creator
{

    private $instance;
    private $event_id;
    private $agenda_id;
    private $comity_id;
    private $default_toform;

    function __construct($event_id, $agenda_id, $comity_id, $cm)
    {
        $this->event_id = $event_id;
        $this->agenda_id = $agenda_id;
        $this->comity_id = $comity_id;
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
        $comity_id = $this->comity_id;


        $event_record = $DB->get_record('comity_events', array('id' => $event_id), '*', $ignoremultiple = false);
        $agenda = $DB->get_record('comity_agenda', array('id' => $agenda_id), '*', $ignoremultiple = false);
        $comity = $DB->get_record("comity", array("id" => $instance)); // get comity record for our instance
        //Date
        $date = new DateTime($event_record->year . "-" . $event_record->month . "-" . $event_record->day);
        //Change the local just for get the date in the format for this language
        setlocale(LC_TIME, $CFG->lang);
        $dateString = utf8_encode(strftime(get_string('format_date', 'comity'), $date->getTimestamp()));
        $time = $event_record->starthour . get_string('separatorhourminute', 'comity') . ZeroPaddingTime($event_record->startminutes) . "-" . $event_record->endhour . get_string('separatorhourminute', 'comity') . ZeroPaddingTime($event_record->endminutes);

        $toform = new stdClass();
        $toform->committee = $comity->name;
        $toform->summary = $event_record->summary;
        $toform->description = $event_record->description;
        $toform->event_id = $event_id;
        $toform->location = $agenda->location;

        if (!$plain_pdf)
        {
            $header_text = get_string('minutes_tab', 'comity');
        }
        else
        {
            $header_text = get_string('agenda_tab', 'comity');
        }

        $comitymembers = array();

        //-----Participants--------------------------------------------------------------
        //Only if plain_pdf is false
        if (!$plain_pdf)
        {
            //Get the committee members
            $commityRecords = $DB->get_records('comity_agenda_members', array('comity_id' => $comity_id, 'agenda_id' => $agenda_id), '', '*', $ignoremultiple = false);

            //--------Committee Members------------------------------------------------------
            $committeemembers = array('present' => array(), 'absent' => array(), 'uabsent' => array()); //Used to store commitee members in an array
            //Used for topics and motions
            $comitymembers = array();

            $FORUM_TYPES = array(
                '0' => get_string('agenda_present', 'comity'),
                '1' => get_string('agenda_absent', 'comity'),
                '2' => get_string('agenda_uabsent', 'comity'));

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

                    $comitymembers[$member->id] = $name;

                    //Get the presence of the user
                    $attendance = $DB->get_record('comity_agenda_attendance', array('comity_agenda' => $agenda_id, 'comity_members' => $member->id), '*', $ignoremultiple = false);


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

            $sql = "SELECT * FROM {comity_agenda_guests} WHERE comity_agenda = ? AND moodleid IS NOT NULL";
            $moodlemembersagenda = $DB->get_records_sql($sql, array($agenda_id), $limitfrom = 0, $limitnum = 0);
            $moodlemembers = array();
            foreach ($moodlemembersagenda as $member)
            {
                $moodlemembers[] = $this->getUserName($member->moodleid);
            }

            // --------------Guest Users ---------------------------------------
            $sql = "SELECT * FROM {comity_agenda_guests} WHERE comity_agenda = ? AND moodleid IS NULL";
            $guests = $DB->get_records_sql($sql, array($agenda_id), $limitfrom = 0, $limitnum = 0);
        }

        // -------------- Topics ------------------------------------------------
        $topics = $DB->get_records('comity_agenda_topics', array('comity_agenda' => $agenda_id), $sort = 'timecreated ASC', '*', $ignoremultiple = false);



        //----Generate PDF --------------------------------------------------------------
        //INIT PDF
        // Needed to put the page format 'LETTER' in parameter for other countries
        $pdf = new Pdf_custom(PDF_PAGE_ORIENTATION, PDF_UNIT, 'LETTER', true, 'UTF-8', false);

        //Set meta data
        $pdf->SetCreator('Moodle - Committee');
        $pdf->SetAuthor('Moodle - Committee');
        $pdf->SetSubject($header_text . ' - ' . $comity->name);
        $pdf->SetKeywords($comity->name);

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
                        <h3 ALIGN=center>$comity->name</h2>");


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
            $pdf->Write($h = 0, get_string('participants', 'comity'), $link = '', $fill = 0, $align = 'L', $ln = true, $stretch = 0, $firstline = false, $firstblock = false, $maxh = 0);
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
                $pdf->WriteHTML('<B>' . get_string('agenda_presents', 'comity') . ' </B>' . implode(', ', $names));
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
                $pdf->WriteHTML('<B>' . get_string('agenda_absents', 'comity') . ' </B>' . implode(', ', $names));
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
                $pdf->WriteHTML('<B>' . get_string('agenda_uabsents', 'comity') . ' </B>' . implode(', ', $names));
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
                $pdf->WriteHTML('<B>' . get_string("guest_members", 'comity') . ' </B>' . implode(', ', $names));
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
        $pdf->Write($h = 0, get_string('topics', 'comity'), $link = '', $fill = 0, $align = 'L', $ln = true, $stretch = 0, $firstline = false, $firstblock = false, $maxh = 0);
        $pdf->SetFont('helvetica', '', 10);

        $pdf->ln(1);



        if ($topics)
        { //check if any topics actually exist
            //possible topic status:
            $topic_statuses = array('open' => get_string('topic_open', 'comity'),
                'in_progress' => get_string('topic_inprogress', 'comity'),
                'closed' => get_string('topic_closed', 'comity'));
            //possible motion status
            $motion_result = array('-1' => '-----',
                '1' => get_string('motion_accepted', 'comity'),
                '0' => get_string('motion_rejected', 'comity'));
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
                    $pdf->WriteHTML('&nbsp;<B>' . get_string('duration_agenda', 'comity') . "</b> " . $topic->duration);
                }



                if (isset($topic->description) && !$topic->description == "")
                {
                    $pdf->WriteHTML('&nbsp;<B>' . get_string('desc_agenda_c', 'comity') . "</b> " . $topic->description);
                }

                if (!$plain_pdf && isset($topic->notes) && $topic->notes)
                {
                    $pdf->MultiCell(0, 0, $topic->notes, $border = 0, $align = 'J', $fill = false, $ln = 1, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = true, $autopadding = true, $maxh = 0, $valign = 'T', $fitcell = false);
                }

                $toform->follow_up[$index] = $topic->follow_up;


                $pdf->ln(6);


                //------------------------------------------------------------------------------


                $motions = $DB->get_records('comity_agenda_motions', array('comity_agenda' => $agenda_id, 'comity_agenda_topics' => $topic->id), '', '*', $ignoremultiple = false);

                //-----MOTIONS------------------------------------------------------------------
                //------------------------------------------------------------------------------
                if (!$plain_pdf && $motions)
                {

                    $sub_index = 1;
                    foreach ($motions as $key => $motion)
                    {

                        $proposing_choices = $comitymembers;
                        $supporting_choices = $comitymembers;

                        $proposing_choices['-1'] = get_string('proposedby', 'comity');
                        $supporting_choices['-1'] = get_string('supportedby', 'comity');

                        $pdf->WriteHTML('&nbsp;<b>' . get_string('motion', 'comity') . $index . '.' . $sub_index . ':</b> ' . $motion->motion);

                        $pdf->SetMargins(PDF_MARGIN_LEFT + 8, $margintop);
                        $pdf->ln(1);

                        //-------DEFAULT VALUEs FOR MOTIONS---------------------------------------------
                        $toform->proposition[$index][$sub_index] = $motion->motion;

                        if (isset($motion->motionby))
                        {

                            $pdf->WriteHTML('&nbsp;<B>' . get_string('proposedby', 'comity') . "</B> " . $proposing_choices[$motion->motionby]);
                        }
                        else
                        {
                            
                        }

                        if (isset($motion->secondedby))
                        {
                            $pdf->WriteHTML('&nbsp;<B>' . get_string('supportedby', 'comity') . "</B> " . $supporting_choices[$motion->secondedby]);
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
                                $result .= "(" . get_string('unanimous', 'comity') . ")";
                            }
                        }

                        if (isset($motion->carried))
                        {
                            $pdf->WriteHTML('&nbsp;<b>' . get_string('motion_outcome', 'comity') . "</b> " . $result);
                        }
                        else
                        {
                            
                        }




                        $toform->aye[$index][$sub_index] = $motion->yea;
                        $toform->nay[$index][$sub_index] = $motion->nay;
                        $toform->abs[$index][$sub_index] = $motion->abstained;


                        $vote_string = '&nbsp;<B>' . get_string('topic_vote_aye', 'comity') . $motion->yea;
                        $vote_string .= ' - <B>' . get_string('topic_vote_nay', 'comity') . $motion->nay;
                        $vote_string .= ' - <B>' . get_string('topic_vote_abs', 'comity') . $motion->abstained;

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

        if (!$plain_pdf)
        {
            $titlePDF = 'minutes-' . $date->format('Y-m-d') . '.pdf';
        }
        else
        {
            $titlePDF = 'agenda-' . $date->format('Y-m-d') . '.pdf';
        }
        $pdf->Output($titlePDF, 'd');
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
