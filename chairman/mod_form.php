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
 * @package   chairman
 * @copyright 2010 Raymond Wainman, Patrick Thibaudeau (Campus St. Jean, University of Alberta)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once ($CFG->dirroot.'/course/moodleform_mod.php');


class mod_chairman_mod_form extends moodleform_mod {

    function definition() {
		global $CFG, $DB;

        $mform    =& $this->_form;
        
        //path to directory to scan
        $directory = "$CFG->dirroot/mod/chairman/img/logos/";
        
        //get all image files with a .jpg extension.
        $images = glob($directory."*.*");
        
        //$images = array('test.jpg', 'test2.gif');
        $logos = array('' => 'Select');
        //print each file name
        $i = 0;
        foreach($images as $image)
        {
            $logos[str_replace($directory, '', $image)] = str_replace($directory, '', $image);
            $i++;
        }
        
        //chairman name
        $mform->addElement('header','chairman_general',get_string('header_general', 'mod_chairman'));
        $mform->addElement('text', 'name', get_string('name','chairman'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', get_string('required'), 'required', null, 'client');
        $mform->addElement('header','chairman_advanced',get_string('header_advanced', 'mod_chairman'));
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
        $mform->addElement('select','logo',get_string('logo','chairman'),$logos);
        $mform->addHelpButton('logo', 'logo', 'chairman');
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

}
?>
