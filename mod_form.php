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

require_once ($CFG->dirroot.'/course/moodleform_mod.php');
require_once ($CFG->dirroot.'/mod/chairman/lib_chairman.php');


class mod_chairman_mod_form extends moodleform_mod {

    //update db migrator if changed
    private $collapse_menu_prepend = 'col_menu_';
    private $mform;
    
    function definition() {
		global $CFG, $DB;

        $mform    =& $this->_form;
        $this->mform = $mform;
        
        //chairman name
        $mform->addElement('header','chairman_general',get_string('header_general', 'mod_chairman'));
        $mform->addElement('text', 'name', get_string('name','chairman'), array('size'=>'48'));
        
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        
        /**
         * Collapseable Menu
         */
        $mform->addElement('header','collaps_menu_label',get_string('collaps_menu_label', 'mod_chairman'));
        $mform->addElement('static','collaps_menu_desc', '', get_string('collaps_menu_desc', 'mod_chairman'));
        
        //NOTE: Any changes here, need to be completed in the db migrator IF they need to be defaulted as open
        $this->add_collapseable_chkbox('members', 'collaps_menu_members', 1);
        $this->add_collapseable_chkbox('addmember', 'collaps_menu_addmembers', 1);
        $this->add_collapseable_chkbox('deletemember', 'collaps_menu_delmembers', 1);
        $this->add_collapseable_chkbox('planner', 'collaps_menu_sched', 1);
        $this->add_collapseable_chkbox('newplanner', 'collaps_menu_newsched', 1);
        $this->add_collapseable_chkbox('viewplanner', 'collaps_menu_sched_respon', 1);
        $this->add_collapseable_chkbox('events', 'collaps_menu_events', 1);
        $this->add_collapseable_chkbox('editevent', 'collaps_menu_editevents', 1);
        $this->add_collapseable_chkbox('addevent', 'collaps_menu_newevents', 1);
        $this->add_collapseable_chkbox('deleteevent', 'collaps_menu_delevents', 1);
        $this->add_collapseable_chkbox('agenda', 'collaps_menu_agenda', 1);
        $this->add_collapseable_chkbox('arising_issues', 'collaps_menu_minutes', 1);
        $this->add_collapseable_chkbox('viewer_events', 'collaps_menu_viewer_events', 1);
        $this->add_collapseable_chkbox('open_topic_list', 'collaps_menu_agenda_ba', 1);
        $this->add_collapseable_chkbox('agenda_archives', 'collaps_menu_agenda_archives', 1);
        $this->add_collapseable_chkbox('filesview', 'collaps_menu_files', 1);
        
        
        $mform->addRule('name', get_string('required'), 'required', null, 'client');
        
        
        $mform->addElement('header','chairman_logo_header',get_string('chairman_logo', 'mod_chairman'));
        
        
        
        $mform->addElement('filemanager', 'chairman_logo', get_string('chairman_logo', 'mod_chairman'), null,
                    array('subdirs' => 0, 'maxfiles' => 1, 'accepted_types'=>array('image')));
       
        $mform->addHelpButton('chairman_logo', 'chairman_logo', 'chairman');
        
        //$draftitemid = file_get_submitted_draft_itemid('chairman_logo');
 
        
        /**
         * ADVANCED
         */
        $mform->addElement('header','chairman_advanced',get_string('header_advanced', 'mod_chairman'));
        
        $month_options = array();
        for($i = 0 ; $i < 12; $i++)
        {
            $month = $i + 1;
            $month_options[$month] = chairman_get_month($month);
            
        }
        
        
        $mform->addElement('select', 'start_month_of_year', get_string('start_month_of_year','chairman'), $month_options);
        $mform->setDefault('start_month_of_year', 7);
        
        $mform->addElement('checkbox','secured',get_string('secured','chairman'));
        $mform->setDefault('secured', 1);
        $mform->addHelpButton('secured', 'secured', 'chairman');
        
        $mform->addElement('checkbox','use_forum',get_string('use_forum','chairman'));
        $mform->setDefault('use_forum', 0);
        $mform->addHelpButton('use_forum', 'use_forum', 'chairman');
        $mform->addElement('checkbox','use_wiki',get_string('use_wiki','chairman'));
        $mform->setDefault('use_wiki', 0);
        $mform->addHelpButton('use_wiki', 'use_wiki', 'chairman');
        if ($DB->get_record('modules', array('name' => 'questionnaire'))){
            $mform->addElement('checkbox','use_questionnaire',get_string('use_questionnaire','chairman'));
            $mform->setDefault('use_questionnaire', 0);
            $mform->addHelpButton('use_questionnaire', 'use_questionnaire', 'chairman');
        }
                
        $mform->addElement('hidden','forum');
        $mform->setDefault('forum', 0);
        $mform->setType('forum', PARAM_INT);
        $mform->addElement('hidden','wiki');
         $mform->setType('wiki', PARAM_INT);
        $mform->setDefault('wiki', 0);
        //intro
        //$this->add_intro_editor(true, get_string('description', 'chairman'));

        $this->standard_coursemodule_elements();

        // buttons
        $this->add_action_buttons();

    }
    
    /**
     * Generates the checkboxes for the collapsible menu settings.
     * The page code identifies which page the checkbox is for, the labelid specifies
     * which string in mod_chairman to include for the checkbox, and the default
     * defines which value to use if a database match is not found for the page
     * and current module.
     * 
     * 
     * @global moodle_database $DB
     * @param string $page_code
     * @param string $labelid
     * @param int $default 0 for collapsed or 1 expanded
     */
    private function add_collapseable_chkbox($page_code, $labelid, $default)
    {
        global $DB;
        
        $cmid = optional_param('update', 0, PARAM_INT);
        
        $this->mform->addElement('advcheckbox', $this->collapse_menu_prepend.$page_code, get_string($labelid, 'mod_chairman'), null, null, array(0, 1));
        $this->mform->setDefault($this->collapse_menu_prepend.$page_code, $default);
        
        if($cmid != 0)
        {
            $cm = get_coursemodule_from_id('chairman', $cmid);
            $select = "chairman_id = ? and ".$DB->sql_compare_text('page_code')." = ?";
            $menu_state = $DB->get_record_select('chairman_menu_state', $select, array($cm->instance,$page_code));
            
            if($menu_state)
               $this->mform->setDefault($this->collapse_menu_prepend.$page_code, $menu_state->state); 
            
        }
        
    }
    
        function data_preprocessing(&$default_values) {
        if ($this->current->instance) {
            // editing existing instance - copy existing files into draft area
            $draftitemid = file_get_submitted_draft_itemid('chairman_logo');
                    file_prepare_draft_area($draftitemid, $this->context->id, 'mod_chairman', 'chairman_logo', 0,
                        array('subdirs' => 0, 'maxfiles' => 1, 'accepted_types'=>array('image')));
            $default_values['chairman_logo'] = $draftitemid;
        }
    }
    

}
?>
