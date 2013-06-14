<?php

/**
 * *************************************************************************
 * *                                Chairman                              **
 * *************************************************************************
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
 * *************************************************************************
 * ************************************************************************ */
/**
 * Faciliates the migration of data from the last version of committee manager
 * to the current version of the chairman plugin.
 * 
 * 
 * @author dddurand
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once("$CFG->dirroot/mod/chairman/lib.php");

class comity_db_migrator {

    private $field_identifier = "___fieldmap";
    private $comity_module_db_file;
    private $migration_map;
    private $clean_mapping = true;
    private $foreign_field_map;
    private $foreign_id_map;

    /**
     * General Constructor for the object
     * @global stdClass $CFG
     * @global moodle_database $DB
     * 
     */
    function __construct() {
        global $CFG, $DB;

        $this->comity_module_db_file = "$CFG->dirroot/mod/comity/db/install.xml";
        $this->migration_map = $this->build_migration_map();
        $this->foreign_field_map = $this->foreign_dependency_map();
    }

    /**
     * Returns whether errors where detected during the generation of the 
     * migration map.
     * 
     * @returns true or false
     */
    function valid_migration_map() {
        return $this->clean_mapping;
    }

    /**
     * Migrates all data present within committee manager module into the
     * new chairman module.
     * 
     * This includes the migration of the sql data, mapping of the foreign table
     * dependencies, and finally updating all the dependencies between tables.
     * 
     * @global object OUTPUT
     * @global moodle_database $DB
     * 
     * 
     */
    function migrate_data() {
        global $DB, $OUTPUT;
        $generated_ids = array();

        echo $OUTPUT->box_start('migrating_committee_data');
        echo $OUTPUT->heading(get_string('committee_migrate', 'chairman'));

        echo get_string('importing_table_desc', 'chairman') . '<br/><br/>';

        //PREPARE FOR CONVERT
        //We need to clean filenames in comity before conversion
        $this->convert_comity_filenames();
        
        //migrate each table
        foreach ($this->migration_map as $comity_table => $chairman_table) {

            //not a table mapping - skip
            if (strpos($comity_table, $this->field_identifier))
                continue;
            $generated_table_ids = array();

            //display header
            echo "<h4>" . get_string('importing_table', 'chairman') . " " . $comity_table . "</h4>";

            //get migration map for current table
            $field_mapping = $this->migration_map[$comity_table . $this->field_identifier];
            $comity_fields = array_keys($field_mapping);

            //get comity records for this table
            $records = $DB->get_records($comity_table, null, '', implode($comity_fields, ","));
            $total_records = count($records);

            //output total number of records to be migrated
            echo get_string('importing_table_count', 'chairman') . $total_records . "</br>";
            echo get_string('importing_table_percent_complete', 'chairman') . " 0%... ";

            $count = 0;
            $percentage_display = 10;
            //migrate each record in table
            foreach ($records as $record) {
                $count++;

                $new_record = new stdClass();

                foreach ($field_mapping as $comity_field => $chairman_field)
                    $new_record->$chairman_field = $record->$comity_field;


                $return_value = $DB->insert_record($chairman_table, $new_record, true, true);

                if (!$return_value) {
                    echo $OUTPUT->error_text(get_string('importing_failure', 'chairman'));
                    $this->failure_cleanup($generated_ids);
                    return false;
                }

                //add generated id to generated ids
                $generated_table_ids[$record->id] = $return_value;

                if ($comity_table == 'comity')
                {
                    $this->migrate_file_data($record, $return_value);
                    $this->load_new_table_defaults($return_value);
                }


                //display updated completed display
                $percentage_display = $this->report_table_import($count, $total_records, $percentage_display);
            }

            $this->report_table_import($count, $total_records, $percentage_display);
            echo "</br></br></br>";

            $generated_ids[$chairman_table] = $generated_table_ids;
            $this->map_foreign_dependencies($chairman_table, $generated_table_ids);
        }

        //replace current instances of comity to chairman instances
        $this->replace_comity_modules();
        echo $OUTPUT->box_end();

        //Update the foreign key references in all chairman tables
        //these are now different than the commity values - mapped in $this->foreign_id_map
        $this->update_foreign_dependencies();

        return true;
    }

    /**
     * Removes all database data in the committee manager, uninstalls committee manager
     * and then attempts to delete the committee module folder automatically.
     * 
     * 
     * @global object $OUTPUT
     */
    function remove_committee_manager() {
        global $OUTPUT;

        echo $OUTPUT->box_start('migrating_committee_data');
        echo "<h3>" . get_string('removing_committee', 'chairman') . "</h3>";
        $this->remove_committee_module();
        $this->remove_committee_db();

        try {
            $this->remove_committee_module_dir();
        } catch (Exception $e) {
            echo $OUTPUT->notification(get_string('dir_delete_failed', 'chairman'));
        }

        echo $OUTPUT->box_end();
    }

    /**
     * Updates all table fields that are foreign ID reference of other table's id,
     * based on the $this->foreign_id_map variable that was build during the migration
     * of the data.
     * 
     * 
     */
    private function update_foreign_dependencies() {

        global $DB, $OUTPUT;

        echo $OUTPUT->box_start('migrating_committee_data');
        echo $OUTPUT->heading(get_string('data_dependencies', 'chairman'));

        //migrate each table
        foreach ($this->migration_map as $comity_table => $chairman_table) {

            //not a table mapping - skip
            if (strpos($comity_table, $this->field_identifier))
                continue;

            //display header
            echo "<h4>" . get_string('updating_table_ids', 'chairman') . " " . $comity_table . "</h4>";

            //get migration map for current table
            $field_mapping = $this->migration_map[$comity_table . $this->field_identifier];

            //get chairman records
            $records = $DB->get_records($chairman_table, null, '', implode($field_mapping, ","));
            $total_records = count($records);

            //output total number of records to be migrated
            echo get_string('importing_table_percent_complete', 'chairman') . " 0%... ";

            $count = 0;
            $percentage_display = 10;
            //migrate each record in table
            foreach ($records as $record) {
                $count++;

                $clean_refs = true;
                foreach ($field_mapping as $chairman_field) {
                    if ($chairman_field == 'chairman_id')
                        continue; //this field maps to course module, which doesn't change
                    if (array_key_exists($chairman_field, $this->foreign_id_map)) {
                        if (!isset($this->foreign_id_map[$chairman_field][$record->$chairman_field])) {
                            continue;
                        }
                        $record->$chairman_field = $this->foreign_id_map[$chairman_field][$record->$chairman_field];

                        if ($record->$chairman_field == null) {
                            $clean_refs = false;
                            break;
                        }
                    }
                }

                if (!$clean_refs)
                    continue;


                $DB->update_record($chairman_table, $record, true, true);

                //display updated completed display
                $percentage_display = $this->report_table_import($count, $total_records, $percentage_display);
            }

            $this->report_table_import($count, $total_records, $percentage_display);
            echo "</br></br></br>";
        }

        echo $OUTPUT->box_end();
        return true;
    }

    /**
     * In the original committee manager module the files names were saved in their
     * raw state, while moodle stored them in a cleaned state. Before migrating them
     * we want to clean them in the DB.
     * 
     * This will help avoid files being missed due to bad file names.
     * Note:(These files worked in committee since moodle autocleans the parameters
     * when grabbing the files, but when we are attempting to get the files - it
     * will cause issues!
     * 
     * 
     * @global moodle_database $DB
     */
    private function convert_comity_filenames()
    {
        global $DB;
        
       $comity_files =  $DB->get_records('comity_files');
        
       foreach($comity_files as $comity_file )
       {
           $comity_file->name = clean_filename($comity_file->name);
           $DB->update_record('comity_files', $comity_file, true);  
       }
    }
    
    /**
     * Transfers all files associated with a comity module record to the associated 
     * chairman module. This includes all files for committee & a committee's agenga.
     * 
     * @global moodle_database $DB
     * @param array $record An entry in the comity table for the committee manager module
     * 
     */
    private function migrate_file_data($record) {
        global $DB;
        $component_comity = 'mod_comity';
        $fileare_comity = 'comity';
        $fileare_comity_agenda = 'attachment';

        $module = $DB->get_record("modules", array('name' => 'comity'));
        $cm = $DB->get_record("course_modules", array('instance' => $record->id, 'module' => $module->id));

        $context = get_context_instance(CONTEXT_MODULE, $cm->id);
        $fs = get_file_storage();

        $files = $fs->get_area_files($context->id, $component_comity, $fileare_comity, false, "", false);
        $files_agenda = $fs->get_area_files($record->id, $component_comity, $fileare_comity_agenda, false, "", false);

        //general committee files
        foreach ($files as $file) {

            //grab assocaited file in comity file db
            $db_file = $DB->get_record('comity_files', array('name' => $file->get_filename(), 'timemodified' => $file->get_timemodified(), 'comity_id' => $cm->id));

            //if file doesn't exist then its an orphaned filed, with no reference and we can ignore it
            if (!$db_file) continue;
            
            //if private we are using a different component space
            $private_annotation = "";
            if ($db_file->private == 1)
                $private_annotation = "_private";

            //build folder tree based on the hiearchy in comity files db
            $filepath = "/";
            $parent_deleted = false;
            while ($db_file->parent != 0) {
                $db_file = $DB->get_record('comity_files', array('id' => $db_file->parent, 'comity_id' => $cm->id));
                
                //a parent folder has been deleted
                //no need to transfer
                if (!$db_file)
                {
                    $parent_deleted = true;
                    break;
                }
                
                //append current element to filepath
                $filepath = "/" . $db_file->name . $filepath;

                //check if permission of this folder is private (then everything underneath is also private)
                if ($db_file->private == 1)
                    $private_annotation = "_private";
            }
            
            //a parent was missing - bail out
            if($parent_deleted) continue;

            $file_record = array('contextid' => $context->id,
                'component' => 'mod_chairman',
                'filearea' => 'chairman' . $private_annotation,
                'itemid' => 0,
                'filepath' => $filepath,
                'filename' => $file->get_filename(),
                'timecreated' => $file->get_timecreated(),
                'timemodified' => $file->get_timemodified());

            //the way the files where done before leaves me wary of potential duplicates
            // - so we check and change filename if need be
        $file_exists = $fs->file_exists($file_record['contextid'], $file_record['component'], $file_record['filearea'], 0, $file_record['filepath'], $file_record['filename']);
            
            $repeat = 0;
            while ($file_exists) {
                $repeat++;
                $file_exists = $fs->file_exists($file_record['contextid'], $file_record['component'], $file_record['filearea'], 0, $file_record['filepath'], $repeat."_".$file_record['filename']);
            }
            
            if($repeat > 0)
                $file_record['filename'] = $repeat . "_" . $file_record['filename'];


            $fs->create_file_from_storedfile($file_record, $file);
        }

        //agenda files
        //These were done on the base of moodle forms and can be directly moved
        foreach ($files_agenda as $file) {
            $file_record = array('contextid' => $context->id,
                'component' => 'mod_chairman',
                'filearea' => 'attachment',
                'itemid' => $file->get_itemid(),
                'filepath' => $file->get_filepath(),
                'filename' => $file->get_filename(),
                'timecreated' => $file->get_timecreated(),
                'timemodified' => $file->get_timemodified());

            $fs->create_file_from_storedfile($file_record, $file);
        }
    }

    /**
     * Constructs a direct map between the old community id of a table entry and
     * the new id for chairman for each potential reference fieldname in
     * $this->$foreign_field_map. This mapping is added to the $this->foreign_id_map
     * array under the key of the "name of the referencing field".
     * 
     * ex:
     * If an entry of comity was 5 and and chairman had a new id of 1.
     * (Along with a bunch of other comity to chairman mappings).
     * 
     * Another table may make a foreign key reference to this table by using
     * "chairman_id" in the table - this is a referencing field. 
     * 
     * The mapping added to $this->foreign_id_map would be:
     * $this->foreign_field_map["chairman_id"] = array(5=>6,..<other mappings>..);
     * 
     * 
     * @param type $chairman_table
     * @param type $generated_ids
     * 
     */
    private function map_foreign_dependencies($chairman_table, $generated_ids) {
        if (array_key_exists($chairman_table, $this->foreign_field_map)) {
            $foreign_fields = $this->foreign_field_map[$chairman_table];

            foreach ($foreign_fields as $foreign_field) {
                $this->foreign_id_map[$foreign_field] = $generated_ids;
            }
        }
    }

    /**
     * Attempts to delete the committee module directory automatically.
     * If permissions cause it to fail - an exception is thrown.
     * 
     * @throws Exception
     * @global object $CFG
     */
    private function remove_committee_module_dir() {
        global $CFG;
        $comity_path = "$CFG->dirroot/mod/comity";
        $this->delete_dir($comity_path);
    }

    /**
     * Recursively removes all folders and their associated files from the filesystem.
     * 
     * @throws Exception
     * @param type $path
     */
    private function delete_dir($dir) {
        $files = array_diff(scandir($dir), array('.', '..'));

        foreach ($files as $file) {
            if (is_dir("$dir/$file")) {
                $this->delete_dir("$dir/$file");
            } else {
                $res = @unlink("$dir/$file");
                if ($res == false)
                    throw new Exception();
            }
        }
        $res = @rmdir($dir);
        if ($res == false)
            throw new Exception();
    }

    /**
     * Uninstalls the committee manager module from moodle.
     * 
     * @global moodle_database $DB
     */
    private function remove_committee_module() {
        uninstall_plugin('mod', "comity");
    }

    /**
     * Drops all of the committee manager tables from the moodle database
     * 
     * @global moodle_database $DB
     */
    private function remove_committee_db() {
        global $DB;
        $dbMan = $DB->get_manager();

        //migrate each table
        foreach ($this->migration_map as $comity_table => $chairman_table) {
            //not a table mapping - skip
            if (strpos($comity_table, $this->field_identifier))
                continue;

            if ($dbMan->table_exists($comity_table)) {
                $xmldb_table = new xmldb_table($comity_table);
                $dbMan->drop_table($xmldb_table);
            }
        }
    }

    /**
     * Prints the $report_at_percent in the format:
     * <$report_at_percent>%... 
     * 
     * When $report_at_percent has been completed based on the <$current_count>
     * and <$total_count> parameters provided.
     * 
     * An updated $report_at_percent is provided.
     * 
     * @global object $OUTPUT
     * 
     * @param type $current_count
     * @param type $total_count
     * @param int $report_at_percent
     * @return int
     * 
     */
    private function report_table_import($current_count, $total_count, $report_at_percent = 10) {
        global $OUTPUT;

        if ($total_count == 0) {
            if ($report_at_percent < 100)
                echo $OUTPUT->notification(get_string('importing_table_complete', 'chairman'), 'notifysuccess');

            $report_at_percent = 101;
            return;
        }

        $percent = ((double) $current_count / (double) $total_count) * 100;

        if ($percent >= $report_at_percent) {
            echo $report_at_percent . "%... ";
            $report_at_percent+= 10;
        }

        while ($percent >= $report_at_percent) {
            echo $report_at_percent . "%... ";
            $report_at_percent+= 10;
        }

        if ($percent == 100 && $report_at_percent != 999) {
            echo $OUTPUT->notification(get_string('importing_table_complete', 'chairman'), 'notifysuccess');
            $report_at_percent = 999;
        }

        return $report_at_percent;
    }

    /**
     * Converts all comity modules into the chairman modules
     * 
     * @param type $generated_ids
     * @global moodle_database $DB;
     * @global object $OUTPUT;
     */
    private function replace_comity_modules() {
        global $DB, $OUTPUT;

        //display header
        echo "<h3>" . get_string('importing_converting_instances', 'chairman') . "</h3>";

        $comity_module = $DB->get_record("modules", array("name" => "comity"));
        $chairman_module = $DB->get_record("modules", array("name" => "chairman"));
        $comity_course_modules = $DB->get_records("course_modules", array("module" => $comity_module->id));

        foreach ($comity_course_modules as $comity_course_module) {
            $comity_course_module->module = $chairman_module->id;
            $comity_course_module->instance = $this->foreign_id_map['chairman_id'][$comity_course_module->instance];
            $DB->update_record("course_modules", $comity_course_module, true);
        }

        //In the pre-moodle 2.0 references are made to the course module as the reference ID instead of the chairman table
        //therefore since "chairman_id" references are actually mapping to course_module ids.
        unset($this->foreign_id_map['chairman_id']);

        get_fast_modinfo(0, 0, true);
        rebuild_course_cache();
        echo $OUTPUT->notification(get_string('importing_table_complete', 'chairman') . "<br/><br/><br/>", 'notifysuccess');
    }

    /**
     * Removes all records created in the current migration of committee manager
     * 
     * @global moodle_database $DB;
     * @param array $generated
     * 
     * array(
     *  table_name => array (1,2,....)
     *  table_name2 => array (1,2,....)
     * 
     * )
     * 
     */
    private function failure_cleanup($generated) {
        global $DB;

        foreach ($generated as $chairman_table => $generated_ids) {
            $DB->delete_records_list($chairman_table, "id", $generated_ids);
        }
    }

    /**
     * Builds a map containing the name of the table in committee manager and
     * the table equivilient in the chairman module.
     * ex: Map
     * {(string)<comity table name>}->{(string)<chairman table name>}
     * 
     * 
     * The map also contains the mappings of the committee manager to chairman fields
     * for the tables above. They are stored in a seperate map at the key of the
     * "<COMITY table name>_fieldmap".
     * ex:
     * {(string)<comity table name>_fieldmap}-> {(map)  {field1-> field1`,....}  }
     * 
     * 
     */
    private function build_migration_map() {
        $table_migration_map = $this->build_table_map();
        $migration_map = $this->build_fields_map($table_migration_map);

        return $migration_map;
    }

    /**
     * Builds a map containing the name of the table in committee manager and
     * the table equivilient in the chairman module.
     * ex: Map
     * {(string)<comity table name>}->{(string)<chairman table name>}
     * 
     * @global moodle_database $DB
     * @global object $OUTPUT
     */
    private function build_table_map() {
        global $DB, $OUTPUT;


        $dbMan = $DB->get_manager();
        $map = array();

        $xmldb_comity = new xmldb_file($this->comity_module_db_file);

        if (!$xmldb_comity->fileExists()) {
            throw new moodle_exception("comity_db_dne", "chairman");
        }

        $xmldb_comity->loadXMLStructure();
        $xmldb_structure = $xmldb_comity->getStructure();
        $tables = $xmldb_structure->getTables();

        foreach ($tables as $table) {
            $comity_table_name = $table->getName();
            $chariman_table_equiv = $this->convert_comity_to_chairman($comity_table_name);

            if ($dbMan->table_exists($comity_table_name) && $dbMan->table_exists($chariman_table_equiv))
                $map[$comity_table_name] = $chariman_table_equiv;
            else {
                $OUTPUT->error_text(get_string('importing_table_dne', 'chairman') . $chariman_table_equiv);
                $this->clean_mapping = false;
            }
        }

        return $map;
    }

    /**
     *  Takes in a map containing the comity table names as keys, and the associated
     *  chairman table names as the value. The function adds a map corresponding to
     *  the migration between the fields in comity -> chairman for each table under
     *  <comity table name>_fieldmap.
     * ex: Map
     * {(string)<comity table name>}->{(string)<chairman table name>}
     * 
     * 
     * The function adds the following:
     *
     * {(string)<comity table name>_fieldmap}-> {(map)  {field1-> field1`,....}  }
     * 
     * 
     * @param array $table_map
     * @global moodle_database $DB
     * @global object $OUTPUT
     * 
     * @returns array $table_map
     * 
     */
    private function build_fields_map($table_map) {
        global $DB, $OUTPUT;
        $dbMan = $DB->get_manager();

        foreach ($table_map as $comity_table => $chairman_table) {
            $comity_fields = $DB->get_columns($comity_table);

            $field_map = array();
            foreach ($comity_fields as $comity_field) {
                $comity_field_name = $comity_field->name;
                $chairman_field_equiv = $this->convert_comity_to_chairman($comity_field_name);

                if ($dbMan->field_exists($comity_table, $comity_field_name) &&
                        $dbMan->field_exists($chairman_table, $chairman_field_equiv))
                    $field_map[$comity_field_name] = $chairman_field_equiv;
                else {
                    $OUTPUT->error_text(get_string('importing_table_field_dne', 'chairman') . $chairman_table . "=>" . $chairman_field_equiv);
                    $this->clean_mapping = false;
                }
            }

            $table_map[$comity_table . $this->field_identifier] = $field_map;
        }

        return $table_map;
    }

    /**
     * Converts the given comity name for a table or field and returns the chairman
     * equivilent. In our case the only change is changing 'comity'->'chairman'.
     * 
     * @param type $comity_name
     * @return string
     * 
     */
    private function convert_comity_to_chairman($comity_name) {
        return str_replace("comity", "chairman", $comity_name);
    }

    private function foreign_dependency_map() {
        $map = array(
            'chairman' => array('chairman_id'),
            'chairman_agenda' => array('chairman_agenda', 'agenda_id'),
            'chairman_agenda_members' => array('motionby', 'secondedby', 'chairman_members'),
            'chairman_agenda_topics' => array('chairman_agenda_topics'),
            'chairman_events' => array('chairman_events_id'),
            'chairman_files' => array('parent'),
            'chairman_members' => array('chairman_member_id'),
            'chairman_planner' => array('planner_id'),
            'chairman_planner_dates' => array('planner_date_id'),
            'chairman_planner_users' => array('planner_user_id')
        );

        return $map;
    }
    
    /**
     * Loads any default data needed for chairman that arises for new tables that
     * are in chairman, but didn't exist in comity.
     */
    private function load_new_table_defaults($chairman_instance_id)
    {
       $this->load_menu_table_defaults($chairman_instance_id); 
    }
    
    /**
     * Updates the chairman menu defaults based on the STATIC state from comity
     * 
     * @param int $chairman_instance_id primary id from the chairman table
     */
    private function load_menu_table_defaults($chairman_instance_id) {
        
        $chairman = new stdClass();
        $chairman->id = $chairman_instance_id;
        $chairman->col_menu_members = 1;
        $chairman->col_menu_addmember = 1;
        $chairman->col_menu_deletemember = 1;
        $chairman->col_menu_planner = 1;
        $chairman->col_menu_newplanner = 1;
        $chairman->col_menu_viewplanner = 1;
        $chairman->col_menu_events = 1;
        $chairman->col_menu_editevent = 1;
        $chairman->col_menu_addevent = 1;
        $chairman->col_menu_deleteevent = 1;
        $chairman->col_menu_agenda = 1;
        $chairman->col_menu_arising_issues = 1;
        $chairman->col_menu_viewer_events = 1;
        $chairman->col_menu_open_topic_list = 1;
        $chairman->col_menu_agenda_archives = 1;
        $chairman->col_menu_filesview = 1;
        
        chairman_update_menu_state($chairman);
    }

}

?>
