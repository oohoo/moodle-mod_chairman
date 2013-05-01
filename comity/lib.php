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
 * @package   comity
 * @copyright 2010 Raymond Wainman, Patrick Thibaudeau (Campus St. Jean, University of Alberta)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/// Library of functions and constants for module comity

function comity_add_instance($comity) {
/// Given an object containing all the necessary data, 
/// (defined by the form in mod.html) this function 
/// will create a new instance and return the id number 
/// of the new instance.

    global $DB;

    $comity->name = format_string($comity->name);
    $comity->timecreated = time();
    $comity->timemodified = time();
    $comity->description = null;
    $comity->intro = '';
    $comity->introformat = 0;

    $comity->id = $DB->insert_record('comity', $comity, true);
    
    //Create or delete forum

    if (isset($comity->use_forum)){
        
        comity_add_module('forum', $comity->course, $comity->name, $comity->id, 'add');
    } else {
        if ($comity->forum > 0){
            comity_add_module('forum', $comity->course, $comity->name, $comity->id, 'del',$comity->forum);
        }
    }
    //Create or delete wiki
    if (isset($comity->use_wiki)){
        comity_add_module('wiki', $comity->course, $comity->name, $comity->id, 'add');
    } else {
        if ($comity->wiki > 0){
            comity_add_module('wiki', $comity->course, $comity->name, $comity->id, 'del',$comity->wiki);
        }
    }
    //Create or delete questionnaire
    if (isset($comity->use_questionnaire)){
        comity_add_module('questionnaire', $comity->course, $comity->name, $comity->id, 'add');
    } else {
        if ($comity->questionnaire > 0){
            comity_add_module('questionnaire', $comity->course, $comity->name, $comity->id, 'del',$comity->questionnaire);
        }
    }
    
    return $comity->id;
}


function comity_update_instance($comity, $mform=null) {
/// Given an object containing all the necessary data, 
/// (defined by the form in mod_form.php) this function
/// will update an existing instance with new data.

    global $DB;
    if (!isset($comity->secured)){
        $comity->secured = 0;
    }
    $comity->id = $comity->instance;
    $comity->timemodified = time();
    $comity->name = format_string($comity->name);
    $comity->description = null;
    
    //Create or delete forum

    if (isset($comity->use_forum)){
        if ($comity->forum == 0) {
            $mod_id = update_module('forum', $comity->course, $comity->name, $comity->id, 'add');
            $comity->forum = $mod_id;
            $DB->update_record('comity', array('id'=>$comity->id, 'forum'=>$comity->forum));
        }
    } else {
        if ($comity->forum > 0){
            comity_add_module('forum', $comity->course, $comity->name, $comity->id, 'del',$comity->forum);
        }
        $comity->use_forum = 0;
    }
    //Create or delete wiki
    if (isset($comity->use_wiki)){
        if ($comity->wiki == 0) {
            $mod_id = comity_update_module('wiki', $comity->course, $comity->name, $comity->id, 'add');
            $comity->wiki = $mod_id;
            $DB->update_record('comity', array('id'=>$comity->id, 'wiki'=>$comity->wiki));
        }
    } else {
        if ($comity->wiki > 0){
            comity_add_module('wiki', $comity->course, $comity->name, $comity->id, 'del',$comity->wiki);
        }
        $comity->use_wiki = 0;
    }
    //Create or delete questionnaire
    if (isset($comity->use_questionnaire)){
        if ($comity->questionnaire == 0) {
            $mod_id = comity_update_module('questionnaire', $comity->course, $comity->name, $comity->id, 'add');
            $comity->questionnaire = $mod_id;
            $DB->update_record('comity', array('id'=>$comity->id, 'wiki'=>$comity->wiki));
        }
    } else {
        if ($comity->questionnaire > 0){
            comity_add_module('questionnaire', $comity->course, $comity->name, $comity->id, 'del',$comity->questionnaire);
        }
        $comity->use_questionnaire = 0;
    }

    return $DB->update_record("comity", $comity);
}


