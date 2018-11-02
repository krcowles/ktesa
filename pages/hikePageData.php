<?php
/**
 * This script provides the data required by hikePageTemplate.php in order
 * to display an individual hike page.
 * PHP 7.0
 * 
 * @package Display_Page
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../mysql/dbFunctions.php";
set_exception_handler('default_exceptions');
$pdo = dbConnect(__FILE__, __LINE__);

$tbl = filter_input(INPUT_GET, 'age');
/**
 * The variable $hikeIndexNo is established below and is used throughout
 * to locate data corresponding to this unique hike identifier.
 */
$hikeIndexNo = filter_input(INPUT_GET, 'hikeIndx', FILTER_SANITIZE_NUMBER_INT);
$distThreshParm = filter_input(INPUT_GET, 'distThreshParm', FILTER_SANITIZE_NUMBER_INT);
$elevThreshParm = filter_input(INPUT_GET, 'elevThreshParm', FILTER_SANITIZE_NUMBER_INT);
$maWindowParm = filter_input(INPUT_GET, 'maWindowParm', FILTER_SANITIZE_NUMBER_INT);
$makeGpsvDebugParm = filter_input(INPUT_GET, 'makeGpsvDebugParm');
$showAscDsc = filter_input(INPUT_GET, 'showAscDsc');
$ehikes = (isset($tbl) && $tbl === 'new') ? true : false;
/**
 * The following variables are used to define the tables to be used
 * in the MySQL queries, based on whether or not in-edit hike data
 * is required.
 */
if ($ehikes) {
    $htable = 'EHIKES';
    $rtable = 'EREFS';
    $gtable = 'EGPSDAT';
    $ttable = 'ETSV';
    $tbl = 'new';
} else {
    $htable = 'HIKES';
    $rtable = 'REFS';
    $gtable = 'GPSDAT';
    $ttable = 'TSV';
    $tbl = 'old';
}
// Form the queries for extracting data from HIKES/EHIKES and TSV/ETSV
$basic = "SELECT * FROM {$htable} WHERE indxNo = :indxNo";
$basicPDO = $pdo->prepare($basic);
$photos = "SELECT folder,title,hpg,mpg,`desc`,lat,lng,thumb,alblnk,date," .
        "mid,imgHt,imgWd FROM {$ttable} WHERE indxNo = :indxNo;";
$photosPDO = $pdo->prepare($photos);
// Execute the transactions:
$pdo->beginTransaction();
$basicPDO->bindValue(':indxNo', $hikeIndexNo);
$basicPDO->execute();
$photosPDO->bindValue("indxNo", $hikeIndexNo);
$photosPDO->execute();
$pdo->commit();
/**
 * This section will extract the data from HIKES/EHIKES table used to fill the
 * basic hike page template.
 */
$row = $basicPDO->fetch(PDO::FETCH_ASSOC);
$hikeTitle = $row['pgTitle'];
$hikeLocale = $row['locale'];
$hikeGroup = $row['cgroup'];
$hikeType = $row['logistics'];
$hikeLength = $row['miles'] . " miles";
$hikeElevation = $row['feet'] . " ft";
$hikeDifficulty = $row['diff'];
$hikeFacilities = $row['fac'];
$hikeWow = $row['wow'];
$hikeSeasons = $row['seasons'];
$hikeExposure = $row['expo'];
$gpxfile = $row['gpx'];
$jsonFile = $row['trk'];
if ($row['aoimg1'] == '') {
    $hikeAddonImg1 = '';
} else {
    $hikeAddonImg1 = unserialize($row['aoimg1']);
}
if ($row['aoimg2'] == '') {
    $hikeAddonImg2 = '';
} else {
    $hikeAddonImg2 = unserialize($row['aoimg2']);
}
$hikePhotoLink1 = $row['purl1'];
$hikePhotoLink2 = $row['purl2'];
$hikeDirections = $row['dirs'];
$hikeTips = $row['tips'];
$hikeInfo = $row['info'];
$hikeEThresh = $row['eThresh'];
$hikeDThresh = $row['dThresh'];
$hikeMaWin = $row['maWin'];
if ($gpxfile == '') {
    $newstyle = false;
    $gpxPath = '';
} else {
    $newstyle = true;
    $gpxPath = '../gpx/' . $gpxfile;
}
/**
 * This section collects the information from TSV/ETSV table needed
 * to build the picture rows...
 */
