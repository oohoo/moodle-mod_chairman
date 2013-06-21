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
 * Select2 Moodle Interface for translation.
 * 
 */
(function ($) {
    "use strict";

    $.extend($.fn.select2.defaults, {
        formatNoMatches: function () { return ""+php_strings['select2_no_matches']; },
        formatInputTooShort: function (input, min) { var n = min - input.length; return ""+php_strings['select2_enter'] + n + php_strings['select2_additional_chars'] + (n == 1 ? "" : php_strings['select2_plural_extension']); },
        formatInputTooLong: function (input, max) { var n = input.length - max; return ""+php_strings['select2_remove_chars'] + n + php_strings['select2_chars'] + (n == 1 ? "" : php_strings['select2_plural_extension']); },
        formatSelectionTooBig: function (limit) { return ""+php_strings['select2_only_select'] + limit + php_strings['select2_item'] + (limit == 1 ? "" : php_strings['select2_plural_extension']); },
        formatLoadMore: function (pageNumber) { return ""+php_strings['select2_loading_more']; },
        formatSearching: function () { return ""+php_strings['select2_searching']; }
    });
})(jQuery);