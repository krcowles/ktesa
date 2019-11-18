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
session_start(); // required by ktesaPanel.php
require "../php/global_boot.php";
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
    <script type="text/javascript">var pg = "map";</script>
</head>

<body>
<p id="geoSetting">ON</p>
<img id="geoCtrl" src="../images/geoloc.png" />
<div id="newHikeBox">Latest Hike:<br>
    <a id="newhike" href="#"><span id="winner"></span></a>
</div>
<?php require "ktesaPanel.php"; ?>
<p id="trail">Welcome!</p>
<p id="page_id" style="display:none;">Home</p>

<div id="map" style="width:100%;"></div>
<p id="viewmore">&lt;&lt;  Scroll Down for the sortable 'Table of Hikes' &gt;&gt;</p>

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
<div id="modals">
    <div id="srch">
        <input id="opt1" type="radio" name="opts" class="modopts" />&nbsp;&nbsp;
            <span class="radiotxt">Show location on map</span><br />
        <input id="opt2" type="radio" name="opts" class="modopts" />&nbsp;&nbsp;
            <span class="radiotxt anchorstyle"><a id="hpg" href="#" target="_self">
                Go to hike web page</a></span><br />
        <div id="neither"></div>
    </div>
</div>

<script src="../scripts/modernizr-custom.js" type="text/javascript"></script>
<script src="../scripts/menus.js" type="text/javascript"></script>
<script src="../scripts/hikeBox.js" type="text/javascript"></script>
<script src="../scripts/markerclusterer.js" type="text/javascript"></script>
<script src="../scripts/filter.js" type="text/javascript"></script>
<script src="../scripts/phpDynamicTbls.js" type="text/javascript"></script>
<script src="../scripts/multi-sort.js" type="text/javascript"></script>
<script src="../scripts/map.js" type="text/javascript"></script>
<script src="../scripts/modal_setup.js" type="text/javascript"></script>
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA2Guo3uZxkNdAQZgWS43RO_xUsKk1gJpU&callback=initMap&v=3&libraries=geometry"
    type="text/javascript">
</script>
</body>
</html>
