<?php
/**
 * This page displays a map showing only the user's favorites, and markers 
 * on the map for each favorite. This script is used only for mobile devices.
 * PHP Version 7.4
 * 
 * @package Ktesa
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
    $hike_tracks = getTrackFileNames($pdo, $hike, 'pub')[0];
    $str_name = '"' . $hike_tracks[0] . '"'; // just main tracks
    array_push($tracks, $str_name);
    $hike = '{name:"' . $hikedat['pgTitle'] . '",indx:' . $hikedat['indxNo'] .
        ',loc:{lat:' . $hikedat['lat']/LOC_SCALE . ',lng:' .
        $hikedat['lng']/LOC_SCALE . '},lgth:' .
        $hikedat['miles'] . ',elev:' . $hikedat['feet'] . ',diff:"' . 
        $hikedat['diff'] . '",dir:"' . $hikedat['dirs'] . '"}';
    array_push($hikeobj, $hike);
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
    <meta name="author" content="Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/mapOnly.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
</head>

<body>
    
<script src="../scripts/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "ktesaNavbar.php"; ?>
<p id="trail">Your Favorites</p>

<p id="geoSetting">ON</p>
<img id="geoCtrl" src="../images/geoloc.png" alt="Geolocation symbol" />

<div id="map"></div>

<!-- Modal when no favorites are found -->
<div id="nofavs" class="modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">No Favorites</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
               You have not marked any favorites yet. You can do so by
               visiting the hike page and clicking the 'Mark as Favorite'
               button
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Links to favorites -->
<button id="favlist" type="button" class="btn btn-primary"
    data-bs-toggle="modal" data-bs-target="#favs">
  List Favorites Pages
</button>
<div id="favs" class="modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Favorites Page Links</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div id="favlinks" class="modal-body">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
 
<script>
    var VC = [];
    var CL = [];
    var NM = <?=$jsHikes;?>;
    var allHikes = <?=$allHikes;?>;
    var locations = <?=$jsLocs;?>;
    var tracks = <?=$jsTracks;?>;
</script>
<script src="../scripts/logo.js"></script>
<script src="../scripts/responsiveFmap.js"></script>
<script src="../scripts/markerclusterer.js"></script>
<script async defer src="<?=GOOGLE_MAP;?>"></script>
</body>
</html>
