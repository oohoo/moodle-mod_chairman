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

/*
 * The upgrade file provides structural and database changes for upgrading 
 * versions of the chairman module. 
 * 
 */

function xmldb_chairman_upgrade($oldversion=0) {

global $CFG, $THEME, $DB;
$dbman = $DB->get_manager();
$result = true;


//Possible check for another install only function in moodle instead of this upgrade
//
//Last check of migrating old data
    //if old committee manager is installed && (migrate flag clear ?? - or do something like check if first install?)
        //if its the latest version
            //migrate data
        //else
            //display - please update committee manager to last version of 
            //committee manager to migrate data to chairman
    //else
        //nothing

return $result;
}

?>
