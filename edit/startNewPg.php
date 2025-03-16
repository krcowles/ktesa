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
session_start();
require "../php/global_boot.php";
$userid = validSession('start');

$pgerror = isset($_SESSION['pgtitle']) ? $_SESSION['pgtitle'] : 'clear';
unset($_SESSION['pgtitle']);

// establish drop-downs for selecting a cluster
$clusterSelect = getClusters($pdo);  // For hike page
$newClusterPage = getClusters($pdo); // For new cluster page
// need to change id of $newClusterPage so it's not a repeat of $clusterSelect:
$newClusterPage = str_replace('id="clusters"', 'id="cpages"', $newClusterPage);
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Start A New Page</title>
    <meta charset="utf-8" />
    <meta name="description" content="Begin New Page" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/startNewPg.css" type="text/css" rel="stylesheet" />
    <?php require "../pages/iconLinks.html"; ?>
    <script src="../scripts/jquery.js"></script>
</head>
<body> 
<script src="../scripts/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">New Hike Page</p>
<p id="active" style="display:none;">Create</p>
<p id="appMode" style="display:none;"><?=$appMode;?></p>

<div id="main">
    <p id="pgerror" style="display:none;"><?=$pgerror;?></p>
    <h3 style="color:DarkBlue;">Begin Your Journey Here!</h3>
    <h4><em>This page must be completed in order to proceed:</em></h4>
    <form action="submitNewPg.php" method="POST">
        <span class="entries">
            Please Enter A Unique Name For This Hike</span>&nbsp;&nbsp;
        <input id="hikename" type="text" name="hikename" size="30"
            maxlength="30" required="required" />
            &nbsp;&nbsp;<span id="maxchars">[30 Characters Max]</span><br />
        <span class="entries">Please Select a Hike Type Below:</span><br />
        <span class="form-check">
            <input class="form-check-input" type="radio" name="type" 
                value="Cluster" id="cluster">
            <label class="form-check-label" for="cluster">
            A Hike That Shares A Trailhead With (or Is In Close Proximity To)
            Existing Hikes
            </label>
        </span>
        <div id="cls">
            <em>Select from the available groups&nbsp;&nbsp;</em>
                <?= $clusterSelect;?><br />
            <span>Or enter a new group name below:</span><br />
            <input id="newgroup" class="new" type="text"
                name="newgroup" />&nbsp;&nbsp;
            <span class="note">
                NOTE: Any entry here will override the selection above.
            </span><br /><br />
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="type" id="normal"
                value="Normal" checked>
            <label class="form-check-label" for="normal">
            All Other Hikes
            </label>
        </div>
        <br />
        <h4 class="shift">Please submit the data when ready. You will be taken
            to a form where you may continue to enter data, or simply return
            later and select "Contribute->Continue Editing Your Hikes" from the
            menu above.
        </h4>
        <input class="btn btn-secondary" type="submit" name="newpg"
            value="Create Hike Page">
    </form>
    <hr />
    <h4>Alternately, Create a new Cluster Group Page</h4>
    <h5>Select a cluster group: <?=$newClusterPage;?></h5>
    <div id="cpgtxt">Or enter a new group name below:<br />
        <input id="newclusgrp" class="new" type="text" name="newclusgrp" />
        <span class="note">&nbsp;&nbsp;NOTE: Any entry here will override
            the selection above.
        </span>
    </div>
    <br />
    <button id="createcpg" type="button" class="btn btn-secondary">
        Create Cluster Page</button>
</div>

<script src="startNewPg.js"></script>

</body>
</html>