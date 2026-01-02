<?php
/**
 * This page is viewable from mobile devices only: it is a simplified version
 * of the home.php page without the side tables.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowle29@gmail.com>
 * @license No license to date
 */
session_start();

require "../php/global_boot.php";
require "autoComplHikes.php";
?> 
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>New Mexico Hikes</title>
    <meta charset="utf-8" />
    <meta name="description"
        content="Listing of hikes the authors have undertaken in New Mexico" />
    <meta name="author" content="Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/jquery-ui.css" rel="stylesheet" />
    <link href="../styles/mapOnly.css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
</head>

<body>

<script src="../scripts/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "mobileNavbar.php"; ?>
<p id="appMode" style="display:none;"><?=$appMode;?></p>

<div id="mapview">
    <div id="imphike" class="ui-widget">
        <style type="text/css">
            ul.ui-widget {
                width: 266px;
                clear: both;
                z-index: 1000;
            }
        </style>
        <input id="search" class="search" placeholder="Search for a Hike" />
        <span id="clear">X</span>
    </div>
    <div id="map"></div>
</div>


<?php
require "../php/mapJsData.php";
require "../pages/getFavorites.php";
?>

<script>
    // data required for map and side tables (from mapJsData.php)
    var CL = <?=$jsClusters;?>;    // cluster hikes
    var NM = <?=$jsHikes;?>;       // normal hikes
    var tracks = <?=$jsTracks;?>;
    var allHikes = <?=$jsIndx;?>;
    var locations = <?=$jsLocs;?>;
    var pages = <?=$jsPages;?>;    // page indxNo for non-hikes
    var pgnames = <?=$jsPageNames;?>;
    var hikeSources = <?=$jsItems;?>; // from autoComplHikes.php
    window.name = "homePage";
    window.newBounds = false;
</script>
<script src="../scripts/markerclusterer.min.js"></script>
<script src="../scripts/searchbar.js"></script>
<script src="../scripts/mapOnly.js"></script>
<script async defer src="<?=GOOGLE_MAP;?>"></script>

</body>
</html>
