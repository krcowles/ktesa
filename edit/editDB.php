<?php
/**
 * This module comprises the framework for editing a hike page. Each tab
 * within the framework is a module which allows editing a section of the 
 * database and/or uploading of key user files. When the apply button is 
 * clicked on any tab, the changes are registered, and the user is returned
 * to the same tab with the refreshed data displayed. Note the use of the 
 * 'tinymce' wysiwyg editor for tab3.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();

require "../php/global_boot.php";
require "dataForEditor.php";
require "../pages/autoComplHikes.php";
$tinymce = "https://cdn.tiny.cloud/1/" .
    "q5s4ci6ofnx0rvv1oix9zgxsd4cvw83kimrrw0n5ugz3n6d3/tinymce/5/tinymce.min.js";
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Edit Database</title>
    <meta charset="utf-8" />
    <meta name="description" content="Edit the selected hike" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/jquery-ui.css" type="text/css" rel="stylesheet" />
    <link href="../styles/editDB.css" type="text/css" rel="stylesheet" />
    <link href="../styles/refs.css" type="text/css" rel="stylesheet" />
    <?php require "../pages/iconLinks.html"; ?>
    <script src="<?=$tinymce;?>" referrerpolicy="origin"></script>
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
</head>

<body> 
<script src="https://unpkg.com/@popperjs/core@2.4/dist/umd/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">Hike Editor</p>
<p id="active" style="display:none">Edit</p>
<p id="hikeNo" style="display:none"><?= $hikeNo;?></p>
<p id="entry" style="display:none"><?= $tab;?></p>
<p id="htitle" style="display:none"><?=$pgTitle;?></p>
<p id="appMode" style="display:none"><?=$appMode;?></p>

<div id="main" style="padding:16px;margin-bottom:0px;">
<h4 style="margin-top:0px;margin-bottom:0px;">
    <em style="font-style:italic;color:DarkBlue;"><?=$pgTitle;?></em>: 
    Changes below will be applied to this hike. To save your edits, 
    select the 'Apply' button. When you are done applying edits,
    or if no edits are being made, you may simply exit this page. Note 
    that the changes, though saved, will not show up on the main site until
    they have been formally published.
</h4>
<p style="font-size:18px;margin-top:8px;">Preview this page with applied edits:&nbsp;
    <button id="preview" type="button" class="btn btn-secondary">
        Preview</button>
    <span id="atxt">Apply the Edits</span>
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

</div> <!-- end main -->

<script>
    var hikeSources = <?=$jsItems;?>;
</script>
<script src="editDB.js"></script>
<script src="ktesaUploader.js"></script>
<script src="exifReader.js"></script>

</body>
</html>
