
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

