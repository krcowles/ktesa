<?php
/**
 * It is now possible to create one or more limited size offline
 * map for use when hiking and no internet is available. This page
 * allows the user to specify a location, site hike, or gpx file
 * which will be saved on the browser in its indexedDB database for
 * a limited time (undetermined right now). The user will need to
 * connect to the site while there is still internet available in
 * order to create and save an offline map. Currently zoom is limited
 * to 13-16 in order to minimize browser memory consumption.
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowle29@gmail.com>
 * @license No license to date
 */
session_start();

require "../php/global_boot.php";
require "autoComplHikes.php";
$userid = validSession('saveOffline.php');
$mapnameReq = "SELECT `saved_maps` FROM `MEMBER_PREFS` WHERE `userid`=?;";
$mapnames = $pdo->prepare($mapnameReq);
$mapnames->execute([$userid]);
$usermaps = $mapnames->fetch(PDO::FETCH_ASSOC);
$saved_maps = explode(",", $usermaps['saved_maps']);
if (!empty($saved_maps)) {
    $select_opts = '';
    foreach ($saved_maps as $saved) {
        $select_opts .= '<option value="' . $saved . '">' . $saved . 
            '</option>' . PHP_EOL;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Create Offline Map</title> 
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description"
          content="Save an offline map for use on phone" />
    <meta name="author" content="Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/leaflet.css" rel="stylesheet" />
    <link href="../styles/jquery-ui.css" rel="stylesheet" />
    <link href="../styles/saveOffline.css" rel="stylesheet" />
    <?php require "../pages/iconLinks.html"; ?>
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
</head>

<body>
<!-- body tag must be read prior to invoking bootstrap.js -->
<script src="../scripts/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php 
require "../pages/mobileNavbar.php";
?>


<p id="active" style="display:none">Offline</p>
<p id="appMode" style="display:none"><?=$appMode;?></p>

<!--<span id="imphike">-->
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
<div id="impgpx">
    <form id="form" enctype="multipart/form-data">
        <button id="gpxfileImport" type="submit" class="btn btn-sm btn-success">
        Import</button>&nbsp;
        <input id="gpxfile" type="file" name="gpxfile" /> 
    </form>
</div>
<h5 id="drawer">
    <span id="lnote">NOTE: Zoom must be set to level 13 or higher;</span><br />
    <span>
        To draw, tap "Draw Rectangle" then tap on the map once at the
        location where you wish to begin drawing the rectangle; drag your
        finger to a new location as desired, then release to finish the
        rectangle.
    </span><br /><br />
    <p>Reload this page to add new maps</p>
    <span>Close this window to begin<br />
        <button id="begin" type="button" class="btn btn-sm btn-success">
            Begin</button>
    </span>
</h5>
<div id="rect_btns">
    <button id="setzoom" type="button" class="btn btn-secondary
        btn-sm" onclick="this.blur();">Set Zoom 13</button>&nbsp;&nbsp;
    <span id="drawbtn">
        <button id="rect" type="button" class="btn btn-primary
        btn-sm" onclick="this.blur();">Draw</button>
    </span>&nbsp;&nbsp;
    <span id="rectr">
        Recenter&nbsp;<input type="checkbox" id="newctr" />
    </span>
</div>

<div id="map"></div>
<br /><br />

<!-- MODALS -->
<!-- Page Intro Modal -->
<div id="intro" class="modal" tabindex="-1"
    aria-labelledby="Set up save choices" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Save Options</h5>
                <button type="button" class="btn-close"
                    data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="options">Select one of the options below to save your
                    offline map. You will be prompted for a map name when you
                    click on the 'Save' button.
                </p>
                <label id="saveRect">
                    <input id="rctg" type="checkbox" />&nbsp;&nbsp;Rectangular Area
                </label><br />
                <label id="saveHike">
                    <input id="site" type="checkbox" />&nbsp;&nbsp;Import Site Hike
                </label><br />
                <label id="saveGpx">
                    <input id="savegpx" type="checkbox" />&nbsp;&nbsp;Import GPX File
                </label><br />
                
                <?php if (empty($saved_maps)) : ?>
                    Saved Maps: &nbsp;No saved maps
                <?php else : ?>
                    <div id="allmaps">
                        Saved Maps:&nbsp;
                        <select id="delchoice"><?=$select_opts;?></select>
                        &nbsp;&nbsp;<button id="delmap" type="button"
                            class="btn btn-danger btn-sm"
                            onclick="this.blur();">Delete Map</button>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- Tile Save Status Modal -->
<div id="stat" class="modal" tabindex="-1"
    aria-labelledby="Map Save Status" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Map Tile Save Status</h5>
                <button type="button" class="btn-close"
                    data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <span>Total Number of Map Tiles to be Saved: </span>
                <span id="tcnt"></span><br />
                <div id="progress">
                    <div id="bar"></div>
                </div>
                <p id="complete" style="display:none;color:brown;">SAVED!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- Recenter Map Modal -->
<div id="rctr" class="modal" tabindex="-1"
    aria-labelledby="Map Save Status" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Recenter Map</h5>
                <button type="button" class="btn-close"
                    data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Lat: <input id="newlat" class="latlngs" />&nbsp;Lng:
                <input id="newlng" class="latlngs" />
            </div>
            <div class="modal-footer">
                <button id="movectr" class="btn-sm btn-success">
                    Recenter Map
                </button>
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- Start Over  Modal -->
<div id="redo" class="modal" tabindex="-1"
    aria-labelledby="Map Save Status" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Save or Start Over</h5>
                <button type="button" class="btn-close"
                    data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Map Name:
                <input id="map_name" type="text" />&nbsp;&nbsp;
                <button type="button" id="save_map" class="btn btn-success btn-sm">
                    Save Map</button><br /><br />
                <span id="newrect">
                    <button id="clearrect" type="button" class="btn btn-sm
                        btn-warning">Clear Rectangle</button>
                        &nbsp;Redraw the rectangle
                </span><br /><br />
                <span>
                    <button id="restart" type="button" class="btn btn-sm
                        btn-danger">Restart</button> &nbsp;Start the map-selection
                        process over
                </span>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var hikeSources = <?=$jsItems;?>;
</script>
<script src="../scripts/ktesaOfflineDB.js"></script>
<script src="../scripts/leaflet.js"></script>
<script src="../scripts/saveOffline.js"></script>

<?php
// During testing, delete certain caches as needed
$deleteCaches = false;
$names = '["carlito", "carl"]';
if ($deleteCaches) {
    echo "<script type='text/javascript'>var cacheNames = {$names}</script>";
    echo '<script src="../scripts/removeTestCache.js"></script>';
}
?>

</body>
</html>
