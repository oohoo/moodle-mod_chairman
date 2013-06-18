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

//$timestart = microtime(true);

require_once('../../../config.php');

$string = optional_param('str',null,PARAM_TEXT); //search query
$id = required_param('id',PARAM_INT); //search query

if($string=='') {
    $users = $DB->get_records('user',array('deleted'=>0),'lastname ASC');
}
else {
    $users = $DB->get_records_select('user','upper(firstname) LIKE upper("%'.$string.'%") OR upper(lastname) LIKE upper("%'.$string.'%") and deleted = 0 ',null,'lastname ASC');
}

echo '<select name="user">';
foreach($users as $user) {
    if(!$DB->get_record('chairman_members', array('user_id'=>$user->id,'chairman_id'=>$id))) {
        echo '<option value="'.$user->id.'">'.$user->lastname.', '.$user->firstname.'</option>';
    }
}
echo '</select>';

//$timeend = microtime(true);
//$total = $timeend-$timestart;

//echo $total;

?>
