<?php
/**
 * This page displays a map showing only the user's favorites with markers 
 * on the map for each favorite. A message is displayed when there are no
 * favorites saved by the user.
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
 * >>> NOTE: This page does not call mapJsData.php but rather creates data below. <<<
 * Get hike data for each favorite (this should be a rather small list!);
 * Form js arrays for side table and marker creation (akin to jsMapData.php 
 * on home.php). No CL hikes on this page.
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
        $hikedat['diff'] . '",dir:"' . $hikedat['dirs'] . '",prev:"' .
        $hikedat['preview'] . '"}';
    array_push($hikeobj, $hike);
    array_push($tracks, '"' . $hikedat['trk'] . '"');
    
}
$locaters = [];
$nmindx = 0;
for ($j=0; $j<count($favarray); $j++) {
    $locater = '{type:"nm",group:' . $nmindx++ . '}';
    array_push($locaters, $locater);
}
// locate the 'thumbs' dir:
$zsizedir = getPicturesDirectory();
$thumbdir = '"' . str_replace('zsize', 'thumbs', $zsizedir) . '"';

$jsHikes  = '[' . implode(",", $hikeobj)  . ']';
$allHikes = '[' . implode(",", $favarray) . ']';
$jsLocs   = '[' . implode(",", $locaters) . ']';
$jsTracks = '[' . implode(",", $tracks)   . ']';

?> 
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>My Favorites</title>
    <meta charset="utf-8" />
    <meta name="description"
        content="Map with User Favorites identified" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/home.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
</head>

<body>
<script src="https://unpkg.com/@popperjs/core@2.4/dist/umd/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "ktesaPanel.php"; ?>
<p id="trail">Your Favorite Hikes</p>
<p id="active" style="display:none;">Favorites</p>

<p id="geoSetting">ON</p>
<img id="geoCtrl" src="../images/geoloc.png" alt="Geolocation symbol" />

<div id="map"></div>

<div id="adjustWidth" class="custom"></div>

<div id="sideTable"></div>

<script>
    var NM = <?=$jsHikes;?>;
    var allHikes = <?=$allHikes;?>;
    var locations = <?=$jsLocs;?>;
    var tracks = <?=$jsTracks;?>;
    var thumb = <?=$thumbdir;?>;
</script>
<script src="../scripts/favSideTable.js"></script>
<script src="../scripts/fmap.js"></script>
<script src="../scripts/markerclusterer.js"></script>
<script async defer src="<?=GOOGLE_MAP;?>"></script>
</body>
</html>
