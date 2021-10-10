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
    `length` DECIMAL(4,2) NULL,
    `min2max` INT NULL,
    `asc` INT NULL,
    `dsc` INT NULL,
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

$gdb->query("DROP TABLE IF EXISTS `META`;");
$gdb->query("DROP TABLE IF EXISTS `GPX`;");
$makeMetaTbl = $gdb->query($metatbl);
$makeDataTbl = $gdb->query($gpxtbl);

// Current listing of hikes vs gpx file(s)
$getGpxFilesReq = "SELECT `indxNo`,`gpx` FROM `HIKES`;";
$gpxFiles = $pdo->query($getGpxFilesReq)->fetchAll(PDO::FETCH_KEY_PAIR);
$fileToIndx = [];
// NOTE: hikes may have multiple gpx files specified per hike
$fileno_count = 0;
foreach ($gpxFiles as $key => $value) {
    if (!empty($value)) { // cluster pages have no gpx file specified
        $fileno_count++;
        if (strpos($value, ",") !== false) {
            $allfiles = explode(",", $value);
            $glist = '';
            foreach ($allfiles as $mult) {
                $fileToIndx[$mult] = $key;
                $glist .= $fileno_count++ . ",";
            }
            $glist = substr_replace($glist, "", -1);
            $fileno_count--;
        } else {
            $fileToIndx[$value] = $key;
            $glist = $fileno_count;
        }
        $updteReq = "UPDATE `HIKES` SET `gpxlist` = ? WHERE `indxNo` = ?;";
        $updte = $pdo->prepare($updteReq);
        $updte->execute([$glist, $key]);
    }
}

$fileno = 0;
$emptyEles = [];
$loadfiles = array_keys($fileToIndx);
foreach ($loadfiles as $gpxfile) {
    /**
     * Save the beginning of the xml and assign a gpx file no. This
     * can be used later to re-create the gpx file as required; The 
     * 'meta' data field also includes any gpx waypoints. These are
     * saved to the standard TSV database. If a gpx download is
     * requested, any waypoints in the TSV database will be inluded.
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
        $hikeno = $fileToIndx[$fname];
        extractWayPts($hikeno, $gpx, $pdo);
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
        $offset = strpos($gpx_string, "</trk>", $offset+50);
        // Without 'trim' below, field data cannot be retrieved!
        $trkext = trim(substr($gpx_string, $pos, $lgth));
        $name = $gpx->trk[$i-1]->name->__toString();

        $saveDataReq = "INSERT INTO `META` (`fname`,`fileno`,`meta`," .
            "`trkno`,`trkext`,`trkname`) VALUES (?,?,?,?,?,?);";
        $saveData = $gdb->prepare($saveDataReq);
        $saveData->execute(
            [$fname, $fileno, $gpxhead, $i, $trkext, $name]
        );
    }
    $gpx_string = null;
    /**
     * Load the track data for each <trkpt> into the GPX table
     * NOTE: trkpts with no <ele> are not written out, and message
     * is printed at end showing affected files
     */
    $trkno = 1; 
    foreach ($gpx->trk as $track) {
        $noEles = 0;
        $segno = 1;
        // some files have no trkseg
        if ($track->trkseg->count() !== 0) {
            foreach ($track->trkseg as $seg) {
                foreach ($seg->trkpt as $row) {
                    if (!writeGPSData($fileno, $trkno, $segno, $row, $gdb)) {
                        $noEles++;
                    }
                }
                $segno++;
            }
        } else {
            foreach ($track->trkpt as $row) {
                if (!writeGPSData($fileno, $trkno, $segno, $row, $gdb)) {
                    $noEles++;
                }
            }
        }
        $trkno++;
        if ($noEles > 0) {
            $emptyEles[$gpxfile] = $noEles;
        }
    }
}
echo "SITE GPX FILES LOADED<br /><br />";
$html  = '<table>' . PHP_EOL;
$html .= '<tbody>' . PHP_EOL;
foreach ($emptyEles as $file => $qty) {
    $html .= '<tr>' . PHP_EOL;
    $html .= '<td>' . $file . '</td>';
    $html .= '<td>' . $qty . '</td>';
    $html .= '</tr>' . PHP_EOL;
}
$html .= '</tbody>' . PHP_EOL;
$html .= '</table>' . PHP_EOL;
echo $html;
