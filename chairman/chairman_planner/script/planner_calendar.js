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
 * Initalization for the planner calendar & the selections lists.
 * 
 */

$(function() {
    $( "#datepicker" ).datepicker({
      changeMonth: true,
      changeYear: true,
      autoSize: true,
      dateFormat: "dd/mm/yy"
    });
  });

$(function() {
    $( "#selected_dates_list" ).bind("mousedown", function(e) {e.metaKey = true;}).selectable();
    $( "#list_members_required" ).bind("mousedown", function(e) {e.metaKey = true;}).selectable();
    $( "#list_members_optional" ).bind("mousedown", function(e) {e.metaKey = true;}).selectable();
    
    $( ".ui-selectable li" ).hover(
            function() {$( this ).addClass("date_select_hover");},
            function() {$( this ).removeClass("date_select_hover");}
    
  );
    
  });

