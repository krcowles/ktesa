<?php
/**
 * This module creates a gpx file representing the hike seen on the hike
 * page - whether the track(s) shown represent one or multiple source gpx
 * files. The file is created from data stored in the _gpx database. When
 * waypoints are present - all gpx files containing waypoints have updated
 * the TSV table - they are included in the constructed file. The resultant
 * file is presented for download to the user's machine. For the case of
 * multiple source files, a simple gpx declaration is provided with no metadata.
 * The file name will be the hike name. For single files, the gpx declaration
 * and metadata will be what was originally uploaded.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license None to date
 */
require "../php/global_boot.php";

$dwnld = filter_input(INPUT_GET, 'type');
$hike  = filter_input(INPUT_GET, 'indx');
$name  = filter_input(INPUT_GET, 'name');
$tbl   = filter_input(INPUT_GET, 'tbl');
$table = $tbl === 'new' ? 'EHIKES' : 'HIKES';
$tsv   = $tbl === 'new' ? 'ETSV' : 'TSV';

$xmldeclaration = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
$gpxdecl = $xmldeclaration .
    '<gpx xmlns="http://www.topografix.com/GPX/1/1" ' .
    'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" version="1.1" '.
    'xsi:schemaLocation="http://www.topografix.com/GPX/1/1 ' .
    'http://www.topografix.com/GPX/1/1/gpx.xsd">' . PHP_EOL;

if ($dwnld === 'main') {
    $fileListReq = "SELECT `gpxlist` FROM {$table} WHERE `indxNo`=?;";
    $fileList = $pdo->prepare($fileListReq);
    $fileList->execute([$hike]);
    $hikefile = $fileList->fetch(PDO::FETCH_NUM);

    if (strpos($hikefile[0], ",") === false) {
        $files = array($hikefile[0]);
    } else {   
        $files = explode(",", $hikefile[0]);
    }
} else { // GPS Data File
    $files = array($hike);
}
// Create the file structure without data
$xml = '';
if (count($files) === 1) {
    $filedataReq = "SELECT `fname`,`meta`,`trkno`,`trkext` FROM `META` WHERE " .
        "`fileno`=?;";
    $filedata = $gdb->prepare($filedataReq);
    $filedata->execute([$files[0]]);
    $head = $filedata->fetchAll(PDO::FETCH_ASSOC);
    $trackcount = count($head); // one entry per track
    $xml .= $head[0]['meta'];
} else { // multiple files
    $xml .= $gpxdecl . PHP_EOL;
}
$name = str_replace(" ", "_", $name) . '.gpx';

// get all hike waypoints
$wptReq = "SELECT `title`,`lat`,`lng`,`iclr` FROM {$tsv} WHERE `indxNo`=? AND " .
    "`thumb` IS NULL;";
$wpts = $pdo->prepare($wptReq);
$wpts->execute([$hike]);
$gpxwpts = $wpts->fetchAll(PDO::FETCH_ASSOC);
$wptloc = strpos($xml, "<wpt");
if ($wptloc !== false) {
    $xml = substr($xml, 0, $wptloc);
}
// add waypoints to xml
foreach ($gpxwpts as $wpt) {
    $pt = '  <wpt lat="' . $wpt['lat']/LOC_SCALE . '" lon="' .
        $wpt['lng']/LOC_SCALE . '">' . PHP_EOL;
    $pt .= '    <name>' . $wpt['title'] . '</name>' . PHP_EOL;
    $pt .= '    <sym>' . $wpt['iclr'] . '</sym>' . PHP_EOL;
    $pt .= '  </wpt>' . PHP_EOL;
    $xml .= $pt;
}

/**
 * All tracks will be included in the file. When there are multiple files,
 * each file's tracks will be appended. `META` track extensions are included.
 */
$exts = [];
foreach ($files as $gpxfile) {
    // how many tracks in this file?
    $getTrackInfo = "SELECT `trkno` FROM `META` WHERE `fileno`=? " .
        "ORDER BY `trkno` DESC LIMIT 1;";
    $trackInfo = $gdb->prepare($getTrackInfo);
    $trackInfo->execute([$gpxfile]);
    $trackcount = $trackInfo->fetch(PDO::FETCH_NUM);
    $noOfTracks = $trackcount[0];
    for ($k=1; $k<=$noOfTracks; $k++) {
        $xml .= '<trk>' . PHP_EOL;
        // get this track's extension
        $trackextReq = "SELECT `trkext` FROM `META` WHERE " .
            "`fileno`=? AND `trkno`=?;";
        $trackext = $gdb->prepare($trackextReq);
        $trackext->execute([$gpxfile, $k]);
        $ext = $trackext->fetch(PDO::FETCH_NUM);
        $xml .= $ext[0] . PHP_EOL;
        // all segments will be merged into one
        $xml .= '  <trkseg>' . PHP_EOL;
        // get track's trkpts
        $gpsReq = "SELECT `lat`,`lon`,`ele`,`time` FROM `GPX` WHERE " .
            "`fileno`=? AND `trackno`=?;";
        $gps = $gdb->prepare($gpsReq);
        $gps->execute([$gpxfile, $k]);
        $trkpts = $gps->fetchAll(PDO::FETCH_ASSOC);
        foreach ($trkpts as $tag) {
            $trkpt = '    <trkpt lat="' . $tag['lat'] . '" lon="' . $tag['lon'] .
                '">' . PHP_EOL;
            $trkpt .= '      <ele>' . $tag['ele'] . '</ele>' . PHP_EOL;
            if (!empty($tag['time'])) {
                $gpxtime = str_replace(" ", "T", $tag['time']) . 'Z'; 
                $trkpt .= '      <time>' . $gpxtime . '</time>' . PHP_EOL;
            }
            $trkpt .= '    </trkpt>' . PHP_EOL;
            $xml .= $trkpt;
        }
        $xml .= '  </trkseg>' . PHP_EOL;
        $xml .= '</trk>' . PHP_EOL;
    }
}

$xml .= '</gpx>';

// download
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"" . $name . "\"");
echo $xml;
