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


return $result;
}

?>
