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
 * Determines if the dropdown that allows the selection of whether a link should
 * be private or public is visible in the form.
 * 
 * The dropdown is hidden if email option isn't selected.
 * 
 */
function update_pdf_dialog_email_type_visibility()
{
    if ($('#export_pdf_type').val() === 'email') {
        $('#export_email_type').show(100);
    } else {
        $('#export_email_type').hide(100);
    }
}

/**
 * Determines if the private option in the link security dialog should be avaliable.
 * 
 * The option is shown if the user is saving the pdf to the private files or the file has
 * already been exported to the private files.
 * 
 */
function update_pdf_email_only_members_visibility()
{
    var private_pdf = $("#private_pdf_avaliable");
    var export_email_type = $("#export_email_type");
    var save_type = $("#pdf_save_type");
    var local_save = $("#local_save");
    var email_option_html = "<option id='export_email_type_private' value='private'>" + php_strings["export_pdf_email_private"] + "</option>"
    var email_private_type = $('#export_email_type_private');

    //save checked and saving to private only
    if ((local_save.is(':checked') && save_type.val() === "private") || private_pdf.val() === '1')
    {
        if (!email_private_type.length > 0)
        {
            export_email_type.append(email_option_html);
        }
    }
    else
    {
        export_email_type.val("public");
        email_private_type.remove();
    }
}

/**
 * Determines if the public option in the link security dialog should be avaliable.
 * 
 * The option is shown if the user is saving the pdf to the public files or the file has
 * already been exported to the public files.
 * 
 */
function update_pdf_email_public_visibility()
{
    var export_pdf = $("#export_pdf_type");
    var public_pdf = $("#public_pdf_avaliable");
    var export_email_type = $("#export_email_type");
    var save_type = $("#pdf_save_type");
    var local_save = $("#local_save");
    var email_option_html = "<option id='export_email_type_public' value='public'>" + php_strings["export_pdf_email_public"] + "</option>"
    var email_public_type = $('#export_email_type_public');

    //save checked and saving to public only
    if ((local_save.is(':checked') && save_type.val() === "public") || public_pdf.val() === '1')
    {
        if (!email_public_type.length > 0)
        {
            export_email_type.append(email_option_html);
        }
        
        if(export_email_type.is(":visible") && export_email_type.val() === 'public' && export_pdf.val() === "email")
            $('#export_pdf_form_info').text(php_strings["export_pdf_email_public_warning"]);
        else
           $('#export_pdf_form_info').text(""); 
        
    }
    else
    {
        export_email_type.val("public");
        email_public_type.remove();
        $('#export_pdf_form_info').text("");
                
        if(export_email_type.children().length === 0)
            export_email_type.hide(100);
    }
}

/**
 * This function should be called whenever any element is changed in the export pdf form.
 * 
 */
function export_form_changed()
{
    
    update_pdf_dialog_email_export_visibility();
    update_pdf_dialog_email_type_visibility();
    update_pdf_email_only_members_visibility();
    update_pdf_email_public_visibility();
    
}


/**
 *  This function determines if the email as link option should be avaiable to the user.
 *  
 *  The option is shown if there is a pdf save in either the public/private chairman files,
 *  or if the user is saving a pdf.
 */
function update_pdf_dialog_email_export_visibility()
{
    var private_pdf = $("#private_pdf_avaliable");
    var public_pdf = $("#public_pdf_avaliable");
    var export_pdf = $("#export_pdf_type");
    var local_save = $("#local_save");
    var email_option_html = "<option id='email_export_option' value='email'>" + php_strings["export_pdf_email"] + "</option>"
    var email_option = $('#email_export_option');

    //if private/public version of the file is already saved, or we are going to save the file
    if (private_pdf.val() === '1' || public_pdf.val() === '1' || local_save.is(':checked'))
    {
        //email is not already present
        if (!email_option.length > 0)
        {
            //add option
            export_pdf.append(email_option_html);
        }
    }
    else 
    {
        export_pdf.val("download");
        email_option.remove();
        
        if(email_option.children().length === 0)
            email_option.hide(100);
        
    }
}

/**
 * Initialization of the pdf export form
 */
