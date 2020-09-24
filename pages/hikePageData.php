<?php
/**
 * This script provides the data required by hikePageTemplate.php in order
 * to display an individual hike page. The hike data may come from either
 * the released HIKES table, or those in-edit (EHIKES);
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require_once "../accounts/getLogin.php";

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

$tbl = filter_input(INPUT_GET, 'age');
$hikeIndexNo = filter_input(INPUT_GET, 'hikeIndx', FILTER_SANITIZE_NUMBER_INT);
$distThreshParm = filter_input(INPUT_GET, 'distThreshParm', FILTER_SANITIZE_NUMBER_INT);
$elevThreshParm = filter_input(INPUT_GET, 'elevThreshParm', FILTER_SANITIZE_NUMBER_INT);
$maWindowParm = filter_input(INPUT_GET, 'maWindowParm', FILTER_SANITIZE_NUMBER_INT);
$makeGpsvDebugParm = filter_input(INPUT_GET, 'makeGpsvDebugParm');
$showAscDsc = filter_input(INPUT_GET, 'showAscDsc');
$ehikes = (isset($tbl) && $tbl === 'new') ? true : false;
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
$hikepage = true;
// Form the queries for extracting data from HIKES/EHIKES and TSV/ETSV
$basic = "SELECT * FROM {$htable} WHERE indxNo = :indxNo";
$basicPDO = $pdo->prepare($basic);
$photos = "SELECT folder,title,hpg,mpg,`desc`,thumb,alblnk,date," .
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
$hikeGroup = $row['cgroup']; // now obsolete
$hikeCluster = $row['cname'];
$hikeType = $row['logistics'];
$hikeLength = $row['miles'] . " miles";
$hikeElevation = $row['feet'] . " ft";
$hikeDifficulty = $row['diff'];
$hikeFacilities = $row['fac'];
$hikeWow = $row['wow'];
$hikeSeasons = $row['seasons'];
$hikeExposure = $row['expo'];
// There may be multiple gpx files in the 'gpx' field
$files = []; // required by multiMap.php
$allgpx = $row['gpx'];
if (!empty($allgpx)) {
    $newstyle = true;
    $gpxfiles = explode(",", $allgpx);
    $gpxfile = $gpxfiles[0];
    $gpxPath = '../gpx/' . $gpxfile;
    for ($j=0; $j<count($gpxfiles); $j++) {
        array_push($files, $gpxfiles[$j]);
    }
} else {
    $newstyle = false;
    $gpxfile = '';
    $gpxPath = '';
    $gpxfiles = [];
}
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
$hikeTips = $row['tips'];
$hikeInfo = $row['info'];
$hikeEThresh = $row['eThresh'];
$hikeDThresh = $row['dThresh'];
$hikeMaWin = $row['maWin'];
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
// if there are additional images (non-captioned), process them here:
if (is_array($hikeAddonImg1)) {
    $aoimg1 = $hikeAddonImg1[0];
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
    $aoimg2 = $hikeAddonImg2[0];
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
        "&gpx={$gpxfile}&hno={$hikeIndexNo}&tbl={$tbl}";
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
    if (isset($elevThreshParm)) { // threshold (meters) for elevation smoothing
        $elevThresh = $elevThreshParm;
    } else {
        $elevThresh = isset($hikeEThresh) ? $hikeEThresh : 1;
    }
    if (isset($distThreshParm)) { // threshold (meters) for distance smoothing
        $distThresh = $distThreshParm;
    } else {
        $distThresh = isset($hikeDThresh) ? $hikeDThresh : 1;
    }
    if (isset($maWindowParm)) { // moving average window size for elevation smoothing
        $maWindow = $maWindowParm;
    } else {
        $maWindow = isset($hikeMaWin) ? $hikeMaWin : 1;
    }

    // Set debug output parameter based on the URL param established in hikePageData.php
    if (isset($makeGpsvDebugParm)) {
        $makeGpsvDebug = "true" ? true : false;
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
