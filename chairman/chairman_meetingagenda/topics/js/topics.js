
//init
$(function() {
   
   //create the yearly accordian
   $(".yearly_accordian").accordion({
       collapsible: true
   });
   
   //hide all saving icons - only visible if a change is made
   $("[id^=save_image_]").hide();
    
});

/**
 * Function to change current visibility state of an src submit image.
 * The image is hidden if the current selected value is not the same as the old_value
 * 
 * @param {string} old_value The original/default value of the html dropdown menu
 * @param {int} index The current instance of the image.
 */
function change_open_topic_save_visiblity(old_value, index, year) {

    var select = $('#chairman_status_selector_' + index+"_"+year);

    var selected_value = select.children(":selected").val();

    if (selected_value !== old_value) {
        $('#save_image_' + index+"_"+year).show(100);
    } else {
        $('#save_image_' + index+"_"+year).hide(100);
    }
}
