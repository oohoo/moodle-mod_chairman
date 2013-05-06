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


function planner_add_date(){
    var day = $("#datepicker");
    var from = $("#from_time").find(":selected").text();
    var to = $("#to_time").find(":selected").text();
    var list = $("#list");

    var from_array = from.split(":");
    var to_array = to.split(":");
    
    //if no day is selected or start time is less than or the same as the ending time
    //we don't want to ignore the attempted add
    if(day == null || from_array[0]*60+from_array[1] >= to_array[0]*60+to_array[1]){
        return;
    }
    
    	var dateTypeVar = $('#datepicker').datepicker('getDate');
    	
    	day_value = $.datepicker.formatDate('dd/mm/yy', dateTypeVar);
    	var value = day_value + '@' + from + '@' + to;
    	
    	day_text = $.datepicker.formatDate("M d, yy", dateTypeVar);
        var text = day_text + ' ' + from + ' - ' + to;
        
        day.datepicker({ dateFormat: "dd/mm/yy" });

       list.append($('<option>', {
            value: value,
            text: text
        }));
  
}

function planner_remove_date(){
    var list = document.getElementById('list');

    for(var i=0; i<list.length; i++){
        if(list.options[i].selected){
            list.remove(i);
        }
    }
}

function planner_submit(){
    var name = document.getElementById('name');
    var nameerror = document.getElementById('nameerror');

    //Check name
    if(name.value == ''){
        nameerror.innerHTML = '*Vous devez fournir un nom';
        return false;
    }
    else{
        nameerror.innerHTML = '';
    }

    //Check dates
    var list = document.getElementById('list');
    var listerror = document.getElementById('listerror');

    if(list.length == 0){
        listerror.innerHTML = '*Vous devez fournir au moins une date';
        return false;
    }
    else{
        listerror.innerHTML = '';
    }

    //Validation complete

    //Check all dates in list
    for(var i = 0; i<list.length; i++){
        list.options[i].selected = true;
    }

    document.newplanner.submit();
}