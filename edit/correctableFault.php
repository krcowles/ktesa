<?php
/**
 * When a 'save tab1' Apply results in uploading gpx files (main or additional),
 * it is possible that a user-correctable error may occur. This script allows
 * the user to correct the sitution and complete the upload and saving of tab1.
 * At this time, the only correctable-fault occurs when a waypoint symbol is not
 * supported by this site at this time.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
require "gpxWaypointSymbols.php";

$hikeNo = filter_input(INPUT_GET, 'hikeNo');
$title = "User Correctable Fault";

$_SESSION['symfault'] = rtrim($_SESSION['symfault'], "|");
$faultInfo = $_SESSION['symfault'];
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>User-Correctable Fault</title>
    <meta charset="utf-8" />
    <meta name="description" content="Edit the selected hike" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/correctableFault.css" rel="stylesheet" />
    <?php require "../pages/iconLinks.html"; ?>
    <script src="../scripts/jquery.js"></script>
</head>
<body>
    <div id="logo">
        <div id="pattern"></div>
        <div id="pgheader">
            <div id="leftside">
                <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
                <span id="logo_left">Hike New Mexico</span>
            </div>
            <div id="center"><?=$title;?></div>
            <div id="rightside">
                <span id="logo_right">w/Tom &amp; Ken</span>
                <img id="tmap" src="../images/trail.png" alt="trail map icon" />
            </div>
        </div>   
    </div>
    <p id="appMode" style="display:none;"><?=$appMode;?></p>
    <p id="hikeNo" style="display:none;"><?=$hikeNo;?></p>
    <div id="content">
        <h4>One or more user-correctable faults were discovered while uploading
            your gpx file. Please make the corrections below, and when all 
            replacements are done, click on 'Continue Upload'
        </h4>
        <hr />
        <h5><em>The following unsupported gpx waypoint symbol(s) was/were identified.
            Please select a replacement symbol from the drop-down box for each symbol
            and click on 'Replace Symbol'. The original gpx file will not be altered.
            </em>
        </h5>
        <p id="fdata" style="display:none;"><?=$faultInfo;?></p>
        <div id="corrections">

        </div>
        <div>
            <hr />
            <button id="finish" type="button" class="btn btn-success">
                Continue Upload</button>
        </div>
    </div>
    <script src="correctableFault.js"></script>
    <script type="text/javascript">
        var html_syms ='<?=$select_sym;?>';
        var $apply = $('<button type="button" class="btn btn-secondary fix">' +
            'Replace Symbol</button>');
    </script>
</body>

</html>