<?php
/**
 * This is a list of waypoint symbols currently supported
 * in nmhikes.com; Note that $icon_opts includes the initial
 * <select> tag but relies on the caller to supply the closing
 * tag. The default value was changed from googlemini to blue
 * flag to prevent confusion on the map with photo markers 
 * when users don't bother to set the symbol.
 * PHP version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$supported_syms = [
    'Flag, Blue' => 'Blue Flag',
    'Flag, Red' => 'Red Flag',
    'Flag, Yellow' => 'Yellow Flag',
    'Flag, Green' => 'Green Flag',
    'Trail Head' => 'Hiker',
    'Parking Area' => 'Parking',
    'Triangle, Red' => 'Red Triangle',
    'Triangle, Yellow' => 'Yellow Triangle',
    'Pin, Green' => 'Green Pin',
    'Pin, Red' => 'Red Pin',
    'Pin, Blue' => 'Blue Pin',
    'googlemini' => 'Google'
];
$icon_opts = '';
foreach ($supported_syms as $sym => $value) {
    $icon_opts .= '<option value="' . $sym . '">' . $value . '</option>';
}
$icon_opts .= '</select>';
$select_sym = '<select class="syms">' . $icon_opts;
$jsSymbols = json_encode(array_keys($supported_syms));
