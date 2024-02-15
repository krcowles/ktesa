<?php
/**
 * This script downloads a gpx file formed from the corresponding
 * json track file(s).
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$name = filter_input(INPUT_POST, 'name');
$json = filter_input(INPUT_POST, 'json_files');
$json_files = explode(",", $json);
$newgpx = simplexml_load_file("gpxHeader.GPX");
foreach ($json_files as $track) {
    $encoded = file_get_contents('../json/' . $track);
    $track_data = json_decode($encoded, true);
    $trk = $track_data['trk'];
    // add xml elements
    $track = $newgpx->addChild('trk');
    $track_segment = $track->addChild('trkseg');
    foreach ($trk as $pt) {
        $trackpt = $track_segment->addChild('trkpt');
        $trackpt->addAttribute('lat', $pt['lat']);
        $trackpt->addAttribute('lon', $pt['lng']);
        $trackpt->addChild('ele', $pt['ele']);
    }
}
$string = $newgpx->asXML();
$dom = new DOMDocument('1.0');
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->loadXML($string);
$domout = $dom->saveXML();
file_put_contents($name, $domout);
echo "OK";
