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
$modal_hikes = isset($_GET['modal_hikes']) ? $_GET['modal_hikes'] : false;
$favmode = $modal_hikes ? 'yes' : 'no';
if ($favmode === 'yes') {
    $favarray = $modal_hikes;
}
$min_south = 37.0000;
$max_north = 31.3333;
$min_west  = -103.000;
$max_east  = -109.0500;
$hnames = [];
$tracks = [];
$marker_locs = [];
foreach ($favarray as $hike) {
    $fhikereq = "SELECT `pgTitle`,`lat`,`lng`,`bounds` FROM `HIKES` WHERE " .
        "`indxNo` = :hno;";
    $fhike = $pdo->prepare($fhikereq);
    $fhike->execute(["hno" => $hike]);
    $hikedat = $fhike->fetch(PDO::FETCH_ASSOC);
    // hike names
    $str_name = '"' . $hikedat['pgTitle'] . '"';
    array_push($hnames, $str_name);
    // marker positions
    $mpos = "{lat:" . $hikedat['lat']/LOC_SCALE . ",lng:" .
        $hikedat['lng']/LOC_SCALE . "}";
    array_push($marker_locs, $mpos);
    // get json files for main track
    $hike_tracks = getTrackFileNames($pdo, $hike, 'pub')[0];
    $str_name = '"' . $hike_tracks[0] . '"'; // just main tracks
    array_push($tracks, $str_name);
    // get bounds for the hike: (bounds -> sw, ne)
    $box = explode(",", $hikedat['bounds']); // [n,s,e,w]
    $max_north = $box[0] > $max_north ? $box[0] : $max_north;
    $min_south = $box[1] < $min_south ? $box[1] : $min_south;
    $max_east  = $box[2] > $max_east  ? $box[2] : $max_east;
    $min_west  = $box[3] < $min_west  ? $box[3] : $min_west;
}

$allHikes  = '[' . implode(",", $favarray) . ']';
$jsHnames  = '[' . implode(",", $hnames) . ']';
$jsMarkers = '[' . implode(",", $marker_locs) . ']';
$jsTracks  = '[' . implode(",", $tracks) . ']';
$jsBounds  = "{east:" . $max_east . ",north:" . $max_north . ",south:" .
    $min_south . ",west:" . $min_west . "}";
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
<?php require "mobileNavbar.php"; ?>
<p id="trail">Your Favorites</p>
<p id="favmode" style="display:none;"><?=$favmode;?></p>

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

<!-- 'Limit Hikes Shown' Modal -->
<div id="favlimit" class="modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Limit Hikes Shown</h5>
                <button type="button" class="btn-close"
                    data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Limit the hikes shown on this page by checking the corresponding
                    boxes:</p>
                <ul id="show_only">
                </ul>
            </div>
            <div class="modal-footer">
                <button id="show_limited" type="button" class="btn btn-success">
                    Display selected hikes
                </button>
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    var allHikes = <?=$allHikes;?>;
    var hikeNames = <?=$jsHnames;?>;
    var marker_pos = <?=$jsMarkers;?>;
    var tracks = <?=$jsTracks;?>;
    var google_bounds = <?=$jsBounds;?>;
</script>
<script src="../scripts/logo.js"></script>
<script src="../scripts/responsiveFmap.js"></script>
<script async defer src="<?=GOOGLE_MAP;?>"></script>
</body>
</html>
