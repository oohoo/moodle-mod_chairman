<?php  //$Id: upgrade.php,v 1.1.8.2 2008-07-11 02:54:54 moodler Exp $

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

// This file keeps track of upgrades to 
// the comity module
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installtion to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php

function xmldb_comity_upgrade($oldversion=0) {

    global $CFG, $THEME, $DB;
$dbman = $DB->get_manager();
    $result = true;


    

     if ($oldversion < 2011021700) {


// Define table comity_agenda to be created
        $table = new xmldb_table('comity_agenda');

        // Adding fields to table comity_agenda
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('comity_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('comity_events_id', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
        $table->add_field('location', XMLDB_TYPE_CHAR, '255', null, null, null, null);

        // Adding keys to table comity_agenda
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for comity_agenda
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

         // Define table comity_agenda_guests to be created
        $table = new xmldb_table('comity_agenda_guests');

        // Adding fields to table comity_agenda_guests
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('comity_agenda', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('firstname', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('lastname', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('moodleid', XMLDB_TYPE_INTEGER, '20', null, null, null, null);

        // Adding keys to table comity_agenda_guests
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('comity_agenda', XMLDB_KEY_FOREIGN, array('comity_agenda'), 'comity_agenda', array('id'));

        // Conditionally launch create table for comity_agenda_guests
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

         // Define table comity_agenda_topics to be created
        $table = new xmldb_table('comity_agenda_topics');

        // Adding fields to table comity_agenda_topics
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('comity_agenda', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('title', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, 'small', null, null, null, null);
        $table->add_field('duration', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('notes', XMLDB_TYPE_TEXT, 'big', null, null, null, null);
        $table->add_field('filename', XMLDB_TYPE_TEXT, 'small', null, null, null, null);
        $table->add_field('follow_up', XMLDB_TYPE_TEXT, 'medium', null, null, null, null);
        $table->add_field('status', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('modifiedby', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '20', null, null, null, null);

        // Adding keys to table comity_agenda_topics
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('comity_agenda', XMLDB_KEY_FOREIGN, array('comity_agenda'), 'comity_agenda', array('id'));

        // Conditionally launch create table for comity_agenda_topics
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

           // Define table comity_agenda_motions to be created
        $table = new xmldb_table('comity_agenda_motions');

        // Adding fields to table comity_agenda_motions
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('comity_agenda', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('comity_agenda_topics', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
        $table->add_field('motion', XMLDB_TYPE_TEXT, 'medium', null, null, null, null);
        $table->add_field('motionby', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('secondedby', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('carried', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('unanimous', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('yea', XMLDB_TYPE_INTEGER, '3', null, null, null, null);
        $table->add_field('nay', XMLDB_TYPE_INTEGER, '3', null, null, null, null);
        $table->add_field('abstained', XMLDB_TYPE_INTEGER, '3', null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '20', null, null, null, null);

        // Adding keys to table comity_agenda_motions
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('comity_agenda', XMLDB_KEY_FOREIGN, array('comity_agenda'), 'comity_agenda', array('id'));

        // Conditionally launch create table for comity_agenda_motions
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

          // Define table comity_agenda_attendance to be created
        $table = new xmldb_table('comity_agenda_attendance');

        // Adding fields to table comity_agenda_attendance
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('comity_agenda', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('comity_members', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('absent', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('unexcused_absence', XMLDB_TYPE_INTEGER, '1', null, null, null, null);
        $table->add_field('notes', XMLDB_TYPE_TEXT, 'small', null, null, null, null);

        // Adding keys to table comity_agenda_attendance
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for comity_agenda_attendance
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

         // Define table comity_agenda_members to be created
        $table = new xmldb_table('comity_agenda_members');

        // Adding fields to table comity_agenda_members
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('comity_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('user_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('role_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('agenda_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table comity_agenda_members
        $table->add_key('id', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for comity_agenda_members
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }




        // comity savepoint reached
        upgrade_mod_savepoint(true, 2011021700, 'comity');
    }

    if ($oldversion < 2011022200) {

       // Define field completed to be added to comity_agenda
        $table = new xmldb_table('comity_agenda');
        $field = new xmldb_field('completed', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'location');

        // Conditionally launch add field completed
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // comity savepoint reached
        upgrade_mod_savepoint(true, 2011022200, 'comity');
    }

    if ($oldversion < 2011030100) {

	   // Define field hidden to be added to comity_agenda_topics
        $table = new xmldb_table('comity_agenda_topics');
         $field = new xmldb_field('hidden', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'status');

        // Conditionally launch add field hidden
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

	 // comity savepoint reached
        upgrade_mod_savepoint(true, 2011030100, 'comity');

    }

    if ($oldversion < 2011030101) {

        // Define field room_reservation_id to be dropped from comity_agenda
        $table = new xmldb_table('comity_agenda');
        $field = new xmldb_field('completed');

        // Conditionally launch drop field room_reservation_id
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

		 // Define field room_reservation_id to be added to comity_agenda
        $table = new xmldb_table('comity_agenda');
        $field = new xmldb_field('room_reservation_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, '0', 'location');

        // Conditionally launch add field room_reservation_id
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // comity savepoint reached
        upgrade_mod_savepoint(true, 2011030101, 'comity');
    }

       if ($oldversion < 2011030102) {

        // Define field room_reservation_id to be added to comity_events
        $table = new xmldb_table('comity_events');
        $field = new xmldb_field('room_reservation_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, '0', 'stamp_t_end');

        // Conditionally launch add field room_reservation_id
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('comity_agenda');
        $field = new xmldb_field('room_reservation_id');

        // Conditionally launch drop field room_reservation_id
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // comity savepoint reached
        upgrade_mod_savepoint(true, 2011030102, 'comity');
    }
    
    if ($oldversion < 2011120800) {

        // + CSS corrections for IE9
        // + PDF correction for justified summary title 
        // + PDF modification of the TCPF PATH to use the Moodle TCPDF
        // + PDF patch for adaptation of the moodle TCPDF because it was an older version than the committee version
        // + Deletion of the TCPF in the comity/meetingagenda directory

        // comity savepoint reached
        upgrade_mod_savepoint(true, 2011120800, 'comity');
    }
    
    if ($oldversion < 2011121200) {

        // + PDF modifications (order of blocks, etc.)
        // + Correction on committee members block (page minutes) to delete space before the members
        // + Alignment of the blocks moodle users and guests 
        // + Correction of the SQL getting the guests to avoid an error if user add guests with the same firstname
        // + Correction of some translations
        //
        // comity savepoint reached
        upgrade_mod_savepoint(true, 2011121200, 'comity');
    }
    if ($oldversion < 2012062100) {

        // Fixed Teacher account - Teacher now is an adin within committees in course
        // Added Title of the comity on the page
        // comity savepoint reached
        upgrade_mod_savepoint(true, 2012062100, 'comity');
    }
        if ($oldversion < 2012070500) {

        // Define field secured to be added to comity
        $table = new xmldb_table('comity');
        $field = new xmldb_field('secured', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'introformat');

        // Conditionally launch add field secured
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // comity savepoint reached
        upgrade_mod_savepoint(true, 2012070500, 'comity');
    }
    if ($oldversion < 2012070501) {

        // Define field notify to be added to comity_events
        $table = new xmldb_table('comity_events');
        $field = new xmldb_field('notify', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'room_reservation_id');

        // Conditionally launch add field notify
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // comity savepoint reached
        upgrade_mod_savepoint(true, 2012070501, 'comity');
    }
    if ($oldversion < 2012070502) {

        // Define field notify_week to be added to comity_events
        $table = new xmldb_table('comity_events');
        $field = new xmldb_field('notify_week', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'notify');

        // Conditionally launch add field notify_week
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // comity savepoint reached
        upgrade_mod_savepoint(true, 2012070502, 'comity');
    }
       if ($oldversion < 2012070503) {

        // Define field notify_sent to be added to comity_events
        $table = new xmldb_table('comity_events');
        $field = new xmldb_field('notify_sent', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'notify_week');

        // Conditionally launch add field notify_sent
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field2 = new xmldb_field('notify_week_sent', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'notify_sent');

        // Conditionally launch add field notify_week_sent
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }

        // comity savepoint reached
        upgrade_mod_savepoint(true, 2012070503, 'comity');
    }
        
    if ($oldversion < 2012080200) {

        // Define field forum to be added to comity
        $table = new xmldb_table('comity');
        $field = new xmldb_field('forum', XMLDB_TYPE_INTEGER, '20', null, null, null, null, 'secured');
        $field2 = new xmldb_field('wiki', XMLDB_TYPE_INTEGER, '20', null, null, null, null, 'forum');

        // Conditionally launch add field forum
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
          // Conditionally launch add field wiki
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }

        // comity savepoint reached
        upgrade_mod_savepoint(true, 2012080200, 'comity');
    }
    
        
    if ($oldversion < 2012080201) {

        // Define field use_forum to be added to comity
        $table = new xmldb_table('comity');
        $field = new xmldb_field('use_forum', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'wiki');
        $field2 = new xmldb_field('use_wiki', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'use_forum');

        // Conditionally launch add field use_forum
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Conditionally launch add field use_forum
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }

        // comity savepoint reached
        upgrade_mod_savepoint(true, 2012080201, 'comity');
    }
        if ($oldversion < 2012080302) {

        // Define field timezone to be added to comity_planner
        $table = new xmldb_table('comity_planner');
        $field = new xmldb_field('timezone', XMLDB_TYPE_CHAR, '255', null, null, null, '99', 'description');

        // Conditionally launch add field timezone
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // comity savepoint reached
        upgrade_mod_savepoint(true, 2012080302, 'comity');
    }
        if ($oldversion < 2012080303) {

        // Define field timezone to be added to comity_events
        $table = new xmldb_table('comity_events');
        $field = new xmldb_field('timezone', XMLDB_TYPE_CHAR, '255', null, null, null, '99', 'notify_week_sent');

        // Conditionally launch add field timezone
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // comity savepoint reached
        upgrade_mod_savepoint(true, 2012080303, 'comity');
    }
    if ($oldversion < 2012080601) {

        /*
         * Added following new features
         * Add associated forum
         * Add associated wiki
         * Timezone selection in when adding ne meeting planner
         * Timezone automatic ajustment in planner
         * Timezone selection in when adding meetings
         * Timezone automatic ajustment in meetings
         * Added link to add meeting to user Moodle calendar
         */
        
        // comity savepoint reached
        upgrade_mod_savepoint(true, 2012080601, 'comity');
    }
    if ($oldversion < 2012100400) {

        // Define field use_questionnaire to be added to comity
        $table = new xmldb_table('comity');
        $field = new xmldb_field('use_questionnaire', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'use_wiki');
        $field2 = new xmldb_field('ue_bbb', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'use_questionnaire');

        // Conditionally launch add field use_questionnaire
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }

        // comity savepoint reached
        upgrade_mod_savepoint(true, 2012100400, 'comity');
    }
    if ($oldversion < 2012100402) {

        // Define field questionnaire to be added to comity
        $table = new xmldb_table('comity');
        $field = new xmldb_field('questionnaire', XMLDB_TYPE_INTEGER, '20', null, null, null, null, 'ue_bbb');
        $field2 = new xmldb_field('bbb', XMLDB_TYPE_INTEGER, '20', null, null, null, null, 'questionnaire');
        $field3 = new xmldb_field('ue_bbb', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'use_questionnaire');

        // Conditionally launch add field questionnaire
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }
        // Launch rename field use_bbb
        $dbman->rename_field($table, $field3, 'use_bbb');

        // comity savepoint reached
        upgrade_mod_savepoint(true, 2012100402, 'comity');
    }
     if ($oldversion < 2012120100) {

        // Define field logo to be added to comity
        $table = new xmldb_table('comity');
        $field = new xmldb_field('logo', XMLDB_TYPE_CHAR, '50', null, null, null, null, 'bbb');

        // Conditionally launch add field logo
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // comity savepoint reached
        upgrade_mod_savepoint(true, 2012120100, 'comity');
    }
    

    return $result;
}

?>
