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

//interface to moodle's language system
 var dataTablesLang = {
        "sEmptyTable":     php_strings["sEmptyTable"],
        "sInfo":           php_strings["sInfo"],
        "sInfoEmpty":      php_strings["sInfoEmpty"],
        "sInfoFiltered":   php_strings["sInfoFiltered"],
        "sInfoPostFix":    php_strings["sInfoPostFix"],
        "sInfoThousands":  php_strings["sInfoThousands"],
        "sLengthMenu":     php_strings["sLengthMenu"],
        "sLoadingRecords": php_strings["sLoadingRecords"],
        "sProcessing":     php_strings["sProcessing"],
        "sSearch":         php_strings["sSearch"],
        "sZeroRecords":    php_strings["sZeroRecords"],
        "oPaginate": {
            "sFirst":    php_strings["sFirst"],
            "sLast":     php_strings["sLast"],
            "sNext":     php_strings["sNext"],
            "sPrevious": php_strings["sPrevious"]
        },
        "oAria": {
            "sSortAscending":  php_strings["sSortAscending"],
            "sSortDescending": php_strings["sSortDescending"]
        }
    };

/**
 * Init for archive
 */
$(function () {
   
    /*
     * Create the topics DataTable
     * 
     */
    var topic_table = $("#agenda_archive_topics").dataTable( {
      "bJQueryUI": true, //use themeroller
      "bScrollInfinite": true,//use infinite scroll
      "bScrollCollapse": true,//make scroll collapsable
      "sScrollY": "200px",//explicit size
      "bStateSave": true,//save state to cookies
      "iDisplayLength": 10,//only show up to 10 rows,
      
      "aaSorting": [[7,'desc'], [8,'desc'], [9,'desc'], [0,'asc'], [1,'asc']],//init sort by year, month, day desc then event name asc
      "asSorting": [[7,'desc'], [8,'desc'], [9,'desc'], [0,'asc'], [1,'asc']],//default sort by year, month, day desc then event name asc
      "aoColumnDefs": [//which fields to show, and sort defaults
            { "bSearchable": true, "aTargets": [ 0 ] },//event name
            { "bSearchable": true, "aTargets": [ 1 ] },//title
            { "bSearchable": true,"aTargets": [ 2 ] },//display date
            { "bSearchable": true, "aTargets": [ 3 ] },//status
            { "bSearchable": true, "aTargets": [ 4 ] },//desc
            { "bSearchable": true, "aTargets": [ 5 ] },//notes
            { "bSearchable": true, "aTargets": [ 6 ] },//files
            { "bSearchable": true, "bVisible": false, "aTargets": [ 7 ] },//year
            { "bSearchable": true, "bVisible": false, "aTargets": [ 8 ] },//month
            { "bSearchable": true, "bVisible": false, "aTargets": [ 9 ] }//day
        ],
        
        //interface to moodle's language system
      "oLanguage": dataTablesLang
    });
    
    //extend object to contain a mapping of date columns nums for filtering
    $.extend(topic_table, {fnDateFilterMap: {day: 9, month: 8, year: 7}});
    
       /*
     * Create the motions DataTable
     * 
     */
    var motion_table = $("#agenda_archive_motions").dataTable( {
      "bJQueryUI": true, //use themeroller
      "bScrollInfinite": true,//use infinite scroll
      "bScrollCollapse": true,//make scroll collapsable
      "sScrollY": "200px",//explicit size
      "bStateSave": true,//save state to cookies
      "iDisplayLength": 10,//only show up to 10 rows,
      
      "aaSorting": [[11,'desc'], [12,'desc'], [13,'desc'], [0,'asc'], [1,'asc'], [2,'asc']],//init sort by year, month, day desc then event name asc
      "asSorting": [[11,'desc'], [12,'desc'], [13,'desc'], [0,'asc'], [1,'asc'], [2,'asc']],//default sort by year, month, day desc then event name asc
      "aoColumnDefs": [//which fields to show, and sort defaults
            { "bSearchable": true, "bVisible": false, "aTargets": [ 11 ] },//year
            { "bSearchable": true, "bVisible": false, "aTargets": [ 12 ] },//month
            { "bSearchable": true, "bVisible": false, "aTargets": [ 13 ] }//day
        ],
        
        //interface to moodle's language system
      "oLanguage": dataTablesLang
    });
    
    //extend object to contain a mapping of date columns nums for filtering
    $.extend(motion_table, {fnDateFilterMap: {day: 13, month: 12, year: 11}});
    

//Add title to toolbar
var topic_search = $("#agenda_archive_topics_filter");
var topic_title = $("<div class='data_tables_title_wrapper'><h3 class='data_tables_title'>"+php_strings['agenda_archive_topic_title']+"</h3></div>");
topic_search.before(topic_title);

var motion_search = $("#agenda_archive_motions_filter");
var motion_title = $("<div class='data_tables_title_wrapper'><h3 class='data_tables_title'>"+php_strings['agenda_archive_motion_title']+"</h3></div>");
motion_search.before(motion_title);

//Setup Year Selector
var topic_year_select = $("#agenda_archive_topics_year_filter_wrapper");
topic_search.after(topic_year_select);

var motion_year_select = $("#agenda_archive_motions_year_filter_wrapper");
motion_search.after(motion_year_select);

//run custom filtering when year is changed
topic_year_select.change(function() {
    topic_table.fnDraw();
});

motion_year_select.change(function() {
    motion_table.fnDraw();
});

/**
 * Extending the filtering to include the year filter for topics and motions
 */
$.fn.dataTableExt.afnFiltering.push(
    function( oSettings, aData, iDataIndex ) {

        var dateFilterMap = oSettings.oInstance.fnDateFilterMap;

        //var date_filter_map
        var year_filter = $(oSettings.nTableWrapper).find('.agenda_archive_year_filter');
        var year_filter_date = year_filter.val();

        //empty means any date is acceptable
        if(year_filter_date === '') return true;

        /*split the date string of MONTH;YEAR into an array
        /where date[0] is the month & date[1] is the year*/
        var date = year_filter_date.split(';');

        
        var filter_month = parseInt(date[0]);
         var filter_year = parseInt(date[1]);
        
        var row_month = parseInt(aData[dateFilterMap.month]);
        var row_year = parseInt(aData[dateFilterMap.year]);
        
        if ( (row_year === (filter_year - 1) && row_month >= filter_month) || (row_year === filter_year && row_month <= filter_month) )
            return true;
        
        return false;
    }
);
    
//force redraw to enforce new custom filter   
topic_table.fnDraw();

});





