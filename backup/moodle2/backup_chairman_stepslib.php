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

/**
 * Define the complete chairman structure for backup, with file and id annotations
 */     
class backup_chairman_activity_structure_step extends backup_activity_structure_step {
 
    protected function define_structure() {
 
        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');
 
        // Define each element separated
        $chairman = $this->chairman_table_element();
        
        /*
         * Generate all table elements, and their intermediate tree elements
         * the first element returned by each function is the db tree element(singular element), 
         * the second element is the intermediate tree element(plural element)
         */
        list($member, $members) = $this->chairman_members_table_element();
        
        list($planner, $planners) = $this->chairman_planner_table_element();
        list($file,$files) = $this->chairman_files_table_element();
        list($event,$events) = $this->chairman_events_table_element();
        list($link,$links) = $this->chairman_links_table_element();
        list($menu,$menus) = $this->chairman_menu_table_element();
        
        
        list($agenda,$agendas) = $this->chairman_agenda_table_element();
        list($pl_date,$pl_dates) = $this->chairman_planner_dates_table_element();
        list($pl_user,$pl_users) = $this->chairman_planner_users_table_element();
        
        list($ag_member, $ag_members) = $this->chairman_agenda_members_table_element();
        list($ag_topic, $ag_topics) = $this->chairman_agenda_topics_table_element();
        list($ag_guest,$ag_guests) = $this->chairman_agenda_guests_table_element();
        list($pl_response,$pl_responses) = $this->chairman_planner_response_table_element();
        
        list($ag_motion,$ag_motions) = $this->chairman_agenda_motions_table_element();
        list($ag_atten,$ag_attens) = $this->chairman_agenda_attendence_table_element();
         
        /*
         * Build Tree Structure
         * Note: A database diagram of the structure is avaliable in the documentation
         */
        
        //Level 2
        $chairman->add_child($members);
        $members->add_child($member);
       
        $chairman->add_child($planners);
        $planners->add_child($planner);
        
        $chairman->add_child($files);
        $files->add_child($file);
        
        $chairman->add_child($events);
        $events->add_child($event);
        
        $chairman->add_child($links);
        $links->add_child($link);
        
        $chairman->add_child($menus);
        $menus->add_child($menu);
        
        
        
        //Level 3
        $planner->add_child($pl_users);
        $pl_users->add_child($pl_user);
        
        $event->add_child($agendas);
        $agendas->add_child($agenda);
        
        $planner->add_child($pl_dates);
        $pl_dates->add_child($pl_date);
        
        
        //Level 4
        $agenda->add_child($ag_members);
        $ag_members->add_child($ag_member);
        
        $agenda->add_child($ag_guests);
        $ag_guests->add_child($ag_guest);
        
        $agenda->add_child($ag_topics);
        $ag_topics->add_child($ag_topic);
        
        $pl_date->add_child($pl_responses);
        $pl_responses->add_child($pl_response);
        
        
        //Level 5
        $ag_topic->add_child($ag_motions);
        $ag_motions->add_child($ag_motion);
        
        $ag_member->add_child($ag_attens);
        $ag_attens->add_child($ag_atten);
        
        /*
         *  Configure Source, annotations, and file annotations
         */
        $this->chairman_table_config($chairman);
        
        $this->chairman_members_table_config($member);
        $this->chairman_planner_table_config($planner);
        $this->chairman_files_table_config($file);
        $this->chairman_events_table_config($event);
        $this->chairman_links_table_config($link);
        $this->chairman_menus_table_config($menu);
        
        $this->chairman_agenda_table_config($agenda);
        $this->chairman_planner_dates_table_config($pl_date);
        $this->chairman_planner_users_table_config($pl_user);
        
        $this->chairman_agenda_topics_table_config($ag_topic);
        $this->chairman_agenda_members_table_config($ag_member);
        $this->chairman_agenda_guests_table_config($ag_guest);
        $this->chairman_planner_response_table_config($pl_response);
        
        $this->chairman_agenda_motions_table_config($ag_motion);
        $this->chairman_agenda_attendence_table_config($ag_atten);
        
        /*
         *  Return the root element (chairman), wrapped into standard activity structure
         */
        return $this->prepare_activity_structure($chairman);
    }
    
    
    /**
     * Generates the backup_nested_element for the chairman table
     * 
     * @return backup_nested_element Chairman Table
     */
    private function chairman_table_element()
    {
        $chairman = new backup_nested_element('chairman', array('id'), array(
            'name', 'timecreated', 'timemodified', 'description',
            'intro', 'introformat', 'secured', 'forum',
            'wiki', 'use_forum', 'use_wiki', 'use_questionnaire',
            'questionnaire', 'bbb', 'use_bbb', 'start_month_of_year'));
        
        return $chairman;
    }
    
