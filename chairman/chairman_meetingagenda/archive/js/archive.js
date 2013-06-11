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
      
      "aaSorting": [[6,'desc'], [7,'desc'], [8,'desc'], [0,'asc']],//init sort by year, month, day desc then title asc
      "asSorting": [[6,'desc'], [7,'desc'], [8,'desc'], [0,'asc']],//default sort by year, month, day desc then title asc
      "aoColumnDefs": [//which fields to show, and sort defaults
            { "bSearchable": true, "aTargets": [ 0 ] },//title
            { "bSearchable": true,"aTargets": [ 1 ] },//display date
            { "bSearchable": true, "aTargets": [ 2 ] },//status
            { "bSearchable": true, "aTargets": [ 3 ] },//desc
            { "bSearchable": true, "aTargets": [ 4 ] },//notes
            { "bSearchable": true, "aTargets": [ 5 ] },//files
            { "bSearchable": true, "bVisible": false, "aTargets": [ 6 ] },//year
            { "bSearchable": true, "bVisible": false, "aTargets": [ 7 ] },//month
            { "bSearchable": true, "bVisible": false, "aTargets": [ 8 ] }//day
        ],
        
        //interface to moodle's language system
      "oLanguage":  {
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
    },
    
    
    });

//Add title to toolbar
var topic_search = $("#agenda_archive_topics_filter");
var topic_title = $("<div class='data_tables_title_wrapper'><h3 class='data_tables_title'>"+php_strings['agenda_archive_topic_title']+"</h3></div>");
topic_search.before(topic_title);

//Setup Year Selector
var topic_year_select = $("#agenda_archive_topics_year_filter_wrapper");
topic_search.after(topic_year_select);

//run custom filtering when year is changed
topic_year_select.change(function() {
    topic_table.fnDraw();
});

/**
 * Extending the filtering to include the year filter for ropics and motions
 */
$.fn.dataTableExt.afnFiltering.push(
    function( oSettings, aData, iDataIndex ) {

        var year_filter = $(oSettings.nTableWrapper).find('.agenda_archive_year_filter');
        var year_filter_date = year_filter.val();

        //empty means any date is acceptable
        if(year_filter_date === '') return true;

        /*split the date string of MONTH;YEAR into an array
        /where date[0] is the month & date[1] is the year*/
        var date = year_filter_date.split(';');

        
        var filter_month = parseInt(date[0]);
         var filter_year = parseInt(date[1]);
        
        var row_month = parseInt(aData[7]);
        var row_year = parseInt(aData[6]);
        
        if ( (row_year === (filter_year - 1) && row_month >= filter_month) || (row_year === filter_year && row_month <= filter_month) )
            return true;
        
        return false;
    }
);
    
//force redraw to enforce new custom filter   
topic_table.fnDraw();

});





