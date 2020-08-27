<?php
/**
 * This page displays a map showing only the user's favorites, and markers 
 * on the map for each favorite.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
$userid = $_SESSION['userid'];

$favreq = "SELECT `hikeNo` FROM `FAVORITES` WHERE `userid` = :uid;";
$usrfavs = $pdo->prepare($favreq);
$usrfavs->execute(["uid" => $userid]);
$favarray = $usrfavs->fetchAll(PDO::FETCH_COLUMN);
/**
 * Get hike data for each favorite (this should be a rather small list!);
 * Form js arrays for side table and marker creation (akin to jsMapData.php 
 * on home.php). No VC or CL hikes on this page.
 */ 
$hikeobj = [];
$tracks = [];
foreach ($favarray as $hike) {
    $fhikereq = "SELECT * FROM `HIKES` WHERE `indxNo` = :hno;";
    $fhike = $pdo->prepare($fhikereq);
    $fhike->execute(["hno" => $hike]);
    $hikedat = $fhike->fetch(PDO::FETCH_ASSOC);
    $hike = '{name:"' . $hikedat['pgTitle'] . '",indx:' . $hikedat['indxNo'] .
        ',loc:{lat:' . $hikedat['lat']/LOC_SCALE . ',lng:' .
        $hikedat['lng']/LOC_SCALE . '},lgth:' .
        $hikedat['miles'] . ',elev:' . $hikedat['feet'] . ',diff:"' . 
        $hikedat['diff'] . '",dir:"' . $hikedat['dirs'] . '"}';
    array_push($hikeobj, $hike);
    array_push($tracks, '"' . $hikedat['trk'] . '"');
    
}
$locaters = [];
$nmindx = 0;
for ($j=0; $j<count($favarray); $j++) {
    $locater = '{type:"nm",group:' . $nmindx++ . '}';
    array_push($locaters, $locater);
}
$jsHikes  = '[' . implode(",", $hikeobj)  . ']';
$allHikes = '[' . implode(",", $favarray) . ']';
$jsLocs   = '[' . implode(",", $locaters) . ']';
$jsTracks = '[' . implode(",", $tracks)   . ']';

?> 
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Your Favorites</title>
    <meta charset="utf-8" />
    <meta name="description"
        content="Map with User Favorites identified" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/jquery-ui.css" type="text/css" rel="stylesheet" />
    <link href="../styles/ktesaPanel.css" type="text/css" rel="stylesheet" />
    <link href="../styles/home.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
</head>

<body>
<?php require "ktesaPanel.php"; ?>
<p id="trail">Welcome!</p>
<p id="page_id" style="display:none;">Favorites</p>

<p id="geoSetting">ON</p>
<img id="geoCtrl" src="../images/geoloc.png" alt="Geolocation symbol" />

<div id="map"></div>

<div id="adjustWidth" class="custom"></div>

<div id="sideTable"></div>

<script>
    var VC = [];
    var CL = [];
    var NM = <?= $jsHikes;?>;
    var allHikes = <?= $allHikes;?>;
    var locations = <?= $jsLocs;?>;
    var tracks = <?= $jsTracks;?>;
</script>
<script src="../scripts/menus.js"></script>
<script src="../scripts/fmap.js"></script>
<script src="../scripts/sideTables.js"></script>
<script src="../scripts/modal_setup.js"></script>
<script src="../scripts/favTable.js"></script>
<script src="../scripts/markerclusterer.js"></script>
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA2Guo3uZxkNdAQZgWS43RO_xUsKk1gJpU&callback=initMap&v=3&libraries=geometry">
</script>
</body>
</html>
