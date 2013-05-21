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


require_once('../../config.php');
require_once('lib.php');
require_once('beta_chairman_lib.php');

$cmid = required_param('id',PARAM_INT);    // Course Module ID
chairman_header("members", "members", "beta_view.php?id=$cmid", $cmid);

        
chairman_footer();
?>
