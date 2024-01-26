<?php
/**
 * This script is invoked via correctableFault.js (ajax) and will replace
 * unsupported gpx waypoint symbols in an uploaded gpx file with supported
 * symbols. The script is invoked on a symbol by symbol basis.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to data
 */
$file_name = filter_input(INPUT_POST, 'syminput');
$wayptname = filter_input(INPUT_POST, 'wptname');
$unsupptd  = filter_input(INPUT_POST, 'symfault');
$replacer  = filter_input(INPUT_POST, 'replacer');

$wptFile = simplexml_load_file($file_name);
foreach ($wptFile->wpt as $wpt) {
    $tag = $wpt->name->__toString();
    $sym = $wpt->sym->__toString();
    if ($tag === $wayptname && $sym === $unsupptd) {
        $wpt->sym = $replacer;
        $wptFile->asXML($file_name);
        break;
    }
}

echo "OK";