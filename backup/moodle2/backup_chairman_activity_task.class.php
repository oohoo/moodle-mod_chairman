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

global $CFG;

require_once($CFG->dirroot . '/mod/chairman/backup/moodle2/backup_chairman_stepslib.php'); // Because it exists (must)
require_once($CFG->dirroot . '/mod/chairman/backup/moodle2/backup_chairman_settingslib.php'); // Because it exists (optional)
 
/**
 * chairman backup task that provides all the settings and steps to perform one
 * complete backup of the activity
 */
class backup_chairman_activity_task extends backup_activity_task {
 
    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity
    }
 
    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        $this->add_step(new backup_chairman_activity_structure_step('chairman_structure', 'chairman.xml'));
    }
 
    /**
     * Code the transformations to perform in the activity in
     * order to get transportable (encoded) links
     */
    static public function encode_content_links($content) {
        return $content;
    }
}

?>
