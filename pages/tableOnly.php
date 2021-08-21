<?php
/**
 * This page presents a sortable, filterable table of hikes
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowle29@gmail.com>
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
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/ktesaNavbar.css" rel="stylesheet" />
    <!--<link href="../styles/jquery-ui.css" type="text/css" rel="stylesheet" />-->
    <link href="../styles/tblPg.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
</head>

<body>
<script src="https://unpkg.com/@popperjs/core@2.4/dist/umd/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "ktesaPanel.php"; ?>
<p id="trail">Sortable Table of Hikes</p>
<p id="active" style="display:none">Table</p>

<div id="main">
<div id="optcontainer">
    <div id="tblopts">
        <strong>Options:</strong>&nbsp;&nbsp;
        <div id="opt1" class="topts">
            <button id="showfilter" class="tblbtn">Filter Hikes</button>
        </div>
        <div id="opt2" class="topts">
            Show Multiple Hikes on a Map
            <button id="multimap" class="tblbtn">Select</button>
        </div>
        <div id="opt3" class="topts">
            <button id="units" class="tblbtn">Show Metric Units</button>
        </div>
        <div id="opt4" class="topts">
            <select id="scroller" class="selhover">
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

<!-- Filtering and SortingOptions: -->
<div id="tblfilter" class="btstrp">
    <div>
        <strong class="blue">Filter hikes:&nbsp;&nbsp;</strong>Hikes within
        <input id="pseudospin" type="text" value="5" />
        <div id="spinicons">
            <div id="uparw"></div>
            <div id="separator">&nbsp;</div>
            <div id="dwnarw"></div>
        </div>
        <span>miles</span>
    </div>
    <div id="areachoice" class="btstrp">
        <?php require "../edit/localeBox.html";?>&nbsp;&nbsp;
        <button id="filtpoi" class="btstrp">Filter by Area</button>
    </div>
    <div id="hikechoice" class="btstrp">
        Hike Name: 
        <input id="usehike" placeholder="Type in Hike Name" list="hikelist" />
        <?=$datalist;?>&nbsp;&nbsp;
        <button id="filthike" class="btstrp">Filter by Hike</button>
        <br />
    </div><br />
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
        <button id="sort" class="btstrp">Sort</button>
    </div>
    <div id="results" style="display:none;">
        
        The results of your search appear in the table below:<br />
        <table id="ftable" class="fsort"><tbody></tbody></table>
    </div>
</div>
<!-- End of sub-table filter/sort -->
<br />
<p id="filtnote">
    <strong id="note">NOTE:</strong>
    Click on any hike column to sort; again to reverse.<br />
</p>

<div id="backup" class="btstrp">Return to top</div>
<div id="refTbl">
    <?php require "../php/makeTables.php"; ?>
</div>

<!-- Exposure info on mouseover -->
<div id="nodata">
    For Group pages, check each hike<br />
    within the group; otherwise, no data
</div>
</div>
<!-- Multimap Modal -->
<div id="usermodal">
    <div id="modalhdr">
        <div id="hdrleft"><strong>Drag Here</strong></div>
        <div id="hdrright"><button id="closer">Close</button></div>
    </div>
    <p>To view multiple hikes on a single map, begin typing the hike name
        in the box below to select the hikes. When you are ready,
        click on 'Draw Map'</p>
    <button id="mapem">Draw Map</button>
    <p id="usearch"><input id="hike2map" list="hikelist" />&nbsp;&nbsp;&nbsp;<?=$datalist;?>
        <button id="hikeclr">Clear Hikes</button>
    </p>
    <span id="hlist">Hikes you have selected:</span>
    <ul id="selections"></ul>
</div>

<script src="../scripts/columnSort.js"></script>
<script src="../scripts/tableOpts.js"></script>
<script src="../scripts/filter.js"></script>
<script src="../scripts/multi-sort.js"></script>
<script src="../scripts/map-multiples.js"></script>

</body>
</html>