$photosData = $photosPDO->fetchAll(PDO::FETCH_ASSOC);
$months = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug",
    "Sep","Oct","Nov","Dec");
$descs = [];
$alblnks = [];
$piclnks = [];
$captions = [];
$aspects = [];
$widths = [];
foreach ($photosData as $pics) {
    if ($pics['hpg'] === 'Y') {
        array_push($descs, $pics['title']);
        array_push($alblnks, $pics['alblnk']);
        array_push($piclnks, $pics['mid']);
        $pDesc = htmlspecialchars($pics['desc']);
        $dateStr = $pics['date'];
        if ($dateStr == '') {
            array_push($captions, $pDesc);
        } else {
            $year = substr($dateStr, 0, 4);
            $month = intval(substr($dateStr, 5, 2));
            $day = intval(substr($dateStr, 8, 2));  # intval strips leading 0
            $date = $months[$month-1] . ' ' . $day . ', ' . $year .
                    ': ' . $pDesc;
            array_push($captions, $date);
        }
            $ht = intval($pics['imgHt']);
            $wd = intval($pics['imgWd']);
            array_push($widths, $wd);
            $picRatio = $wd/$ht;
            array_push($aspects, $picRatio);
    }
}
$capCnt = count($descs);
// if there are additional images (non-captioned), process them here:
if (is_array($hikeAddonImg1)) {
    $aoimg1 = '../images/' . $hikeAddonImg1[0];
    array_push($descs, $hikeAddonImg1[0]);
    array_push($alblnks, '');
    array_push($piclnks, $aoimg1);
    array_push($captions, '');
    $ht = $hikeAddonImg1[1];
    $wd = $hikeAddonImg1[2];
    array_push($widths, $wd);
    $imgRatio = $wd/$ht;
    array_push($aspects, $imgRatio);
}
if (is_array($hikeAddonImg2)) {
    $aoimg2 = '../images/' . $hikeAddonImg2[0];
    array_push($descs, $hikeAddonImg2[0]);
    array_push($alblnks, '');
    array_push($piclnks, $aoimg2);
    array_push($captions, '');
    $ht = $hikeAddonImg2[1];
    $wd = $hikeAddonImg2[2];
    array_push($widths, $wd);
    $imgRatio = $wd/$ht;
    array_push($aspects, $imgRatio);
}
/**
 * There are two possible types of hike page displays. If the hike page
 * has a map and elevation chart to display, the variable $newstyle is
 * true, and these items are displayed.  Otherwise, a page with a hike
 * summary table is presented with photos and information, but no map or
 * elevation chart ($newstyle is false).
 */
if ($newstyle) {
    /**
     * In the case of hike map and elevation chart, in order for the map to be
     * displayed in an iframe, a file is created and stored in the maps/tmp
     * sub-directory. The file is deleted upon exiting the page.
     */
    $extLoc = strrpos($gpxfile, '.');
    $gpsvMap = substr($gpxfile, 0, $extLoc); // strip file extension
    $tmpMap = "../maps/tmp/{$gpsvMap}.html";
    if (($mapHandle = fopen($tmpMap, "w")) === false) {
        $mapmsg = "Contact Site Master: could not open tmp map file: " .
            $tmpMap . ", for writing";
        die($mapmsg);
    }
    $fpLnk = "../maps/fullPgMapLink.php?maptype=page&hike={$hikeTitle}" .
        "&gpx={$gpxPath}&hno={$hikeIndexNo}&tbl={$tbl}";
    $map_opts = [
        'show_geoloc' => 'true',
        'zoom' => 'auto',
        'map_type' => 'ARCGIS_TOPO_WORLD',
        'street_view'=> 'false',
        'zoom_control' => 'large',
        'map_type_control' => 'menu',
        'center_coordinates' => 'true',
        'measurement_tools' => 'false',
        'utilities_menu' => "{ 'maptype':true, 'opacity':true, " .
            "'measure':true, 'export':true }",
        'tracklist_options' => 'false',
        'marker_list_options' => 'false',
        'show_markers' => 'true',
        'dynamicMarker' => 'true'  
    ];
    include "../php/makeGpsv.php";
    fputs($mapHandle, $maphtml);
    fclose($mapHandle);
}
