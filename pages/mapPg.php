<?php
/**
 * This page sets up the format for the user-selected page display type.
 * If the page is "Table Only", no map is required or shown. If the
 * page is "Full Map", no table is displayed. For "Map + Table", the
 * display will present both.
 * PHP Version 7.1
 * 
 * @package Display_Page
 * @author  Tom Sandberg and Ken Cowles <krcowle29@gmail.com>
 * @license No license to date
 */
$geoVar = filter_input(INPUT_GET, "geo");
$tblVar = filter_input(INPUT_GET, "tbl");
// T -> Table only; D -> Dual Map + Table; M -> full page map
// required for ALL cases:
$usr = 'mstr'; // this is actually a "don't care"
$age = 'old';
$show = 'all';
$includeZoom = ($tblVar === 'D') ? true : false;
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
    <!-- jQuery UI widgets -->
    <link href="../styles/jquery-ui.css" type="text/css" rel="stylesheet" />
<?php if ($tblVar === 'T') : ?>
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <link href="../styles/tblPg.css" type="text/css" rel="stylesheet" />
    <link href="../styles/filter.css" type="text/css" rel="stylesheet" />
<?php elseif ($tblVar === 'D') : ?>
    <link href="../styles/mapTblPg.css" type="text/css" rel="stylesheet" />
    <link href="../styles/filter.css" type="text/css" rel="stylesheet" />
<?php else : ?>
    <link href="../styles/mapPg.css" type="text/css" rel="stylesheet" />
<?php endif; ?>
    <script src="../scripts/jquery-1.12.1.js"></script>
</head>

<body>
<!-- GEOSETTING -->
<?php if ($tblVar !== 'T') : ?>
    <p id="geoSetting">
    <?php if ($geoVar === 'ON') : ?>
        ON</p>
        <div id="geoCtrl">Geolocate Me!</div>
    <?php else : ?>
        OFF</p>
    <?php endif; ?>
    <div id="newHikeBox">Latest Hike:<br>
        <a id="newhike" href="#"><span id="winner"></span></a>
    </div>
<?php endif; ?>
<!-- PAGE SETTING -->
<?php if ($tblVar === 'T' || $tblVar === 'D') : ?>
    <?php if ($tblVar === 'T') : ?>
        <div id="logo">
            <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
            <p id="logo_left">Hike New Mexico</p>
            <img id="tmap" src="../images/trail.png" alt="trail map icon" />
            <p id="logo_right">w/Tom &amp; Ken</p>
        </div>
        <p id="trail">Sortable Table of Hikes</p>
        <script type="text/javascript">var pg = "tbl";</script>
    <?php else : ?>
        <div id="map" style="width:100%;"></div>
        <script type="text/javascript">var pg = "map";</script>
    <?php endif; ?>
    <!-- Table Filtering Options: -->
    <div id="tblfilter">
        <button id="showfilter"><strong>Show Table Filtering Options</strong></button>
        <div id="dispopts">
            <strong style="color:darkblue;">Sort the table of hikes by proximity:</strong><br />
            Hikes within <input id="spinner" />&nbsp;miles of&nbsp;&nbsp;(Choose either:)
            <div style="margin-left: 190px;">
                <label id="loclbl" class="normal">Area:</label>
                    <input id="loc" type="radio" name="prox" />
                <div id="selloc" class="hidden">
                    (Select)&nbsp;<?php include "../build/localeBox.html";?>
                </div>&nbsp;&nbsp;<span style="color:brown;">OR</span><br />
                <label id="hikelbl" class="normal">Hike/Trail</label>
                    <input id="hike" type="radio" name="prox" />
                <div id="selhike" class="hidden">
                    <input id="link" type="text" name="link" size="35"
                        placeholder="...select hike by clicking link in table" />
                </div>
            </div>
            <button id="apply">Apply Filter</button>
        </div>
    </div>
    <p id="filtnote">
        <strong id="note">NOTE:</strong>
        All table columns can be sorted alphabetically/numerically by clicking
        on the column header at the top of the column. Clicking again reverses
        the sort.
    </p>
    <div id="refTbl">
    <?php include "../php/makeTables.php"; ?>
    </div>
    <!-- usrTbl for map+table page only -->
    <?php if ($tblVar === 'D') : ?>
        <div id="usrTbl"></div>
    <?php endif; ?>
    <div style="margin-top:20px;"><p id="metric" class="dressing">
        Click here for metric units</p></div>
<?php else : ?>
    <div id="map" style="width:100%;"></div>
    <div id="refTbl">;
    <?php include "../php/makeTables.php"; ?>
    </div>
<?php endif; ?>
<script src="../scripts/modernizr-custom.js"></script>
<script src="../scripts/jquery-ui.min.js"></script>
<?php if ($tblVar !== 'T') : ?>
    <script src="../scripts/hikeBox.js"></script>
    <script src="../scripts/map.js"></script>
    <?php if ($tblVar === 'D') : ?>
        <script src="../scripts/filter.js"></script>
        <script src="../scripts/phpDynamicTbls.js"></script>
    <?php endif; ?>
    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA2Guo3uZxkNdAQZgWS43RO_xUsKk1gJpU&callback=initMap&v=3&libraries=geometry">
    </script>
<?php else : ?>
    <script src="../scripts/filter.js"></script>
    <script src="../scripts/tblOnlySort.js"></script>
<?php endif; ?>
</body>
</html>
