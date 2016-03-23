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

/// Library of functions and constants for module chairman

function chairman_add_instance($chairman) {
/// Given an object containing all the necessary data, 
/// (defined by the form in mod.html) this function 
/// will create a new instance and return the id number 
/// of the new instance.

    global $DB;

    $chairman->name = format_string($chairman->name);
    $chairman->timecreated = time();
    $chairman->timemodified = time();
    $chairman->description = null;
    $chairman->intro = '';
    $chairman->introformat = 0;

    $chairman->id = $DB->insert_record('chairman', $chairman, true);
    
    //Create or delete forum

    if (isset($chairman->use_forum)){
        
        chairman_add_module('forum', $chairman->course, $chairman->name, $chairman->id, 'add');
    } else {
        if ($chairman->forum > 0){
            chairman_add_module('forum', $chairman->course, $chairman->name, $chairman->id, 'del',$chairman->forum);
        }
    }
    //Create or delete wiki
    if (isset($chairman->use_wiki)){
        chairman_add_module('wiki', $chairman->course, $chairman->name, $chairman->id, 'add');
    } else {
        if ($chairman->wiki > 0){
            chairman_add_module('wiki', $chairman->course, $chairman->name, $chairman->id, 'del',$chairman->wiki);
        }
    }
    //Create or delete questionnaire
    if (isset($chairman->use_questionnaire)){
        chairman_add_module('questionnaire', $chairman->course, $chairman->name, $chairman->id, 'add');
    } else {
        if (isset($chairman->questionnaire) && $chairman->questionnaire > 0){
            chairman_add_module('questionnaire', $chairman->course, $chairman->name, $chairman->id, 'del',$chairman->questionnaire);
        }
    }
       
        $modcontext = context_module::instance($chairman->coursemodule);
        file_save_draft_area_files($chairman->chairman_logo, $modcontext->id, 'mod_chairman', 'chairman_logo',
                   0, array('subdirs' => 0, 'maxfiles' => 1, 'accepted_types'=>array('image')));
    
    chairman_update_menu_state($chairman);
    
    return $chairman->id;
}

/**
 * 
 * Traverses all the menu states(expanded or collapsed) from a chairman mod_form
 * data object, and inserts or updates the table as required.
 * 
 * @global moodle_database $DB
 * @param type $chairman
 */
function chairman_update_menu_state($chairman)
{
    global $DB;
    
    $prepend = 'col_menu_';
    $cm = get_coursemodule_from_instance('chairman', $chairman->id);
    $chairman_properties =  (array) $chairman;

    foreach ($chairman_properties as $identifier => $value) {
        if(strpos($identifier, $prepend) !== 0) continue;
        
        $clean_page_code = str_replace($prepend, '', $identifier);

        $select = "chairman_id = ? and ".$DB->sql_compare_text('page_code')." = ?";
        $record = $DB->get_record_select('chairman_menu_state', $select, array($chairman->id,$clean_page_code));
        
        if(!$record)
        {
           $newrecord = new stdClass();
           $newrecord->page_code = $clean_page_code;
           $newrecord->chairman_id = $chairman->id;
           $newrecord->state = $value;
           $DB->insert_record('chairman_menu_state', $newrecord);
        }
          else
        {
           $record->state = $value;
           $DB->update_record('chairman_menu_state', $record);
        }
        
    } 
}



function chairman_update_instance($chairman, $mform=null) {
/// Given an object containing all the necessary data, 
/// (defined by the form in mod_form.php) this function
/// will update an existing instance with new data.

    global $DB;
    if (!isset($chairman->secured)){
        $chairman->secured = 0;
    }
    $chairman->id = $chairman->instance;
    $chairman->timemodified = time();
    $chairman->name = format_string($chairman->name);
    $chairman->description = null;
    
    //Create or delete forum

    if (isset($chairman->use_forum)){
        if ($chairman->forum == 0) {
            $mod_id = chairman_update_module('forum', $chairman->course, $chairman->name, $chairman->id, 'add');
            $chairman->forum = $mod_id;
            $DB->update_record('chairman', array('id'=>$chairman->id, 'forum'=>$chairman->forum));
        }
    } else {
        if ($chairman->forum > 0){
            chairman_add_module('forum', $chairman->course, $chairman->name, $chairman->id, 'del',$chairman->forum);
        }
        $chairman->use_forum = 0;
    }
    //Create or delete wiki
    if (isset($chairman->use_wiki)){
        if ($chairman->wiki == 0) {
            $mod_id = chairman_update_module('wiki', $chairman->course, $chairman->name, $chairman->id, 'add');
            $chairman->wiki = $mod_id;
            $DB->update_record('chairman', array('id'=>$chairman->id, 'wiki'=>$chairman->wiki));
        }
    } else {
        if ($chairman->wiki > 0){
            chairman_add_module('wiki', $chairman->course, $chairman->name, $chairman->id, 'del',$chairman->wiki);
        }
        $chairman->use_wiki = 0;
    }
    //Create or delete questionnaire
    if (isset($chairman->use_questionnaire)){
        if ($chairman->questionnaire == 0) {
            $mod_id = chairman_update_module('questionnaire', $chairman->course, $chairman->name, $chairman->id, 'add');
            $chairman->questionnaire = $mod_id;
            $DB->update_record('chairman', array('id'=>$chairman->id, 'wiki'=>$chairman->wiki));
        }
    } else {
        if (isset($chairman->questionnaire) && $chairman->questionnaire > 0){
            chairman_add_module('questionnaire', $chairman->course, $chairman->name, $chairman->id, 'del',$chairman->questionnaire);
        }
        $chairman->use_questionnaire = 0;
    }
    
        $cm = get_coursemodule_from_instance('chairman', $chairman->id);
        $modcontext = context_module::instance($cm->id);
    
        file_save_draft_area_files($chairman->chairman_logo, $modcontext->id, 'mod_chairman', 'chairman_logo',
                   0, array('subdirs' => 0, 'maxfiles' => 1, 'accepted_types'=>array('image')));
    
    chairman_update_menu_state($chairman);

    return $DB->update_record("chairman", $chairman);
}


