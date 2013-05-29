<?php

/**
 * *************************************************************************
 * *                                Chairman                              **
 * *************************************************************************
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
 * *************************************************************************
 * ************************************************************************ */

/**
 * The general library for Agenda/Meeting Extension to Committee Module.
 *
 *
 * @package   Agenda/Meeting Extension to Committee Module
 * @copyright 2011 Dustin Durand
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/*
 * Converts month, as a number, to a literary term.
 *
 * @param int $monthAsNum Numerical representation of a month
 */
function toMonth($monthAsNum) {
    if ($monthAsNum == 1) {
        $month = get_string('january', 'chairman');
    }
    if ($monthAsNum == 2) {
        $month = get_string('february', 'chairman');
    }
    if ($monthAsNum == 3) {
        $month = get_string('march', 'chairman');
    }
    if ($monthAsNum == 4) {
        $month = get_string('april', 'chairman');
    }
    if ($monthAsNum == 5) {
        $month = get_string('may', 'chairman');
    }
    if ($monthAsNum == 6) {
        $month = get_string('june', 'chairman');
    }
    if ($monthAsNum == 7) {
        $month = get_string('july', 'chairman');
    }
    if ($monthAsNum == 8) {
        $month = get_string('august', 'chairman');
    }
    if ($monthAsNum == 9) {
        $month = get_string('september', 'chairman');
    }
    if ($monthAsNum == 10) {
        $month = get_string('october', 'chairman');
    }
    if ($monthAsNum == 11) {
        $month = get_string('november', 'chairman');
    }
    if ($monthAsNum == 12) {
        $month = get_string('december', 'chairman');
    }

    return $month;
}

/*
 * Pads an amount of minutes of time less than 10 to be 0X, ex: 4 = 04
 *
 * @param int $time An integer representing an amount of minutes.
 *
 */

function ZeroPaddingTime($time) {

    if (strlen($time) == 1) {
        return '0' . $time;
    }
    return $time;
}

/*
 * Turns a given amount of secounds into time, hh:mm
 *
 * @param int $secs An amount of seconds.
 */

function formatTime($secs) {
    $times = array(3600, 60, 1);
    $time = '';
    $tmp = '';
    for ($i = 0; $i < 2; $i++) {
        $tmp = floor($secs / $times[$i]);
        if ($tmp < 1) {
            $tmp = '00';
        } elseif ($tmp < 10) {
            $tmp = '0' . $tmp;
        }
        $time .= $tmp;
        if ($i < 1) {
            $time .= ':';
        }
        $secs = $secs % $times[$i];
    }
    return $time . " " . get_string('hours', 'chairman');
    ;
}

/*
 * Adds an element if $data is not null or empty
 *
 * @param object $mform Form Object
 * @param mixed $data Data to be put into object at a later time
 * @param string $html_id Unique html element identifier
 * @param string $string_label Label printed next to element
 */

function conditionally_add_static($mform, $data, $html_id, $string_label) {

    if (isset($data) && $data != "") {
        $mform->addElement('static', $html_id, $string_label, $data);
    }
}

