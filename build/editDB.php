<?php
/**
 * This module comprises the framework for editing a hike page. Each tab
 * within the framework is a module which allows editing a section of the 
 * database and/or uploading of key user files. When the apply button is 
 * clicked on any tab, the changes are registered, and the user is returned
 * to the same tab with the refreshed data displayed.
 * PHP Version 7.1
 * 
 * @package Editing
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
require "dataForEditor.php";
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Edit Database</title>
    <meta charset="utf-8" />
    <meta name="description" content="Edit the selected hike" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/jquery-ui.css" type="text/css" rel="stylesheet" />
    <link href="../styles/ktesaPanel.css" type="text/css" rel="stylesheet" />
    <link href="editDB.css" type="text/css" rel="stylesheet" />
    <link href="refs.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
</head>

<body>   
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">Hike Editor</p>
<p id="page_id" style="display:none">Build</p>
<p id="hikeNo" style="display:none"><?= $hikeNo;?></p>
<p id="entry" style="display:none"><?= $tab;?></p>

<div id="main" style="padding:16px;margin-bottom:0px;">
<h3 style="margin-top:0px;margin-bottom:0px;">
    <em style="font-style:italic;color:DarkBlue;"><?= $pgTitle;?></em>: 
    Changes below will be applied to this hike. To save your edits, 
    select the 'Apply' button at the top. When you are done applying edits,
    or if no edits are being made, you may simply exit this page. Note 
    that the changes, though saved, will not show up on the main site until
    they have been formally released.
</h3>
<p style="font-size:18px;">Preview page with applied edits:&nbsp;
    <button id="preview">Preview</button><span id="atxt">Apply the Edits</span>
</p>
<!-- tabs -->
<button id="t1" class="tablist active">Basic Data</button>
<button id="t2" class="tablist">Photo Selection</button>
<button id="t3" class="tablist">Descriptive Text</button>
<button id="t4" class="tablist">Related Hike Info</button>
<div id="line"></div>

<div id="tab1" class="active tab-panel">
<form id="f1" action="saveTab1.php" method="POST" enctype="multipart/form-data">
    <?php require 'tab1display.php';?>
</form>
</div>

<div id="tab2" class="tab-panel">
    <?php require 'tab2display.php';?>
</div>

<div id='tab3' class='tab-panel'>
<form id="f3" action="saveTab3.php" method="POST">
    <?php require 'tab3display.php';?>
</form>
</div>

<div id="tab4" class="tab-panel">
<form id="f4" action="saveTab4.php" method="POST" enctype="multipart/form-data">
    <?php require 'tab4display.php';?>
</form>
</div>

</div>

<script src="../scripts/menus.js"></script>
<script src="editDB.js"></script>
<script src="ktesaUploader.js"></script>
<script src="exifReader.js"></script>

</body>
</html>
