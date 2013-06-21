/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

//jQuery selector for the select fields that contain all the header names in use on the page
var topic_header_text_selector = "[id^=id_topic_header_],[id^=topic_header_]";

var topic_fieldset_selector = "[id^=id_mod-committee-topic_header],[id^=mod-committee-topic_header]";

$(function() {
    $("#id_participants_attendance").multipanetransfer({height: '200px'});
    $("#id_participants_attendance").parent().css('margin', '10px').css('width', '100%');
    $("#id_guest_members").select2({placeholder: php_strings['no_guests'],});
    
    /**
     * Initialize the dynamic headers
     */
    add_topic_headers(topic_fieldset_selector, topic_header_text_selector, ".mform", "fieldset", "div");//update headers
    
});


