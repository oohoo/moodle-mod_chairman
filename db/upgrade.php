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

if ($oldversion < 2013061700) {
        // Define field introformat to be added to book
        $table = new xmldb_table('chairman_agenda_topics');
        $field = new xmldb_field('topic_order', XMLDB_TYPE_INTEGER, '20');

        // Launch add field introformat
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // book savepoint reached
        upgrade_mod_savepoint(true, 2013061700, 'chairman');
}

if ($oldversion < 2013062000) {
        // Define field introformat to be added to book
        $table = new xmldb_table('chairman_agenda_topics');
        $field = new xmldb_field('topic_header', XMLDB_TYPE_CHAR, '255');
        
        // Launch add field introformat
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        $field = new xmldb_field('presentedby', XMLDB_TYPE_INTEGER, '20');
        
        // Launch add field introformat
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
      
        $table = new xmldb_table('chairman_agenda');
        $field = new xmldb_field('message', XMLDB_TYPE_TEXT);
        
        // Launch add field introformat
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        $field = new xmldb_field('footer', XMLDB_TYPE_CHAR, '255');

        // Launch add field introformat
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // book savepoint reached
        upgrade_mod_savepoint(true, 2013062000, 'chairman');
}

if ($oldversion < 2013062100) {
        // Define field introformat to be added to book
        $table = new xmldb_table('chairman_agenda_topics');
        $field = new xmldb_field('presentedby_text', XMLDB_TYPE_TEXT);
        
        // Launch add field introformat
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        

        // book savepoint reached
        upgrade_mod_savepoint(true, 2013062100, 'chairman');
}

return $result;
}

?>
