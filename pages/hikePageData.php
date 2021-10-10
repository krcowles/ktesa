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
 */
$files = []; // required by multiMap.php
$allgpx = $row['gpxlist'];
if (!empty($allgpx)) {
    $files    = explode(",", $allgpx);
    $gpxfile  = $files[0]; // the main gpx file to be displayed on the page
} else { // cluster pages defined later...
    $gpxfile  = '';
}
$jsonFile = $row['trk'];
// Pages with old Flickr photos
$hikePhotoLink1 = $row['purl1'];
$hikePhotoLink2 = $row['purl2'];
$photoAlbum = '<br />';
if (!empty($row['purl1'])) {
    $link = '<a href="' . $row['purl1'] . '" target="_blank">Photo Album Link</a>';
    $photoAlbum = '<p id="albums">For improved photo viewing,<br />check out
        the following album(s):</p>';
    $photoAlbum .= '<p id="alnks">' . $link;
    if (!empty($row['purl2'])) {
        $photoAlbum .= '<br /><a href="' . $row['purl2']
            .'" target="_blank">Additional Album Link</a></p>';
    }

}
$infoHd         = $clusterPage ? 'area:' : 'hike:';
$hikeDirections = $row['dirs'];
$hikeTips       = $row['tips'];
$hikeInfo       = "<span id='ihd'>About this {$infoHd}</span><br />" . $row['info'];
if ($tbl === 'old') {
    $hikedLast  = $row['last_hiked'];
} else {
    $hikedLast  = false;
}
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
        $indx = 0;
        $nme_start = 0;
        foreach ($clushikes as $hike) {
            // Get info associated with this hike id (indxNo):
            $hikeReq = "SELECT `locale`,`logistics`,`miles`,`feet`,`diff`,`wow`," .
                "`seasons`,`expo`,`gpxlist` FROM `HIKES` WHERE `indxNo`=?;";
            $hikedat = $pdo->prepare($hikeReq);
            $hikedat->execute([$hike]);
            $sidepnl = $hikedat->fetch(PDO::FETCH_ASSOC); // only 1 entry per indxNo
            // any given hike may have multiple files
            $filelist = explode(",", $sidepnl['gpxlist']);
            $files = array_merge($files, $filelist);
            foreach ($filelist as $fileid) {
                // Each file may have multiple tracks
                $clidTrackReq = "SELECT `trkname`,`length`,`min2max`," .
                "`asc`,`dsc` FROM `META` WHERE `fileno`=?;";
                $clidTracks = $gdb->prepare($clidTrackReq);
                $clidTracks->execute([$fileid]);
                $tracks = $clidTracks->fetchAll(PDO::FETCH_ASSOC);
                $noOfTrks = count($tracks);
                if ($indx === 0) {
                    // for clusters, some fields defined above are null
                    $hikeLocale     = $sidepnl['locale'];
                    $hikeDifficulty = $sidepnl['diff'];
                    $hikeType       = $sidepnl['logistics'];
                    $hikeExposure   = $sidepnl['expo'];
                    $hikeSeasons    = $sidepnl['seasons'];
                    $hikeWow   = $sidepnl['wow'];
                    $mainmiles = $sidepnl['miles'];
                    $mainfeet  = $sidepnl['feet'];
                    $ascent    = $tracks[0]['asc'];
                    $descent   = $tracks[0]['dsc'];
                    $indx      = 100;
                } 
                for ($j=0; $j<$noOfTrks; $j++) {
                    $tname = $nme_start++;
                    $trkpanel = $sidepnl;
                    $trkpanel['miles'] = $tracks[$j]['length'];
                    $trkpanel['feet']  = $tracks[$j]['min2max'];
                    $trkpanel['asc']   = $tracks[$j]['asc'];
                    $trkpanel['dsc']   = $tracks[$j]['dsc'];
                    $trkrel = array($tname => $trkpanel);
                    $hike_data += $trkrel;  // "+" union operator for (assoc) arrays
                }
            }
        }
        $sidePanelData = json_encode($hike_data);
    } else { // no published hikes for this group yet
        $ctrlat = $cpdata['lat']/LOC_SCALE;
        $ctrlng = $cpdata['lng']/LOC_SCALE;
        createPseudoGpx($ctrlat, $ctrlng, $gpxfile, $files);
    }
} elseif ($gpxfile === '') {  // hike page has no gpx file
    if (empty($row['lat']) || empty($row['lng'])) {
        // use NM State geographic center
        $ctrlat = 34.450;
        $ctrlng = -106.042;
    } else {
        $ctrlat = $row['lat'];
        $ctrlng = $row['lng'];
    }
    createPseudoGpx($ctrlat, $ctrlng, $gpxfile, $files);
} else {  // hike page has at least one gpx file
    $hike_data = []; // array to collect info for javascript
    $trkcnt = 0;
    foreach ($files as $fileno) {
        $hikeTrkCnt = $gdb->query(
            "SELECT `trkno` FROM `META` WHERE `fileno`={$fileno} " .
            "ORDER BY `trkno` DESC LIMIT 1;"
        )->fetch(PDO::FETCH_NUM);
        $trkcount = $hikeTrkCnt[0];
        for ($j=1; $j<=$trkcount; $j++) {
            $statsReq = "SELECT `length`,`min2max`,`asc`,`dsc` FROM `META` WHERE " .
                "`fileno`=? AND `trkno`=?;";
            $stats = $gdb->prepare($statsReq);
            $stats->execute([$fileno, $j]);
            $pnldat  = $stats->fetch(PDO::FETCH_ASSOC);
            $miles   = $pnldat['length'];
            $feet    = $pnldat['min2max'];
            $ascent  = $pnldat['asc'];
            $descent = $pnldat['dsc'];
            $sidepanel = array(
                'logistics' => $hikeType,
                'miles' => $miles,
                'feet' => $feet,
                'diff' => $hikeDifficulty,
                'wow' => $hikeWow,
                'seasons' => $hikeSeasons,
                'expo' => $hikeExposure,
                'asc' => $ascent,
                'dsc' => $descent
            );
            $hike_data[$trkcnt++] = $sidepanel;
            if ($j === 1 && $fileno === $files[0]) {
                $mainfeet = $feet;
                $mainmiles = $miles;
            }
        }
    }
    $sidePanelData = json_encode($hike_data);
}
require "relatedInfo.php";
/**
 * This section collects the information from TSV/ETSV table needed
 * to build the picture rows a hike page
 */
if (!$clusterPage) {
    $photosReq = "SELECT `folder`,`title`,`hpg`,`mpg`,`desc`,`thumb`," .
        "`alblnk`,`date`,`mid`,`imgHt`,`imgWd`,`org` FROM {$ttable} " .
        "WHERE `indxNo` = :indxNo;";
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
    foreach ($photos as $pics) {
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
 * In the case of hike map and elevation chart, in order for the map to be
 * displayed in an iframe, a file is created and stored in the maps/tmp
 * sub-directory. The file is deleted after loading the page.
 */
$gpsvMap = "GpxFile" . $gpxfile;
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
    $fpLnk .= "&gpx={$allgpx}";
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

/* NOT UPDATED FOR GPX FILES IN DATABASE"
// Open debug files with headers, if requested by query string
$handleDfa = null;
$handleDfc = null;
if ($makeGpsvDebug) {
    $handleDfa = gpsvDebugFileArray($gpxPath);
    $handleDfc = gpsvDebugComputeArray($gpxPath);
}
*/

require '../php/multiMap.php';

// this is the html for the map: precede it with cache-control:
$php  = "<?php header('Cache-Control: max-age=0'); ?>" . $maphtml;
fputs($mapHandle, $php);
fclose($mapHandle);
