<?php // $Id: mod_form.php,v 1.11.2.1 2008-02-21 14:11:18 skodak Exp $
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
require_once ($CFG->dirroot.'/course/moodleform_mod.php');


class mod_comity_mod_form extends moodleform_mod {

    function definition() {
		global $CFG, $DB;

        $mform    =& $this->_form;
        
        //path to directory to scan
        $directory = "$CFG->dirroot/mod/comity/img/logos/";
        
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
        
        //comity name
        $mform->addElement('header','comity_general',get_string('header_general', 'mod_comity'));
        $mform->addElement('text', 'name', get_string('name','comity'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', get_string('required'), 'required', null, 'client');
        $mform->addElement('header','comity_advanced',get_string('header_advanced', 'mod_comity'));
        $mform->addElement('checkbox','secured',get_string('secured','comity'));
        $mform->setDefault('secured', 1);
        $mform->addHelpButton('secured', 'secured', 'comity');
        
        $mform->addElement('checkbox','use_forum',get_string('use_forum','comity'));
        $mform->setDefault('use_forum', 0);
        $mform->addHelpButton('use_forum', 'use_forum', 'comity');
        $mform->addElement('checkbox','use_wiki',get_string('use_wiki','comity'));
        $mform->setDefault('use_wiki', 0);
        $mform->addHelpButton('use_wiki', 'use_wiki', 'comity');
        if ($DB->get_record('modules', array('name' => 'questionnaire'))){
            $mform->addElement('checkbox','use_questionnaire',get_string('use_questionnaire','comity'));
            $mform->setDefault('use_questionnaire', 0);
            $mform->addHelpButton('use_questionnaire', 'use_questionnaire', 'comity');
        }
        $mform->addElement('select','logo',get_string('logo','comity'),$logos);
        $mform->addHelpButton('logo', 'logo', 'comity');
        $mform->addElement('hidden','forum');
        $mform->setDefault('forum', 0);
        $mform->addElement('hidden','wiki');
        $mform->setDefault('wiki', 0);
        //intro
        //$this->add_intro_editor(true, get_string('description', 'comity'));

        $this->standard_coursemodule_elements();

        // buttons
        $this->add_action_buttons();

    }

}
?>
