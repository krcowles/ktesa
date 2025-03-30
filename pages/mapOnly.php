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
    <!-- https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous" -->
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
<div id="trail">Welcome!</div>
<p id="appMode" style="display:none;"><?=$appMode;?></p>

<div id="map"></div>

<img id="geoCtrl" src="../images/geoloc.png" alt="Geolocation symbol" />

<div class="ui-widget">
  <style type="text/css">
      ul.ui-widget {
        width: 300px;
        clear: both;
      }
  </style>
  <input id="search" placeholder="Search for a Hike" />
</div>

<?php
require "../php/mapJsData.php";
require "getFavorites.php";
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
<script src="../scripts/logo.js"></script>
<script src="../scripts/markerclusterer.min.js"></script>
<script src="../scripts/searchbar.js"></script>
<script src="../scripts/mapOnly.js"></script>
<script async defer src="<?=GOOGLE_MAP;?>"></script>

</body>
</html>
