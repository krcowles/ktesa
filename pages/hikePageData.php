<?php
/**
 * This script provides the data required by hikePageTemplate.php in order
 * to display an individual hike or cluster page. The hike data may come from
 * either the released HIKES tables, or those in-edit (EHIKES); When a cluster
 * page, the value of $clusterPage may be either 'y', or a positive or negative
 * number corresponding to the 'page' field in CLUSTERS [depending  on whether
 * the page-in-edit is currently published (positive) or not (negative)]. If the
 * script was invoked via a link from standard published page code, e.g. map
 * link, the $clusterPage will be 'y'.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */

// Delete all tmp map files older than threshold
$time = time() - 45; // seconds ago
$dir_iterator = new RecursiveDirectoryIterator(
    "../maps/tmp", RecursiveDirectoryIterator::SKIP_DOTS
);
$iterator = new RecursiveIteratorIterator(
    $dir_iterator, RecursiveIteratorIterator::SELF_FIRST
);
foreach ($iterator as $file) {
    if ($file->isFile()) {
        if ($file->getMTime() < $time) {
            $leaf = $iterator->getSubPathName();
            if ($leaf !== 'README') {
                unlink("../maps/tmp/" . $leaf);
            }
        }
    }
}

/**
 * Main data acquisition for populating HTML
 */
$tbl = filter_input(INPUT_GET, 'age');
$hikeIndexNo = filter_input(INPUT_GET, 'hikeIndx', FILTER_SANITIZE_NUMBER_INT);
$clusterPage = isset($_GET['clus']) ? filter_input(INPUT_GET, 'clus') : false;
// optional ascent/descent parameters
$distThreshParm = isset($_GET['distThreshParm']) ?
    filter_input(INPUT_GET, 'distThreshParm', FILTER_SANITIZE_NUMBER_INT) : false;
$elevThreshParm = isset($_GET['elevThreshParm']) ?
    filter_input(INPUT_GET, 'elevThreshParm', FILTER_SANITIZE_NUMBER_INT) : false;
$maWindowParm = isset($_GET['maWindowParm']) ?
    filter_input(INPUT_GET, 'maWindowParm', FILTER_SANITIZE_NUMBER_INT) : false;
$makeGpsvDebugParm = isset($_GET['makeGpxvDebug']) ?
    filter_input(INPUT_GET, 'makeGpsvDebugParm') : false;
$showAscDsc = isset($_GET['showAscDsc']) ?
    filter_input(INPUT_GET, 'showAscDsc') : false;

// assign tables based on whether published or in-edit
$ehikes = (isset($tbl) && $tbl === 'new') ? true : false;
if ($ehikes) {
    $htable = 'EHIKES';
    $rtable = 'EREFS';
    $gtable = 'EGPSDAT';
    $ttable = 'ETSV';
    $tbl    = 'new';
} else {
    $htable = 'HIKES';
    $rtable = 'REFS';
    $gtable = 'GPSDAT';
    $ttable = 'TSV';
    $tbl    = 'old';
}
$cluspg   = 'no'; // hidden <p> element => is/not cluster page, for js
$hikepage = true; // see multiMap.php

$basic = "SELECT * FROM {$htable} WHERE indxNo = :indxNo";
$basicPDO = $pdo->prepare($basic);
$basicPDO->execute(["indxNo" => $hikeIndexNo]);
$row = $basicPDO->fetch(PDO::FETCH_ASSOC);
$hikeTitle      = $row['pgTitle'];
$hikeLocale     = $row['locale'];
$hikeGroup      = $tbl === 'new' ? $row['cname'] : '';
$hikeType       = $row['logistics'];
$hikeLength     = $row['miles'] . " miles";
$hikeElevation  = $row['feet'] . " ft";
$hikeDifficulty = $row['diff'];
$hikeFacilities = $row['fac'];
$hikeWow        = $row['wow'];
$hikeSeasons    = $row['seasons'];
$hikeExposure   = $row['expo'];
/**
 * It is permitted to have more than one gpx file per hike (e.g. Knife's Edge)
 * Also, 'old style' hikes and cluster pages will have no files specified
 */
