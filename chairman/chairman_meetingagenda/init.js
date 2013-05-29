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



