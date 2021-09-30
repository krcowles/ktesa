<?php
/**
 * Extract and prepare data from gpx file for javascript module
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
//require "../php/gpxFunctions.php";

$fileno = filter_input(INPUT_GET, 'fileno');
$waypts = filter_input(INPUT_GET, 'wpts') === 'y' ? true : false;
$chart  = filter_input(INPUT_GET, 'chrt') === 'y' ? true :false;
$stats  = filter_input(INPUT_GET, 'stats') === 'y' ? true : false;

$tracksReq = "SELECT MAX(trackno) FROM `GPX` WHERE `fileno` = ?;";
$tracks = $gdb->prepare($tracksReq);
$tracks->execute([$fileno]);
$trackmax = $tracks->fetch(PDO::FETCH_NUM);
$trackcnt = $trackmax[0];

if ($chart || $stats) {
    // track lat/lngs
    $tlatReq = "SELECT `lat` FROM `GPX` WHERE `fileno`=? AND `trackno`=?;";
    $tlats = $gdb->prepare($tlatReq);
    $tlats->execute([$fileno, $j]);
    $latdat = $tlats->fetchAll(PDO::FETCH_COLUMN);
    $tlngReq = "SELECT `lon` FROM `GPX` WHERE `fileno`=? AND `trackno`=?;";
    $tlngs = $gdb->prepare($tlngReq);
    $tlngs->execute([$fileno, $j]);
    $lngdat = $tlngs->fetchAll(PDO::FETCH_COLUMN);
    // track elevations
    $teleReq = "SELECT `ele` FROM `GPX` WHERE `fileno`=? AND `trackno`=?;";
    $teles = $gdb->prepare($teleReq);
    $teles->execute([$fileno, $j]);
    $eles = $teles->fetchAll(PDO::FETCH_COLUMN); // Note: these are in meters
}
if ($chart) { 
    $trkNames = [];
    $trkLats  = [];
    $trkLngs  = [];
    $trkRows  = [];
    $trkMaxs  = [];
    $trkMins  = [];
    $trkEles  = [];
    /**
     * Each track will have a name, a min elev, a max elev,
     * and arrays of lats, lngs, and elevations
     */ 
    for ($j=1; $j<= $trackcnt; $j++) {
        // track names
        $trkNameReq = "SELECT `trkname` FROM `META` WHERE `fileno`=? AND " .
            "`trkno`=?;";
        $trkName = $gdb->prepare($trkNameReq);
        $trkName->execute([$fileno, $j]);
        $trkname = $trkName->fetch(PDO::FETCH_ASSOC);
        array_push($trkNames, $trkname['trkname']);
        
        array_push($trkLats, $latdat);
        
        array_push($trkLngs, $lngdat);
        
        array_push($trkEles, $eles);
        $trkMax = max($eles) * 3.2808; // Convert to feet
        $trkMin = min($eles) * 3.2808;
        array_push($trkMaxs, $trkMax);
        array_push($trkMins, $trkMin);
    }
    $returndat = array($trkNames, $trkLats, $trkLngs, $trkEles, $trkMaxs, $trkMins);
    echo json_encode($returndat);
    exit;
}
if ($stats) {

}
if ($waypts) {

}
