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

var tips = $(".form_information");

/**
 *  Determines whether a provided field is considered empty.
 *  
 *  If the field is empty, then an error is presented in the form_information
 *  label. The error presented is based on the error identifier given, which must be
 *  present in the php_strings global array.
 *  
 * @param {text based field} field
 * @param {string} error
 * @returns {Boolean} true on non-empty, false on empty
 * 
 */
function notEmpty(field, error) {
    if (field.val().length === 0) {
        field.addClass("ui-state-error");
        update_form_info(error);
        return false;
    } else {
        return true;
    }
}

/**
 * Updates the form_information field based on the text identifier provided.
 * The text presented is based on the text identifier given, which must be
 *  present in the php_strings global array.
 * 
 * @param {type} text
 * @returns {undefined}
 */
function update_form_info(text) {
    tips
            .text(php_strings[text])
            .addClass("ui-state-highlight");
    setTimeout(function() {
        tips.removeClass("ui-state-highlight", 1500);
    }, 500);
}

/**
 * Makes an ajax based call to add an external link to the chairman module.
 * 
 * If successful the link is added to the list.
 * If fails, the user is informed in the form_information field
 * 
 * @param {string} name
 * @param {string} link
 */
function add_link_ajax(name, link)
{
    $.ajax({
        type: "POST",
        url: php_strings['ajax_url'],
        beforeSend: function() {
            update_form_info(php_strings["link_ajax_sending"])
        },
        data: {type: "add", name: name.val(), link: link.val(), id: php_strings["id"]}
    })
            .done(function(id) {

        //For case when user only adds: www.asdf.com
        var clean_link = link.val();
        if (clean_link.indexOf('http') !== 0) {
            clean_link = "http://" + clean_link;
        }

        $("#chairman_links")
                .prepend("<li id='link_" + id + "'><a target='_blank' href='" + clean_link + "'>" + name.val() + "</a><div ><span name='delete_link_" + id + "' class='ui-icon ui-icon-minusthick'/></div></li>");

        register_delete_link_listeners();
        $("#chairman_links").menu("refresh");

        $("#link_dialog_form").dialog("close");
        
    })
            .fail(function() {
        update_form_info(php_strings["link_ajax_failed"]);
    });
}

/**
 * Removes a link from the chairman module based on the link id
 * 
 * @param {int} id of the link in chairman_links
 */
function remove_link_ajax(id)
{
    $.ajax({
        type: "POST",
        url: php_strings['ajax_url'],
        beforeSend: function() {
            update_form_info(php_strings["link_ajax_sending"])
        },
        data: {type: "delete", link_id: id, id: php_strings["id"]}
    })
            .always(function() {
        $("#link_" + id).remove();
        $("#link_delete_confirm").dialog("close");
    });
}

/**
 * Hide contents of the menu in the given timeframe
 * 
 * @param {type} time
 * @returns {undefined}
 */
function hide_menu(time)
{
    $("#menu_title").hide(time);
    $("#chairman_menu_container").hide(time);
    $("#nav_title").hide(time);
    $("#chairman_links_container").hide(time);
    $("#link_title").hide(time);
    $("#chairman_nav_root").addClass("collapsed_menu");
    $("#chairman_menu_collapse").removeClass("ui-icon-arrowthickstop-1-w");
    $("#chairman_menu_collapse").addClass("ui-icon-arrowthickstop-1-e");
}

/**
 * Animated process of hiding the navigation menu
 * 
 * @param {type} animate_time
 * @param {type} content_time
 * @returns {undefined}
 */
function hide_menu_animated(animate_time, content_time)
{
    $("#chairman_main").animate({'margin-left': '45px'}, animate_time);
    hide_menu(content_time);

    $("#chairman_nav_root").animate({'width': '30px', 'overflow': 'hidden'}, animate_time, function() {
    });
}

/**
 * Animated process of showing a collapsed navigation menu.
 * 
 * 
 * @param {type} animate_time
 * @param {type} content_time
 * @returns {undefined}
 */
