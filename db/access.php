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

/*
 * This access file defines a write privilege for chairman in the user context.
 * Managers of a course are automatically given this permission.
 */

$capabilities = array(

    'mod/chairman:admin' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array (
            'manager' => CAP_ALLOW
            )
        ),

    'mod/chairman:addinstance' => array(
        
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    )
);


