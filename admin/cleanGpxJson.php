<?php
/**
 * This page will present a list of any gpx or json files that exist
 * in those directories, but are not specified in the database. Note that
 * one important exception is the json/areas.json file used by the table
 * page when filtering hikes by locatio. The admin may then choose to delete
 * any or all of the extraneous files. The E-tables are not scanned, as
 * those may be in various states of hike edits or creation of new hikes.
 * PHP Version 7.2
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();

require_once "../php/global_boot.php";

$hikeGpxReq   = "SELECT `gpx` FROM `HIKES`;";
$hikeJSONReq  = "SELECT `trk` FROM `HIKES`;";
$gpsDatGpxReq = "SELECT `url` FROM `GPSDAT`;";

$hikeGpx   = $pdo->query($hikeGpxReq)->fetchAll(PDO::FETCH_ASSOC);
$hikeJSON  = $pdo->query($hikeJSONReq)->fetchAll(PDO::FETCH_ASSOC);
$gpsDatGpx = $pdo->query($gpsDatGpxReq)->fetchAll(PDO::FETCH_ASSOC);
$dbGpx    = [];
$dbJSON   = [];
$dbGPSDAT = [];
// Remove empty items; NOTE: HIKES gpx files can be a comma-separated list
foreach ($hikeGpx as $dbgpx) {
    if (!empty($dbgpx['gpx'])) {
        if (strpos($dbgpx['gpx'], ',') !== false) {
            $list = explode(",", $dbgpx['gpx']);
            foreach ($list as $item) {
                array_push($dbGpx, $item);
            }
        } else {
            array_push($dbGpx, $dbgpx['gpx']);
        }
    }
}
foreach ($hikeJSON as $dbjson) {
    if (!empty($dbjson['trk'])) {
        array_push($dbJSON, $dbjson['trk']);
    }
}
foreach ($gpsDatGpx as $gps) {
    $ext = pathinfo($gps['url'], PATHINFO_EXTENSION);
    if (strtolower($ext) === 'gpx') {
        array_push($dbGPSDAT, pathinfo($gps['url'], PATHINFO_BASENAME));
    }
}
$dir_iterator = new DirectoryIterator('../gpx');
// First, are any files in the gpx directory that are not in either the HIKES
// or the GPSDAT table? If so, they are 'extraneous'
$extraneousGpx  = [];
$missingHikes   = [];
$missingGPS     = [];
foreach ($dir_iterator as $file) {
    if (!$file->isDot() && !$file->isDir()) {
        $gpxname = $file->getFilename();
        $kmltest = pathinfo($gpxname, PATHINFO_EXTENSION);
        if ($gpxname !== ".DS_Store" && strtolower($kmltest) !== 'kml'
            && $gpxname !== 'filler.gpx'
        ) {
            if (!in_array($file->getFilename(), $dbGpx)
                && !in_array($file->getFilename(), $dbGPSDAT)
            ) {
                array_push($extraneousGpx, $file->getFilename());
            } 
        }
    }
}
// Does every gpx file in the HIKES table have a corresponding file
// in the gpx directory?
$gpxdir = scandir('../gpx');
foreach ($dbGpx as $dbentry) {
    if (!in_array($dbentry, $gpxdir)) {
        array_push($missingHikes, $dbentry);
    }
}
// does the GPSDAT table have a corresponding gpx file in the gpx directory
foreach ($dbGPSDAT as $gps) {
    if (!in_array($gps, $gpxdir)) {
        array_push($missingGPS, $gps);
    }
}
// next, check the JSON files against the db entries:
$extraneousJSON = [];
$missingJSON    = [];
// are any json files not in the database collection (HIKES table)
$json_iterator = new DirectoryIterator('../json');
foreach ($json_iterator as $json) {
    if (!$json->isDot()) {
        $jsonName = $json->getFilename();
        if (!in_array($jsonName, $dbJSON)
            && $jsonName !== '.DS_Store' && $jsonName !== 'areas.json'
        ) {
            array_push($extraneousJSON, $json->getFilename());
        } 
    }
}
// does the HIKES table have a corresponding trk file in the json directory
$jsonfiles = scandir('../json');
foreach ($dbJSON as $dbjson) {
    if (!in_array($dbjson, $jsonfiles)) {
        array_push($missingJSON, $dbjson);
    }
}
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Cleanup GPX and JSON files</title>
    <meta charset="utf-8" />
    <meta name="description" content="Check for extraneous photos" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/ktesaNavbar.css" rel="stylesheet" />
    <link href="../styles/cleanGpxJson.css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
<body>
<script src="https://unpkg.com/@popperjs/core@2.4/dist/umd/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">Gpx & JSON File Cleanup</p>
<p id="active" style="display:none">Admin</p>

<div id="main">
<form id="rms" method="post" action="rmGpxJson.php">
    <h5>Delete the following extraneous files (files in directories not specified
        in database),<br />or check the individual boxes:
    </h5>
    <button id="apply" type="submit" class="btn-sm btn-danger">
        Delete Checked Boxes</button><br />
    <div id="forceCheck">
    <input id="del_egpx" type="checkbox" />&nbsp;&nbsp;Delete Extraneous gpx<br />
    <input id="del_ejson" type="checkbox" />&nbsp;&nbsp;Delete Extraneous json<br />
    <input id="del_all" type="checkbox" />&nbsp;&nbsp;Delete All Extraneous<br />
    </div>

    <h5>There are <?=count($extraneousGpx);?> gpx files existing in the gpx
        directory that are NOT found in the HIKES table and are also NOT found
        in the GPSDAT table
    </h5>
    <div id="extgpx">
    <?php 
    if (count($extraneousGpx) > 0) {
        foreach ($extraneousGpx as $gpx) {
            $line = "<input name='egpx[]' class='egpx' type='checkbox' " .
                "value='" . $gpx . "' />&nbsp;&nbsp;<span>" . $gpx . 
                "</span><br />" . PHP_EOL;
            echo $line;
        }
    } else {
        echo "No extraneous gpx files found";
    }
    ?>
    </div>

    <h5>The following files are specified in the HIKES gpx db, but NOT found
        in the gpx directory
    </h5>
    <div id="mgpx">
    <?php 
    if (count($missingHikes) > 0) {
        foreach ($missingHikes as $hikegpx) {
            echo $hikegpx . "<br />";
        }  
    } else {
        echo "No missing HIKES 'gpx' files found";
    }
    ?>
    </div>

    <h5>The following files are specified in the GPSDAT table, but NOT found
        in the gpx directory
    </h5>
    <div id="mgps">
    <?php
    if (count($missingGPS) > 0 ) {
        foreach ($missingGPS as $gps) {
            echo $gps . "<br />";
        }
    } else {
        echo "No  missing GPSDAT 'gpx' files found";
    }
    ?>
    </div>

    <h5>There are <?=count($extraneousJSON);?> JSON files are in the json directory,
        that are NOT specifiedin the HIKES table
    </h5>
    <div id="extjson">
    <?php
    if (count($extraneousJSON) > 0) {
        foreach ($extraneousJSON as $ejson) {
            $line = "<input name='ejson[]' class='ejson' type='checkbox' ".
                "value='" . $ejson . "' />&nbsp;&nbsp;<span>" . $ejson . 
                "</span><br />" . PHP_EOL;
            echo $line;
        }
    } else {
        echo "No extraneous JSON files found";
    }
    ?>
    </div>

    <h5>Finally, the HIKES trk files that are specified in the db but NOT found
        in the json directory
    </h5>
    <?php
    if (count($missingJSON) > 0) {
        $indx = 0;
        foreach ($missingJSON as $mjson) {
            echo $mjson . "<br />";
        }
    } else {
        echo "No missing HIKES 'trk' files found" . PHP_EOL;
    }
    ?>
</form>
</div><br /><br />

<script src="cleanGpxJson.js"></script>

</body>
</html>
