<?php
/**
 * This script will provide ajax data to import a site hike area onto
 * the leaflet Offline Map selection grid. Note that the return encoded
 * string includes
 *   1: the nw corner lat/lng
 *   2: the se corner lat/lng
 *   3: the map center
 *   4: one or more tracks
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
$import = filter_input(INPUT_POST, 'hike');
$polylines = []; // holds bounds corners, map center, and data for polylines

// get indxNo for this hike...
$getHikeNoReq = "SELECT `indxNo`,`bounds` FROM `HIKES` WHERE `pgTitle`=?;";
$getHike = $pdo->prepare($getHikeNoReq);
$getHike->execute([$import]);
$hikeInfo = $getHike->fetch(PDO::FETCH_ASSOC);
$hikeNo = $hikeInfo['indxNo'];
$hikeBounds = $hikeInfo['bounds'];
// Note: any incorrect bounds data must be corrected in the database
$bounds = explode(",", $hikeBounds);
$nw = [$bounds[0], $bounds[3]]; // result 0
$se = [$bounds[1], $bounds[2]]; // result 1
array_push($polylines, $nw);
array_push($polylines, $se);
$ctrLat = $bounds[1] + ($bounds[0] - $bounds[1])/2;
$ctrLng = $bounds[2] + ($bounds[3] - $bounds[2])/2;
$map_center = [$ctrLat, $ctrLng];
array_push($polylines, $map_center); // result 2

$json = getTrackFileNames($pdo, $hikeNo, 'pub');
/**
 * There are three entries in $json[0]: 1) array of all track names;
 * 2) all track names as a string list; and 3) the name of the main
 * track file. The array [1st item] is processed below to read each
 * track name and translate it into a leaflet polyline. Each polyline
 * is a separate array and is pushed on to the return array ($polylines).
 * $polylines already holds the lat/lngs for the nw & se corners, and
 * the map's center.
 */
$tracklist = $json[0];
foreach ($tracklist as $track) {
    $json = "../json/{$track}";
    $track_file = file_get_contents($json);
    $track_data = json_decode($track_file);
    $trkptr = $track_data->trk;
    $polyline = [];
    // convert json into polyline data format
    foreach ($trkptr as $loc) {
        $point = [$loc->lat, $loc->lng]; 
        array_push($polyline, $point);
    }
    array_push($polylines, [$polyline]); // result 3...n
}
$track_data = json_encode($polylines);
//file_put_contents("poly.txt", $track_data);
// return all data to js for storage in indexedDB
echo $track_data;
