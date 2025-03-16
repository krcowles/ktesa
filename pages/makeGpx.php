<?php
/**
 * This script downloads a gpx file formed from the corresponding json
 * track file(s), and includes waypoints originally in the file if any.
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

$hike = filter_input(INPUT_POST, 'id');
$name = filter_input(INPUT_POST, 'name');
$json = filter_input(INPUT_POST, 'json_files');

$dotpos = strrpos($name, ".");
$lgth = strlen($name) - strlen(substr($name, $dotpos));
$track_name = substr($name, 0, $lgth);

// Any waypoints originally in the gpx file?
$requestWPTS = "SELECT * FROM `WAYPTS` WHERE `type`='gpx' AND `indxNo`={$hike};";
$waypts = $pdo->query($requestWPTS)->fetchAll(PDO::FETCH_ASSOC);
$hasWaypts = count($waypts) > 0 ? true : false;
$json_files = explode(",", $json);
$newgpx = simplexml_load_file("gpxHeader.GPX");
if ($hasWaypts) {
    foreach ($waypts as $wpt) {
        $lat = (float) $wpt['lat']/LOC_SCALE;
        $lng = (float) $wpt['lng']/LOC_SCALE;
        $way = $newgpx->addChild('wpt');
        $way->addAttribute('lat', $lat);
        $way->addAttribute('lon', $lng);
        $way->addChild('name', $wpt['name']);
        $way->addChild('sym', $wpt['sym']);
    }
}
foreach ($json_files as $track) {
    $encoded = file_get_contents('../json/' . $track);
    $track_data = json_decode($encoded, true);
    $trk = $track_data['trk'];
    // add xml elements
    $track = $newgpx->addChild('trk');
    $trkname = $track->addChild('name', $track_name);
    $track_segment = $track->addChild('trkseg');
    foreach ($trk as $pt) {
        $meters = round($pt['ele']/3.28084, 2);
        $trackpt = $track_segment->addChild('trkpt');
        $trackpt->addAttribute('lat', $pt['lat']);
        $trackpt->addAttribute('lon', $pt['lng']);
        $trackpt->addChild('ele', $meters);
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
