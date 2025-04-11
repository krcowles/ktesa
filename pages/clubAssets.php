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

$alert = '';
if (isset($_SESSION['upload_msg']) && $_SESSION['upload_msg'] !== "OK") {
    $alert = $_SESSION['upload_msg'];
}
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
<p id="alert" style="display:none"><?=$alert;?></p>
<p id="pointer" style="display:none;"></p>

<div id="regionid"></div>
<div id="sidebar">
    <div id="states">
        <p>Out of state assets:</p>
        <select id="oos_locs" name="states">
            <option value="Colorado">Colorado</option>
            <option value="Arizona">Arizona</option>
            <option value="Utah">Utah</option>
        </select><br /><br />
        <button id="out-of-state" type="button" class="btn btn-success">
            Retrieve assets</button>
    </div>
    <div id="uploads">
        <form id="uploader" method="post" action="../php/assetUpload.php"
                    enctype="multipart/form-data">
                <p>You can upload a club file here:</p>
                <input id="filename" type="file"  placeholder="GPX filename" 
                    name="asset" />
                <br /><br />
                <textarea placeholder="File Label" rows="1" name="label"></textarea>
                <br /><br />
                Region:
                <select id="uload_loc:" name="nm_location">
                    <option value="box1" class="location">NW Deserts</option>
                    <option value="box2" class="location">Jemez & Abiquiu</option>
                    <option value="box3" class="location">Sangre de Cristos</option>
                    <option value="box4" class="location">NE Plains</option>
                    <option value="box5" class="location">Mt Taylor & Zuni</option>
                    <option value="box6" class="location">Sandias & Monzanos</option>
                    <option value="box7" class="location">Gila & Bootheel</option>
                    <option value="box8" class="location">Lower Rio Grande</option>
                    <option value="box9" class="location">Sierra Blanca Region</option>
                    <option value="box10" class="location">SE New Mexico</option>
                    <option value="Colorado">Colorado</option>
                    <option value="Arizona">Arizona</option>
                    <option value="Utah">Utah</option>
                </select><br /><br />
                <button id="asset_file" type="submit" class="btn btn-success">
                    Upload</button><br />
        </form>
    </div>
</div>

<div class="vertical_rule"></div>

<div id="contents"> <!-- all within text-align: center -->
    <span id="howto">Click on a section below to view its assets</span>
    <div id="map_box">
        <div id="tray">
            <div id="box1" class="sizing"></div>
            <div id="box2" class="sizing"></div>
            <div id="box3" class="sizing"></div>
            <div id="box4" class="sizing"></div>
            <div id="box5" class="sizing"></div>
            <div id="box6" class="sizing"></div>
            <div id="box7" class="sizing"></div>
            <div id="box8" class="sizing"></div>
            <div id="box9" class="sizing"></div>
            <div id="box10" class="sizing"></div>
        </div>
        <div id="nm">
            <img id="nmap" src="../images/nmmap.gif" />
        </div>
    </div>
</div>
<br />

<script src="../scripts/clubAssets.js"></script>
</body>
</html>

