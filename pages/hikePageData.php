<?php
/**
 * This script provides the data required by hikePageTemplate.php in order
 * to display an individual hike or cluster page. The hike data may come from
 * either the released HIKES tables, or those in-edit (EHIKES); When a cluster
 * page, the value of $clusterPage may be either 'y', or a positive or negative
 * number corresponding to the 'page' field in CLUSTERS [depending on whether
 * the page-in-edit is currently published (positive) or not (negative)]. If the
 * script was invoked via a link from standard published page code, e.g. map
 * link, the $clusterPage will be 'y'.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
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
 * Data for displaying on hikePageTemplate.php
 */
$tbl = filter_input(INPUT_GET, 'age');
$hikeIndexNo = filter_input(INPUT_GET, 'hikeIndx', FILTER_SANITIZE_NUMBER_INT);
$clusterPage = isset($_GET['clus']) ? filter_input(INPUT_GET, 'clus') : false;

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
if (empty($row)) {
    throw new Exception("Hike index {$hikeIndexNo} not found");
}
$files          = []; // required by multiMap.php
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

$infoHd         = $clusterPage ? 'area:' : 'hike:';
$hikeDirections = $row['dirs'];
$hikeTips       = $row['tips'];
$hikeInfo       = "<span id='ihd'>About this {$infoHd}</span><br />" . $row['info'];
if ($tbl === 'old') {
    $hikedLast  = $row['last_hiked'];
} else {
    $hikedLast  = false;
}

$asc = 0;
$dsc = 0;
/**
 * It is permitted to have more than one gpx/track file per hike (e.g. Knife's Edge);
 * The main gpx filename is $gpxfile, and $hike_tracks is an array of all the hike's
 * uploaded tracknames [as json files]. $hike_tracks are physically located in
 * '../json'.
 */
if (empty($row['gpx'])) {
    if (empty($row['lat']) || empty($row['lng'])) {
        // use NM State geographic center
        $ctrlat = 34.450;
        $ctrlng = -106.042;
    } else {
        // use lat/lng currently residing in db
        $ctrlat = $row['lat'];
        $ctrlng = $row['lng'];
    }
    createPseudoJson($ctrlat, $ctrlng);
    $gpxfile = 'Filler';
    $hike_tracks = ['filler.json'];
} else {
    $stdClassGpx = json_decode($row['gpx'], true);
    // Convert stdClass to array: 
    $gpx_arr = [];
    foreach ($stdClassGpx as $item => $value) {
        $gpx_arr[$item] = $value;
    }
    // NOTE: track names will be the same as the original filename (sans '.gpx')
    $main = $gpx_arr["main"];
    $gpxfile = array_keys($main)[0];   // original filename
    $gpxjson = array_values($main)[0]; // associated json track files [array]
    $add1 = empty($gpx_arr["add1"]) ? [] : array_values($gpx_arr["add1"])[0];
    $add2 = empty($gpx_arr["add2"]) ? [] : array_values($gpx_arr["add2"])[0];
    $add3 = empty($gpx_arr["add3"]) ? [] : array_values($gpx_arr["add3"])[0];
    $hike_tracks = array_merge($gpxjson, $add1, $add2, $add3);
}
$noOfTrks = count($hike_tracks);
// for hikePageTemplate.php js:
$hike_file_list = json_encode($hike_tracks);

