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
