<?php
/**
 * This is the default home page for the ktesa site. It consists of
 * a page-wide google map w/pins indicating hike locations, and a table
 * of hikes below which can be sorted/filtered.
 * PHP Version 7.1
 * 
 * @package Home
 * @author  Tom Sandberg and Ken Cowles <krcowle29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
require "alphabeticHikes.php";
?> 
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>New Mexico Hikes</title>
    <meta charset="utf-8" />
    <meta name="description"
        content="Listing of hikes the authors have undertaken in New Mexico" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/jquery-ui.css" type="text/css" rel="stylesheet" />
    <link href="../styles/ktesaPanel.css" type="text/css" rel="stylesheet" />
    <link href="../styles/home.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery-1.12.1.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
</head>

<body>

<?php require "ktesaPanel.php"; ?>
<p id="trail">Welcome!</p>
<p id="page_id" style="display:none;">Home</p>

<div id="map"></div>

<div id="adjustWidth" class="custom"></div>
<div id="sideTable">
</div>

<p id="geoSetting">ON</p>
<img id="geoCtrl" src="../images/geoloc.png" alt="Geolocation symbol" />

<input id="searchbar" placeholder="Search for a hike" list="hikelist" />
<?= $datalist; ?>

<?php
require "../php/mapJsData.php";
?>
<script>
// data required for map and side tables
var VC = <?= $jsVCs;?>;
var CL = <?= $jsClusters;?>;
var NM = <?= $jsHikes;?>;
var tracks = <?= $jsTracks;?>;
var allHikes = <?= $jsIndx;?>;
var locations = <?= $jsLocs;?>;
</script>
<script src="../scripts/modernizr-custom.js"></script>
<script src="../scripts/menus.js"></script>
<script src="../scripts/markerclusterer.js"></script>
<script src="../scripts/map.js"></script>
<script src="../scripts/sideTables.js"></script>
<script src="../scripts/modal_setup.js"></script>
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA2Guo3uZxkNdAQZgWS43RO_xUsKk1gJpU&callback=initMap&v=3&libraries=geometry">
</script>
</body>
</html>
