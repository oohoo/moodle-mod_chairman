<?php
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
 * @package   chairman
 * @copyright 2010 Raymond Wainman, Patrick Thibaudeau (Campus St. Jean, University of Alberta)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
global $CFG;
?>

div.ui-datepicker{
 font-size:10px;
}

.cornerBox { position: relative; background: #cfcfcf; }
.lightcornerBox { position: relative; background: #dadbda; }
.corner { position: absolute; width: 10px; height: 10px; background: url('<?php echo $CFG->wwwroot;?>/mod/chairman/pix/corners.gif') no-repeat; font-size: 0%; }
.lightcorner { position: absolute; width: 10px; height: 10px; background: url('<?php echo $CFG->wwwroot;?>/mod/chairman/pix/lightcorners.jpg') no-repeat; font-size: 0%; }
.cornerBoxInner { padding: 10px; }
.TL { top: 0; left: 0; background-position: 0 0; }
.TR { top: 0; right: 0; background-position: -10px 0; }
.BL { bottom: 0; left: 0; background-position: 0 -10px; }
.BR { bottom: 0; right: 0; background-position: -10px -10px; }

.content { font-size: 12px; }
.title {font-weight: bold; border-bottom: 1px solid black; margin-bottom:10px; }
.add {font-size: 12px; font-weight:0; color:black;}
.addentrylink:link {color:black; }
.addentrylink:hover {color:black; }

.file { border: 1px solid black; margin:10px;}

#image_cell{ margin-right:3px; }
#icon { margin:10px; }
#back { margin-left:10px; }