if (!$clusterPage) {
    // Pages with old Flickr photos
    $hikePhotoLink1 = $row['purl1'];
    $hikePhotoLink2 = $row['purl2'];
    $photoAlbum = '<br />';
    if (!empty($row['purl1'])) {
        $link = '<a href="' . $row['purl1'] .
            '" target="_blank">Photo Album Link</a>';
        $photoAlbum = '<p id="albums">For additional photos, click here:';
        $photoAlbum .= '<br /><span id="alnks">' . $link;
        if (!empty($row['purl2'])) {
            $photoAlbum .= '<br /><a href="' . $row['purl2']
                .'" target="_blank">Additional Album Link</a>';
        }
        $photoAlbum .= '</span></p>';
    }
    // arrays filled by mapping data routine:
    $trk_nmes = [];
    $gpsv_trk = [];
    $trk_lats = [];
    $trk_lngs = [];
    $gpsv_tick = [];
    $side_panel_data = prepareMappingData(
        $hike_tracks, $trk_nmes, $gpsv_trk, $trk_lats, $trk_lngs, $gpsv_tick
    );
    // Prepare SidePanel data
    $hike_data = []; // used to construct side panel
    $trkno = 1;
    $main_asc = 0;
    $main_dsc = 0;
    $main_echg = 0;
    for ($j=0; $j<count($hike_tracks); $j++) {
        /**
         * Retrieve unique side panel data for each trackfile
         * Track elevations & miles are in metric units and are
         * converted below.
         */
        $miles   = round(0.00062137119223733 * $side_panel_data[0][$j], 1);
        $miles = round($miles, 1);
        $ascent  = round($side_panel_data[2][$j] * 3.28084);
        $descent = round($side_panel_data[3][$j] * 3.28084);
        $max2min = 3.28084 * ($side_panel_data[1][$j]);
        $feet  = round($max2min);
        $sidepanel = array(
            'logistics' => $hikeType,
            'miles' => $miles,
            'feet' => $feet,
            'ascent' => $ascent,
            'descent' => $descent,
            'diff' => $hikeDifficulty,
            'wow' => $hikeWow,
            'seasons' => $hikeSeasons,
            'expo' => $hikeExposure
        );
        $trkrel = array($trk_nmes[$j] => $sidepanel);
        $hike_data += $trkrel;  // "+" is union operator for arrays
        if ($trkno++ === 1) {
            $main_dist = $miles;
            $main_asc  = $ascent;
            $main_dsc  = $descent;
            $main_echg = $feet;
        }
    }
    $sidePanelData = json_encode($hike_data);
    /**
     * This section collects the information from TSV/ETSV table needed
     * to build the picture rows a hike page
     */
    $photosReq = "SELECT `folder`,`title`,`hpg`,`mpg`,`desc`,`thumb`,`alblnk`," .
        "`date`,`mid`,`imgHt`,`imgWd`,`org` FROM {$ttable} WHERE " .
        "`indxNo` = :indxNo;";
    $photosPDO = $pdo->prepare($photosReq);
    $photosPDO->execute(["indxNo" =>$hikeIndexNo]);
    $photos = $photosPDO->fetchAll(PDO::FETCH_ASSOC);
    usort($photos, "cmp"); // sort by stored sequence number 
    $months = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug",
        "Sep","Oct","Nov","Dec");
    $descs = [];
    $alblnks = [];
    $piclnks = [];
    $captions = [];
    $aspects = [];
    $widths = [];
    /**
     * NOTE: In some cases, no captions were provided by the user, so only the
     * date string appears as a caption. Since the popupCaptions.js uses the
     * caption to match the popup to the pictures, duplicate date strings cause
     * a mis-placed caption. To prevent this, when a caption repeats during the
     * creation of the $captions array, it is given a unique identifier.
     */
    $unique_id = 1;
    foreach ($photos as $pics) {
        if ($pics['hpg'] === 'Y') {
            array_push($descs, $pics['title']);
            array_push($alblnks, $pics['alblnk']);
            $fbase = $pics['mid'] . "_" . $pics['thumb'];
            array_push($piclnks, $fbase);
            $pDesc = htmlspecialchars($pics['desc']);
            $dateStr = $pics['date'];
            if ($dateStr == '') {
                $thiscap = $pDesc;    
            } else {
                $year = substr($dateStr, 0, 4);
                $month = intval(substr($dateStr, 5, 2));
                $day = intval(substr($dateStr, 8, 2)); // intval strips leading 0
                $thiscap = $months[$month-1] . ' ' . $day . ', ' . $year .
                        ': ' . $pDesc;
            }
            if (in_array($thiscap, $captions)) {
                $thiscap .= " (" . $unique_id++ . ")";
            } 
            array_push($captions, $thiscap);
            $ht = intval($pics['imgHt']);
            $wd = intval($pics['imgWd']);
            array_push($widths, $wd);
            $picRatio = $wd/$ht;
            array_push($aspects, $picRatio);
        }
    }
    $capCnt = count($descs);
} else {
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
    // optional smoothing param must be defined for multiMap.php
    $makeGpsvDebug = false;
    $cluspg = 'yes'; 
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
        $ctrlat = $cpdata['lat']/LOC_SCALE;
        $ctrlng = $cpdata['lng']/LOC_SCALE;
        createPseudoGpx($ctrlat, $ctrlng, $gpxfile, $files);
    }
}
require "relatedInfo.php";

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
    /*
    $fpLnk .= "&clus=y";
    foreach ($files as $gpx) {
        $fpLnk .= "&gpx[]={$gpx}";
    }
    */
} else {
    $query_items = implode(",", $hike_tracks);
    $fpLnk .= "&json={$query_items}";
}

$zoom = isset($respPg) && $respPg ? 'small' : 'large';
$map_opts = [
    'zoom' => 18,
    'map_type' => 'ARCGIS_TOPO_WORLD',
    'street_view'=> 'false',
    'zoom_control' => "{$zoom}",
    'map_type_control' => 'menu',
    'center_coordinates' => 'true',
    'measurement_tools' => 'false',
    'utilities_menu' => "{ 'maptype':true, 'opacity':true, " .
        "'measure':true, 'export':true }",
    'tracklist_options' => 'true',
    'marker_list_options' => 'false',
    'dynamicMarker' => 'true'  
];

require '../php/multiMap.php';

// this is the html for the map: precede it with cache-control:
$php  = "<?php header('Cache-Control: max-age=0'); ?>" . $maphtml;
fputs($mapHandle, $php);
fclose($mapHandle);