function export_pdf_dialog($event_id, $agenda_id, $chairman_id, $cm_instance, $plain_pdf) {
    global $CFG, $DB;

    echo "<div id='pdf_export_dialog' title='" . get_string('export', 'chairman') . " PDF'>";
    echo "<form id='pdf_export_dialog_form' action='$CFG->wwwroot/mod/chairman/chairman_meetingagenda/util/pdf_script.php' >";
    echo "<table>";

    if (chairman_isadmin($chairman_id)) {
        //save checkbox
        echo "<tr>";
        echo "<td><b>" . get_string("save_pdf_local", 'chairman') . "</b></td>";
        echo "<td><b>" . "<input type='checkbox' id='local_save' name='local_save'/>" . "</b></td>";
        echo "</tr>";

        //save public/private
        echo "<tr>";
        echo "<td></td>";
        echo "<td>";
        echo "<select id='pdf_save_type' name='pdf_save_type'/>";
        echo "<option value='public'>" . get_string("public_export_pdf", 'chairman') . "</option>";
        echo "<option value='private'>" . get_string("private_export_pdf", 'chairman') . "</option>";
        echo "</select>";
        echo "</td>";
        echo "</tr>";
    }

    //export type
    echo "<tr>";
    echo "<td><b>" . get_string("export_pdf_type", 'chairman') . "</b></td>";
    echo "<td>";
    echo "<select id='export_pdf_type' name='export_pdf_type'/>";
    echo "<option value='download'>" . get_string("export_pdf_download", 'chairman') . "</option>";
    echo "<option id='email_export_option' value='email'>" . get_string("export_pdf_email", 'chairman') . "</option>";
    echo "</select>";
    echo "</td>";
    echo "</tr>";

    //email type
    echo "<tr>";
    echo "<td></td>";
    echo "<td>";
    echo "<select id='export_email_type' name='export_email_type'/>";
    echo "<option id='export_email_type_private' value='private'>" . get_string("export_pdf_email_private", 'chairman') . "</option>";
    echo "<option id='export_email_type_public' value='public'>" . get_string("export_pdf_email_public", 'chairman') . "</option>";
    echo "</select>";
    echo "</td>";
    echo "</tr>";

    echo "</table>";

    echo "<span style='font-size:x-small;font-style:italic;' id='export_pdf_form_info'></span>";

    require_once("$CFG->dirroot/mod/chairman/chairman_meetingagenda/util/pdf.php");
    $pdf = new pdf_creator($event_id, $agenda_id, $chairman_id, $cm_instance);
    $pdf->set_plain_pdf($plain_pdf);
    $context = get_context_instance(CONTEXT_MODULE, $chairman_id);


    $private_fileinfo = generate_pdf_fileinfo($context, 0, $pdf);
    $public_fileinfo = generate_pdf_fileinfo($context, 1, $pdf);

    $private_avaliable = 0;
    $public_avaliable = 0;

    if (file_exists_from_fileinfo($private_fileinfo))
        $private_avaliable = 1;

    if (file_exists_from_fileinfo($public_fileinfo))
        $public_avaliable = 1;

    //http://www.7tech.co.in/php/get-url-of-current-page-php-wordpress/
    //Not a fan of this, but couldn't find a better method than to redirect back...
    $current_page_URL = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on') ? 'https://' : 'http://';
    $current_page_URL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];

    echo "<input type='hidden' id='event_id' name='event_id' value='$event_id'/>";
    echo "<input type='hidden' id='plain_pdf' name='plain_pdf' value='$plain_pdf'/>";
    echo "<input type='hidden' id='return_url' name='return_url' value='" . $current_page_URL . ";'/>";

    echo "<input type='hidden' id='private_pdf_avaliable' name='private_pdf_avaliable' value='$private_avaliable' />";
    echo "<input type='hidden' id='public_pdf_avaliable' name='public_pdf_avaliable' value='$public_avaliable' />";

    echo "</form>";

    echo "</div>";
}

/**
 * Determines if the given $fileinfo corresponds to an existing file, and returns
 * true on exists, false otherwise. 
 * 
 * @param array $fileinfo
 */
function file_exists_from_fileinfo($fileinfo) {
    $file = file_from_fileinfo($fileinfo);
    return $file ? true : false;
}

/**
 * Retrieves a file provided a given fileinfo. If it doesn't exists,
 * the function returns false.
 * 
 * @param array $fileinfo
 */
function file_from_fileinfo($fileinfo) {
    $fs = get_file_storage();
    $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'], $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);
    return $file;
}

/*
 * Loads given content to a specified DIV.
 *
 * @param string $div The div to load content into
 * @param string $content The content to load.
 */

function load_content($div, $content) {

    print '<script type="text/javascript">';
    print 'document.getElementById(\'' . $div . '\').innerHTML=\'' . $content . "'";
    print '</script>';
}

/*
 * Runs a javascript fucntion with given parameters
 *
 * @param string $func The javascript function to call.
 * @param string $params The parameters of the javascript function delimted by ":"
 *
 */

function js_function($func, $params) {

    $parameters = explode(':', $params);

    $string = '<script type="text/javascript">';
    $string .= "$func(";

    foreach ($parameters as $parameter) {
        $string .= "'$parameter',";
    }

    $string = substr($string, 0, -2);
    $string .= '\');</script>';


    print $string;
}

/**
 * Generates the file information for an agenda/minute PDF export file (which are
 * located in the chairman module files).
 * 
 * @param object $context
 * @param string $save_security "private" or "public" to indicate whether the file is a private or public chairman file
 * @param pdf_creator $pdf
 */
function generate_pdf_fileinfo($context, $save_security, $pdf) {

    $private = ($save_security == 'private') ? "_private" : "";
    // Prepare file record object
    $fileinfo = array(
        'contextid' => $context->id, // ID of context
        'component' => 'mod_chairman',
        'filearea' => 'chairman' . $private,
        'itemid' => 0,
        'filepath' => "/" . $pdf->get_event_name() . "/", // any path beginning and ending in /
        'filename' => $pdf->get_title($pdf->is_plain_pdf())); // any filename

    return $fileinfo;
}

/**
 * Outputs the image used to open the export pdf dialog.
 */
function output_export_pdf_image() {
    print '<div id="chairman_save_pdf">';
    print '<input type="image" id="export_pdf_image" SRC="../pix/pdf_export.png" VALUE="Submit now"/></span>';
    print '</div>';
}

?>