$files = []; // required by multiMap.php
$allgpx = $row['gpx'];
if (!empty($allgpx)) {
    $newstyle = true; 
    $files    = explode(",", $allgpx);
    $gpxfile  = $files[0];
    $gpxPath  = '../gpx/' . $gpxfile;
} else { // for 'old style' pages; cluster pages defined later...
    $newstyle = false;
    $gpxfile  = '';
    $gpxPath  = '';
}
$jsonFile = $row['trk'];
// Pages with old Flickr photos
$hikePhotoLink1 = $row['purl1'];
$hikePhotoLink2 = $row['purl2'];
$photoAlbum = '<br />';
if (!empty($row['purl1'])) {
    $photoAlbum = '<p id="albums">For improved photo viewing,<br />check out
        the following album(s):</p>';
    $photoAlbum .= '<p id="alnks"><a href="' . $row['purl1']
        . '" target="_blank">Photo Album Link</a>';
    if (!empty($row['purl2'])) {
        $photoAlbum .= '<br /><a href="' . $row['purl2']
            .'" target="_blank">Additional Album Link</a></p>';
    }
}
$hikeDirections = $row['dirs'];
$hikeTips       = $row['tips'];
$hikeInfo       = $row['info'];
$hikeEThresh    = $row['eThresh'];
$hikeDThresh    = $row['dThresh'];
$hikeMaWin      = $row['maWin'];
$displayAscDsc  = ($showAscDsc == true) || is_numeric($hikeEThresh) ? true : false;

/**
 * For Cluster Pages only: find all the hikes in this cluster and extract the
 * data required to display each hike's corresponding info in the side panel.
 * Also, since there are no gpx files listed in [E]HIKES for a Cluster Page,
 * the $files array (used in multiMap.php) is populated here for map creation.
 * Note that the GPSV map 'tracklist' displays the TRACK name found in the gpx
 * file, not the gpx FILE name. For that reason, a javascript object is formed
 * which correlates track name with gpx file. Thus, when a track is chosen
 * for display on the map, its corresponding gpx file data will populate the
 * side panel.
 */
if ($clusterPage) {
    $cluspg = 'yes'; 
    $newstyle = true;
    // get cluster id
    $clusPgReq= "SELECT `clusid`,`lat`,`lng` FROM `CLUSTERS` WHERE `group`=?;";
    $clusPg = $pdo->prepare($clusPgReq);
    $clusPg->execute([$hikeTitle]);
    $cpdata = $clusPg->fetch(PDO::FETCH_ASSOC);
    // Use NM state midpoint so that a map can be drawn
    if (empty($cpdata['lat'])) {
        $cpdata['lat'] = 34.450;
    }
    if (empty($cpdata['lng'])) {
        $cpdata['lng'] = -106.042;
    }

    /**
     * NOTE: Only show published hikes as there may also be an in-edit
     * version of the hike, and the complications managing hikes-in-edit
     * along with published hikes seems high effort with low return. In
     * addition, new hikes may not yet have gpx files, complicating the 
     * formation of $files for multiMap.php
     */
    $chikesReq = "SELECT `indxNo` FROM `CLUSHIKES` WHERE `cluster`=? AND `pub`='Y';";
    $chikes = $pdo->prepare($chikesReq);
    $chikes->execute([$cpdata['clusid']]);
    $clushikes = $chikes->fetchAll(PDO::FETCH_COLUMN);
    if (count($clushikes) > 0) {
        $hike_data = []; // array to collect info for javascript
        foreach ($clushikes as $hike) {
            // Get the side panel data for this $hike ('indxNo')
            $hikeReq = "SELECT `logistics`,`miles`,`feet`,`diff`,`wow`," .
                "`seasons`,`expo`,`gpx` FROM `HIKES` WHERE `indxNo`=?;";
            $hikedat = $pdo->prepare($hikeReq);
            $hikedat->execute([$hike]);
            $sidepnl = $hikedat->fetch(PDO::FETCH_ASSOC);
            $gpxfnames = array_pop($sidepnl);
            // there may be multiple files associated with this hike
            $filelist = explode(",", $gpxfnames);
            // associate each trackname in any file with its corresponding gpx file
            foreach ($filelist as $gpx) {
                array_push($files, $gpx);
                $contents = simplexml_load_file("../gpx/" . $gpx);
                // there may be more than one track in a file (e.g. Black Canyon)
                $noOfTrks = $contents->trk->count();
                for ($j=0; $j<$noOfTrks; $j++) {
                    $tname = $contents->trk[$j]->name->__toString();
                    $trkrel = array($tname => $sidepnl);
                    $hike_data += $trkrel;  // "+" is union operator for arrays
                }
            }
        }
        $sidePanelData = json_encode($hike_data);
    } else { // no published hikes for this group yet
        // hence, no gpx file. Pseudo file created to allow display of map:
        $ctrlat = $cpdata['lat']/LOC_SCALE;
        $ctrlng = $cpdata['lng']/LOC_SCALE;
        $pseudo = simplexml_load_file("../build/pseudo.gpx");
        $y = $pseudo->trk->trkseg[0];
        $y->trkpt[0]['lat'] = $ctrlat;
        $y->trkpt[0]['lon'] = $ctrlng;
        $y->trkpt[1]['lat'] = $ctrlat + .004507;
        $y->trkpt[1]['lon'] = $ctrlng;
        $y->trkpt[2]['lat'] = $ctrlat - .004507;
        $y->trkpt[2]['lon'] = $ctrlng;
        $y->trkpt[3]['lat'] = $ctrlat;
        $y->trkpt[3]['lon'] = $ctrlng;
        $y->trkpt[4]['lat'] = $ctrlat;
        $y->trkpt[4]['lon'] = $ctrlng - .005477;
        $y->trkpt[5]['lat'] = $ctrlat;
        $y->trkpt[5]['lon'] = $ctrlng + .005477;
        $pseudo->asXML("../gpx/filler.gpx");
        $gpxfile = "filler.gpx";
        $files = [$gpxfile];
    }
}
require "relatedInfo.php";
/**
 * This section collects the information from TSV/ETSV table needed
 * to build the picture rows a hike page
 */
