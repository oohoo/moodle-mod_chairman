/**
 **************************************************************************
 **                                Chairman                              **
 **************************************************************************
 * @package mod                                                          **
 * @subpackage chairman                                                  **
 * @name Chairman                                                        **
 * @copyright oohoo.biz                                                  **
 * @link http://oohoo.biz                                                **                                           **
 * @author Dustin Durand                                                 **
 * @license                                                              **
 http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later                **
 **************************************************************************
 **************************************************************************/

/**
 * This jQuery plugin takes a multiselect html item, and converts it into an
 * multipane exclusive chooser. All options in the multi-select element are
 * considered selected, and can be organized into different exclusive values
 * (or group values) as determined by the value present in the optgroup tag.
 * 
 * EX: 
 *  <select multiple name="multipane_transfer" id="multipane_transfer">
 <optgroup value="1" label="Swedish Car">
 <option>A</option>
 <option>B</option>
 </optgroup>
 <optgroup value="2" label="German Car">
 <option>Mercedes</option>
 <option>Audi</option>
 </optgroup>
 <optgroup value="3" label="Dustin's Car">
 <option>Bike</option>
 <option>Wheel On A Stick</option>
 </optgroup>
 
 * In this case there are three exclusive groups that each member can be sorted under.
 * They are either a Swedish car, German Car, or Dustin's Car. All options will be submitted
 * with a single value of either 1,2 or 3.
 * 
 * Labels are taken from the optgroups for display labels
 * 
 * Panes can be added dynamically.
 * 
 * Generate by:
 * $("#multipane_transfer").multipanetransfer();
 * 
 * or
 * 
 * $("#multipane_transfer").multipanetransfer({height: '200px'});
 * 
 * 
 */

