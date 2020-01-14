<?php
/**
 * This is the default home page for the ktesa site. It consists of
 * a page-wide google map w/pins indicating hike locations, and a table
 * of hikes below which can be sorted/filtered.
 * PHP Version 7.1
 * 
 * @package Home
 * @author  Tom Sandberg and Ken Cowles <krcowle29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
require "alphabeticHikes.php";

// the following are required for ALL options (used by makeTables.php)
$usr = 'mstr'; // this is actually a "don't care", but needs to be specified
$age = 'old';
$show = 'all';
$includeZoom = true;
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
    <link href="../styles/jquery-ui.css" type="text/css" rel="stylesheet" />
    <link href="../styles/ktesaPanel.css" type="text/css" rel="stylesheet" />
    <link href="../styles/home.css" type="text/css" rel="stylesheet" />
    <link href="../styles/filter.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery-1.12.1.js"></script>
    <script src="../scripts/jquery-ui.js"></script>

    <!-- Used by scripts/filter.js depending on whether table page or map page -->
    <script type="text/javascript">
        var pg = "map";
    </script>
</head>

<body>

<?php require "ktesaPanel.php"; ?>
<p id="trail">Welcome!</p>
<p id="page_id" style="display:none;">Home</p>

<div id="map"></div>

<!-- GPS Position Location -->
<p id="geoSetting">ON</p>
<img id="geoCtrl" src="../images/geoloc.png" />

<input id="searchbar" placeholder="Search for a hike" list="hikelist" />
<?= $datalist; ?>

<a id="legend" href="#">Map Legend</a>
<div id="maplegend">
    <div id="header">Drag Here<div id="can">X</div></div>
    <ul id="mapsyms">
        <li><img src="../images/markerclusters/m1.png" alt="Cluster Marker" 
            width="32" />Click on any color cluster group (or zoom in) to
            expand the hikes</li>
        <li><img src="../images/bluepin.png" alt="Blue Pin" height="32" />
            Click this pin to show multiple hikes with 
            trailheads in close proximity</li>
        <li><img src="../images/redpin.png" alt="Red Pin" height="32" />
            Click this pin to show a single hike at this 
            location</li>
        <li><img src="../images/yellow.png" alt="Yellow Pin" height="32" />
            Click this pin to see the hikes associated with a 
            Visitor's Center</li>
        <li><img src="../images/geoloc.png" alt="Geolocation Symbol"
            height="32" />Click this symbol to locate your
            current position on the map</li>
    </ul>
</div>

<div id="sideTable">
</div>

<div id="tbl_container">
<div id="tblfilter">
    <button id="showfilter"><strong>Show/Hide Table Filtering</strong></button>
    <div id="dispopts">
        <strong class="blue">Sort the table of hikes by proximity:</strong><br />
        Hikes within 
        <input id="spinner" />&nbsp;miles of&nbsp;&nbsp;(Choose either:)
        <input id="loc" type="radio" name="prox" />
        <label id="loclbl" class="normal">Area:</label>
        <div id="selloc" class="hidden">
            (Select)&nbsp;<?php require "../build/localeBox.html";?>
        </div>&nbsp;&nbsp;<span style="color:brown;">OR</span>
        <input id="hike" type="radio" name="prox" />
        <label id="hikelbl" class="normal">Hike/Trail</label>
        <div id="selhike" class="hidden">
            <input style="font-size:14px;" 
                id="link" type="text" name="link" size="35"
                placeholder="...select hike by clicking link in table" />
        </div><br /><br />
        <button id="apply">Apply Filter</button><br />
        <strong class="blue">Then sort the table:</strong><br />By&nbsp;&nbsp;
        <select id="sort1">
            <option value="No Sort">Do Not Sort</option>
            <option value="WOW Factor">WOW Factor</option>
            <option value="Length">Length</option>
            <option value="Elev Chg">Elev Chg</option>
            <option value="Difficulty">Difficulty</option>
            <option value="Exposure">Exposure</option>
            
        </select>&nbsp;&nbsp;Then by: 
        <select id="sort2">
            <option value="No Sort">Do Not Sort</option>
            <option value="WOW Factor">WOW Factor</option>
            <option value="Length">Length</option>
            <option value="Elev Chg">Elev Chg</option>
            <option value="Difficulty">Difficulty</option>
            <option value="Exposure">Exposure</option>
        </select>&nbsp;&nbsp;
        <button id="sort">Sort</button><br /><br />
        <div id="results" style="display:none;">
            <button id="redo">Reset Search</button><br />
            The results of your search appear in the table below:<br />
            <table id="ftable" class="fsort"><tbody></tbody></table>
        </div>
    </div>
</div>
<p id="filtnote">
    <strong id="note">NOTE:</strong>
    All table columns can be sorted alphabetically/numerically by clicking
    on the column header at the top of the column. Clicking again reverses
    the sort.
</p>
<div id="refTbl">
<?php require "../php/makeTables.php"; ?>
</div>
<div id="usrTbl"></div>
<div style="margin-top:20px;"><p id="metric" class="dressing">
    Click here for metric units</p>
</div>
</div>
<div id="debug"></div>

<script src="../scripts/modernizr-custom.js" type="text/javascript"></script>
<script src="../scripts/menus.js" type="text/javascript"></script>
<script src="../scripts/markerclusterer.js" type="text/javascript"></script>
<script src="../scripts/filter.js" type="text/javascript"></script>
<script src="../scripts/multi-sort.js" type="text/javascript"></script>
<script src="../scripts/map.js" type="text/javascript"></script>
<script src="../scripts/sideTables.js" type="text/javascript"></script>
<script src="../scripts/modal_setup.js" type="text/javascript"></script>
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA2Guo3uZxkNdAQZgWS43RO_xUsKk1gJpU&callback=initMap&v=3&libraries=geometry"
    type="text/javascript">
</script>
</body>
</html>
