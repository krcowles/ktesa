<?php
/**
 * Hiking club memmbers will have access to 'club assets',
 * comprising unverified gpx files available for download.
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Club Assets</title>
    <meta charset="utf-8" />
    <meta name="description" content="Club assets" />
    <meta name="author" content="Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/clubAssets.css" rel="stylesheet" />
    <?php require "../pages/iconLinks.html"; ?>
    <script src="../scripts/jquery.js"></script>
</head>
<body>
<script src="../scripts/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "ktesaPanel.php"; ?>
<p id="active" style="display:none">Assets</p>

<div id="sidebar">
    <div id="states">
        <p>Out of state assets:</p>
        <select id="oos_locs">
            <option value="Colorado">Colorado</option>
            <option value="Arizona">Arizona</option>
            <option value="Utah">Utah</option>
        </select><br /><br />
        <button id="out-of-state" type="button" class="btn btn-success">
            Retrieve assets</button>
    </div>
    <div id="uploads">
        <form action="../php/uploadgpx.php" method="POST"
                enctype="multipart/form-data">
            <p>You can upload a gpx file here:</p>
            <input id="filename" type="file"  placeholder="GPX filename" />
            <br /><br />Region:
            <select id="uload_loc:">
                <option value="nw1">Northwest</option>
                <option value="nw2">Northcentral</option>
                <option value="more">More to come...</option>
            </select><br /><br />
            <button id="gpxfile" type="submit" class="btn btn-success">
                Upload</button>
        </form>
    </div>
</div>

<div class="vertical_rule"></div>

<div id="contents">
    <span id="howto">Click on a section below to view its assets</span>
    <div id="map_box">
        <img id="nm" src="../images/nmmap.gif" />
        <div id="tray">
            <div id="slice1" class="overlay">
                <div id="nw1" class="sections">A</div>
                <div id="nw2" class="sections">B</div>
                <div id="nw3" class="sections">C</div>
            </div>
            <div id="slice2" class="overlay">
                <div id="lnw1" class="sections">D</div>
                <div id="lnw2" class="sections">E</div>
                <div id="lnw3" class="sections">F</div>
            </div>
            <div id="slice3" class="overlay">
                <div id="c1" class="sections">G</div>
                <div id="c2" class="sections">H</div>
                <div id="c3" class="sections">I</div>
            </div>
            <div id="slice4" class="overlay">
                <div id="cs1" class="sections"></div>
                <div id="cs2" class="sections"></div>
            </div>
            <div id="slice5" class="overlay">
                <div id="s1" class="sections"></div>
                <div id="s2" class="sections"></div>
            </div>
        </div>
    </div>
</div>

<script src="../scripts/clubAssets.js"></script>
</body>
</html>

