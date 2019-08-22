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
// required by makeTables.php
$usr = 'mstr'; // this is actually a "don't care", but needs to be specified
$age = 'old';
$show = 'all';
$includeZoom = false;
?> 
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>New Mexico Hikes</title>
    <meta charset="utf-8" />
    <meta name="description"
        content="Table of hikes the authors have undertaken in New Mexico" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/jquery-ui.css" type="text/css" rel="stylesheet" />
    <link href="../styles/ktesaPanel.css" type="text/css" rel="stylesheet" />
    <link href="../styles/tblPg.css" type="text/css" rel="stylesheet" />
    <link href="../styles/filter.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery-1.12.1.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
    <script type="text/javascript">var pg = "tbl";</script> 
</head>

<body>
<?php require "ktesaPanel.php"; ?>
<p id="trail">Sortable Table of Hikes</p>
<p id="page_id" style="display:none">Table</p>

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
<!-- End of sub-table filter/sort -->
<p id="filtnote">
    <strong id="note">NOTE:</strong>
    All table columns can be sorted alphabetically/numerically by clicking
    on the column header at the top of the column. Clicking again reverses
    the sort.
</p>
<div id="refTbl">
    <?php require "../php/makeTables.php"; ?>
</div>
<div style="margin-top:20px;"><p id="metric" class="dressing">
        Click here for metric units</p>
</div>
<script src="../scripts/modernizr-custom.js"></script>
<script src="../scripts/menus.js"></script>
<script src="../scripts/filter.js"></script>
<script src="../scripts/tblOnlySort.js"></script>
<script src="../scripts/multi-sort.js"></script>
</body>
</html>
