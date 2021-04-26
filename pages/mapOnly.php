<?php
/**
 * This page is viewable from mobile devices only: it is a simplified version
 * of the map with sideTables page.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowle29@gmail.com>
 * @license No license to date
 */
session_start();
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
    <meta name="author" content="Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous" />
    <link href="../styles/ktesaNavbar.css" rel="stylesheet" />
    <link href="../styles/mapOnly.css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>
</head>

<body>

<?php require "ktesaNavbar.php"; ?>
<div id="trail">Welcome!</div>

<div id="map"></div>

<img id="geoCtrl" src="../images/geoloc.png" alt="Geolocation symbol" />

<input id="searchbar" placeholder="Search for a hike" list="hikelist" />
<?= $datalist; ?>

<?php
require "../php/mapJsData.php";
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
    window.name = "homePage";
</script>
<script src="../scripts/markerclusterer.js"></script>
<script src="../scripts/searchbar.js"></script>
<script src="../scripts/mapOnly.js"></script>
<script async defer src="<?=Google_Map;?>"></script>

</body>
</html>
