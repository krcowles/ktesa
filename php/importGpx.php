<?php
/**
 * This script will provide ajax data to import a gpx file and display
 * its track onto the leaflet Offline Map selection grid. It is constructed
 * to return data comparable to that produced by importHike.php so that
 * the same ajax processing can be applied.
 *
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

/**
 * Validate the gpx file
 */
if ($_FILES['gpxfile']['error'] !== UPLOAD_ERR_OK) {
    echo "Upload";
    exit;
}
$combined_data = [];    
$user_gpx = $_FILES['gpxfile']['name'];
$tmpfile  = $_FILES['gpxfile']['tmp_name'];
$extension = strToLower(pathinfo($user_gpx, PATHINFO_EXTENSION));
if ($extension !== 'gpx') {
    echo "Extension";
    exit;
}
// editFunctions.php relies on the 'alerts' session var to transmit errors
$_SESSION['alerts'][0] = '';
$dom = new DOMDocument;
if (!$dom->load($tmpfile)) {
    displayGpxUserAlert($tmpfile, 0);
    echo $_SESSION['alerts'][0];
    exit;
}
if (!$dom->schemaValidate(
    "http://www.topografix.com/GPX/1/1/gpx.xsd", LIBXML_SCHEMA_CREATE
)
) {
    displayGpxUserAlert($filename, 0);
    echo $_SESSION['alerts'][0];
    exit;
}
// passed validation
$xml = simplexml_load_file($tmpfile);
if ($xml === false) {
    echo "SimpleXML load fail";
    exit;
}
$trk_cnt = $xml->trk->count();
$trk_data = gpxLatLng($xml, $trk_cnt, true);
$latlngbounds = array_pop($trk_data);
$poly = [];
$alltracks = [];
// reorganize for polylines; there may be multiple tracks...
foreach ($trk_data as $track) {
    for ($j=0; $j<count($track[0]); $j++) { // [0], [1] same length
        $llpair = [$track[0][$j], $track[1][$j]]; 
        array_push($poly, $llpair);
    }
    array_push($alltracks, $poly);
}

$bounds = explode(",", $latlngbounds);
$nw = [$bounds[0], $bounds[3]]; 
$se = [$bounds[1], $bounds[2]]; 

array_push($combined_data, $nw);
array_push($combined_data, $se);
$ctrLat = $bounds[1] + ($bounds[0] - $bounds[1])/2;
$ctrLng = $bounds[2] + ($bounds[3] - $bounds[2])/2;
$map_center = [$ctrLat, $ctrLng];
array_push($combined_data, $map_center);
array_push($combined_data, $alltracks);
$return_data = json_encode($combined_data);
echo $return_data;
