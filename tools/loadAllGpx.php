<?php
/**
 * This module loads all the current gpx files into a new database
 * [nmhikesc_gpx]. This tool is intended to convert the current state of
 * the site to the new methodology for storing gpx files, and thus will
 * be used only once. Afterwards, new hikes or edits to existing hikes will
 * utilize a separate script in the 'edit' directory to update the tables.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowle29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
require "toolFunctions.php";

$metatbl = <<<META
CREATE TABLE `META` (
    `gpxindx` INT NOT NULL AUTO_INCREMENT,
    `fname` VARCHAR(100) NOT NULL,
    `fileno` INT NOT NULL,
    `meta` TEXT NULL,
    `trkno` INT NOT NULL,
    `trkext` VARCHAR(2500) NULL,
    `trkname` VARCHAR(200) NULL,
    PRIMARY KEY (`gpxindx`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
META;

$gpxtbl = <<<GPX
CREATE TABLE `GPX` (
    `indx` INT NOT NULL AUTO_INCREMENT,
    `fileno` INT NOT NULL,
    `trackno` INT NOT NULL,
    `segno` INT NOT NULL,
    `lat` DECIMAL(13,11) NULL,
    `lon` DECIMAL(14,11) NULL,
    `ele` DECIMAL(6,2) NULL,
    `time` DATETIME NULL,
    PRIMARY KEY (`indx`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
GPX;

$waypts = <<<WAY
CREATE TABLE `WAYPTS` (
    `wptindx` INT NOT NULL AUTO_INCREMENT,
    `fileno` INT NOT NULL,
    `name` VARCHAR(200) NULL,
    `lat` DECIMAL(13,11) NULL,
    `lon` DECIMAL(14,11) NULL,
    `sym` VARCHAR(100) NULL,
    PRIMARY KEY (`wptindx`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
WAY;

$gdb->query("DROP TABLE IF EXISTS `META`;");
$gdb->query("DROP TABLE IF EXISTS `GPX`;");
$gdb->query("DROP TABLE IF EXISTS `WAYPTS`;");
$makeMetaTbl = $gdb->query($metatbl);
$makeDataTbl = $gdb->query($gpxtbl);
$makeWptsTbl = $gdb->query($waypts);

// Current listing of hikes vs gpx file(s)
$getGpxFilesReq = "SELECT `indxNo`,`gpx` FROM `HIKES`;";
$gpxFiles = $pdo->query($getGpxFilesReq)->fetchAll(PDO::FETCH_KEY_PAIR);
// NOTE: hikes may have multiple gpx files specified per hike
$loadfiles   = [];
foreach ($gpxFiles as $hike => $fileset) {
    if (!empty($fileset)) {
        $indx = count($loadfiles);
        if (strpos($fileset, ",") !== false) {
            // this hike has multiple gpx files specified
            $newlist = '';
            $filelist = explode(",", $fileset);
            for ($k=0; $k<count($filelist); $k++) {
                $newlist .= ($indx + $k + 1) . ",";
                array_push($loadfiles, $filelist[$k]);
            }
            $newlist = substr_replace($newlist, "", -1);
        } else {
            array_push($loadfiles, $fileset);
            $newlist = $indx + 1;
        }
        $updteReq = "UPDATE `HIKES` SET `gpxlist` = ? WHERE `indxNo` = ?;";
        $updte = $pdo->prepare($updteReq);
        $updte->execute([$newlist, $hike]);
    }
}

$fileno = 0;
foreach ($loadfiles as $gpxfile) {
    /**
     * Save the beginning of the xml and assign a gpx file no. This
     * can be used later to re-create the gpx file as required; also
     * capture any waypoints and track extensions. Note that although
     * the 'beginning' includes any waypoint data, if waypoints have
     * been edited later, the waypoint data in text string will also
     * require updating in order to recreate a gpx file for download.
     */
    $fname = $gpxfile;
    $file2load = "../gpx/" . $fname;
    $fileno++;
    $gpxhead = '';
    $gpxmeta = file($file2load);
    foreach ($gpxmeta as $line) {
        if (strpos($line, "<trk>") === false) {
            $gpxhead .= $line;
        } else {
            break;
        }
    }
    $gpxmeta = null;
    $gpx = simplexml_load_file($file2load);
    if ($gpx === false) {
        throw new Exception("Could not load {$fname} as simplexml");
    }
    if ($gpx->wpt->count() > 0) {
        extractWayPts($fileno, $gpx, $gdb);
    }
    // Extract any track extensions
    $gpx_string = file_get_contents($file2load);
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
        $offset = strpos($gpx_string, "</trk>", $offset);
        // Without 'trim' below, field data cannot be retrieved!
        $trkext = trim(substr($gpx_string, $pos, $lgth));
        $nmstart = strpos($trkext, "<name>") + 6;
        $nmend = strpos($trkext, "</name>");
        $lgth = $nmend - $nmstart;
        $name = substr($trkext, $nmstart, $lgth);
        $saveDataReq = "INSERT INTO `META` (`fname`,`fileno`,`meta`," .
            "`trkno`,`trkext`,`trkname`) VALUES (?,?,?,?,?,?);";
        $saveData = $gdb->prepare($saveDataReq);
        $saveData->execute(
            [$fname, $fileno, $gpxhead, $i, $trkext, $name]
        );
    }
    $gpx_string = null;
    // Load track data
    $trkno = 1; 
    foreach ($gpx->trk as $track) {
        $segno = 1;
        foreach ($track->trkseg as $seg) {
            foreach ($seg->trkpt as $row) {
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
                $addRowReq = "INSERT INTO `GPX` (`fileno`,`trackno`," .
                    "`segno`,`lat`,`lon`,`ele`,`time`) " .
                    "VALUES (?,?,?,?,?,?,?);";
                $addRow = $gdb->prepare($addRowReq);
                $addRow->execute(
                    [$fileno, $trkno, $segno, $lat, $lon, $ele, $time]
                );
            }
            $segno++;
        }
        $trkno++;
    }
}
echo "SITE GPX FILES LOADED";
