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
 * Takes the current status of the online jquery calendar and the "from" & "to" times
 * and constructs a date for the meeting. This date is added to the list of dates when
 * this meeting will occur.
 * 
 * If no date is selected in the inline calendar or the from time is less than the to time
 * nothing will occur.
 * 
 */
function planner_add_date(){
    var day = $("#datepicker");
    var from = $("#from_time").find(":selected").text();
    var to = $("#to_time").find(":selected").text();
    var list = $("#selected_dates_list");

    var from_array = from.split(":");
    var to_array = to.split(":");
   
    
    var from_value = parseInt(from_array[0]*60+from_array[1]);
    var to_value = parseInt(to_array[0]*60+to_array[1]);
    
    //if no day is selected or start time is less than or the same as the ending time
    //we don't want to ignore the attempted add
    if(day == null || from_value >= to_value){
        return;
    }
    
    	var dateTypeVar = $('#datepicker').datepicker('getDate');
    	
    	day_value = $.datepicker.formatDate('dd/mm/yy', dateTypeVar);
    	var value = day_value + '@' + from + '@' + to;
    	
    	day_text = $.datepicker.formatDate("M d, yy", dateTypeVar);
        var text = day_text + ' ' + from + ' - ' + to;
        
        day.datepicker({ dateFormat: "dd/mm/yy" });

       list.append($('<li>', {
            datevalue: value,
            text: text
        }));
        
        $( "#selected_dates_list li" ).hover(
            function() {$( this ).addClass("date_select_hover");},
            function() {$( this ).removeClass("date_select_hover");}
    
  );
  
}

/**
 * Removes all entries in the list of dates that are currently selected.
 * 
 * 
 */
function planner_remove_date()
{
	$("#selected_dates_list .ui-selected").remove();
}

/**
 * This function will validate the form has been fully completed, will prepared it for submission,
 * and make the call to actually submit it.
 * 
 * If the form isn't correctly filled out an appropriate error message is displayed.
 * 
 * @returns false when invalid or missing data is present in form
 */
function planner_submit(){
    var name = $('#name');
    var nameerror = $('#nameerror');

    //Check name
    if(name.val() === ''){
        nameerror.text(moodleMsgs.planner_empty_name);
        return false;
    }
    else{
    	nameerror.text('');
    }

    //Check dates
    var list_elements = $('#selected_dates_list li');
    var listerror = $('#listerror');
    var dates = $('#dates');
    var rules = $('#rule');

    if(list_elements.length === 0){
        listerror.text(moodleMsgs.planner_empty_dates);
        return false;
    }
    else{
        listerror.text('');
    }

    //convert dates into submission format 
    //<input type="hidden name="date[1]" value="data"....
    //...
    var lastElement = dates;
    $.each(list_elements, function(i, item) {
    	
    	var element = $('<input/>', {
    	    id: 'dates',
    	    name: 'dates['+i+']',
    	    type: 'hidden',
    	    value: $(item).attr('datevalue')
            
    	});
    	
        element.insertAfter(lastElement);
        lastElement = element;
    });
     
    //convert members required/optional into
    //<input type="hidden name="rule[<memberid>]" value="0/1"....
    //...
    //Note: if not required - they must be optional
    $.each($('#list_members_required option'), function(index, item) {
        
        var value = 0;
        if ( $(item).prop("selected") )
            value = 1;
        
        var element = $('<input/>', {
    	    id: 'rule',
    	    name: 'rule['+$(item).attr('value')+']',
    	    type: 'hidden',
    	    value: value
    	});
        
        element.insertAfter(lastElement);
        lastElement = element;
    });
    
    //submit form
    $('#newplanner').submit();
}