    /**
     * Generates the backup_nested_element for the chairman members table
     * 
     * @return backup_nested_element Chairman Members Table
     */
    private function chairman_members_table_element()
    {
        $chairman_members = new backup_nested_element('chairman_members');
        $chairman_member = new backup_nested_element('chairman_member', array('id'), array(
            'user_id', 'role_id', 'chairman_id'));
        
        return array($chairman_member, $chairman_members);
    }
    
        /**
     * Generates the backup_nested_element for the chairman menu table
     * 
     * @return backup_nested_element Chairman Menu Table
     */
    private function chairman_menu_table_element()
    {
        $chairman_menus = new backup_nested_element('chairman_menus');
        $chairman_menu = new backup_nested_element('chairman_menu', array('id'), array(
            'chairman_id', 'page_code', 'state'));
        
        return array($chairman_menu, $chairman_menus);
    }
    
     /**
     * Generates the backup_nested_element for the chairman planner table
     * 
     * @return backup_nested_element Chairman Planner Table
     */
    private function chairman_planner_table_element()
    {
        $chairman_planners = new backup_nested_element('chairman_planners');
        $chairman_planner = new backup_nested_element('chairman_planner', array('id'), array(
            'active', 'name', 'timezone', 'description', 'chairman_id'));
        
        return array($chairman_planner,$chairman_planners);
    }
    
     /**
     * Generates the backup_nested_element for the chairman planner table
     * 
     * @return backup_nested_element Chairman Files Table
     */
    private function chairman_files_table_element()
    {
        $chairman_files = new backup_nested_element('chairman_files');
        $chairman_file = new backup_nested_element('chairman_file', array('id'), array(
            'private', 'user_id', 'timemodified', 'name', 'parent', 'type', 'chairman_id'));
        
        return array($chairman_file,$chairman_files);
    }
    
    /**
     * Generates the backup_nested_element for the chairman events table
     * 
     * @return backup_nested_element Chairman Events Table
     */
    private function chairman_events_table_element()
    {
        $chairman_events = new backup_nested_element('chairman_events');
        $chairman_event = new backup_nested_element('chairman_event', array('id'), array(
            'user_id', 'day', 'month', 'year', 'starthour', 'startminutes', 'endhour', 'endminutes',
            'summary', 'description', 'stamp_start', 'stamp_end', 'stamp_t_start', 'stamp_t_end',
            'room_reservation_id','notify','notify_week', 'notify_sent', 'notify_week_sent', 'timezone', 
            'chairman_id'));
        
        return array($chairman_event, $chairman_events);
    }
    
    private function chairman_links_table_element()
    {
       $chairman_links = new backup_nested_element('chairman_links');
        $chairman_link = new backup_nested_element('chairman_link', array('id'), array(
            'name', 'link','chairman_id'));
        
        return array($chairman_link, $chairman_links);
    }
    
     /**
     * Generates the backup_nested_element for the chairman links table
     * 
     * @return backup_nested_element Chairman Links Table
     */
    private function chairman_agenda_table_element()
    {
        $chairman_agendas = new backup_nested_element('chairman_agendas');
        $chairman_agenda = new backup_nested_element('chairman_agenda', array('id'), array(
            'location', 'chairman_id', 'message', 'footer'));
        
        return array($chairman_agenda, $chairman_agendas);
    }
    
     /**
     * Generates the backup_nested_element for the chairman planner dates table
     * 
     * @return backup_nested_element Chairman Planner Dates Table
     */
    private function chairman_planner_dates_table_element()
    {
        $chairman_dates = new backup_nested_element('chairman_planner_dates');
        $chairman_date = new backup_nested_element('chairman_planner_date', array('id'), array(
            'from_time', 'to_time'));
        
        return array($chairman_date,$chairman_dates);
    }
    
