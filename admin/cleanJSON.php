<?php
/**
 * This page will present a list of any json files that exist in the
 * json/ directory and are not specified in the database. Note that
 * two important exceptions are the json/areas.json file used by the table
 * page when filtering hikes by location, and the filler.json used when
 * no gpx file has been uploaded. The admin may then choose to delete
 * any or all of the extraneous files.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();

require_once "../php/global_boot.php";

$hikeGpxReq   = "SELECT `indxNo`,`gpx` FROM `HIKES`;";
$ehikeGpxReq  = "SELECT `indxNo`,`gpx` FROM `EHIKES`;";
$gpsDatReq = "SELECT `datId`,`url`  FROM `GPSDAT`;";
$egpsDatReq   = "SELECT `datId`,`url`  FROM `EGPSDAT`;";

$hikeGpx    = $pdo->query($hikeGpxReq)->fetchAll(PDO::FETCH_KEY_PAIR);
$ehikeGpx   = $pdo->query($ehikeGpxReq)->fetchAll(PDO::FETCH_KEY_PAIR);
$gpsDatGpx  = $pdo->query($gpsDatReq)->fetchAll(PDO::FETCH_KEY_PAIR);
$egpsDatGpx = $pdo->query($egpsDatReq)->fetchAll(PDO::FETCH_KEY_PAIR);

$dbJSON    = [];
/**
 * For each of the db areas queried above, collect json track files
 */
$hikeNos = array_keys($hikeGpx);
for ($j=0; $j<count($hikeNos); $j++) {
    if (!empty($hikeGpx[$hikeNos[$j]])) {
        $trk_array = getTrackFileNames($pdo, $hikeNos[$j], 'pub')[0];
        $dbJSON = array_merge($dbJSON, $trk_array);
    } 
}
if (count($ehikeGpx) > 0) {
    $ehikeNos = array_keys($ehikeGpx);
    for ($k=0; $k<count($ehikeNos); $k++) {
        if (!empty($ehikeGpx[$ehikeNos[$k]])) {
            $trk_array = getTrackFileNames($pdo, $ehikeNos[$k], 'edit')[0];
            $dbJSON = array_merge($dbJSON, $trk_array);  
        } 
    }
}
$gpsIds = array_keys($gpsDatGpx);
for ($i=0; $i<count($gpsIds); $i++) {
    if (substr($gpsDatGpx[$gpsIds[$i]], 0, 2) !== '..') {
        $gps_arr = json_decode($gpsDatGpx[$gpsIds[$i]], true);
        $trk_array = array_values($gps_arr)[0];
        $dbJSON = array_merge($dbJSON, $trk_array);
    }
}
if (!empty($egpsDatGpx)) {
    $eNos = array_keys($egpsDatGpx);
    for ($m=0; $m<count($eNos); $m++) {
        if (substr($egpsDatGpx[$eNos[$m]], 0, 2) !== "..") {
            $gps_arr = json_decode($egpsDatGpx[$eNos[$m]], true);
            $trk_array = array_values($gps_arr)[0];
            $dbJSON = array_merge($dbJSON, $trk_array);
        }
    }
}

// All database entries for json files has been collected...
$dir_iterator = new DirectoryIterator('../json');

$extraneousHikesJSON = [];
$missingTracks       = [];
// First, are there any files in the json directory that are not in either the
// HIKES/EHIKES or the GPSDAT/EGPSDAT tables? If so, they are 'extraneous'
foreach ($dir_iterator as $file) {
    if (!$file->isDot() && !$file->isDir()) {
        $trackName = $file->getFilename();
        if ($trackName !== ".DS_Store" && $trackName !== 'areas.json'
            && $trackName !== 'filler.json'
        ) {
            if (!in_array($trackName, $dbJSON)) {
                array_push($extraneousHikesJSON, $trackName);
            } 
        }
    }
}
// Does every database track entry have a corresponding json file?
$trackfiles = scandir('../json');
foreach ($dbJSON as $db_entry) {
    if (!in_array($db_entry, $trackfiles)) {
        array_push($missingTracks, $db_entry);
    }
}
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Cleanup JSON files</title>
    <meta charset="utf-8" />
    <meta name="description" content="Check for extraneous photos" />
    <meta name="author" content="Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?php require "../pages/favicon.html";?>
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/cleanGpxJson.css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
<body>
<script src="../scripts/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">JSON File Cleanup</p>
<p id="active" style="display:none">Admin</p>

<div id="main">
<form id="rms" method="post" action="rmJSON.php">
    <h5>Delete the following extraneous files (files in directories not specified
        in database),<br />or check the individual boxes:
    </h5>
    <button id="apply" type="submit" class="btn-sm btn-danger">
        Delete Checked Boxes</button><br />
    <div id="forceCheck">
    <input id="del_all" type="checkbox" />
        &nbsp;&nbsp;Delete All Extraneous JSON files<br />
    </div>
    <h5>There are <?=count($extraneousHikesJSON);?> JSON files existing in
        the database tables that are NOT found in the HIKES/EHIKES tables and
        are also NOT found in the GPSDAT/EGPSDAT table
    </h5>
    <div id="extraneous">
    <?php 
    if (count($extraneousHikesJSON) > 0) {
        foreach ($extraneousHikesJSON as $track) {
            $line = "<input name='ext[]' class='ext' type='checkbox' " .
                "value='" . $track . "' />&nbsp;&nbsp;<span>" . $track . 
                "</span><br />" . PHP_EOL;
            echo $line;
        }
    } else {
        echo "No extraneous gpx files found";
    }
    ?>
    </div>

    <h5>The following files are specified in the database tables, but NOT found
        in the json directory
    </h5>
    <div id="mjson">
    <?php 
    if (count($missingTracks) > 0) {
        foreach ($missingTracks as $hiketrack) {
            echo $hiketrack . "<br />";
        }  
    } else {
        echo "There are no track files missing from the database tables.";
    }
    ?>
    </div>
</form>
</div><br /><br />  <!-- end main -->

<script src="cleanJSON.js"></script>

</body>
</html>
