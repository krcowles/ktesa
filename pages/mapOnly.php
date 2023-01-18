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

<script src="https://unpkg.com/@popperjs/core@2.4/dist/umd/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "ktesaNavbar.php"; ?>
<div id="trail">Welcome!</div>

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

<div id="offline" class="modal" tabindex="-1">
    <div class="modal-dialog" style="max-width:60%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Internet Offline</h5>
                <button type="button" class="btn-close"
                    data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5>The Internet is Not Connected; Track data will still
                  accumulate. When Internet resumes, track will display
                  again.</h5>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script>
    // data required for map and side tables (from mapJsData.php)
    var CL = <?=$jsClusters;?>;    // cluster hikes
    var NM = <?=$jsHikes;?>;       // normal hikes
    var tracks = <?=$jsTracks;?>;
    var allHikes = <?=$jsIndx;?>;
    var locations = <?=$jsLocs;?>;
    var pages = <?=$jsPages;?>;    // page indxNo for non-hikes
    var pgnames = <?=$jsPageNames;?>;
    var hikeSources = <?=$jsItems;?>;
    window.name = "homePage";
    window.newBounds = false;
</script>
<script src="../scripts/logo.js"></script>
<script src="../scripts/markerclusterer.js"></script>
<script src="../scripts/searchbar.js"></script>
<script src="../scripts/mapOnly.js"></script>
<script async defer src="<?=GOOGLE_MAP;?>"></script>

</body>
</html>