if (!$clusterPage) {
    $photos = "SELECT `folder`,`title`,`hpg`,`mpg`,`desc`,`thumb`,`alblnk`,`date`," .
    "`mid`,`imgHt`,`imgWd` FROM {$ttable} WHERE `indxNo` = :indxNo;";
    $photosPDO = $pdo->prepare($photos);
    $photosPDO->execute(["indxNo" =>$hikeIndexNo]);
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
            $fbase = $pics['mid'] . "_" . $pics['thumb'];
            array_push($piclnks, $fbase);
            $pDesc = htmlspecialchars($pics['desc']);
            $dateStr = $pics['date'];
            if ($dateStr == '') {
                array_push($captions, $pDesc);
            } else {
                $year = substr($dateStr, 0, 4);
                $month = intval(substr($dateStr, 5, 2));
                $day = intval(substr($dateStr, 8, 2)); // intval strips leading 0
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
     * sub-directory. The file is deleted after loading the page.
     */
    $extLoc = strrpos($gpxfile, '.');
    $gpsvMap = substr($gpxfile, 0, $extLoc); // strip file extension
    $date = date_create();
    $date_str = date_format($date, 'YmdHisu');
    $tmpMap = "../maps/tmp/" . "_" . $gpsvMap . "_" . $date_str . ".php";
    if (($mapHandle = fopen($tmpMap, "w")) === false) {
        $mapmsg = "Contact Site Master: could not open tmp map file: " .
            $tmpMap . ", for writing";
        throw new Exception($mapmsg);
    }
    $fpLnk = "../maps/fullPgMapLink.php?hike={$hikeTitle}" .
        "&hno={$hikeIndexNo}&tbl={$tbl}";
    if ($clusterPage) {
        $fpLnk .= "&clus=y";
        foreach ($files as $gpx) {
            $fpLnk .= "&gpx[]={$gpx}";
        }
    } else {
        $fpLnk .= "&gpx={$gpxfile}";
    }
        
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
        'tracklist_options' => 'true',
        'marker_list_options' => 'false',
        'show_markers' => 'true',
        'dynamicMarker' => 'true'  
    ];
    /**
     * Set smoothing parameter values per the following hierarchy:
     *  from query string
     *  hike-specific value from database,
     *  default value defined here.
    */
    if ($elevThreshParm) { // threshold (meters) for elevation smoothing
        $elevThresh = $elevThreshParm;
    } else {
        $elevThresh = isset($hikeEThresh) ? $hikeEThresh : 1;
    }
    if ($distThreshParm) { // threshold (meters) for distance smoothing
        $distThresh = $distThreshParm;
    } else {
        $distThresh = isset($hikeDThresh) ? $hikeDThresh : 1;
    }
    if ($maWindowParm) { // moving average window size for elevation smoothing
        $maWindow = $maWindowParm;
    } else {
        $maWindow = isset($hikeMaWin) ? $hikeMaWin : 1;
    }
    if ($makeGpsvDebugParm) {
        $makeGpsvDebug = $makeGpsvDebug === "true" ? true : false;
    } else {
        $makeGpsvDebug = false;
    }

    // Open debug files with headers, if requested by query string
    $handleDfa = null;
    $handleDfc = null;
    if ($makeGpsvDebug) {
        $handleDfa = gpsvDebugFileArray($gpxPath);
        $handleDfc = gpsvDebugComputeArray($gpxPath);
    }
    
    include '../php/multiMap.php';
  
    // this is the html for the map: precede it with cache-control:
    $php  = "<?php header('Cache-Control: max-age=0'); ?>" . $maphtml;
    fputs($mapHandle, $php);
    fclose($mapHandle);
}
