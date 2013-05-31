<?php

require_once('../../config.php');
require_once('lib.php');
require_once('lib_chairman.php');

global $CFG, $PAGE, $OUTPUT;

$id = optional_param('id',0,PARAM_INT);    // Course Module ID

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->js('/mod/chairman/jquery/plugins/multipane_transfer/js/jquery.oohoo.multipaneTransfer.js');
$PAGE->requires->css('/mod/chairman/jquery/plugins/multipane_transfer/css/jquery.oohoo.multipaneTransfer.css');

// print header
chairman_check($id);
chairman_header($id,'members','view.php?id='.$id);

echo <<<BAM


<select multiple name="multipane_transfer" id="multipane_transfer">
  <optgroup value="1" label="Swedish Cars">
    <option>A</option>
    <option>B</option>
  </optgroup>
<optgroup value="2" label="German Cars">
    <option>Mercedes</option>
    <option>Audi</option>
  </optgroup>
<optgroup value="3" label="Dustin's Cars">
    <option>Bike</option>
    <option>Wheel On A Stick</option>
  </optgroup>


<optgroup value="4" label="Dustin's Cars2"/>

</select>


BAM;


echo <<<AAAA
    <script>
        window.onload = function(){
         $("#multipane_transfer").multipanetransfer();

    $( "ul.droptrue" ).sortable({
      connectWith: "ul"
    });

         }
        </script>;
AAAA;
chairman_footer();


?>
