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

$("#id_moodle_users").select2({
    
    //displayed when no values are present
    placeholder: php_strings['search_moodle_users'],
    
    //min search length before ajax call
    minimumInputLength: 3,
    
    //allow multiple selection
    multiple: true,
    
    //ajax call with search data
    ajax: {
        //ajax controller backend
        url: php_strings['wwwroot'] + "/mod/chairman/chairman_meetingagenda/business/ajax/user_ajax_controller.php",
        
        //ajax return
        dataType: 'json',
        
        //tolerance
        quietMillis: 100,
        
        //ajax data to be sent
        data: function (term, page) { // page is the one-based page number tracked by Select2
            return {
                type: "text",//search based on test
                search: term, //search term
                page_size: 10, // page size
                current_page: page, // page number
                id: php_strings['id'] // cmid
            };
        },
        
        /*
         * Takes the returned json data - MUST BE IN THE FOLLOWING FORMAT FOR select2
         * 
         * { data: [{},{},...] - users
         *   total: # - TOTAL results without paging
         * }
         */
        results: function (data, page) {
            var more = (page * 10) < data.total; // whether or not there are more results available
 
            // notice we return the value of more so Select2 knows if more results can be loaded
            return {results: data.users, more: more};
        }
    },
    
    /**
     * Format display when list of users is displayed to select
     */
    formatResult: function(user) {
        var html = '<table><tr>';
        html+= "<td>"+user.image+"</td><td>"+
                "<span>" + user.firstname + " " + user.lastname + " - " + user.email + 
                "</span></td></tr></table>";
   
            return html; 
      },
        
        /**
         * Converts the initial list of ids (already selected) into proper values based on
         * an ajax call.
         * 
         */
        initSelection: function(element, callback) {

        var ids=$(element).val();//list of user ids 1,2,3...
        
        //no ids - no initial ajax call
        if (ids!=="") {
            $.ajax(php_strings['wwwroot'] + "/mod/chairman/chairman_meetingagenda/business/ajax/user_ajax_controller.php", {
                data: {
                        type: "idlist",//inform controller its a list of user ids
                        search: ids,//list of ids
                        id: php_strings['id'] //cmid
                },
                dataType: "json"
                //callback to format selected
            }).done(function(data) { callback(data); });
        }
     },
   
   /**
    * Display for a selected user
    */
   formatSelection: function(user) {
       return user.firstname + " " + user.lastname + " - " + user.email;
   }, // omitted for brevity, see the source of this page
    
   dropdownCssClass: "bigdrop", // apply css that makes the dropdown taller
    
   escapeMarkup: function (m) { return m; } // we do not want to escape markup since we are displaying html in results
});