$(function() {

    $("#pdf_save_type").hide();
    $("#export_email_type").hide();
    export_form_changed();

    $("#pdf_export_dialog").dialog({
        autoOpen: false,
        height: 320,
        width: 440,
        resizable: false,
        modal: true,
        open: function(event, ui) {
            $('#local_save').attr('checked', false);
            $('#export_pdf_type').val("download");

        },
        buttons: [{text: php_strings["export"], click: function() {

                    $("#pdf_export_dialog_form").submit();
                    $("#pdf_export_dialog").dialog('close');
                }
            },
            {
                text: php_strings["cancel"], click: function() {
                    $("#pdf_export_dialog").dialog('close');
                }
            }]


    });

    //open export dialog listener setup
    $("#export_pdf_image").click(function()
    {      
        $("#pdf_export_dialog").dialog('open');    
    });


    /**
     * Save checkbox changed
     */
    $('#local_save').change(function() {
        if ($(this).is(':checked')) {
            $('#pdf_save_type').show(100);
        } else {
            $('#pdf_save_type').hide(100);
        }
        
        export_form_changed();

    });

    /**
     * Export pdf as changed
     */
    $('#export_pdf_type').change(function() {
        export_form_changed();
    });

    /**
     * public/private save checkbox changed
     */
    $('#pdf_save_type').change(function() {
        export_form_changed();
    });
    
    /**
     * public/private email checkbox changed
     */
    $('#export_email_type').change(function() {
        export_form_changed();
    });



    $('.viwer_menu ul ul li').each(function() {
         $(this).hover(
        function() {
            $(this).addClass("ui-state-hover");
        },
        function() {
            $(this).removeClass("ui-state-hover");
        });     
                
    });



});


/**
 * Moodle Forms (Agenda/Minutes) Manipulation
 * @param {string} container_selector_start The begining of the textfield's id without the repeat #
 * 
 */
function convert_moodle_field_to_accordian_textfields(container_selector_start)
{
      $("[id^="+container_selector_start+"]").each(function(index, element) {
          var container_id = $(element).attr("id");
          var position = find_repeated_field_num(container_id);
          var textfield = $("#"+container_selector_start+position);
          var value = $.trim(textfield.val());
          
          var active = false;
          if(value && value !== "")
              active = 0;
          
          convert_moodle_field_to_accordian("#"+container_id, active);
        
      });
}

/**
 * Warps a filemanager into a dropdown accordian
 * @param {string} container_selector_start The begining of the filemanager's id without the repeat #
 */
function convert_moodle_field_to_accordian_filemanagers(container_selector_start)
{
      $("[id^="+container_selector_start+"]").each(function(index, element) {
          var container_id = $(element).attr("id");
          var position = find_repeated_field_num(container_id);
          
          convert_moodle_field_to_accordian("#"+container_selector_start+position+" div.filemanager", false);
        
          //Not a pretty method, but since the file manager is using ajax to pull the information (through YUI), we cannot
          //detect when its updated in any practical way that is pratical for performance - (polling or watching dom tree changes).
          //We are waiting for a couple seconds(to let YUI finish init and make its ajax calls) and then updating the whether the 
          //attachment should be opened or closed.
          setTimeout(function()
            {
                 var filemanagercontent_child = $("#"+container_selector_start+position+" div.fp-content div.fp-iconview");
                
                var active = false;
                if(filemanagercontent_child.length > 0)
                    active = 0;
                
                $("#"+container_selector_start+position+" div.filemanager").parent().parent().accordion("option", "active", active);
            }, 3500);
        
      });
}

/**
 * Wraps a given field into an dropdown single accordian. The parent of the element
 * should be a div - it will be used for the accordian wrapper.
 * 
 * @param {string} container_selector
 * @param {false or int} active false = closed, otherwise 0 based tab to be opened
 * @param {string} title title for the dropdown
 */
function convert_moodle_field_to_accordian(container_selector, active, title)
{
        //element
        var element = $(container_selector);
        
        //div wrapper for element
        var div = $("<div/>");
        
        //parent is the accordian
        var parent = element.parent();
        var children = element.parent().children();
        
        //if null title - make empty
        if((typeof title !== "undefined") )
            $(parent).prepend($("<h3/>", {text: title}));
        else
            $(parent).prepend("<h3/>");
        
        //append div wrapped element
        $(parent).append($(div));
        
        div.append($(children)); 
        
        //create accordian from original parent div
        $(parent).accordion({
            heightStyle: "content",
            collapsible: true,
            active: active
        });
        
        //add to global array of collapsable elements
        collapsables.push(parent);
}

