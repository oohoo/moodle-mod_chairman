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
 * Runs an ajax search to filter events via ajax. Text for the search is pulled
 * from the text input and sent to the server. The server searches for matching
 * events and outputs them. On success completion, this function will replace the current
 * event results with the new results from the search.
 * 
 * During the ajax call a loading image and message is displayed.
 * On failure an error message is presented to the user, and the old results displayed.
 * 
 */
function ajax_search_events()
{
    var search = $("#meeting_search").val();

    $.ajax({
        type: "POST",
        url: php_strings['ajax_event_search_url'],
        beforeSend: function() {
            ajax_loading(true);
            $("#events_container").text("")
        },
        data: {ajax_request: 1, search: search, id: php_strings["id"], archive: is_archive}
    })
            .done(function(data) {
        $(".events_container").accordion("destroy");
        $(".events_container").remove();
        $("#events_root").append(data);
        create_accordian();
        ajax_loading(false)

    })
            .fail(function(data) {
        $("#search_error").text(php_strings["event_search_error"]);
        $("#events_root").append(data);

        ajax_loading(false);

    });

}

/**
 * A toggle for displaying the events or an ajax loading message.
 * 
 * @param bool isloading True = loading ajax shown, false = results shown
 */
function ajax_loading(isloading)
{
    var loading = $("#ajax_loading");

    if ($(loading).css("display") === "none") {
        if (isloading)
            show_ajax_loading();
    }
    else
    {
        if (!isloading)
            hide_ajax_loading();
    }


}

/**
 * Shows the ajax loading message, and hides the events results
 */
function show_ajax_loading()
{
    var loading = $("#ajax_loading");
    var result = $(".events_container");

    $(loading).show(500);
    $(result).hide(500);
}

/**
 * Shows the events results, and hides the ajax loading message
 */
function hide_ajax_loading()
{
    var loading = $("#ajax_loading");
    var result = $(".events_container");

    $(loading).hide(500);
    $(result).show(500);
}

/**
 * Creates an horizontal accordian from the element called "events_container"
 */
function create_accordian()
{
    var active_tab = $("#current_active_month").val();
    $(".events_container").accordion({
        collapsible: true,
        active: parseInt(active_tab, 0),
        heightStyle: "content"
    });
}

//init
$(function() {

    create_accordian();

    //create search button
    $("#search_button").button();

    //search button pressed listener
    $("#search_button").click(function() {
        ajax_search_events();
    });

    //enter key in search automatically starts search
    $("#meeting_search").keypress(function(event) {
        if (event.which === 13) {
            ajax_search_events();
        }

    });
    
    $(function() {
    $( ".date_time_picker" ).datepicker({
      changeMonth: false,
      changeYear: false,
      autoSize: true,
      dateFormat: "dd/mm/yy",
      defaultDate: (function() {
        var day = $("#date_time_day").val();
        var month = $("#date_time_month").val();
        var year = $("#date_time_year").val();

        return day + '/' + month + '/' + year;
      }()),
      onSelect: function(date) {
        var day = $("#date_time_day");
        var month = $("#date_time_month");
        var year = $("#date_time_year");
        
        var date_elements = date.split('/');
        
        day.val(date_elements[0]);
        month.val(date_elements[1]);
        year.val(date_elements[2]);
        
        }
      
    });
    
  });

});