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
    var list = $("#list");

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
            value: value,
            text: text
        }));
  
}

/**
 * Removes all entries in the list of dates that are currently selected.
 * 
 * 
 */
function planner_remove_date()
{
	$("#list .ui-selected").remove();
}

function planner_submit(){
    var name = $('name');
    var nameerror = $('nameerror');

    //Check name
    if(name.val() == ''){
        nameerror.val('*Vous devez fournir un nom');
        return false;
    }
    else{
    	nameerror.val('');
    }

    //Check dates
    var list = $('#list');
    var list_elements = $('#list li');
    var listerror = $('#listerror');
    var dates = $('#dates');

    if(list_elements.length == 0){
        listerror.val('*Vous devez fournir au moins une date');
        return false;
    }
    else{
        listerror.val('');
    }

    var lastElement = dates;
    $.each(list_elements, function(i, item) {
    	
    	var element = $('<input/>', {
    	    id: 'dates',
    	    name: 'dates['+i+']',
    	    type: 'hidden',
    	    value: $(item).attr('value')
    	});
    	
        element.insertAfter(lastElement);
        lastElement = element
    });
    

    $('#newplanner').submit();
}