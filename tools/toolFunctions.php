<?php
/**
 * This module contains the functions only required when utilizing the
 * one-time tools in the tools directory.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
/**
 * This function will add data to the TSV table for the gpx file specified
 * First, the TSV table is checked to see if waypoints already exist, and
 * if they do (and are not duplicates), then the gpx waypoints are appended.
 * Since names and symbols may be changed during edit, but probably not the
 * lats/lngs, the latter is checked for duplicate entries.
 * 
 * @param integer          $hikeno  The file # associated with the waypoints
 * @param simpleXMLElement $xmlfile The file loaded as simpleXMLElement
 * @param PDO              $pdo     The PDO class for the TSV table
 * 
 * @return null;
 */
function extractWayPts($hikeno, $xmlfile, $pdo)
{
    // retrieve any current data
    $currentTsvReq = "SELECT `lat`,`lng` FROM `TSV` WHERE " .
        "`indxNo`=? AND `thumb` IS NULL;";
    $currentTsv = $pdo->prepare($currentTsvReq);
    $currentTsv->execute([$hikeno]);
    $tsvdata = $currentTsv->fetchAll(PDO::FETCH_ASSOC); // array, even if empty
    foreach ($xmlfile->wpt as $waypt) {
        $wlat  = floor($waypt['lat'] * LOC_SCALE);
        $wlon  = floor($waypt['lon'] * LOC_SCALE);
        $wname = $waypt->name->__toString();
        $wsym  = $waypt->sym->__toString();
        $add = true;
        foreach ($tsvdata as $twpt) {
            if ($wlat == $twpt['lat'] && $wlon == $twpt['lng']) {
                $add = false;
                break;
            }
        }
        if ($add) {
            $wptSaveReq = "INSERT INTO `TSV` (`indxNo`,`title`,`mpg`,`lat`,`lng`," .
                "`iclr`) VALUES (?,?,?,?,?,?);";
            $wptSave = $pdo->prepare($wptSaveReq);
            $wptSave->execute(
                [$hikeno, $wname, 'Y', $wlat, $wlon, $wsym]
            );
        }
    }
}
/**
 * A function to extract GPS data from simplexml element and write to GPX table
 * (Created to avoid code duplication)
 * 
 * @param number           $fileno The file no in the db for the subject gpx file
 * @param number           $trkno  The track number being processed 
 * @param number           $segno  The <trkseg> being processed
 * @param simpleXMLElement $row    The <trkpt> tag data
 * @param PDO              $gdb    The PDO class instantiated for the GPX table
 * 
 * @return boolean
 */
function writeGPSData($fileno, $trkno, $segno, $row, $gdb)
{
    $lat = $row['lat']->__toString();
    $lon = $row['lon']->__toString();
    $ele = null;
    $time = null;
    if ($row->count() > 0) {
        foreach ($row->children() as $child) {
            $name = $child->getName();
            if ($name === 'ele') {
                $ele = $child->__toString();
            }
            if ($name === 'time') {
                $time = $child->__toString();
                $tim  = str_replace('T', ' ', $time);
                $time = str_replace('Z', '', $tim);
            }
        }
    }
    if (empty($ele)) { // don't write a <trkpt> with no elevation data
        return false;
    } else {
        $addRowReq = "INSERT INTO `GPX` (`fileno`,`trackno`," .
            "`segno`,`lat`,`lon`,`ele`,`time`) " .
            "VALUES (?,?,?,?,?,?,?);";
        $addRow = $gdb->prepare($addRowReq);
        $addRow->execute(
            [$fileno, $trkno, $segno, $lat, $lon, $ele, $time]
        );
        return true;
    }
}
