<?php
session_start();
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
    <link href="editDB.css" type="text/css" rel="stylesheet" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
</head>

<body>   
<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">Hike Editor</p>
<div id="main" style="padding:16px;margin-bottom:0px;">
<h3 style="margin-top:0px;">Edits made to this hike will be retained
    in the New/Active-Edit database, and will not show up when displaying
    published hikes until these edits have been formally released</h3>
<p id="hikeNo" style='display:none'><?= $hikeNo;?></p>
<p id="entry" style="display:none"><?= $dispTab;?></p>
<em style="color:DarkBlue;font-size:18px;">Any changes below will be made for 
    the hike: "<?= $hikeTitle;?>". To save your edits, select the 
    'Apply' button at the bottom. When you are done applying edits, or if no
    edits are being made, you may simply exit this page.
</em><br /><br />
<p style="font-size:18px;color:Brown;">Preview page with current edits
    (i.e. edits already applied):&nbsp;<button id="preview"
    style="font-size:18px;color:DarkBlue;">Preview</button></p>
<!-- tabs -->
<button id="t1" class="tablist active">Basic Data</button>
<button id="t2" class="tablist">Photo Selection</button>
<button id="t3" class="tablist">Descriptive Text</button>
<button id="t4" class="tablist">Related Hike Info</button>
<button id="t5" class="tablist">File Uploads</button>
<div id="line"></div>
<div id="tab1" class="active tab-panel">
<form action="saveTab1.php" method="POST">
    <?php
    require 'tab1display.php';
    ?>
</form>
</div>

<div id="tab2" class="tab-panel">
<form id="part1" action="newPhotos.php" method="POST">
    <?php
    require 'tab2display.php';
    ?>
</form>      
</div>

<div id='tab3' class='tab-panel'>
<form action="saveTab3.php" method="POST">
    <?php
    require 'tab3display.php';
    ?>
</form>
</div>

<div id="tab4" class="tab-panel">
<form action="saveTab4.php" method="POST">
    <?php
    require 'tab4display.php';
    ?>
</form>
</div>

<div id="tab5" class="tab-panel">
<form action="saveTab5.php" method="POST" enctype="multipart/form-data">
    <?php
    require 'tab5display.php';
    ?>
</form>
</div>

</div> <!-- MAIN -->
<div class="popupCap"></div>
<!-- jQuery script source is included in photoSelect.php -->
<script src="editDB.js"></script>
</body>
</html>