         /**
     * Generates the backup_nested_element for the chairman planner dates table
     * 
     * @return backup_nested_element Chairman Planner Dates Table
     */
    private function chairman_planner_users_table_element()
    {
        $chairman_users = new backup_nested_element('chairman_planner_users');
        $chairman_user = new backup_nested_element('chairman_planner_user', array('id'), array(
            'chairman_member_id','rule'));
        
        return array($chairman_user,$chairman_users);
    }
    
    /**
     * Generates the backup_nested_element for the chairman agenda topics table
     * 
     * @return backup_nested_element Chairman Agenda Topics Table
     */
    private function chairman_agenda_topics_table_element()
    {
        $chairman_topics = new backup_nested_element('chairman_agenda_topics');
        $chairman_topic = new backup_nested_element('chairman_agenda_topic', array('id'), array(
            'title', 'description', 'duration', 'notes', 'filename', 'follow_up', 'status','hidden',
            'modifiedby', 'timemodified', 'timecreated', 'topic_order', 'topic_header', 'presentedby', 'presentedby_text'));
        
        return array($chairman_topic,$chairman_topics);
    }
    
    /**
     * Generates the backup_nested_element for the chairman agenda members table
     * 
     * @return backup_nested_element Chairman Agenda Members Table
     */
    private function chairman_agenda_members_table_element()
    {
        $chairman_members = new backup_nested_element('chairman_agenda_members');
        $chairman_member = new backup_nested_element('chairman_agenda_member', array('id'), array(
            'user_id', 'role_id', 'chairman_id'));
        
        return array($chairman_member,$chairman_members);
    }
    
    /**
     * Generates the backup_nested_element for the chairman agenda guests table
     * 
     * @return backup_nested_element Chairman Agenda Guests Table
     */
    private function chairman_agenda_guests_table_element()
    {
        $chairman_guests = new backup_nested_element('chairman_agenda_guests');
        $chairman_guest = new backup_nested_element('chairman_agenda_guest', array('id'), array(
            'planner_date_id','firstname', 'lastname', 'email', 'moodleid'));
        
        return array($chairman_guest,$chairman_guests);
    }
    
    
    /**
     * Generates the backup_nested_element for the chairman planner response table
     * 
     * @return backup_nested_element Chairman Planner Response Table
     */
    private function chairman_planner_response_table_element()
    {
        $chairman_responses = new backup_nested_element('chairman_planner_responses');
        $chairman_response = new backup_nested_element('chairman_planner_response', array('id'), array(
            'planner_user_id','response'));
        
        return array($chairman_response,$chairman_responses);
    }
    
    
    /**
     * Generates the backup_nested_element for the chairman agenda motions table
     * 
     * @return backup_nested_element Chairman Agenda Motions Table
     */
    private function chairman_agenda_motions_table_element()
    {
        $chairman_motions = new backup_nested_element('chairman_agenda_motions');
        $chairman_motion = new backup_nested_element('chairman_agenda_motion', array('id'), array(
            'motion','motionby','secondedby','carried','unanimous','yea','nay','abstained','timemodified'));
        
        return array($chairman_motion,$chairman_motions);
    }
    
        /**
     * Generates the backup_nested_element for the chairman agenda attendance table
     * 
     * @return backup_nested_element Chairman Agenda Attendance Table
     */
    private function chairman_agenda_attendence_table_element()
    {
        $chairman_attendences = new backup_nested_element('chairman_agenda_attendances');
        $chairman_attendence = new backup_nested_element('chairman_agenda_attendance', array('id'), array(
            'absent','unexcused_absence','notes'));
        
        return array($chairman_attendence, $chairman_attendences);
    }
    
    /**
     * Sets the source for the chairman table
     */
    private function chairman_table_config($table)
    {
        $table->set_source_table('chairman', array('id' => backup::VAR_ACTIVITYID));
        $table->annotate_files('mod_chairman', 'chairman', null);
        $table->annotate_files('mod_chairman', 'chairman_private', null);
        $table->annotate_files('mod_chairman', 'chairman_logo', null);
    }
    
