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

require_once('../../config.php');
include('lib_chairman.php');

global $CFG, $DB;
    //Get all events
    $events = $DB->get_records('chairman_events');
        
    foreach($events as $event){
        //check for event coming up in a week
        $week = (60*60*24)*7; //60 seconds * 60 minutes * 24 hours *7 days
        //get committee president for sending email
        $president = $DB->get_record('chairman_members', array('chairman_id' => $event->chairman_id, 'role_id' => 1));
        //email information
        $from = $DB->get_record('user', array('id' => $president->user_id));
        $subject = get_string('notify_reminder', 'chairman').$event->summary;
        $emailmessage = get_string('notify_week_message', 'chairman').'<p>'.$event->description.'</p>';
        if ($event->notify_week == 1){
            
            //First get course module to retrieve instance id
            $cm = $DB->get_record('course_modules',array('id'=>$event->chairman_id));
            //get chairman information
            $chairman = $DB->get_record('chairman',array('id' => $cm->instance));
            //get date 7 days later then today
            $one_week_prior = time() + $week; //Now plus 7 days
            //Convert to human readible format
            $one_week_prior_day = date('d', $one_week_prior);
            $one_week_prior_month = date('m', $one_week_prior);
            $one_week_prior_year = date('Y', $one_week_prior);
            //If one_week_prior = event day AND no email has been sent
            if ($one_week_prior_day == $event->day AND $one_week_prior_month == $event->month AND $one_week_prior_year == $event->year AND $event->notify_week_sent == 0){
                
                //get all member emails.
                $members = $DB->get_records('chairman_members', array('chairman_id' => $event->chairman_id));
                
                $i=0;
                
                foreach ($members as $member){
                    $user = $DB->get_record('user',array('id' => $member->user_id));
                    $message[$i] = str_replace('{a}', "$user->firstname", $emailmessage);
                    $message[$i] = str_replace('{c}', "$chairman->name", $message[$i]);
                    $message[$i] = str_replace('{b}', "$event->day/$event->month/$event->year", $message[$i]);
                    echo "$subject<br>$message[$i]";
                    $i++;
                    email_to_user($user, $from, $subject, $message[$i]);
                }
                //update sent notification
                $chairman_event = new object();
                $chairman_event->id = $event->id;
                $chairman_event->notify_week_sent = 1;
                //enter info into DB
                $update_event = $DB->update_record('chairman_events', $chairman_event);
            } else {
                echo "No email sent<br>";
            }    
        }
        //Now the same thing if it is a day prior
        $day = 60*60*24;
        if ($event->notify == 1){
            //get date 1 day later then today
            $one_day_prior = time() + $day; //Now plus 7 days
            //Convert to human readible format
            $one_day_prior_day = date('d', $one_day_prior);
            $one_day_prior_month = date('m', $one_day_prior);
            $one_day_prior_year = date('Y', $one_day_prior);
            //If one_week_prior = event day AND no email has been sent
            if ($one_day_prior_day == $event->day AND $one_day_prior_month == $event->month AND $one_day_prior_year == $event->year AND $event->notify_sent == 0){
                //get all member emails.
                $members = $DB->get_records('chairman_members', array('chairman_id' => $event->chairman_id));
             
                $i=0;
                foreach ($members as $member){
                    $user = $DB->get_record('user',array('id' => $member->user_id));
                    
                    $message[$i] = str_replace('{a}', "$user->firstname", $emailmessage);
                    $message[$i] = str_replace('{c}', "$chairman->name", $message[$i]);
                    $message[$i] = str_replace('{b}', "$event->day/$event->month/$event->year", $message[$i]);
                    echo "$subject<br>$message[$i]";
                    $i++;
                    email_to_user($user, $from, $subject, $message[$i]);
                }
                //update sent notification
                $chairman_event = new object();
                $chairman_event->id = $event->id;
                $chairman_event->notify_sent = 1;
                //enter info into DB
                $update_event = $DB->update_record('chairman_events', $chairman_event);
            } else {
                
                echo "No email sent<br>";
            }    
        }
    }
?>
