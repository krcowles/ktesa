<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Load All Tables</title>
    <meta charset="utf-8" />
    <meta name="description" content="Present tools for admin of site" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <link href="admintools.css" type="text/css" rel="stylesheet" />
    <style type="text/css">
        #progress { width: 420px; height: 36px; background-color: #ace600; }
        #bar { width: 0px; height: 36px; background-color: #aa0033; }
    </style>
    <script src="../scripts/jquery-1.12.1.js"></script>
<body>
<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>	
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">Loading Database</p>
<div style="margin-left:16px;">
<p>Please wait until the 'DONE' message appears below</p>
<div id="progress">
    <div id="bar"></div>
</div>
<script src="load_progress.js"></script>
<?php
require_once "../mysql/dbFunctions.php";
$link = connectToDb(__FILE__, __LINE__);
// Temporary variable, used to store current query
$templine = '';
// Read in entire file
$dbFile = "../data/id140870_hikemaster.sql";
$lines = file($dbFile);
if (!$lines) {
    $thisFile = __FILE__;
    $thisLine = __LINE__;
    die("Failure in {$thisFile} line: {$thisLine}: Failed to read database from file: {$dbFile}.");
}
// Loop through each line
$gottbl = false;
$totalQs = 0;
// doing this twice, once just to get info for the progress bar:
foreach ($lines as $line) {
    // Skip it if it's a comment
    if (substr($line, 0, 2) == '--' || $line == '') {
        continue;
    }
    if (substr(trim($line), -1, 1) == ';') {
        $totalQs++;
    }
}
echo "<script type='text/javascript'>var totq = {$totalQs};</script>";
$qcnt = 0;
foreach ($lines as $line) {
    // Skip it if it's a comment
    if (substr($line, 0, 2) == '--' || $line == '') {
        continue;
    }
    // Add this line to the current segment
    $templine .= $line;
    // If it has a semicolon at the end, it's the end of the query
    $qstr = trim($templine);
    if (substr(trim($line), -1, 1) == ';') {
        // look for create and table name
        $createTbl = strpos($qstr, "CREATE TABLE");
        if ($createTbl !== false) {
            $tblLgth = strpos($qstr, '(') - 3 - ($createTbl + 13);
            $tblName = substr($qstr, $createTbl+14, $tblLgth);
            $gottbl = true;
        }
        // Perform the query
        $req = mysqli_query($link, $qstr);
        if (!$req) {
            die("<p>load_all_tables.php: Failed: " .
                mysqli_error($link) . "</p>");
        }
        if (!is_bool($req)) {
        mysqli_free_result($req);
        }
        $qcnt++;
        echo "<script type='text/javascript'>var qcnt = {$qcnt};</script>";
        if ($gottbl) {
            $gottbl = false;
            echo "<br />Completed " . $tblName . " at: " . date('l jS \of F Y h:i:s A');
            flush();
        } else {
            echo "<br />Completed " . substr($qstr, 0, 32) . date('l jS \of F Y h:i:s A');
            flush();
        }
        $templine = '';    // Reset temp variable to empty
    }
}
mysqli_close($link);
?>
<p>DONE: Tables imported successfully</p>
</div>
