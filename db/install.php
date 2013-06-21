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
require_once("$CFG->dirroot/mod/chairman/db_committee_migrator/comity_db_migrator.php");

echo "<link rel='stylesheet' type='text/css' href='$CFG->wwwroot/mod/chairman/db_committee_migrator/css/migrate.css'>";


    /**
     * A callback that occurs on first install of the chairman module.
     * 
     * If the committee manager module is currently installed, chairman will migrate all
     * of the existing instances of the module to chairman module instances.
     * (Along with all of their data)
     * 
     * @global moodle_database $DB
     */
    function xmldb_chairman_install()
    {
        global $DB;
        $comity_module = $DB->get_record('modules',array('name'=>'comity'));
        
        
        if($comity_module)
        {
           $migrator = new comity_db_migrator();
           
           if(!$migrator->valid_migration_map())
           {
               echo get_string("migration_failed",'chairman');
               return false;
           }
           
           $result = $migrator->migrate_data();
        
           if(!$result)
           {
               echo get_string("migration_failed",'chairman');
               return false;
           }
           
           $result = $migrator->remove_committee_manager();
           if(!$result) return false;
           
           
           return true;
        }   
    }

?>