    /**
     * Sets the source for the chairman members table
     * 
     */
    private function chairman_members_table_config($table)
    {   
        $table->set_source_table('chairman_members', array('chairman_id' => backup::VAR_MODID));   
        $table->annotate_ids('user', 'user_id');
    }
    
   /**
     * Sets the source for the chairman menu table
     * 
     */
    private function chairman_menus_table_config($table)
    {   
        $table->set_source_table('chairman_menu_state', array('chairman_id' => backup::VAR_ACTIVITYID));   
    }
    
    
    
     /**
     * Sets the source for the chairman planner table
     * 
     */
    private function chairman_planner_table_config($table)
    {
        $table->set_source_table('chairman_planner', array('chairman_id' => backup::VAR_MODID));
    }
    
     /**
     * Sets the source for the chairman planner table
     * 
     */
    private function chairman_files_table_config($table)
    {
        $table->set_source_table('chairman_files', array('chairman_id' => backup::VAR_MODID), 'id ASC');
        $table->annotate_ids('user', 'user_id');
    }
    
    /**
     * Generates the backup_nested_element for the chairman events table
     * 
     */
    private function chairman_events_table_config($table)
    {
        $table->set_source_table('chairman_events', array('chairman_id' => backup::VAR_MODID));
        $table->annotate_ids('user', 'user_id');
    }
    
    /**
     * Generates the backup_nested_element for the chairman links table
     * 
     */
    private function chairman_links_table_config($table)
    {
        $table->set_source_table('chairman_links', array('chairman_id' => backup::VAR_MODID));
    }
    
     /**
     * Sets the source for the chairman agenda table
     * 
     */
    private function chairman_agenda_table_config($table)
    {
        $table->set_source_table('chairman_agenda', array('chairman_id' => backup::VAR_MODID,'chairman_events_id' => backup::VAR_PARENTID));
    }
    
     /**
     * Sets the source for the chairman planner dates table
     * 
     */
    private function chairman_planner_dates_table_config($table)
    {
        $table->set_source_table('chairman_planner_dates', array('planner_id' => backup::VAR_PARENTID));
    }
    
    /**
     * Sets the source for the chairman planner dates table
     * 
     */
    private function chairman_planner_users_table_config($table)
    {
        $table->set_source_table('chairman_planner_users', array('planner_id' => backup::VAR_PARENTID));
    }
    
    /**
     * Sets the source for the chairman agenda topics table
     * 
     */
    private function chairman_agenda_topics_table_config($table)
    {
        $table->set_source_table('chairman_agenda_topics', array('chairman_agenda' => backup::VAR_PARENTID));
        $table->annotate_files('mod_chairman', 'attachement', 'filename');
        $table->annotate_ids('user', 'modifiedby');
        
    }
    
    /**
     * Sets the source for the chairman agenda members table
     * 
     */
    private function chairman_agenda_members_table_config($table)
    {
        $table->set_source_table('chairman_agenda_members', array('agenda_id' => backup::VAR_PARENTID, 'chairman_id'=> backup::VAR_MODID));
        $table->annotate_ids('user', 'user_id');
    }
    
    /**
     * Sets the source for the chairman agenda guests table
     * 
     */
    private function chairman_agenda_guests_table_config($table)
    {
        $table->set_source_table('chairman_agenda_guests', array('chairman_agenda' => backup::VAR_PARENTID));
        $table->annotate_ids('user', 'moodleid');
    }
    
    
    /**
     * Sets the source for the chairman planner response table
     * 
     */
    private function chairman_planner_response_table_config($table)
    {
        $table->set_source_table('chairman_planner_response', array('planner_date_id' => backup::VAR_PARENTID));
    }
    
    
    /**
     * Sets the source for the chairman agenda motions table
     * 
     */
    private function chairman_agenda_motions_table_config($table)
    {
        $table->set_source_table('chairman_agenda_motions', array('chairman_agenda_topics' => backup::VAR_PARENTID, 'chairman_agenda' => '../../../../id' ));
    }
    
    /**
     * Sets the source for the chairman agenda attendance table
     * 
     */
    private function chairman_agenda_attendence_table_config($table)
    {
        $table->set_source_table('chairman_agenda_attendance', array('chairman_members' => backup::VAR_PARENTID, 'chairman_agenda' => '../../../../id'));
    } 
}

?>