//global array of collapsable elements
var collapsables = new Array();

/**
 * Setups up the expandall/collapseall  listener with our global set of collapsable
 * elements
 */
 function setup_collapseall_listener() 
 {
         $(".collapseexpand").click(function(){
        
        var is_expand = false;
        if($(this).hasClass("collapse-all"))
            is_expand = 0;
        
        $(collapsables).each(function(index, element) {
           $(element).accordion("option", "active", is_expand );
        });
    });
 }
 
 /**
  * Expands a given topic accordian
  * 
  * @param {string} selector A jquery selector for a topic 
  */
 function expand_topic_fields(selector)
 {
    var position = find_repeated_field_num($(selector).attr("id"));
     
     $("#id_topic_description_"+position).parent().parent().accordion("option", "active", 0 );
     $("#fitem_id_attachments_"+position+" div.filemanager").parent().parent().accordion("option", "active", 0 );
 }
 
 /**
  * Finds the repeated number at the send of an id ex: id_topic_{x} or id_topic_{x}_{y}
  * 
  * If not delimiter_occurance_from_end is given it retrieves the last one
  * -1 returned on failure
  * 
  * @param {type} field_id the id of the field with the repeating id
  * @param {type} delimiter_occurance_from_end How occurances(for hierchy of repeated #'s) from end of string
  * @returns {int} # based on id, or -1 on failure
  */
  function find_repeated_field_num(field_id, delimiter_occurance_from_end)
 {
     //if undefined assume its the last position
     if(typeof delimiter_occurance_from_end === "undefined") delimiter_occurance_from_end = 1;
     
      //no id - fail
      if(!field_id) return -1;
      
      //split into delimited pieces
     var pieces = field_id.split("_");
     
     //calc corresponding piece from end
     var split_location = pieces.length-delimiter_occurance_from_end;
     
      //too few pieces - fail
      if(pieces.length < split_location) return -1;
     
      //retrieve repeated number
      var repeat_num = pieces[split_location];
     
     //is numeric
     if(!isNaN(parseInt(repeat_num)) && isFinite(repeat_num))
        return repeat_num;
 
        //wasn't a repeated number
        return -1;
  }
 

$(function() {
    //setup accordians for page
    convert_moodle_field_to_accordian_textfields("id_topic_description_");
    convert_moodle_field_to_accordian_filemanagers("fitem_id_attachments_");
    convert_moodle_field_to_accordian_motion("motion_header_");
    convert_moodle_field_to_accordian_motion("motion_new_header_");
    
    //expand empty topic
    var last_topic_id = $("[id^=id_mod_committee_create_topics_]").last().attr("id");
    expand_topic_fields("#"+last_topic_id);
    
    build_topics_sortable();

    
    
    //@TODO Look for a way for YUI & Query to communicate or interact to avoid this.
    setTimeout(function() { 
        setup_collapseall_listener();
        $(".mceLayout").css('width', "100%");
        $(".mform .mceIframeContainer").children().css('width', "100%");
    }, 3000);
    
    //@TODO Look for a way for YUI & Query to communicate or interact to avoid this.
    setTimeout(function() { 
        setup_collapseall_listener();
    
    }, 4000);

});

/**
 * Moodle Forms (Agenda/Minutes) Manipulation
 * 
 * @param {string} container_selector_start Beginning of the html id for a motion wrapper
 */
function convert_moodle_field_to_accordian_motion(container_selector_start)
{
      $("[id^="+container_selector_start+"]").each(function(index, element) {
          var container_id = $(element).attr("id");
          var last = find_repeated_field_num(container_id, 1);
          var second_last = find_repeated_field_num(container_id, 2);
          
          var id = -1;
          if(second_last === -1)
              id = container_selector_start + last;
          else
              id = container_selector_start + second_last + "_" + last;         
          
          var title_element = $(this).parent().children().first();
          var label = title_element.text();
          title_element.remove();
          
          convert_moodle_field_to_accordian("#"+id, false, label);
      });
      
}

/**
 * Converts the topic elements within an agenda into a sortable list
 */
