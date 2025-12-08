<?php
/**
 * This script is invoked when a user routine encounters an ajax error
 * in production mode. The admin is notified of the error and its code.
 * Because of the number of ajax calls, the message construction has
 * many options.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$gpxname = filter_input(INPUT_POST, 'name');
$fname = "{$gpxname}.gpx";
$gpxdata = $_POST['linedata'];
$trkpts = json_decode($gpxdata, true); // aray of arrays
$newgpx = simplexml_load_file("../pages/gpxHeader.GPX");
$track = $newgpx->addChild('trk');
$trkname = $track->addChild('name', $gpxname);
$track_segment = $track->addChild('trkseg');
foreach ($trkpts as $trkpt) {
    $gpxpt = $track_segment->addChild('trkpt');
    $gpxpt->addAttribute('lat', $trkpt['lat']);
    $gpxpt->addAttribute('lon', $trkpt['lng']);
}
$string = $newgpx->asXML();
$dom = new DOMDocument('1.0');
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->loadXML($string);
$domout = $dom->saveXML();
echo $domout;