function chairman_delete_instance($id) {
/// Given an ID of an instance of this module, 
/// this function will permanently delete the instance 
/// and any data that depends on it.  

    global $DB;

    if (! $chairman = $DB->get_record("chairman", array("id"=>$id))) {
        return false;
    }

    $result = true;

    if (! $DB->delete_records("chairman", array("id"=>$chairman->id))) {
        $result = false;
    }

    return $result;
}

function chairman_get_participants($chairmanid) {
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
function chairman_get_coursemodule_info($coursemodule) {
    global $DB;

    if ($chairman = $DB->get_record('chairman', array('id'=>$coursemodule->instance))) {
        $info = new object();
        $info->name = $chairman->name;
        return $info;
    } else {
        return null;
    }
}

function chairman_get_view_actions() {
    return array();
}

function chairman_get_post_actions() {
    return array();
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function chairman_reset_userdata($data) {
    return array();
}

/**
 * Returns all other caps used in module
 */
function chairman_get_extra_capabilities() {
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
function chairman_supports($feature) {
    switch($feature) {
        case FEATURE_IDNUMBER:                return false;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_GROUPMEMBERSONLY:        return false;
        case FEATURE_MOD_INTRO:               return false;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return false;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_MOD_ARCHETYPE:           return MOD_CLASS_ACTIVITY;
        case FEATURE_BACKUP_MOODLE2:          return true;

        default: return null;
    }
}

/**
 * Serves the chairman images or files. Implements needed access control ;-)
 *
 * @global stdClass $CFG
 * @global moodle_database $DB
 * @param object $course
 * @param object $cm
 * @param object $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @return bool false if file not found, does not return if found - justsend the file
 */
function chairman_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    global $CFG, $DB, $USER;

    //TCPDF needs images to be avaliable anonymously (uses a curl connection)
    //Therefore for these two areas we don't care about the user being logged in.
    if ($filearea === "chairman_logo" || $filearea === "chairman_logo_default") {

        $chairmancontentid = (int) array_shift($args);
        //Now gather file information
        $fs = get_file_storage();
        $relativepath = implode('/', $args);
        $fullpath = "/$context->id/mod_chairman/$filearea/$chairmancontentid/$relativepath";

        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            return false;
        }

        send_stored_file($file, 0, 0, $forcedownload);
    }

    //The following code is for security
    require_course_login($course, true, $cm);

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    $fileareas = array('chairman', 'attachment', "chairman_private", "chairman_logo", "chairman_logo_default");
    if (!in_array($filearea, $fileareas)) {
        return false;
    }
    //id of the content row
    $chairmancontentid = (int) array_shift($args);

    if (!$tab = $DB->get_record('chairman', array('id' => $cm->instance))) {
        return false;
    }

    //Now gather file information
    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/mod_chairman/$filearea/$chairmancontentid/$relativepath";

    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

    if ($filearea === "chairman_private") {
        $cuser = $DB->get_record('chairman_members', array('user_id' => $USER->id, 'chairman_id' => $cm->id));

        if (!$cuser) {
            return false;
        }
    }

    // finally send the file
    send_stored_file($file, 0, 0, $forcedownload);
}

function chairman_cron(){
    global $CFG, $DB;
    //Get all events
    $events = $DB->get_records('chairman_events');
        
    foreach($events as $event){
        //check for event coming up in a week
        $week = (60*60*24)*7; //60 seconds * 60 minutes * 24 hours *7 days
        //get committee president for sending email
        $president = $DB->get_record('chairman_members', array('chairman_id' => $event->chairman_id, 'role_id' => 1));
        //Add condition if the president is not defined
        if($president === false)
        {
            $copresident = $DB->get_record('chairman_members', array('chairman_id' => $event->chairman_id, 'role_id' => 2));
            if($copresident === false)
            {
                $adminchairman = $DB->get_record('chairman_members', array('chairman_id' => $event->chairman_id, 'role_id' => 4));
                if($copresident === false)
                {
                    $from = $DB->get_record('user', array('id' => 2));
                }
                else
                {
                    $from = $DB->get_record('user', array('id' => $adminchairman->user_id));
                }
            }
            else
            {
                $from = $DB->get_record('user', array('id' => $copresident->user_id));
            }
        }
        else
        {
            //email information
            $from = $DB->get_record('user', array('id' => $president->user_id));
        }
        
        $subject = get_string('notify_reminder', 'chairman').$event->summary;
        $emailmessage = get_string('notify_week_message', 'chairman').'<p>'.$event->description.'</p>';
        //First get course module to retrieve instance id (The actual committee id)
        $cm = $DB->get_record('course_modules',array('id'=>$event->chairman_id));
        if($cm == false)
        {
            continue;
        }
        //get chairman information
        $chairman = $DB->get_record('chairman',array('id' => $cm->instance));
        
        if ($event->notify_week == 1){
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
                echo " Weekly email ready to be sent - {$event->summary}\n";
                foreach ($members as $member){
                    $user = $DB->get_record('user',array('id' => $member->user_id));
                    $message[$i] = str_replace('{a}', "$user->firstname", $emailmessage);
                    $message[$i] = str_replace('{c}', "$chairman->name", $message[$i]);
                    $message[$i] = str_replace('{b}', "$event->day/$event->month/$event->year", $message[$i]);
                    //echo "$subject<br>$message[$i]<br>";
                    email_to_user($user, $from, $subject, strip_tags($message[$i]), $message[$i]);
                    echo " - Weekly email sent to {$user->email}\n";
                    $i++;
                    
                }
                //update sent notification
                $chairman_event = new object();
                $chairman_event->id = $event->id;
                $chairman_event->notify_week_sent = 1;
                //enter info into DB
                $update_event = $DB->update_record('chairman_events', $chairman_event);
            } else {
                echo "No Week email sent - {$event->summary}\n";
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
                echo " Daily email ready to be sent - {$event->summary}\n";
                foreach ($members as $member){
                    $user = $DB->get_record('user',array('id' => $member->user_id));
                    
                    $message[$i] = str_replace('{a}', "$user->firstname", $emailmessage);
                    $message[$i] = str_replace('{c}', "$chairman->name", $message[$i]);
                    $message[$i] = str_replace('{b}', "$event->day/$event->month/$event->year", $message[$i]);
                    email_to_user($user, $from, $subject, strip_tags($message[$i]), $message[$i]);
                    echo " - Daily email sent to {$user->email}\n";
                    $i++;
                    
                }
                //update sent notification
                $chairman_event = new object();
                $chairman_event->id = $event->id;
                $chairman_event->notify_sent = 1;
                //enter info into DB
                $update_event = $DB->update_record('chairman_events', $chairman_event);
            } else {
                echo "No Day email sent - {$event->summary}\n";
            }    
        }
    }
}
//This function adds either a wiki or forum module
// to the course in section 500
function chairman_add_module($type, $courseid, $committee_name, $committee_id, $action, $mod_id = null) {
    global $DB, $CFG, $USER;
    
    
    //include("$CFG->wwwroot/course/lib.php");
    if(!$cmodule = $DB->get_record('modules', array('name' => $type))){
        return null;
    }
    
    if ($action == 'del') {
        
        //do nothing for now
        
        
    } else {


//if no chairman make a chairman
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
        //Update chairman information with forum mod id
        $committee = new stdClass;
        $committee->id = $committee_id;
        $committee->forum =$mod->id;
        
        $DB->update_record('chairman',$committee);
         

        
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
        //Update chairman information with forum mod id
        $committee = new stdClass;
        $committee->id = $committee_id;
        $committee->wiki =$mod->id;
            
        $DB->update_record('chairman',$committee); 
        
        
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
        //Update chairman information with forum mod id
        $committee = new stdClass;
        $committee->id = $committee_id;
        $committee->questionnaire =$mod->id;
            
        $DB->update_record('chairman',$committee); 
        
        
    }
}
}
//This function adds either a wiki or forum module
// to the course in section 500
function chairman_update_module($type, $courseid, $committee_name, $committee_id, $action, $mod_id = null) {
    global $DB, $CFG, $USER;
    
    
    //include("$CFG->wwwroot/course/lib.php");
    if(!$cmodule = $DB->get_record('modules', array('name' => $type))){
        return null;
    }
   


//if no chairman make a chairman
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
        //Update chairman information with forum mod id
        $committee = new stdClass;
        $committee->id = $committee_id;
        $committee->forum =$mod->id;
        
        return $committee->forum;// $DB->update_record('chairman',$committee);
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
        //Update chairman information with forum mod id
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
        //Update chairman information with forum mod id
        $committee = new stdClass;
        $committee->id = $committee_id;
        $committee->questionnaire =$mod->id;
            
        return $committee->questionnaire;
        break;
        
    }
    
    
}
?>
