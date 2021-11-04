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
function saveWayPts($hikeno, $xmlfile, $pdo)
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
function writeTrkData($fileno, $trkno, $segno, $row, $gdb)
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
/**
 * This script will upload a GPSDAT file to the META/GPX table
 * 
 * @param string $hikeno        The indxNo of the subject hike
 * @param string $gpx_file_name Where the file is located
 * @param PDO    $gdb           The GPX/META table db
 * 
 * @return null
 */
function uploadGPSDATA($hikeno, $gpx_file_name, $gdb)
{
    $loc = '../gpx/' . $gpx_file_name;
    $gpxarray = file($loc);
    if ($gpxarray === false) {
        throw new Exception("Failed to load file for hike {$hikeno}");
    }
    $gpxhead = '';
    foreach ($gpxarray as $line) {
        if (strpos($line, "<trk>") === false) {
            $gpxhead .= $line;
        } else {
            break;
        }
    }
    $gpxarray = null;
    $lastFilenoReq = "SELECT MAX(`fileno`) FROM `META`;";
    $lastFileno = $gdb->query($lastFilenoReq);
    $last = $lastFileno->fetch(PDO::FETCH_NUM);
    $fileno = 0;
    if ($last !== false) {
        $fileno = $last[0] + 1;
    } else {
        throw new Exception("Couldn't retrieve last fileno");
    }
    $gpx = simplexml_load_file($loc);
    $gpx_string = file_get_contents($loc);
    $trkcnt = substr_count($gpx_string, "<trk>");
    $offset = 0;
    for ($i=1; $i<=$trkcnt; $i++) {
        $pos = strpos($gpx_string, "<trk", $offset) + 5;
        // Note: <trkseg> is not required and may not be present...
        if (strpos($gpx_string, "<trkseg") === false) {
            $end = strpos($gpx_string, "<trkpt", $offset);
        } else {
            $end = strpos($gpx_string, "<trkseg", $offset);
        }
        $lgth = $end - $pos;
        $offset = strpos($gpx_string, "</trk>", $offset+50);
        // Without 'trim' below, field data cannot be retrieved!
        $trkext = trim(substr($gpx_string, $pos, $lgth));
        $name = $gpx->trk[$i-1]->name->__toString();
        // If $name looks like a timestamp:
        $dtime = explode(" ", $name);
        $dte   = explode("-", $dtime[0]);
        if (count($dte) === 3) {
            if (strlen($dte[0]) === 4 
                && strlen($dte[1]) === 2 && strlen($dte[2]) === 2
            ) {
                // $name is a date format
                $name = substr($gpx_file_name, 0, 7) . '_' . $i;
            }
        }
        // no length, etc. yet...
        $saveDataReq = "INSERT INTO `META` (`fname`,`fileno`,`meta`," .
            "`trkno`,`trkext`,`trkname`) VALUES (?,?,?,?,?,?);";
        $saveData = $gdb->prepare($saveDataReq);
        $saveData->execute(
            [$gpx_file_name, $fileno, $gpxhead, $i, $trkext, $name]
        );
    }
    /**
     * Load the track data for each <trk>'s <trkpt> into the GPX table
     * NOTE: trkpts with no <ele> are not written out, and message can
     * be printed at end showing affected files
     */
    $trkno = 1; 
    foreach ($gpx->trk as $track) {
        $noEles = 0;
        $segno = 1;
        // some files have no trkseg
        if ($track->trkseg->count() !== 0) {
            foreach ($track->trkseg as $seg) {
                foreach ($seg->trkpt as $row) {
                    if (!writeTrkData($fileno, $trkno, $segno, $row, $gdb)) {
                        $noEles++;
                    }
                }
                $segno++;
            }
        } else {
            foreach ($track->trkpt as $row) {
                if (!writeTrkData($fileno, $trkno, $segno, $row, $gdb)) {
                    $noEles++;
                }
            }
        }
        $trkno++;
    }
    getGPSDATStats($fileno, $gdb);
}
/**
 * Create the track data for the specified fileno
 * 
 * @param string $fileno The fileno in the GPX database for the file
 * @param PCO    $gdb    The PDO class for EGPX/EMETA
 * 
 * @return null;
 */
function getGPSDATStats($fileno, $gdb)
{
    $getTracks = "SELECT `trkno` FROM `META` WHERE `fileno`={$fileno} " .
        "ORDER BY `trkno` DESC LIMIT 1;";
    $noOfTracks = $gdb->query($getTracks)->fetch(PDO::FETCH_NUM);
    $trkcount = $noOfTracks[0];
    for ($k=1; $k<=$trkcount; $k++) {
        $getData = "SELECT `lat`,`lon`,`ele` FROM `GPX` WHERE `fileno`=? " .
            "AND `trackno`=?;";
        $gpsdata = $gdb->prepare($getData);
        $gpsdata->execute([$fileno, $k]);
        $gps = $gpsdata->fetchAll(PDO::FETCH_ASSOC);
        // in case of a missing fileno
        if ($gps !== false) {
            $length = (float) 0;
            $maxele = (float) 0;
            $minele = (float) 100000;
            $asc = 0;
            $dsc = 0;
            for ($i=0; $i<count($gps)-1; $i++) {
                $calcs = distance(
                    floatval($gps[$i]['lat']), floatval($gps[$i]['lon']), 
                    floatval($gps[$i+1]['lat']), floatval($gps[$i+1]['lon'])
                );
                $length += $calcs[0];
                $maxele = floatval($gps[$i]['ele']) > $maxele ? 
                    floatval($gps[$i]['ele']) : $maxele;
                $minele = floatval($gps[$i]['ele']) < $minele ? 
                    floatval($gps[$i]['ele']) : $minele;
                $delta = round($gps[$i+1]['ele'], 2) - round($gps[$i]['ele'], 2);
                if ($delta < 0) {
                    $dsc -= $delta;
                } else {
                    $asc += $delta;
                }
            }
            // convert from meters to feet
            $min2max   = ($maxele - $minele) * 3.2808;
            $asc       = round($asc*3.2808);
            $dsc       = round($dsc*3.2808);
            $length    = ($length * 3.2808)/5280;
            $dbmin2max = round($min2max);
            $dblength  = round($length, 2);

            $add2dbReq = "UPDATE `META` SET `length`=?,`min2max`=?,`asc`=?," .
                "`dsc`=? WHERE `fileno`=? AND `trkno`=?;";
            $add2db = $gdb->prepare($add2dbReq);
            $add2db->execute([$dblength, $dbmin2max, $asc, $dsc, $fileno, $k]);
        }
    }
}