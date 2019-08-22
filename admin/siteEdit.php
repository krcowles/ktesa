<?php
/**
 * This module changes the state between 'Editing Allowed' and 'No Edites Allowed'
 * for the site on which it is invoked (e.g. test site, main site, etc)
 * PHP Version 7.1
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

$current = filter_input(INPUT_GET, 'button');
$modeSettings = 'mode_settings.php';

if (strpos($current, 'Editing Allowed') !== false) {
    $modes = file_get_contents($modeSettings);
    $newMode = str_replace("editing = 'yes'", "editing = 'no'", $modes);
    file_put_contents($modeSettings, $newMode);
    echo "No Editing Mode";
} else {
    $modes = file_get_contents($modeSettings);
    $newMode = str_replace("editing = 'no'", "editing = 'yes'", $modes);
    file_put_contents($modeSettings, $newMode);
    echo "Editing Allowed";
}
