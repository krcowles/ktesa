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
 * This function will add data to the WAYPTS table for the file specified
 * 
 * @param integer          $fileno  The file # associated with the waypoints
 * @param simpleXMLElement $xmlfile The file loaded as simpleXMLElement
 * @param PDO              $gdb     The PDO class instantiated for the WAYPTS table
 * 
 * @return null;
 */
function extractWayPts($fileno, $xmlfile, $gdb)
{
    foreach ($xmlfile->wpt as $waypt) {
        $wlat  = $waypt['lat'];
        $wlon  = $waypt['lon'];
        $wname = $waypt->name->__toString();
        $wsym  = $waypt->sym->__toString();
        $wptSaveReq = "INSERT INTO `WAYPTS` (`fileno`,`name`,`lat`,`lon`," .
            "`sym`) VALUES (?,?,?,?,?);";
        $wptSave = $gdb->prepare($wptSaveReq);
        $wptSave->execute(
            [$fileno, $wname, $wlat, $wlon, $wsym]
        );
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