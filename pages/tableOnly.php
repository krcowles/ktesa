<?php
/**
 * This page presents a sortable, filterable table of hikes
 * PHP Version 7.1
 * 
 * @package Display_Page
 * @author  Tom Sandberg and Ken Cowles <krcowle29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
require "alphabeticHikes.php";
// required by makeTables.php
$age = 'old';
$show = 'all';
$pageType = 'FullTable';
?> 
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>New Mexico Hikes</title>
    <meta charset="utf-8" />
    <meta content-type="text/html" />
    <meta name="description"
        content="Table of hikes the authors have undertaken in New Mexico" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/jquery-ui.css" type="text/css" rel="stylesheet" />
    <link href="../styles/ktesaPanel.css" type="text/css" rel="stylesheet" />
    <link href="../styles/tblPg.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
</head>

<body>
<?php require "ktesaPanel.php"; ?>
<p id="trail">Sortable Table of Hikes</p>
<p id="page_id" style="display:none">Table</p>

<div id="optcontainer">
    <div id="tblopts">
        <strong>Options:</strong>&nbsp;&nbsp;
        <div id="opt1" class="topts">
            <button id="showfilter">Filter Hikes</button>
        </div>
        <div id="opt2" class="topts">
            Show Multiple Hikes on a Map
            <button id="multimap">Select</button>
        </div>
        <div id="opt3" class="topts">
            <button id="units">Show Metric Units</button>
        </div>
        <div id="opt4" class="topts">
            <select id="scroller">
                <option value="none">Scroll to:</option>
                <option value="0">Top</option>
                <option value="1">C's</option>
                <option value="2">E's</option>
                <option value="3">L's</option>
                <option value="4">P's</option>
                <option value="5">T's</option>
            </select>
        </div>
    </div>
</div>
<div id="backup">Return to top</div>

<!-- Filtering and SortingOptions: -->
<div id="tblfilter">
    <div>
        <strong class="blue">Filter hikes:&nbsp;&nbsp;</strong>Hikes within 
        <input id="spinner" />&nbsp;miles of
    </div>
    <div id="areachoice">
        <?php require "../build/localeBox.html";?>&nbsp;&nbsp;
        <button id="filtpoi">Filter By Area</button>
    </div>
    <div id="hikechoice">
        Hike Name: 
        <input id="usehike" placeholder="Type in Hike Name" list="hikelist" />
        <?=$datalist;?>&nbsp;&nbsp;
        <button id="filthike">Filter By Hike</button>
        <br /><br />
    </div>
    <div id="sorter">
        <strong class="blue">Then sort the results:</strong><br />By&nbsp;&nbsp;
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
        <button id="sort">Sort</button>
    </div>
    <div id="results" style="display:none;">
        
        The results of your search appear in the table below:<br />
        <table id="ftable" class="fsort"><tbody></tbody></table>
    </div>
</div>
<!-- End of sub-table filter/sort -->
<p id="filtnote">
    <strong id="note">NOTE:</strong>
    Click on any hike column to sort; again to reverse.<br />
</p>

<div id="refTbl">
    <?php require "../php/makeTables.php"; ?>
</div>

<!-- Multimap Modal -->
<div id="usermodal">
    <div id="modalhdr">
        <div id="hdrleft"><strong>Drag Here</strong></div>
        <div id="hdrright"><button id="closer">Close</button></div>
    </div>
    <p>To view multiple hikes on a single map, select the hikes below. When
    you are ready, click on 'Draw Map'</p>
    <button id="mapem">Draw Map</button>
    <p><input id="hike2map" placeholder="Type in next Hike Name"
        list="hikelist" />&nbsp;&nbsp;&nbsp;
        <button id="hikeclr">Clear Entries</button>
        <?=$datalist;?></p>
    <span id="hlist">Hikes you have selected:</span>
    <ul id="selections"></ul>
</div>
<script src="../scripts/menus.js"></script>
<script src="../scripts/tblOnlySort.js"></script>
<script src="../scripts/filter.js"></script>
<script src="../scripts/multi-sort.js"></script>
<script src="../scripts/map-multiples.js"></script>

</body>
</html>
