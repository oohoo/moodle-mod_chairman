
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
 * IMPORTANT:
 * 
 * This javascript is a temporary fix for a bug in moodle. In moodle 2.5 logic was
 * added so that the first header would never be collapsed. If there were 2 headers,
 * then the second wouldn't be collapse but if there was 3 or more than headers 2+
 * would all be collapsed.
 * 
 * This logic fails when using repeated elements in moodle forms. From the testing
 * that was done (using both repeating and normal elements) the repeated elements
 * are always expanded. Even using the $mform->setExpanded('headerid',false) or
 * even $mform->setExpanded('headerid[0]',false) had no effect.
 * 
 * Until this issue is fixed this simply adds the collapsed class to each of the topics
 * manually once the page has loaded.
 * 
 * @TODO Remove once moodle bug has been fixed.
 * 
 */

$(function() {
    $("fieldset[id^='id_mod_committee_create_topics_']").each(function(index, element){
        $(element).addClass('collapsed');
    });
});

/**
 * Dynamic Topic Headers
 * All topics with the same consecuative header name are grouped
 * 
 */

//jQuery selector for the text fields that contains the header name 
var topic_header_text_selector = "[id^=id_topic_header_group_][id$=_topic_header],[id^=topic_header_group_][id$=_topic_header]";

//jQuery selector for the select fields that contain all the header names in use on the page
var topic_header_select_selector = "[id^=id_topic_header_group_][id$=_topic_header_select],[id^=topic_header_group_][id$=_topic_header_select]";

//jQuery selector for the select fieldsets theat contain the objects
var topic_fieldset_selector = "[id^=id_mod_committee_create_topics_],[id^=mod_committee_create_topics_]";

//jQuery selector for the parent that contains the lists of topics
var topic_container_selector = '#topics_sortable';


/**
 * INIT
 */
$(function() {
    
    //on page load
    load_avaliable_headers();//load select with current headers
    add_topic_headers(topic_fieldset_selector, topic_header_text_selector, topic_container_selector, "li", "li");//update headers

/*
 * when the header select changes
 */
$(topic_header_select_selector).change(function() {
   
   var parent = $(this).parent();//get LI parent
   var text = parent.find(topic_header_text_selector);//get the textfield - another child of current element
   
   //set text value to the new select header value just selected
   $(text).val($(this).val()); 
   
   //update the headers
    add_topic_headers(topic_fieldset_selector, topic_header_text_selector, topic_container_selector, "li", "li");//update headers
});

/*
 * On any change of the text field
 */
$(topic_header_text_selector).bind('input', function(){ 
   load_avaliable_headers();//update selects with new header info
    add_topic_headers(topic_fieldset_selector, topic_header_text_selector, topic_container_selector, "li", "li");//update headers
});

/**
 * On sorting finished
 */
$("#topics_sortable").on("sortstop",function( event, ui ) {
    add_topic_headers(topic_fieldset_selector, topic_header_text_selector, topic_container_selector, "li", "li");//update headers
} );

});





/**
 * Updates all the topic header selects with the values of all headers on the page
 *
 *
 */
function load_avaliable_headers() {

    //grab all topic groupings (text & selector)
    var topic_groups = $("[id^=fgroup_id_topic_header_group_]");
    
    var selectors = new Array();//list of selectors
    var topics = $('<select/>');//temp "container" for our created options
    
    //go through each topic
    topic_groups.each(function(index, element) {

       var textfield = $(element).find(topic_header_text_selector);//get text field for current topic
       var selector = $(element).find(topic_header_select_selector);//get select field for current topic
        
        //get topic from text field
        var topic = textfield.val();
        
        //see if any of the existing options have matching values
        var matching_topics = $(topics).find('option[value="'+topic+'"]');
        
        //if not empty or no duplicates - create new header option
        if($.trim(topic) !== '' && matching_topics.length === 0)
            $(topics).append($("<option/>", {text:topic, value:topic}));
        
        //add selectors to list of selectors
        selectors.push(selector);   
    });
    
    //for each of the topic header selectors
     $(selectors).each(function(index, element) {
      element.children().remove();//remove old options
      
      element.append($("<option/>", {text:"-----",value:"" }));//append ignore option
      element.append($(topics).children().clone());//append a copy of our options
         
     });
    
    
}

