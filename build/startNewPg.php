<?php
/**
 * This script initiates the editing process for new hike pages.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license None to date
 */
require "../php/global_boot.php";

$usr = filter_input(INPUT_GET, 'usr');
$clusterSelect = getClusters($pdo);
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Start A New Page</title>
    <meta charset="utf-8" />
    <meta name="description" content="Begin New Page" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/jquery-ui.css" type="text/css" rel="stylesheet" />
    <link href="../styles/ktesaPanel.css" type="text/css" rel="stylesheet" />
    <link href="startNewPg.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
</head>
<body>  
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">New Hike Page</p>
<p id="page_id" style="display:none">Build</p>

<div id="main">
    <h2 style="color:DarkBlue;">Begin Your Journey Here!</h2>
    <h3><em>This page must be completed in order to proceed:</em></h3>
    <form action="submitNewPg.php" method="POST">
        <span class="entries">
            Please Enter A Unique Name For This Hike</span>&nbsp;&nbsp;
        <input id="newname" type="text" name="newname" size="30"
            maxlength="30" required="required" />
            &nbsp;&nbsp;<span class="brown">[30 Characters Max]</span><br />
        <span class="entries">Please Select a Hike Type Below:</span><br />
        <input id="cluster" type="radio" name="type" value="Cluster" />&nbsp;
            A Hike That Shares A Trailhead With (or Is In 
            Close Proximity To) Existing Hikes<br />
        <em id="cls">
            Select from the available groups
            <?= $clusterSelect;?>
            <br />
            [Or, check the box to select a new group on the next page]
            <input type="checkbox" name="mknewgrp" />
        </em>
        <input id="normal" type="radio" name="type" value="Normal" />&nbsp;
            All Other Hikes<br /><br />
        <h4>Please submit the data when ready. You will be taken to a form where
            you may continue to enter data, or simply return later and select
            "Edit Hikes: New/Active Edits" from the main page.
        </h4>
        <input type="hidden" name="uid" value="<?= $usr;?>" />
        <input type="submit" name="newpg" value="Submit This Page" />
    </form>
</div>

<script src="../scripts/menus.js"></script>
<script src="startNewPg.js"></script>

</body>
</html>