function show_menu_animated(animate_time, content_time)
{
    $("#chairman_main").animate({'margin-left': '160px'}, animate_time);
    $("#chairman_nav_root").effect("size", {to: {width: '150px'}}, animate_time, function() {
        show_menu(content_time);
    });
}

/**
 * Shows the contents of the menu
 * 
 * @param {type} time
 * @returns {undefined}
 */
function show_menu(time)
{
    $("#chairman_nav_root").css("width", "150px");
    $("#chairman_main").css("margin-left", "160px");
    $("#menu_title").show(time);
    $("#chairman_menu_container").show(time);
    $("#nav_title").show(time);
    $("#chairman_links_container").show(time);
    $("#link_title").show(time);
    $("#chairman_nav_root").removeClass("collapsed_menu");
    $("#chairman_menu_collapse").removeClass("ui-icon-arrowthickstop-1-e");
    $("#chairman_menu_collapse").addClass("ui-icon-arrowthickstop-1-w");
}

function register_delete_link_listeners() {
        //register all delete icons for the links
    $("[name^='delete_link_']").each(function(index, icon)
    {
        var id = $(icon).attr('name').replace("delete_link_", "");

        $(icon).click(function(event)
        {
            event.preventDefault();//don't let a tag event to occur
            $("#link_delete_confirm").data('id', id).dialog("open");

        });

        //icon hover outline
        $(icon).hover(
                function() {
                    $(icon).addClass("chairman_bordered");
                },
                function() {
                    $(icon).removeClass("chairman_bordered");
                }
        );

    });
}

/**
 * Global Initalization
 */
$(function() {
    
  $(function()
    {
        if (menu_state_default === 0)
        {
            hide_menu_animated(0, 0);
        }
    });
    
    $("#chairman_menu").menu();//generate nav menu
    $("#chairman_links").menu();//generate links menu

    //setup hover css
    $("#chairman_menu_collapse_button").hover(
            function() {
                $(this).addClass("ui-state-hover");
            },
            function() {
                $(this).removeClass("ui-state-hover");
            }
    );

    //menu shrink/expand on click of collapse button
    $("#chairman_menu_collapse_button").click(
            function() {
                if ($("#chairman_nav_root").hasClass("collapsed_menu"))//already colapsed - expand
                {
                    show_menu_animated(1000, 800);
                }
                else //collapse menu
                {
                    hide_menu_animated(1000, 800);
                }
            });


    //generate buttons for add link dialog
    var buttons = {};
    buttons[php_strings["addlink"]] = function() {
        var bValid = true;
        var name = $("#chairman_link_name");
        var link = $("#chairman_link");

        name.removeClass("ui-state-error");
        link.removeClass("ui-state-error");

        bValid = bValid && notEmpty(name, "emptyname");
        bValid = bValid && notEmpty(link, "emptylink");

        add_link_ajax(name, link);


    };

    buttons[php_strings["cancel"]] = function() {
        $(this).dialog("close");
    };



    //create add link dialog
    $("#link_dialog_form").dialog({
        autoOpen: false,
        height: 265,
        width: 245,
        modal: true,
        buttons: buttons,
        resizable: false,
        close: function() {
            $("#chairman_link_name").removeClass("ui-state-error");
            $("#chairman_link").removeClass("ui-state-error");
            update_form_info("form_info_default");
        }
    });


    //register on click for add link row
    $("#chairman_add_link").click(function()
    {
        $("#link_dialog_form").dialog("open");
    });


    //remove links dialog buttons
    buttons = {};
    buttons[php_strings["remove_link"]] = function() {
        remove_link_ajax($(this).data('id'));
    };

    buttons[php_strings["cancel"]] = function() {
        $(this).dialog("close");
    };

    //generate delete links confirmation dialog
    $("#link_delete_confirm").dialog({
        autoOpen: false,
        resizable: false,
        height: 160,
        modal: true,
        buttons: buttons
    });

    register_delete_link_listeners();

});




