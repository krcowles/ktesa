<?php
/**
 * This is the home page for the ktesa site when not being viewed by a mobile
 * device. It consists of a google map with markers indicating hike locations,
 * and a side table showing all the hikes in the viewing area, along with some
 * links, info, and a thumbnail for each.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowle29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
require "alphabeticHikes.php";
// find level at which pictures directory resides
$current = getcwd();
$startDir = $current;
$ups = 0;
$rels = '';
while (!in_array('pictures', scandir($current))) {
    chdir('..');
    $current = getcwd();
    $ups++;
    $rels .= '../';  // used in passing info to javascript, below
    if ($ups > 5) { 
        throw new Exception("Can't find pictures directory!");
    }
}
chdir($startDir);  
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
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/home.css" type="text/css" rel="stylesheet" />    
    <script src="../scripts/jquery.js"></script>
</head>

<body>
 <!-- body tag must be read prior to invoking bootstrap.js -->
<script src="https://unpkg.com/@popperjs/core@2.4/dist/umd/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "ktesaPanel.php"; ?>
<p id="trail">Find Your Hike!</p>
<p id="active" style="display:none">Home</p>

<div id="map"></div>

<div id="adjustWidth" class="custom"></div>
<div id="sideTable">
</div>

<img id="geoCtrl" src="../images/geoloc.png" alt="Geolocation symbol" />

<input id="searchbar" placeholder="Search for a hike" list="hikelist" />
<?= $datalist; ?>

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
var favlist = <?=$favlist;?>;
var thumb    = '<?=$rels;?>' + 'pictures/thumbs/';
var preview  = '<?=$rels;?>' + 'pictures/previews';
var loadSpreader; // interval timer for spacing out thumbnail loads
var cluster_click = false; // linked to clicking a clusterer marker
</script>
<script src="../scripts/markerclusterer.js"></script>
<script src="../scripts/map.js"></script>
<script src="../scripts/sideTables.js"></script>
<script async defer src="<?=Google_Map;?>"></script>
</body>
</html>
