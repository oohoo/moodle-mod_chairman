<?php

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
 * A class for retrieving a collection of user's firstname, lastname, and email.
 * The object is built for paging based on the the number of elements returned in 
 * each search, and the current page or collection being returned.
 * 
 */
class moodle_user_selector {

    private $cm;
    private $elements_per_page;

    /**
     * General Constructor
     * @param int $elements_per_page The number of elements the paging system will display on one page.

     */
    function __construct($cm, $elements_per_page = 10) {
        $this->elements_per_page = $elements_per_page;
        $this->cm = $cm;
    }
    
    /**
     * Retrieve a collection of users based on a given search text (against email,first & last name)
     * and the current page or collection for the given search.
     * 
     * OR 
     * 
     * Retrieve a collection of users based on a given list of ids - PAGING IGNORED
     * 
     * The users contain their moodle id, first name, last name, and email.
     * 
     * @global moodle_database $DB
     * @param string $search
     * @param int $current_page The current page of the display.
     * 
     */
   function find_users_records($search, $current_page = 1, $is_id_list = false) {
       global $DB;

       //get sql for retrieving users
       $SQL = $this->get_user_search_sql($search, $current_page, false, $is_id_list);
       
       //retrieve records
       $records = $DB->get_records_sql($SQL);

       return $records;   
    }
    
        /**
     * Retrieve the number of matching records for a given search
     * 
     * @global moodle_database $DB
     * @param string $search
     * @param int $current_page The current page of the display.
     */
   function find_users_count($search) {
       global $DB;

       //get sql for retrieving users
       $SQL = $this->get_user_search_sql($search, 1, true);
       
       //retrieve records
       $records = $DB->count_records_sql($SQL);

       return $records;   
    }
    
    /**
     * A function that generates the sql for retrieveing a collection of users
     * based on a given search on their firstname, lastname or email. The page 
     * controls how many records are to be skipped (in conjunction with # users per collection or page)
     * 
     * The function can also return the TOTAL number of members that match that search (ignoring paging)
     * 
     * @global type $DB
     * @param string $search
     * @param int $current_page The current page of the display.
     */
     private function get_user_search_sql($search, $current_page, $total_count = false, $is_id_list = false) {
       
         if($total_count)//if total count - we want to count ids
            $SELECT = "SELECT count(id) FROM {user} "; 
         else   //otherwise we are after the actual user fields
            $SELECT = "SELECT id, firstname,lastname,email,picture, imagealt FROM {user} ";
       
       //default
       $WHERE_SEARCH = ' WHERE ';
       
       //not empty search
       if(trim ($search) != '')
       { 
           //text based search (default)
           $WHERE_SEARCH = " WHERE ((firstname LIKE '%$search%') OR " .
                " (lastname LIKE '%$search%') OR " .
                " (email LIKE '%$search%')) AND ";
       
       //list of users based on ids
       if($is_id_list)
           $WHERE_SEARCH = " WHERE id IN ($search) AND ";
           
       }
       
       
       $WHERE_ACTIVE = " deleted = 0 ";
       
       $SORT = " ORDER BY firstname, lastname, email ";
       
       $LIMIT='';
       if(!$total_count && !$is_id_list)
            $LIMIT = " LIMIT " . (($current_page - 1) * $this->elements_per_page) . ", $this->elements_per_page ";

       $TERMINATOR = ";";
      
       $SQL = $SELECT . $WHERE_SEARCH . $WHERE_ACTIVE . $SORT . $LIMIT . $TERMINATOR;

       return $SQL;
     }
     
    /**
     * Retrieve a collection of users based on a given search (against email,first & last name)
     * and the current page or collection for the given search in the json format.
     * 
     * {
     * data: [{...},...]
     * total: #
     * }
     * 
     * data contains the list of user objects.
     * total contains the total number of matches (without paging)
     * 
     * The users contain their moodle id, first name, last name, and email.
     * 
     * @global moodle_database $DB
     * @param string $search
     * @param int $current_page The current page of the display.
     */
     function get_users_as_json($search, $current_page) {
       $matches = $this->find_users_records($search, $current_page);
       
       //Build our top level object for json
       //our json object contains
       // root->data : array of records with id, firstname, lastname, image(html), picture, imgalt
       // root->total : amount of TOTAL records matching WITHOUT paging
       $object = new stdClass();
       $object->total = $this->find_users_count($search);
       $object->users = $this->generate_formatted_user_json_output($matches);

       //encode and output
       return json_encode($object); 
     }
     
         /**
     * Retrieve a collection of users based on a given id list (against email,first & last name)
     * and the current page or collection for the given search in the json format.
     * 
     * {
     * data: [{...},...]
     * total: #
     * }
     * 
     * data contains the list of user objects.
     * total contains the total number of matches (without paging)
     * 
     * The users contain their moodle id, first name, last name, and email.
     * 
     * @global moodle_database $DB
     * @param string $search
     * @param int $current_page The current page of the display.
     */
     function get_users_as_json_by_id($idlist) {
       $matches = $this->find_users_records($idlist, 1, true);
       $formatted_matches = $this->generate_formatted_user_json_output($matches);

       //encode and output
       return json_encode($formatted_matches); 
     }
     
     /**
      * Formats a given collection of users from the moodle table for output
      * to json.
      * 
      * @global type $OUTPUT
      * @param array $matches User table entries
      * @return json
      */
     function generate_formatted_user_json_output($matches) {
          global $OUTPUT;
          $matches_array = array();

       //php only allows native arrays with numeric index to be json arrays
       //therefore we convert, and add the html for an image
       $count = 0;
       foreach($matches as $match)
       {
           $matches_array[$count] = $match;

           $options = array('size'=>false, 'link'=>false, 'courseid'=>$this->cm->course);
           $output = $OUTPUT->user_picture($match, $options);

           $match->image = $output;
           $count++;   
       }
       
       return $matches_array;
         
     }
    
}

?>
