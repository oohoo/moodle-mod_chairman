<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * @package   comity
 * @copyright 2011 Raymond Wainman, Dustin Durand, Patrick Thibaudeau (Campus St. Jean, University of Alberta)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('lib.php');
require_once('lib_comity.php');
echo '<link rel="stylesheet" type="text/css" href="style.php">';

$id = optional_param('id', 0, PARAM_INT);    // Course Module ID
$comityid = optional_param('comityid', 0, PARAM_INT);    // Course Module ID
global $CFG,$DB;
comity_check($comityid);
//comity_header($comityid, 'deleteplanner', 'planner.php?id=' . $comityid);
//delete planner dates
$DB->delete_records('comity_planner_dates', array('planner_id'=> $id));
//Delete planner users
$DB->delete_records('comity_planner_users', array('planner_id'=> $id));
//delete planner event
if($DB->delete_records('comity_planner',array('id' => $id))) {
	redirect($CFG->wwwroot.'/mod/comity/planner.php?id='.$comityid, 'Planner event has been deleted', 3);
}

//comity_footer();