jQuery.widget("oohoo.multipanetransfer", {
    //The only controllable option is the header
    options: {
        height: 150
   },
    /**
     * General Constructor
     */
    _create: function() {
        this.element.addClass("multipanetransfer");//add class to original select element
        this.auto_inc_id = 0;//incremented id for each select option

        //wrapper for new display for select
        this.display = jQuery('<div/>', {class: 'multipanetransfer'});

        //map for connecting new display to original select options (done by mpt_id (from this.auto_inc_id) )
        this.map = new Array();

        //map for connection new display panes to original selects - (done by mpt_id (from this.num_panes) )
        this.pane_map = new Array();

        //convert all selects to new display
        this._process_panes(this.display);

        //add new display after original select
        this.element.after(this.display);

        //hide original
        this.element.hide();

        //generate sortable lists
        this._generate_sortable();

        //set the height from options
        jQuery(".multipanetransfer").css("height", this.options['height']);

        //set width based on # of panes
        jQuery(".mpt-div-container").css("width", 95 / this.num_panes + "%");


    },
    /**
     * Only applicable option is height
     *   -height value is directly given to css height
     */
    _setOption: function(key, value) {
        if (key === "height") {
            value = this.element.css('height', value);
        }
        this._super(key, value);
    },
    /**
     * Takes the labels off the optgroups and genreated display labels
     */
    _process_title: function(pane, container) {
        var title = jQuery(pane).attr('label');

        var title_item = jQuery('<div/>', {
            text: title,
            class: "ui-widget-header mpt-div-header"
        });

        container.append(title_item);

    },
    /**
     * Itterates through all of the optgroups and generates the display for each one
     * based on the original information
     * 
     * label - taken for new display labels for each pane
     * value - the value to be associated with each option in that pane
     * 
     */
    _process_panes: function(container) {
        var self = this; //to allow reference to widget in each context
        this.num_panes = 0;//used to count # panes & id of panes

        //retrieve all panes
        var panes = this.element.children("optgroup");

        //need to track which pane is the last one
        var last_pane_ul = null;

        //itterate through all panes and create display
        panes.each(function(index, pane) {

            //map pane to our new li display
            self.pane_map[self.num_panes] = pane;

            //create div as pane container
            var pane_container = jQuery('<div/>', {class: "mpt-div-container"});
            container.append(pane_container);

            //attach title
            self._process_title(pane, pane_container);

            //create list
            var pane_ul = jQuery('<ul/>');
            jQuery(pane_container).append(pane_ul);

            pane = jQuery(pane);

            //add all attributes from original pane to new display pane(the list)
            pane_ul.attr("mpt_id", self.num_panes);
            pane_ul.attr("value", pane.attr("value"));
            pane_ul.addClass("mpt-acceptor");

            //Add specific class to first pane
            if (index === 0) {
                self.first = pane;
                pane_ul.addClass("mpt-first-pane");

            } else //otherwise its a mid pane
                pane_ul.addClass("mpt-mid-pane");

            //keep track of number of panes
            self.num_panes++;

            //process pane's elements
            var elements = pane.children("option");

            //process each element
            elements.each(function(index, element) {
                element = jQuery(element);
                
                var value = element.attr("value");
                if(/^__BLANK__.*$/.test(value)) {
                    element.remove();
                    return;
                }
                
                var element_li = jQuery('<li/>');//new li to display each options
                self._process_elements(element_li, pane, element);//add original values to li
                pane_ul.append(element_li);//add li
            });

            //keep previous pain
            last_pane_ul = pane_ul;
        });

        //label last pane
        last_pane_ul.addClass("mpt-last-pane");
        last_pane_ul.removeClass(["mpt-last-pane", "mpt-mid-pane"]);



    },
    /**
     * Converts all ul lists in the display to sortable lists, with drag capabilities
     * Also adds listeners for click events & click indiation display
     */
    _generate_sortable: function()
    {
        var self = this;//allow access to widget in itteration contexts

        //convert all lists to sortable lists with specific callbacks & properties    
        jQuery(".mpt-acceptor").sortable({
            connectWith: "ul.mpt-acceptor",
            containment: "div.multipanetransfer",
            //when an li is moved we need to update its value, and move it in the original(& change value)     
            receive: function(event, ui) {
                var item = ui['item'];//li being moved
                var item_id = jQuery(item).attr("mpt_id");//li's mpt_id
                var this_pane_id = jQuery(this).attr("mpt_id");//parent's mpt_id

                //update value for display
                var new_val = jQuery(this).attr('value');
                jQuery(item).attr("value", new_val);

                var original_item = self.map[item_id];//get li's corresponding option
                var original_new_pane = self.pane_map[this_pane_id];//get new panes corresponding select

                //update value for display & move to proper new ul
                jQuery(original_item).appendTo(original_new_pane);
                jQuery(original_item).attr("value", new_val);

            },
            //Stop click events on dragging
            start: function(event, ui) {
                ui.item.bind("click.prevent",
                        function(event) {
                            event.preventDefault();
                        });
            },
            //allow click events when not dragging
            stop: function(event, ui) {
                setTimeout(function() {
                    ui.item.unbind("click.prevent");
                }, 300);
            }

        });

    },
    /**
     * Moves all original data from option to new li display element
     *  -Also saves a mapping of new display id to original option
     */
    _process_elements: function(element_li, pane, element) {
        var id = this.auto_inc_id++;//get new mpt-id for display li
        var value = pane.attr("value");//get value from pane
        var text = element.text();//get text from option

        element_li.attr("value", value);//set value on display
        element.attr("value", value);//set value on option

        element_li.attr("mpt_id", id);//set mpt_id on display
        element_li.addClass("ui-state-default");

        element_li.text(text);//set text
        this.map[id] = element[0];//do mapping from display mpt_id to option

        this._set_listeners(element_li);//setup various listeners

    },
    /**
     * Determines whether the pane id of the element that this element would move
     * to if clicked.
     * Thie is determined by whether the offset is to the left or right of the middle
     * of the li.
     * If first pane always looks to the right, if the last pane always looks to the right pane ids.
     */
    _get_target_ul_mpt_id: function(li_panel, xoffset)
    {
        var id = jQuery(li_panel).parent().attr("mpt_id");//get mpt_id
        var is_first = jQuery(li_panel).parent().hasClass("mpt-first-pane");//check if its the first pane
        var is_last = jQuery(li_panel).parent().hasClass("mpt-last-pane");//check if its the last pane
        var new_ul_id = -1;

        if (is_first)//if first always move right
            new_ul_id = (parseInt(id) + 1);//Pane ids are assumed consecutive
        else if (is_last)//if left always move left
            new_ul_id = (parseInt(id) - 1);
        else//determine based on offset
        {
            //middle of li
            var middle = jQuery(li_panel).width() / 2;
            
            //offset
            var click_x_position = xoffset;

            if (click_x_position >= middle)//if its more than the middle - going left
                new_ul_id = (parseInt(id) + 1);
            else //if its less than the middle going right
                new_ul_id = (parseInt(id) - 1);
        }

        //return new id/
        return new_ul_id;
    },
    
    /**
     * Sets the click and mouseover listeners for the li given.
     */
    _set_listeners: function(li_panels)
    {
        var self = this;//for context issues
        jQuery(li_panels).click(function(event)
        {
            //get id for new ul target
            var new_ul = "ul[mpt_id=" + self._get_target_ul_mpt_id(this, event['offsetX']) + "]";

            //move to new ul
            jQuery(this).appendTo(new_ul);

            //create our sudo event
            var event = jQuery([]);
            event['item'] = this;

            //trigger the recieve event for sortable
            jQuery(new_ul).data('ui-sortable')._trigger("receive", null, event);
        });

        //When the mouse leaves an li, remove the direction indicator for clicking
        jQuery(li_panels).hover(
                function() {

                },
                function() {
                    jQuery(this).find('.mpt-dir-indc').remove();
                });

        //On mousing over, remove old direction indication, and add a new one with
        //the proper direction
        jQuery(li_panels).mousemove(function(event) {
            jQuery(this).find('.mpt-dir-indc').remove();//remove old indicator
            var current_id = jQuery(this).parent().attr("mpt_id");//get current mpt_id
            var id = self._get_target_ul_mpt_id(this, event['offsetX']);////get mpt_id for where it would go on click
            
            //if current id is larger - going left
            if (current_id > id)
                jQuery(this).prepend("<span style='float:left;' class='mpt-dir-indc ui-icon ui-icon-arrowstop-1-w'>left</span>");
            else if (current_id < id)//if current id is smaller going right
                jQuery(this).append("<span style='float:right;' class='mpt-dir-indc ui-icon ui-icon-arrowstop-1-e'>right</span>");

        });


    },
    
    /**
     * Destroy widget
     */
    destroy: function() {
        this.element
                .removeClass("multipanetransfer");

        //destory sortable lists
        jQuery(".mpt-acceptor").sortable("destroy");
        //show original display
        jQuery(this.element).show();
        //remove new display
        jQuery(this.display).remove();
        // Call the base destroy function.
        jQuery.Widget.prototype.destroy.call(this);
    }

});