function comity_delete_instance($id) {
/// Given an ID of an instance of this module, 
/// this function will permanently delete the instance 
/// and any data that depends on it.  

    global $DB;

    if (! $comity = $DB->get_record("comity", array("id"=>$id))) {
        return false;
    }

    $result = true;

    if (! $DB->delete_records("comity", array("id"=>$comity->id))) {
        $result = false;
    }

    return $result;
}

function comity_get_participants($comityid) {
//Returns the users with data in one resource
//(NONE, but must exist on EVERY mod !!)

    return false;
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 * See get_array_of_activities() in course/lib.php
 */
function comity_get_coursemodule_info($coursemodule) {
    global $DB;

    if ($comity = $DB->get_record('comity', array('id'=>$coursemodule->instance))) {
        $info = new object();
        $info->name = $comity->name;
        return $info;
    } else {
        return null;
    }
}

function comity_get_view_actions() {
    return array();
}

function comity_get_post_actions() {
    return array();
}

function comity_get_types() {
    $types = array();

    $type = new object();
    $type->modclass = MOD_CLASS_ACTIVITY;
    $type->type = "comity";
    $type->typestr = get_string('modulename', 'comity');
    $types[] = $type;

    return $types;
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function comity_reset_userdata($data) {
    return array();
}

/**
 * Returns all other caps used in module
 */
function comity_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}

/**
 * @uses FEATURE_IDNUMBER
 * @uses FEATURE_GROUPS
 * @uses FEATURE_GROUPINGS
 * @uses FEATURE_GROUPMEMBERSONLY
 * @uses FEATURE_MOD_INTRO
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE_GRADE_HAS_GRADE
 * @uses FEATURE_GRADE_OUTCOMES
 * @param string $feature FEATURE_xx constant for requested feature
 * @return bool|null True if module supports feature, false if not, null if doesn't know
 */
function comity_supports($feature) {
    switch($feature) {
        case FEATURE_IDNUMBER:                return false;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_GROUPMEMBERSONLY:        return false;
        case FEATURE_MOD_INTRO:               return false;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return false;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_BACKUP_MOODLE2:          return false;

        default: return null;
    }
}

function comity_pluginfile($course, $cminfo, $context, $filearea, $args, $forcedownload) {
    global $CFG, $DB;

    $fs = get_file_storage();
    $relativepath = '/'.implode('/', $args);
    
    $hash = $fs->get_pathname_hash($context->id, 'mod_comity', 'comity', 0, $relativepath, '');

    $file = $fs->get_file_by_hash($hash);

    // finally send the file
    send_stored_file($file, 86400, 0, true);
}

function comity_cron(){
    global $CFG, $DB;
    //Get all events
    $events = $DB->get_records('comity_events');
        
    foreach($events as $event){
        //check for event coming up in a week
        $week = (60*60*24)*7; //60 seconds * 60 minutes * 24 hours *7 days
        //get committee president for sending email
        $president = $DB->get_record('comity_members', array('comity_id' => $event->comity_id, 'role_id' => 1));
        //email information
        $from = $DB->get_record('user', array('id' => $president->user_id));
        $subject = get_string('notify_reminder', 'comity').$event->summary;
        $emailmessage = get_string('notify_week_message', 'comity').'<p>'.$event->description.'</p>';
        if ($event->notify_week == 1){
            
            //First get course module to retrieve instance id (The actual committee id)
            $cm = $DB->get_record('course_modules',array('id'=>$event->comity_id));
            //get comity information
            $comity = $DB->get_record('comity',array('id' => $cm->instance));
            //get date 7 days later then today
            $one_week_prior = time() + $week; //Now plus 7 days
            //Convert to human readible format
            $one_week_prior_day = date('d', $one_week_prior);
            $one_week_prior_month = date('m', $one_week_prior);
            $one_week_prior_year = date('Y', $one_week_prior);
            //If one_week_prior = event day AND no email has been sent
            if ($one_week_prior_day == $event->day AND $one_week_prior_month == $event->month AND $one_week_prior_year == $event->year AND $event->notify_week_sent == 0){
                
                //get all member emails.
                $members = $DB->get_records('comity_members', array('comity_id' => $event->comity_id));
                
                $i=0;
                
                foreach ($members as $member){
                    $user = $DB->get_record('user',array('id' => $member->user_id));
                    $message[$i] = str_replace('{a}', "$user->firstname", $emailmessage);
                    $message[$i] = str_replace('{c}', "$comity->name", $message[$i]);
                    $message[$i] = str_replace('{b}', "$event->day/$event->month/$event->year", $message[$i]);
                    //echo "$subject<br>$message[$i]<br>";
					email_to_user($user, $from, $subject, $message[$i]);
                    $i++;
                    
                }
                //update sent notification
                $comity_event = new object();
                $comity_event->id = $event->id;
                $comity_event->notify_week_sent = 1;
                //enter info into DB
                $update_event = $DB->update_record('comity_events', $comity_event);
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
                $members = $DB->get_records('comity_members', array('comity_id' => $event->comity_id));
             
                $i=0;
                foreach ($members as $member){
                    $user = $DB->get_record('user',array('id' => $member->user_id));
                    
                    $message[$i] = str_replace('{a}', "$user->firstname", $emailmessage);
                    $message[$i] = str_replace('{c}', "$comity->name", $message[$i]);
                    $message[$i] = str_replace('{b}', "$event->day/$event->month/$event->year", $message[$i]);
                    //echo "$subject<br>$message[$i]<br>";
					email_to_user($user, $from, $subject, $message[$i]);
                    $i++;
                    
                }
                //update sent notification
                $comity_event = new object();
                $comity_event->id = $event->id;
                $comity_event->notify_sent = 1;
                //enter info into DB
                $update_event = $DB->update_record('comity_events', $comity_event);
            } else {
                
                echo "No email sent<br>";
            }    
        }
    }
}
//This function adds either a wiki or forum module
// to the course in section 500
function comity_add_module($type, $courseid, $committee_name, $committee_id, $action, $mod_id = null) {
    global $DB, $CFG, $USER;
    
    
    //include("$CFG->wwwroot/course/lib.php");
    if(!$cmodule = $DB->get_record('modules', array('name' => $type))){
        return null;
    }
    
    if ($action == 'del') {
        
        //do nothing for now
        
        
    } else {


//if no comity make a comity
    if ($type == 'forum') {
        $new_forum = new stdClass;       
        $new_forum->course = $courseid;
        $new_forum->name = $committee_name;
        $new_forum->intro = $committee_name;
        $new_forum->introformat = 1;
        $new_forum->timemodified = time();

        //Create forum
        $new_forum->id = $DB->insert_record('forum',$new_forum);
       
        //Create actual course module
        $new_course_module = new stdClass;
        $new_course_module->course = $courseid;
        $new_course_module->module = $cmodule->id;
        $new_course_module->instance = $new_forum->id;
        $new_course_module->added = time();
        $new_course_module->visible = 1;
        $new_course_module->visibleold = 1;
       
        $new_course_module->id = $DB->insert_record('course_modules',$new_course_module);
        
        $mod = $DB->get_record('course_modules',array('id'=>$new_course_module->id));
        
        //Create section object
        
        //get section information
        if (!$section = $DB->get_record('course_sections', array('course'=>$mod->course,'section' => 500))){
            //Create new section if it doesn't already exist
            $new_module_section = new stdClass;
            $new_module_section->course = $mod->course;
            $new_module_section->section = 500;
            $new_module_section->sequence = ','.$mod->id;
            $new_module_section->visible = 1;
            $section_id = $DB->insert_record('course_sections', $new_module_section);
            $DB->update_record('course_modules', array('id' => $mod->id, 'section'=>$section_id));
        } else {
            //update section
            $module_section = new stdClass;
            $module_section->id = $section->id;
            $module_section->sequence = $section->sequence.','.$mod->id;
            
            $DB->update_record('course_sections',$module_section);
            $DB->update_record('course_modules', array('id' => $mod->id, 'section'=>$section->id));
        }
        //Update comity information with forum mod id
        $committee = new stdClass;
        $committee->id = $committee_id;
        $committee->forum =$mod->id;
        
        $DB->update_record('comity',$committee);
         

        
//if a wiki        
    } else if ($type == 'wiki') {

//fill the data to be added
        $new_wiki = new stdclass();
        $new_wiki->course = $courseid;
        $new_wiki->name = $committee_name;
        $new_wiki->intro = $committee_name;
        $new_wiki->timecreated = time();
        $new_wiki->timemodified = time();
        
        $new_wiki->id = $DB->insert_record('wiki',$new_wiki);
        
        //Create actual course module
        $new_course_module = new stdClass;
        $new_course_module->course = $courseid;
        $new_course_module->module = $cmodule->id;
        $new_course_module->instance = $new_wiki->id;
        $new_course_module->added = time();
        $new_course_module->visible = 1;
        $new_course_module->visibleold = 1;
       
        $new_course_module->id = $DB->insert_record('course_modules',$new_course_module);
        
        $mod = $DB->get_record('course_modules',array('id'=>$new_course_module->id));
        
        //Create section object
        
        //get section information
        if (!$section = $DB->get_record('course_sections', array('course'=>$mod->course,'section' => 500))){
            //Create new section if it doesn't already exist
            $new_module_section = new stdClass;
            $new_module_section->course = $mod->course;
            $new_module_section->section = 500;
            $new_module_section->sequence = ','.$mod->id;
            $new_module_section->visible = 1;
            $section_id = $DB->insert_record('course_sections', $new_module_section);
            //update secition in course module
            $DB->update_record('course_modules', array('id' => $mod->id, 'section'=>$section_id));
        } else {
            //update section
            $module_section = new stdClass;
            $module_section->id = $section->id;
            $module_section->sequence = $section->sequence.','.$mod->id;
            
            $DB->update_record('course_sections',$module_section);
            $DB->update_record('course_modules', array('id' => $mod->id, 'section'=>$section->id));
        }
        //Update comity information with forum mod id
        $committee = new stdClass;
        $committee->id = $committee_id;
        $committee->wiki =$mod->id;
            
        $DB->update_record('comity',$committee); 
        
        
    } else if ($type == 'questionnaire') { //if questionnaire

//fill the data to be added
        $new_questionnaire = new stdclass();
        $new_questionnaire->course = $courseid;
        $new_questionnaire->name = $committee_name;
        $new_questionnaire->intro = $committee_name;
        $new_questionnaire->timecreated = time();
        $new_questionnaire->timemodified = time();
        
        $new_questionnaire->id = $DB->insert_record('questionnaire',$new_questionnaire);
        
        $new_questionnaire_survey = new stdClass();
        $new_questionnaire_survey->name = $new_questionnaire->name;
        $new_questionnaire_survey->owner = $courseid;
        $new_questionnaire_survey->realm = 'private';
        $new_questionnaire_survey->status = 0;
        $new_questionnaire_survey->title = $new_questionnaire->name;
        
        $new_questionnaire_survey->id = $DB->insert_record('questionnaire_survey', $new_questionnaire_survey);
        //now update questionnaire sid filed
        $DB->update_record('questionnaire', array('id' => $new_questionnaire->id, 'sid' => $new_questionnaire_survey->id));
        
        //Create actual course module
        $new_course_module = new stdClass;
        $new_course_module->course = $courseid;
        $new_course_module->module = $cmodule->id;
        $new_course_module->instance = $new_questionnaire->id;
        $new_course_module->added = time();
        $new_course_module->visible = 1;
        $new_course_module->visibleold = 1;
       
        $new_course_module->id = $DB->insert_record('course_modules',$new_course_module);
        
        $mod = $DB->get_record('course_modules',array('id'=>$new_course_module->id));
        
        //Create section object
        
        //get section information
        if (!$section = $DB->get_record('course_sections', array('course'=>$mod->course,'section' => 500))){
            //Create new section if it doesn't already exist
            $new_module_section = new stdClass;
            $new_module_section->course = $mod->course;
            $new_module_section->section = 500;
            $new_module_section->sequence = ','.$mod->id;
            $new_module_section->visible = 1;
            $section_id = $DB->insert_record('course_sections', $new_module_section);
            $DB->update_record('course_modules', array('id' => $mod->id, 'section'=>$section_id));
        } else {
            //update section
            $module_section = new stdClass;
            $module_section->id = $section->id;
            $module_section->sequence = $section->sequence.','.$mod->id;
            
            $DB->update_record('course_sections',$module_section);
            $DB->update_record('course_modules', array('id' => $mod->id, 'section'=>$section->id));
        }
        //Update comity information with forum mod id
        $committee = new stdClass;
        $committee->id = $committee_id;
        $committee->questionnaire =$mod->id;
            
        $DB->update_record('comity',$committee); 
        
        
    }
}
}
//This function adds either a wiki or forum module
// to the course in section 500
function comity_update_module($type, $courseid, $committee_name, $committee_id, $action, $mod_id = null) {
    global $DB, $CFG, $USER;
    
    
    //include("$CFG->wwwroot/course/lib.php");
    if(!$cmodule = $DB->get_record('modules', array('name' => $type))){
        return null;
    }
   


//if no comity make a comity
    switch ($type){
    case 'forum':
        $new_forum = new stdClass;       
        $new_forum->course = $courseid;
        $new_forum->name = $committee_name;
        $new_forum->intro = $committee_name;
        $new_forum->introformat = 1;
        $new_forum->timemodified = time();

        //Create forum
        $new_forum->id = $DB->insert_record('forum',$new_forum);
       
        //Create actual course module
        $new_course_module = new stdClass;
        $new_course_module->course = $courseid;
        $new_course_module->module = $cmodule->id;
        $new_course_module->instance = $new_forum->id;
        $new_course_module->added = time();
        $new_course_module->visible = 1;
        $new_course_module->visibleold = 1;
       
        $new_course_module->id = $DB->insert_record('course_modules',$new_course_module);
        
        $mod = $DB->get_record('course_modules',array('id'=>$new_course_module->id));
        
        //Create section object
        
        //get section information
        if (!$section = $DB->get_record('course_sections', array('course'=>$mod->course,'section' => 500))){
            //Create new section if it doesn't already exist
            $new_module_section = new stdClass;
            $new_module_section->course = $mod->course;
            $new_module_section->section = 500;
            $new_module_section->sequence = ','.$mod->id;
            $new_module_section->visible = 1;
            $section_id = $DB->insert_record('course_sections', $new_module_section);
            $DB->update_record('course_modules', array('id' => $mod->id, 'section'=>$section_id));
        } else {
            //update section
            $module_section = new stdClass;
            $module_section->id = $section->id;
            $module_section->sequence = $section->sequence.','.$mod->id;
            
            $DB->update_record('course_sections',$module_section);
            $DB->update_record('course_modules', array('id' => $mod->id, 'section'=>$section->id));
        }
        //Update comity information with forum mod id
        $committee = new stdClass;
        $committee->id = $committee_id;
        $committee->forum =$mod->id;
        
        return $committee->forum;// $DB->update_record('comity',$committee);
        break;

        
       
    case 'wiki':

//fill the data to be added
        $new_wiki = new stdclass();
        $new_wiki->course = $courseid;
        $new_wiki->name = $committee_name;
        $new_wiki->intro = $committee_name;
        $new_wiki->timecreated = time();
        $new_wiki->timemodified = time();
        
        $new_wiki->id = $DB->insert_record('wiki',$new_wiki);
        
        //Create actual course module
        $new_course_module = new stdClass;
        $new_course_module->course = $courseid;
        $new_course_module->module = $cmodule->id;
        $new_course_module->instance = $new_wiki->id;
        $new_course_module->added = time();
        $new_course_module->visible = 1;
        $new_course_module->visibleold = 1;
       
        $new_course_module->id = $DB->insert_record('course_modules',$new_course_module);
        
        $mod = $DB->get_record('course_modules',array('id'=>$new_course_module->id));
        
        //Create section object
        
        //get section information
        if (!$section = $DB->get_record('course_sections', array('course'=>$mod->course,'section' => 500))){
            //Create new section if it doesn't already exist
            $new_module_section = new stdClass;
            $new_module_section->course = $mod->course;
            $new_module_section->section = 500;
            $new_module_section->sequence = ','.$mod->id;
            $new_module_section->visible = 1;
            $section_id = $DB->insert_record('course_sections', $new_module_section);
            $DB->update_record('course_modules', array('id' => $mod->id, 'section'=>$section_id));
        } else {
            //update section
            $module_section = new stdClass;
            $module_section->id = $section->id;
            $module_section->sequence = $section->sequence.','.$mod->id;
            $DB->update_record('course_modules', array('id' => $mod->id, 'section'=>$section->id));
            
            $DB->update_record('course_sections',$module_section);
        }
        //Update comity information with forum mod id
        $committee = new stdClass;
        $committee->id = $committee_id;
        $committee->wiki =$mod->id;
            
        return $committee->wiki;
        break;
   
  case 'questionnaire':

//fill the data to be added
        $new_questionnaire = new stdclass();
        $new_questionnaire->course = $courseid;
        $new_questionnaire->name = $committee_name;
        $new_questionnaire->intro = $committee_name;
        $new_questionnaire->navigate = 1;
        $new_questionnaire->timecreated = time();
        $new_questionnaire->timemodified = time();
        
        $new_questionnaire->id = $DB->insert_record('questionnaire',$new_questionnaire);
        
        $new_questionnaire_survey = new stdClass();
        $new_questionnaire_survey->name = $new_questionnaire->name;
        $new_questionnaire_survey->owner = $courseid;
        $new_questionnaire_survey->realm = 'private';
        $new_questionnaire_survey->status = 0;
        $new_questionnaire_survey->title = $new_questionnaire->name;
        
        $new_questionnaire_survey->id = $DB->insert_record('questionnaire_survey', $new_questionnaire_survey);
        //now update questionnaire sid filed
        $DB->update_record('questionnaire', array('id' => $new_questionnaire->id, 'sid' => $new_questionnaire_survey->id));
        
        //Create actual course module
        $new_course_module = new stdClass;
        $new_course_module->course = $courseid;
        $new_course_module->module = $cmodule->id;
        $new_course_module->instance = $new_questionnaire->id;
        $new_course_module->added = time();
        $new_course_module->visible = 1;
        $new_course_module->visibleold = 1;
       
        $new_course_module->id = $DB->insert_record('course_modules',$new_course_module);
        
        $mod = $DB->get_record('course_modules',array('id'=>$new_course_module->id));
        
        //Create section object
        
        //get section information
        if (!$section = $DB->get_record('course_sections', array('course'=>$mod->course,'section' => 500))){
            //Create new section if it doesn't already exist
            $new_module_section = new stdClass;
            $new_module_section->course = $mod->course;
            $new_module_section->section = 500;
            $new_module_section->sequence = ','.$mod->id;
            $new_module_section->visible = 1;
            $section_id = $DB->insert_record('course_sections', $new_module_section);
            $DB->update_record('course_modules', array('id' => $mod->id, 'section'=>$section_id));
        } else {
            //update section
            $module_section = new stdClass;
            $module_section->id = $section->id;
            $module_section->sequence = $section->sequence.','.$mod->id;
            
            $DB->update_record('course_sections',$module_section);
            $DB->update_record('course_modules', array('id' => $mod->id, 'section'=>$section->id));
        }
        //Update comity information with forum mod id
        $committee = new stdClass;
        $committee->id = $committee_id;
        $committee->questionnaire =$mod->id;
            
        return $committee->questionnaire;
        break;
        
    }
}
?>
