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
 * The moodle form for displaying and editing the moodle forms for chairman files.
 */

global $CFG;
require_once("$CFG->libdir/formslib.php");
 
class files_edit_form extends moodleform {
 
    private $is_private;
    private $is_admin;
    private $cm;
    
    /**
     * General Constructor
     * 
     * @param Object $private
     * @param Object $is_admin
     * @param Object $cm
     */
    function __construct($private, $is_admin, $cm) {
        $this->is_private = $private;
        $this->is_admin = $is_admin;
        $this->cm = $cm;
        parent::__construct();
    }
    
    /**
     * The structural definition for creating the moodle form for the files view.
     * 
     */
    function definition() {
        
        $mform =& $this->_form;
        
        $this->build_hidden_info($mform);
        $this->build_private_files_form($mform);
        $this->build_submission();
        $this->build_public_files_form($mform); 
        $this->build_submission();

    } 
    
    /**
     * Constructs and adds the required hidden elements for this form.
     * 
     * @param files_edit_form $mform
     */
    private function build_hidden_info($mform)
    {
        $mform->addElement('hidden', 'id', $this->cm);
        $mform->setType('id', PARAM_INT);
    }
    
    /**
     *  Constructs and adds the required submissions elements for this form,
     *  based on the passed in permissions.
     */
    private function build_submission()
    {
        if ($this->is_admin){
           $this->add_action_buttons(); 
        }
        
    }
    
    /**
     * Constructs and adds the file manager for private chairman files.
     * 
     * @param files_edit_form $mform
     */
    private function build_private_files_form($mform)
    {
        if($this->is_private == 1)
        {
            $mform->addElement('header', 'private_files_container', get_string('private_files_label', 'chairman'));
            $mform->addElement('filemanager', 'private_files', null, null, array('accepted_types' => '*'));
            $mform->closeHeaderBefore('public_files_container');
        }
        
    }
    
        /**
     * Constructs and adds the file manager for public chairman files.
     * 
     * @param files_edit_form $mform
     */
    private function build_public_files_form($mform)
    {
        $mform->addElement('header', 'private_files_container', get_string('public_files_label', 'chairman'));
        $mform->addElement('filemanager', 'public_files', null, null, array('accepted_types' => '*'));
        $mform->closeHeaderBefore('private_files_container');
    }
    
}
?>