function build_topics_sortable() {
    
    var previous_element = $('#id_mod_committee_create_topics_0, #mod_committee_create_topics_0');//first element before topics
    
    var topic_elements = $('fieldset[id^=id_mod_committee_create_topics_],fieldset[id^=mod_committee_create_topics_]');//get the list of topics
    var topics_list_wrapper = $('<ul/>', {id:'topics_sortable'});//create a list to wrap sortable list rows(topics)
    
    //insert list after the element before where the first topic
    previous_element.after(topics_list_wrapper);
    
    //itterate through each topic element and put the topics into the list
    topic_elements.each(function(index, topic) {
        
        var topic_wrapper = $('<li/>');//create an LI wrapper for the topic
        topic_wrapper.prepend($('<span/>', {class:"sortable_icon ui-icon ui-icon-arrow-4"}));
        topic_wrapper.append(topic);//append the topic to the wrapper
        topics_list_wrapper.append(topic_wrapper);//attach the li topic wrapper to the list
        
    });
    
    
    //create sortable list
    topics_list_wrapper.sortable({
    items: 'li:not(.sort_ignore)',
    //whenever a topic is dropped into the list
    stop: function(event, ui) {
        var list = ui.item.parent();//grab list
        var order = $("input[name='topics_order']");//get hidden object that contains order information (in json format)
        
        var json_string = order.val();//get string value of order info
        
        if(json_string == null) return;
        
        console.log(json_string);
        
        var order_json = $.parseJSON(json_string);//convert to object from json
        
        //itterate through all topics and record new order
        var position = 0;
        $(list).children(":not(.sort_ignore)").each(function(index, element) {//each child is a topic
            
            var topic_fieldset = $(element).find('[id^=id_mod_committee_create_topics_], [id^=mod_committee_create_topics_]');//get an id that contains the original index
            var original_index = find_repeated_field_num(topic_fieldset.attr('id'));//get the ORIGINAL INDEX of the current topic
            
            var topic_id_element = $('input[name="topic_id['+original_index+']"]')//retrieve the hidden element that contains topic id
            var topic_id = topic_id_element.val();//get topic id value
            
            //use -1 for new topic that has no id
            if(topic_id === '')
                topic_id = '-1';
            
            //update position for this topic
            order_json[topic_id] = position;
            position++;
        });
        
        //convert new json object to string
        json_string = JSON.stringify(order_json);
        order.val(json_string);//save order state back to hidden field
        }
    });
    
}


/**
 * Update Topic Headers based on the values in the topic's header textfield
 * 
 * Headers are added before each unique header in a series of topics (Only considering the last seen header - consecutive unqiueness).
 * Upon finding a header that is same as the previous topic, it is considered part of 
 * the previous group and no header is added.
 * 
 * @param {string} topic_header_text_selector jQuery selector used to select the text value containing the header value
 * @param {string} topic_fieldset_selector jQuery selector used to select the fieldset that contains the whole topic
 * @param {string} topics_container_selector jQuery selector used to select the parent that contains the list of topics ex: ul or form
 * @param {string} topics_container_type ex: li or fieldset : type of element that a topic is contained in
 * @param {string} output_wrapper ex: li or div, etc : When the header is added, what should it be wrapped in
 */
function add_topic_headers(topic_fieldset_selector, topic_header_text_selector, topics_container_selector, topics_container_type, output_wrapper ) {


var list = $(topics_container_selector);//get container of topics - ex: ul or form
list.find('.topic_dynamic_headers').remove();//remove all the headers

var previous = '';//consider empty header as the previous one

//for each of the topics found
list.find(topic_fieldset_selector).each(function(index, element) {
    
    var textfield = $(this).find(topic_header_text_selector);//get topic header text field
    var topic_val = $.trim(textfield.val());//get trimmed value in field
    
    //if this header is the same as the last - we skip (its part of previous header group)
    if(topic_val === previous)
        return;
    
    
    previous = topic_val;//update previous with new value
    
    //get parent li
    
    var parent = $(this).parent(topics_container_type);
    
    //if no valid parent is found use the current object
    //Note: Minutes page is using this property
    if(parent.length === 0)
        parent = $(this);
    
    
    //add the header before the current li element
    var spacer = $("<br/>");
    var title = $("<h3/>", {text: topic_val});
    
    //header wrapped in desired wrapper element
    var title_list = $("<"+output_wrapper+"/>", {class:'topic_dynamic_headers sort_ignore'}).append(spacer).append(title);
    
    parent.before(title_list);
    
});


}
