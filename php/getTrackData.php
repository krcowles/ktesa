<?php
/**
 * Extract and prepare data from a single gpx file for charting tracks on a
 * map ($chart = true), or when creating a map to display ($mapdata is set).
 * When charting elevation data, the script may be invoked rapidly multiple 
 * times when multi-track files are being loaded (e.g. BlackCanyonComnposite.gpx),
 * or when multiple gpx files have been specified (e.g. Knife's Edge hike).
 * In these cases, the asynchronous nature of ajax results in a potentially
 * non-sequential return of data. For this reason, the unique track name for
 * the track is associated with its data sets for identification during charting.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require_once "../php/global_boot.php";

if (isset($mapdata)) {  // when invoked via multiMap.php
    $chart = false;
    $xtable = $clusterPage && count($clushikes) > 0 ? 'GPX' : $xtable;
    // track lat/lngs
    $tlatReq = "SELECT `lat` FROM {$xtable} WHERE `fileno`=? AND `trackno`=?;";
    $tlats = $gdb->prepare($tlatReq);
    $tlats->execute([$fileno, $k]);
    $latdat = $tlats->fetchAll(PDO::FETCH_COLUMN);
    $tlngReq = "SELECT `lon` FROM {$xtable} WHERE `fileno`=? AND `trackno`=?;";
    $tlngs = $gdb->prepare($tlngReq);
    $tlngs->execute([$fileno, $k]);
    $lngdat = $tlngs->fetchAll(PDO::FETCH_COLUMN);
} else {  // when ajaxed via prepareTracks.js
    // May be for either published or hikes-in-edit
    verifyAccess('ajax');

    $fileno = filter_input(INPUT_GET, 'fileno');
    $tbl    = filter_input(INPUT_GET, 'tbl');
    $clusterPg = (isset($_GET['clus']) && $_GET['clus'] === 'y') ? true : false;
    if (isset($_GET['chrt'])) {
        $chart  = filter_input(INPUT_GET, 'chrt') === 'y' ? true :false;
    } else {
        $chart = false;
    }
    $cpg_nofiles = isset($_GET['pseudo']) ? true : false;
    if (isset($_GET['wpts'])) {
        $waypts = filter_input(INPUT_GET, 'wpts') === 'y' ? true : false;
    } else {
        $waypts = false;
    }
    if (!$clusterPg && $tbl === 'new' || $cpg_nofiles) {
        $xtable = 'EGPX';
        $mtable = 'EMETA';
    } else {
        $xtable = 'GPX';
        $mtable = 'META';
    }
    $tracksReq = "SELECT MAX(trkno) FROM {$mtable} WHERE `fileno` = ?;";
    $tracks = $gdb->prepare($tracksReq);
    $tracks->execute([$fileno]);
    $trackmax = $tracks->fetch(PDO::FETCH_NUM);
    $trackcnt = $trackmax[0];
}
if ($chart) {
    $trkNames = []; 
    $trkLats  = [];
    $trkLngs  = [];
    $trkRows  = [];
    $trkMaxs  = [];
    $trkMins  = [];
    /**
     * Each track will have a name, a min elev, a max elev,
     * and arrays of lats, lngs, and elevations
     */ 
    for ($j=1; $j<= $trackcnt; $j++) {
        $chartrow = [];
        // track names
        $trkNameReq = "SELECT `trkname` FROM {$mtable} WHERE `fileno`=? AND " .
            "`trkno`=?;";
        $trkName = $gdb->prepare($trkNameReq);
        $trkName->execute([$fileno, $j]);
        $trkname = $trkName->fetch(PDO::FETCH_NUM);
        $trkid = $trkname[0];
        array_push($trkNames, $trkid);
        //array_push($trkNames, $trkname['trkname']);
        // track lat/lngs
        $tlatReq = "SELECT `lat` FROM {$xtable} WHERE `fileno`=? AND `trackno`=?;";
        $tlats = $gdb->prepare($tlatReq);
        $tlats->execute([$fileno, $j]);
        $latdat = $tlats->fetchAll(PDO::FETCH_COLUMN);
        $tlngReq = "SELECT `lon` FROM {$xtable} WHERE `fileno`=? AND `trackno`=?;";
        $tlngs = $gdb->prepare($tlngReq);
        $tlngs->execute([$fileno, $j]);
        $lngdat = $tlngs->fetchAll(PDO::FETCH_COLUMN);
        array_push($trkLats, $latdat);
        array_push($trkLngs, $lngdat);
        // track elevations
        $teleReq = "SELECT `ele` FROM {$xtable} WHERE `fileno`=? AND `trackno`=?;";
        $teles = $gdb->prepare($teleReq);
        $teles->execute([$fileno, $j]);
        $eles = $teles->fetchAll(PDO::FETCH_COLUMN); // Note: these are in meters
        // create plot data as js objects (when json_encoded)
        $miles = 0;
        $elev1_ft = round($eles[0] * 3.2808);
        $chartrow[0] = ["x" => $miles,"y" => $elev1_ft];
        $limit = count($latdat) - 1;
        for ($n=0; $n<$limit; $n++) {
            $dist = distance(
                (float)$latdat[$n], (float)$lngdat[$n],
                (float)$latdat[$n+1], (float)$lngdat[$n+1]
            );
            $miles += round($dist[0]*3.2808/5280, 4);
            $chartrow[$n+1] = ["x" => $miles, "y" => round($eles[$n] * 3.2808)];
        }
        array_push($trkRows, $chartrow);
        // associate maxs/mins
        $trkMax = round(max($eles) * 3.2808); // Convert to feet
        $trkMin = round(min($eles) * 3.2808);
        array_push($trkMaxs, $trkMax);
        array_push($trkMins, $trkMin);
    }
    if (!isset($mapdata)) {
        $data = array(
            $trkNames, $trkRows, $trkLats, $trkLngs, $trkMaxs, $trkMins
        );
        $returndata = json_encode($data);
        echo $returndata;
    }
}
