<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Start A New Page</title>
    <meta charset="utf-8" />
    <meta name="description" content="Begin New Page" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <link href="startNewPg.css" type="text/css" rel="stylesheet" />
</head>
<body>  
    
<?php 
require "selectData.php";
$usr = filter_input(INPUT_GET,'usr');
?>
<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">New Hike Page</p>
<div id="main">
    <h2 style="color:DarkBlue;">Begin Your Journey Here!</h2>
    <h3><em>This page must be completed in order to proceed:</em></h3>
    <form action="submitNewPg.php" method="POST">
        <span class="entries">
            Please Enter A Unique Name For This Hike</span>&nbsp;&nbsp;
        <input id="newname" type="text" name="newname" size="30"
            required="required" /><br />
        <span class="entries">Please Select a Hike Type Below:</span><br />
        <input id="atvc" type="radio" name="marker" value="At VC" />&nbsp;
            A Hike That Begins In Close Prosimity to a Visitor Center<br />
            <em id="vcs">Select The Associated Visitor Center:
                <select id="vcsel" name="vchike">
                    <?php for ($i=0; $i<$vccnt; $i++) :?>
                    <option value="<?= $vcnos[$i];?>"><?= $vchikes[$i];?></option>
                    <?php endfor;?>
                </select>
            </em>
        <input id="cluster" type="radio" name="marker" value="Cluster" />&nbsp;
            A Hike That Otherwise Shares A Trailhead With (or Is In 
            Close Proximity To) Existing Hikes<br />
            <em id="cls">Select The Associated Group of Hikes:
                <select id="clsel" name="clus">
                    <?php for ($j=0; $j<$clcnt; $j++) :?>
                    <option value="<?= $cldat[$j];?>"><?= $clhikes[$j];?></option>
                    <?php endfor;?>
                </select>
            </em>
        <input id="normal" type="radio" name="marker" value="Normal" />&nbsp;
            All Other Hikes<br /><br />
        <h4>Please submit the data when ready. You will be taken to a form where
            you may continue to enter data, or simply return later and select
            "Edit Hikes: New/Active Edits" from the main page.
        </h4>
        <input type="hidden" name="uid" value="<?= $usr;?>" />
        <input type="submit" name="newpg" value="Submit This Page" />
    </form>
</div>

<script src="../scripts/jquery-1.12.1.js"></script>
<script src="startNewPg.js"></script>
</body>
</html>