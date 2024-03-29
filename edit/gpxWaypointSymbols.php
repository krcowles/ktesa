<?php
/**
 * This is a list of waypoint symbols currently supported
 * in nmhikes.com
 * PHP version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$supported_syms = [
    'googlemini' => '[Default] Google',
    'Flag, Red' => 'Red Flag',
    'Flag, Blue' => 'Blue Flag',
    'Flag, Yellow' => 'Yellow Flag',
    'Flag, Green' => 'Green Flag',
    'Trail Head' => 'Hiker',
    'Parking Area' => 'Parking',
    'Triangle, Red' => 'Red Triangle',
    'Triangle, Yellow' => 'Yellow Triangle',
    'Pin, Green' => 'Green Pin',
    'Pin, Red' => 'Red Pin',
    'Pin, Blue' => 'Blue Pin'
];
$icon_opts = '';
foreach ($supported_syms as $sym => $value) {
    $icon_opts .= '<option value="' . $sym . '">' . $value . '</option>';
}
$icon_opts .= '</select>';
$select_sym = '<select class="syms">' . $icon_opts;
$jsSymbols = json_encode(array_keys($supported_syms));
