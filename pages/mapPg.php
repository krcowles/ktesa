<?php
/**
 * This page sets up the format for the user-selected page display type.
 * If the page is "Table Only", no map is required or shown. If the
 * page is "Full Map", no table is displayed. For "Map + Table", the
 * display will present both. The $includeZoom variable, if true, will add
 * the 'map it' symbol to the table, which, when the user clicks it, will
 * cause the map to center and zoom on the selected hike.
 * PHP Version 7.1
 * 
 * @package Display_Page
 * @author  Tom Sandberg and Ken Cowles <krcowle29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
// T -> Table only; D -> Dual Map + Table; M -> full page map;
$tblVar = filter_input(INPUT_GET, "tbl");
// the following are required for ALL options:
$usr = 'mstr'; // this is actually a "don't care", but needs to be specified
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
    <!-- jQuery UI widgets styles -->
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
<!-- Geosetting and New Hike Box -->
<?php if ($tblVar !== 'T') : ?>
    <p id="geoSetting">ON</p>
    <img id="geoCtrl" src="../images/geoloc.png" />
    <div id="newHikeBox">Latest Hike:<br>
        <a id="newhike" href="#"><span id="winner"></span></a>
    </div>
<?php endif; ?>
<!-- Page Type Settings -->
<?php if ($tblVar === 'T' || $tblVar === 'D') : ?>
    <?php if ($tblVar === 'T') : ?>
    <?php include "pageTop.php"; ?>
    <p id="trail">Sortable Table of Hikes</p>
    <script type="text/javascript">var pg = "tbl";</script>
    <?php else : ?>
        <div id="map" style="width:100%;"></div>
        <script type="text/javascript">var pg = "map";</script>
    <?php endif; ?>
    <!--Sub-table Filtering and SortingOptions: -->
    <div id="tblfilter">
        <button id="showfilter"><strong>Show/Hide Table Filtering</strong></button>
        <div id="dispopts">
            <strong class="blue">Sort the table of hikes by proximity:</strong><br />
            Hikes within 
            <input id="spinner" />&nbsp;miles of&nbsp;&nbsp;(Choose either:)
            <input id="loc" type="radio" name="prox" />
            <label id="loclbl" class="normal">Area:</label>
            <div id="selloc" class="hidden">
                (Select)&nbsp;<?php include "../build/localeBox.html";?>
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
    <!-- End of sub-table filter/sort -->
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
    <script src="../scripts/markerclusterer.js"></script>
    <script src="../scripts/map.js"></script>
    <?php if ($tblVar === 'D') : ?>
        <script src="../scripts/filter.js"></script>
        <script src="../scripts/phpDynamicTbls.js"></script>
        <script src="../scripts/multi-sort.js"></script>
    <?php endif; ?>
    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA2Guo3uZxkNdAQZgWS43RO_xUsKk1gJpU&callback=initMap&v=3&libraries=geometry">
    </script>
<?php else : ?>
    <script src="../scripts/filter.js"></script>
    <script src="../scripts/tblOnlySort.js"></script>
    <script src="../scripts/multi-sort.js"></script>
<?php endif; ?>
</body>
</html>
