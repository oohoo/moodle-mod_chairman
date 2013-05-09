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

class comity_db_migrator {

    private $field_identifier = "___fieldmap";
    private $comity_module_db_file;
    private $migration_map;
    private $clean_mapping = true;

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
                array_push($generated_table_ids, $return_value);

                //display updated completed display
                $percentage_display = $this->report_table_import($count, $total_records, $percentage_display);
            }

            $this->report_table_import($count, $total_records, $percentage_display);
            echo "</br></br></br>";
            $generated_ids[$chairman_table] = $generated_table_ids;
        }

        $this->replace_comity_modules();
        echo $OUTPUT->box_end();
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
            $DB->update_record("course_modules", $comity_course_module, true);
        }

        echo $OUTPUT->notification(get_string('importing_table_complete', 'chairman') . "<br/><br/><br/>",'notifysuccess');
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

}

?>
