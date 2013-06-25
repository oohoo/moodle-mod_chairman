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
 * The class represents a structure step that is required to restore an chairman module.
 */
class restore_chairman_activity_structure_step extends restore_activity_structure_step {

    /**
     * Defines the restore structure for the chairman module
     * 
     */
    protected function define_structure() {

        $paths = array();

        $base = '/activity'; //base mapping for activity
        $chairman_base = "$base/chairman"; //base mapping for chairman activity
        //level 2 mappings
        $chairman_member = "$chairman_base/chairman_members/chairman_member";
        $chairman_planner = "$chairman_base/chairman_planners/chairman_planner";
        $chairman_file = "$chairman_base/chairman_files/chairman_file";
        $chairman_events = "$chairman_base/chairman_events/chairman_event";
        $chairman_links = "$chairman_base/chairman_links/chairman_link";
        $chairman_menu = "$chairman_base/chairman_menus/chairman_menu";

        //level 3 mapping
        $chairman_agenda = "$chairman_events/chairman_agendas/chairman_agenda";
        $chairman_planner_date = "$chairman_planner/chairman_planner_dates/chairman_planner_date";
        $chairman_planner_user = "$chairman_planner/chairman_planner_users/chairman_planner_user";
        $chairman_agenda_topic = "$chairman_agenda/chairman_agenda_topics/chairman_agenda_topic";

        //level 4 mapping
        $chairman_agenda_member = "$chairman_agenda/chairman_agenda_members/chairman_agenda_member";
        $chairman_agenda_guest = "$chairman_agenda/chairman_agenda_guests/chairman_agenda_guest";
        $chairman_planner_response = "$chairman_planner_date/chairman_planner_responses/chairman_planner_response";

        //level 5 mapping
        $chairman_agenda_motion = "$chairman_agenda_topic/chairman_agenda_motions/chairman_agenda_motion";
        $chairman_agenda_attendance = "$chairman_agenda_member/chairman_agenda_attendances/chairman_agenda_attendance";

        $paths[] = new restore_path_element('chairman', $chairman_base);
        $paths[] = new restore_path_element('chairman_member', $chairman_member);
        $paths[] = new restore_path_element('chairman_planner', $chairman_planner);
        $paths[] = new restore_path_element('chairman_file', $chairman_file);
        $paths[] = new restore_path_element('chairman_event', $chairman_events);
        $paths[] = new restore_path_element('chairman_link', $chairman_links);
        $paths[] = new restore_path_element('chairman_menu', $chairman_menu);
        $paths[] = new restore_path_element('chairman_agenda', $chairman_agenda);
        $paths[] = new restore_path_element('chairman_planner_date', $chairman_planner_date);
        $paths[] = new restore_path_element('chairman_planner_user', $chairman_planner_user);
        $paths[] = new restore_path_element('chairman_agenda_topic', $chairman_agenda_topic);
        $paths[] = new restore_path_element('chairman_agenda_member', $chairman_agenda_member);
        $paths[] = new restore_path_element('chairman_agenda_guest', $chairman_agenda_guest);
        $paths[] = new restore_path_element('chairman_planner_response', $chairman_planner_response);
        $paths[] = new restore_path_element('chairman_agenda_motion', $chairman_agenda_motion);
        $paths[] = new restore_path_element('chairman_agenda_attendance', $chairman_agenda_attendance);
        
        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Executes after the completion of the rest of the backup.
     *  -Restores all backed up files for chairman
     * 
     */
    protected function after_execute() {
        // Add choice related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_chairman', 'attachement', 'filename');
        $this->add_related_files('mod_chairman', 'chairman', null);
        $this->add_related_files('mod_chairman', 'chairman_private', null);
        $this->add_related_files('mod_chairman', 'chairman_logo', null);
    }

    /**
     * Processes all instances of chairman present in the backup,
     * and inserts them into the appropriate chairman table in moodle.
     * 
     * @global moodle_database $DB
     * @param Object $data
     */
    protected function process_chairman($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // insert the choice record
        $newitemid = $DB->insert_record('chairman', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
        $this->set_mapping('chairman', $oldid, $newitemid);
    }

    /**
     * Processes all instances of chairman menu state present in the backup,
     * and inserts them into the appropriate chairman table in moodle.
     * 
     * @global moodle_database $DB
     * @param Object $data
     */
    protected function process_chairman_menu($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        
        $data->chairman_id = $this->get_new_parentid('chairman');

        $newitemid = $DB->insert_record('chairman_menu_state', $data);
        $this->set_mapping('chairman_menu_state', $oldid, $newitemid);
    }
    
    /**
     * Processes all instances of chairman members present in the backup,
     * and inserts them into the appropriate chairman table in moodle.
     * 
     * @global moodle_database $DB
     * @param Object $data
     */
    protected function process_chairman_member($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        
        $data->chairman_id = $this->get_course_module_id("chairman_id", $data);
        $data->user_id = $this->get_mappingid("user", $data->user_id);

        $newitemid = $DB->insert_record('chairman_members', $data);
        $this->set_mapping('chairman_member', $oldid, $newitemid);
    }

    /**
     * Processes all instances of chairman planner present in the backup,
     * and inserts them into the appropriate chairman table in moodle.
     * 
     * @global moodle_database $DB
     * @param Object $data
     */
    protected function process_chairman_planner($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $data->chairman_id = $this->get_course_module_id("chairman_id", $data);

        $newitemid = $DB->insert_record('chairman_planner', $data);

        $this->set_mapping('chairman_planner', $oldid, $newitemid);
    }

    /**
     * Processes all instances of chairman files present in the backup,
     * and inserts them into the appropriate chairman table in moodle.
     * 
     * @global moodle_database $DB
     * @param Object $data
     */
    protected function process_chairman_file($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $data->chairman_id = $this->get_course_module_id("chairman_id", $data);
        $data->user_id = $this->get_mappingid("user", $data->user_id);

        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $data->parent = $this->get_mappingid("chairman_file", $data->parent);

        // insert the choice record
        $newitemid = $DB->insert_record('chairman_files', $data);

        $this->set_mapping('chairman_file', $oldid, $newitemid);
    }

    /**
     * Processes all instances of chairman events present in the backup,
     * and inserts them into the appropriate chairman table in moodle.
     * 
     * @global moodle_database $DB
     * @param Object $data
     */
    protected function process_chairman_event($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $data->chairman_id = $this->get_course_module_id("chairman_id", $data);
        $data->user_id = $this->get_mappingid('user', $data->user_id);

        // insert the choice record
        $newitemid = $DB->insert_record('chairman_events', $data);

        $this->set_mapping('chairman_event', $oldid, $newitemid);
    }
    
        /**
     * Processes all instances of chairman links present in the backup,
     * and inserts them into the appropriate chairman table in moodle.
     * 
     * @global moodle_database $DB
     * @param Object $data
     */
    protected function process_chairman_link($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $data->chairman_id = $this->get_course_module_id("chairman_id", $data);

        // insert the choice record
        $newitemid = $DB->insert_record('chairman_links', $data);

        $this->set_mapping('chairman_link', $oldid, $newitemid);
    }

    /**
     * Processes all instances of chairman agendas present in the backup,
     * and inserts them into the appropriate chairman table in moodle.
     * 
     * @global moodle_database $DB
     * @param Object $data
     */
    protected function process_chairman_agenda($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $data->chairman_id = $this->get_course_module_id("chairman_id", $data);
        $data->chairman_events_id = $this->get_new_parentid('chairman_event');

        // insert the choice record
        $newitemid = $DB->insert_record('chairman_agenda', $data);

        $this->set_mapping('chairman_agenda', $oldid, $newitemid);
    }

    /**
     * Processes all instances of chairman planner dates present in the backup,
     * and inserts them into the appropriate chairman table in moodle.
     * 
     * @global moodle_database $DB
     * @param Object $data
     */
    protected function process_chairman_planner_date($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->planner_id = $this->get_new_parentid('chairman_planner');
        $data->from_time = $this->apply_date_offset($data->from_time);
        $data->to_time = $this->apply_date_offset($data->to_time);


        // insert the choice record
        $newitemid = $DB->insert_record('chairman_planner_dates', $data);

        $this->set_mapping('chairman_planner_date', $oldid, $newitemid);
    }

    /**
     * Processes all instances of chairman planner users present in the backup,
     * and inserts them into the appropriate chairman table in moodle.
     * 
     * @global moodle_database $DB
     * @param Object $data
     */
    protected function process_chairman_planner_user($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $data->planner_id = $this->get_new_parentid('chairman_planner');
        $data->chairman_member_id = $this->get_mappingid('chairman_member', $data->chairman_member_id);

        // insert the choice record
        $newitemid = $DB->insert_record('chairman_planner_users', $data);

        $this->set_mapping('chairman_planner_user', $oldid, $newitemid);
    }

    /**
     * Processes all instances of chairman agenda topics present in the backup,
     * and inserts them into the appropriate chairman table in moodle.
     * 
     * @global moodle_database $DB
     * @param Object $data
     */
    protected function process_chairman_agenda_topic($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $data->chairman_agenda = $this->get_new_parentid('chairman_agenda');

        $data->modifiedby = $this->get_mappingid('user', $data->modifiedby);

        $data->presentedby = $this->get_mappingid('chairman_agenda_member', $data->presentedby, null);
        
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // insert the choice record
        $newitemid = $DB->insert_record('chairman_agenda_topics', $data);

        $this->set_mapping('chairman_agenda_topic', $oldid, $newitemid);
    }

    /**
     * Processes all instances of chairman agenda members present in the backup,
     * and inserts them into the appropriate chairman table in moodle.
     * 
     * @global moodle_database $DB
     * @param Object $data
     */
    protected function process_chairman_agenda_member($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        //Should be valid as it is always this
        $data->chairman_id = $this->get_course_module_id("chairman_id", $data);

        $data->user_id = $this->get_mappingid('user', $data->user_id);
        $data->agenda_id = $this->get_new_parentid('chairman_agenda');

        // insert the choice record
        $newitemid = $DB->insert_record('chairman_agenda_members', $data);

        $this->set_mapping('chairman_agenda_member', $oldid, $newitemid);
    }

    /**
     * Processes all instances of chairman agenda guests present in the backup,
     * and inserts them into the appropriate chairman table in moodle.
     * 
     * @global moodle_database $DB
     * @param Object $data
     */
    protected function process_chairman_agenda_guest($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $data->chairman_agenda = $this->get_new_parentid('chairman_agenda');
        $data->moodleid = $this->get_mappingid('user', $data->moodleid, null);

        // insert the choice record
        $newitemid = $DB->insert_record('chairman_agenda_guests', $data);

        $this->set_mapping('chairman_agenda_guest', $oldid, $newitemid);
    }

    /**
     * Processes all instances of chairman planner responses present in the backup,
     * and inserts them into the appropriate chairman table in moodle.
     * 
     * @global moodle_database $DB
     * @param Object $data
     */
    protected function process_chairman_planner_response($data) {
        global $DB;
  
        $data = (object) $data;
        $oldid = $data->id;
        
        $data->planner_date_id = $this->get_new_parentid('chairman_planner_date');
        $data->planner_user_id = $this->get_mappingid("chairman_planner_user", $data->planner_user_id);
        
        // insert the choice record
        $newitemid = $DB->insert_record('chairman_planner_response', $data);

        $this->set_mapping('chairman_planner_response', $oldid, $newitemid);
    }

    /**
     * Processes all instances of chairman agenda motions present in the backup,
     * and inserts them into the appropriate chairman table in moodle.
     * 
     * @global moodle_database $DB
     * @param Object $data
     */
    protected function process_chairman_agenda_motion($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $data->chairman_agenda = $this->get_new_parentid('chairman_agenda');
        $data->chairman_agenda_topics = $this->get_new_parentid('chairman_agenda_topic');

        $data->motionby = $this->get_mappingid("chairman_agenda_member", $data->motionby, null);
        $data->secondedby = $this->get_mappingid("chairman_agenda_member", $data->secondedby, null);
        $data->timemodified = $this->apply_date_offset($data->timemodified);


        // insert the choice record
        $newitemid = $DB->insert_record('chairman_agenda_motions', $data);

        $this->set_mapping('chairman_agenda_motion', $oldid, $newitemid);
    }

    /**
     * Processes all instances of chairman agenda attendance present in the backup,
     * and inserts them into the appropriate chairman table in moodle.
     * 
     * @global moodle_database $DB
     * @param Object $data
     */
    protected function process_chairman_agenda_attendance($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $data->chairman_agenda = $this->get_new_parentid('chairman_agenda');
        $data->chairman_members = $this->get_new_parentid('chairman_agenda_member');

        $newitemid = $DB->insert_record('chairman_agenda_attendance', $data);

        $this->set_mapping('chairman_agenda_attendance', $oldid, $newitemid);
    }

    /**
     * This method takes in a data object that represents the new data entry to
     * be added into moodle. This data object must contain an old course module id
     * as an attribute contained at $data->$cm_identifier.
     * 
     * A check is made to see if a new course module is present, which will be
     * returned. If there is no new course module present the one originally present
     * in the $data object is returned.
     * 
     * @global String $cm_identifier
     * @param Object $data
     */
    private function get_course_module_id($cm_identifier, $data) {
        //Should be valid as it is always this
        $new_cm = $this->get_mappingid('course_module', $data->$cm_identifier);

        /*
         * Making assumption that if the course module isn't included then only
         * the module has been backed up and the old CM is still valid 
         */
        if (!$new_cm)
            return $data->$cm_identifier;

        return $new_cm;
    }

}

?>
