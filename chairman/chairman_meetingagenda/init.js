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

    if (private_pdf.val() === '1' || public_pdf.val() === '1' || local_save.is(':checked'))
    {
        if (!email_option.length > 0)
        {
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





});


/**
 * Moodle Forms (Agenda/Minutes) Manipulation
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
                
                console.log(active);
                $("#"+container_selector_start+position+" div.filemanager").parent().parent().accordion("option", "active", active);
            }, 3500);
        
      });
}

function convert_moodle_field_to_accordian(container_selector, active)
{
        var element = $(container_selector);
        
        var div = $("<div/>");
        var parent = element.parent();
        var children = element.parent().children();
        
        $(parent).prepend("<h3/>");
        $(parent).append($(div));
        
        div.append($(children)); 
        
        $(parent).accordion({
            heightStyle: "content",
            collapsible: true,
            active: active
        });
        
        collapsables.push(parent);
}

var collapsables = new Array();

 function setup_collapseall_listener() 
 {
         $(".collapseexpand").click(function(){
        
        var is_expand = false;
        if($(this).hasClass("collapse-all"))
            is_expand = 0;
        
        $(collapsables).each(function(index, element) {
            console.log(element);
           $(element).accordion("option", "active", is_expand );
        });
    });
 }
 
 function expand_topic_fields(selector)
 {
    var position = find_repeated_field_num($(selector).attr("id"));
     
     $("#id_topic_description_"+position).parent().parent().accordion("option", "active", 0 );
     $("#fitem_id_attachments_"+position+" div.filemanager").parent().parent().accordion("option", "active", 0 );
 }
 
 function find_repeated_field_num(field_id)
 {
     if(!field_id) return;
     var pieces = field_id.split("_");
     if(pieces.length === 0) return -1;
     var position = pieces[pieces.length-1];
     return position;
 }
 

$(function() {
    convert_moodle_field_to_accordian_textfields("id_topic_description_");
    convert_moodle_field_to_accordian_filemanagers("fitem_id_attachments_");
    
    var last_topic_id = $("[id^=id_mod_committee_create_topics_]").last().attr("id");
    expand_topic_fields("#"+last_topic_id);
    
    setup_collapseall_listener();
});

