<?php
/**
 * Convert one or more gpx files to their equivalent track files: specify
 * either an alphabetic range or a hike name in the query string.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

$range = isset($_GET['range']) ? filter_input(INPUT_GET, 'range') : false;
$hike  = isset($_GET['hike'])  ? filter_input(INPUT_GET, 'hike')  : false;

if ($hike) {
    $hikeReq = "SELECT `indxNo`,`gpx` FROM `HIKES` WHERE `pgTitle`=?;";
    $c_data   = $pdo->prepare($hikeReq);
    $c_data->execute([$hike]);
    $conv_data = $c_data->fetch(PDO::FETCH_ASSOC);
    $hikeNo = $conv_data['indxNo'];
    $gpxField = $conv_data['gpx'];
    // gpx may be a comma-separated list
    $gpxfiles = explode(",", $gpxField);
    $mainfile = "../gpx/" . $gpxfiles[0];
    $gpxdat = simplexml_load_file($mainfile);
    if ($gpxdat === false) {
        throw new Exception(
            __FILE__ . "Line " . __LINE__ . "Could not load {$gpxfile} as " .
            "simplexml"
        );
    }
    if ($gpxdat->wpt->count() > 0) {
        $waypoints = [];
        foreach ($gpxdat->wpt as $waypt) {
            if (!empty($waypt->sym)) {
                $name = empty($waypt->name) ? "" : $waypt->name;
                $wpts = '{"lat":' . $waypt['lat'] . ',"lng":' . $waypt['lon'] .
                    ',"name":"' . $name . '","sym":"' . $waypt->sym . '"}';
                array_push($waypoints, $wpts);
            }
        }
        $allpts = '[' . implode(",", $waypoints) . '],';
    } else {
        $allpts = '[],';
    }
    $json_data = '{"wpts":' . $allpts; 
    $track_files = gpxLatLng($gpxdat, 1); // returns array of arrays
    $json_array = $track_files[0];
    $no_of_entries = count($json_array[0]); // lats, lngs, eles have same cnt
    $jdat = '"trk":[';   // array of objects
    for ($n=0; $n<$no_of_entries; $n++) {
        $jdat .= '{"lat":' . $json_array[0][$n] . ',"lng":' .
            $json_array[1][$n] . ',"ele":' . $json_array[2][$n] . '},';
    }
    $jdat = rtrim($jdat, ","); 
    $jdat .= ']}';
    $json_data .= $jdat;
    // now save the json file data for this track
    $basename = 'pmn' . $hikeNo . "_1.json";
    $jname = "../json/" . $basename;
    file_put_contents($jname, $json_data);
} elseif ($range) {

}