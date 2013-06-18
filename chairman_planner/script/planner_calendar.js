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

/**
 * Initialize Calendar
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
   /**
   * Simulate pressing control at all times for the date multi select
   */
    $( "#selected_dates_list" ).bind("mousedown", function(e) {e.metaKey = true;}).selectable();

/**
 * Initialize member required multiselect
 */
$( "#list_members_required" ).multiselect({locale: 'en'});

});

/**
 * Hover highlighting for date multiselect
 */
  $( "#selected_dates_list li" ).hover(
            function() {$( this ).addClass("date_select_hover");},
            function() {$( this ).removeClass("date_select_hover");}
    
  );

/**
 * State handler for multiselect
 */
$("#list_members_required").bind('multiselectChange', function(evt, ui) {
    // ui.optionElements: [JavaScript array of OPTION elements],
    // ui.selected: {true|false} if the optionsElements were just selected or not
    
    if(ui.selected)
        {
           $(ui.optionElements).each(function() {
             $(this).attr("selected", "selected");  
           });
        }
        else
            {
                $(ui.optionElements).each(function() {
             $(this).removeAttr("selected");  
           });
            }
    
});

