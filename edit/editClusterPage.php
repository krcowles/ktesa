<?php
/**
 * This is the editing routine for cluster pages. The basic page data
 * is extracted from EHIKES, and the references from EREFS via the 
 * getRefs.php script below.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license None to date
 */
session_start();
require "../php/global_boot.php";

$indxNo = filter_input(INPUT_GET, 'hikeNo', FILTER_VALIDATE_INT);

// get page data
$dataReq = "SELECT `pgTitle`,`stat`,`locale`,`lat`,`lng`,`dirs`,`info` FROM " .
     "`EHIKES` WHERE `indxNo`=?;";
$cpdata = $pdo->prepare($dataReq);
$cpdata->execute([$indxNo]);
$pgdata = $cpdata->fetch(PDO::FETCH_ASSOC);
$page   = $pgdata['pgTitle'];
$area   = !empty($pgdata['locale']) ? $pgdata['locale'] : '';
$lat    = !empty($pgdata['lat']) ?    $pgdata['lat']/LOC_SCALE : '';
$lng    = !empty($pgdata['lng']) ?    $pgdata['lng']/LOC_SCALE : '';
$dirs   = !empty($pgdata['dirs']) ?   $pgdata['dirs'] : '';
$info   = !empty($pgdata['info']) ?   $pgdata['info'] : '';
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Cluster Page Editor</title>
    <meta charset="utf-8" />
    <meta name="description" content="Edit the selected hike" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/jquery-ui.css" type="text/css" rel="stylesheet" />
    <link href="../styles/ktesaPanel.css" type="text/css" rel="stylesheet" />
    <link href="editClusterPage.css" type="text/css" rel="stylesheet" />
    <link href="refs.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
</head>
<body>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">Cluster Page Editor</p>

<div id="main">
    <p id="locale" style="display:none;"><?=$area;?></p>
    <form id="form" action="saveClusterPage.php" method="post">
        <input type="hidden" name="indxNo" value="<?=$indxNo;?>" />
        <input type="hidden" name="clustergroup" value="<?=$page;?>" />
        <h3 id="hdr">Enter the data required to create a new page for
            the "<?=$page;?>"</h3>
        <div id="buttons">
            <button id="preview">Preview</button>
            <button id="submit">Apply</button>
        </div>
        <span id="locale">Enter a representative locale for this group&nbsp;&nbsp;
            <?php require "localeBox.html"; ?><br /><br />
        <p>GPS Coordinates: <input class="coords ta" type="text" value="<?=$lat;?>"
            name="lat" placeholder="Latitude" />
            &nbsp;&nbsp;<input class="coords ta" type="text" name="lng"
                value="<?=$lng;?>" placeholder="Longitude" />
        </p>
        <textarea id="info" class="ta" name="info" rows="12" 
            placeholder="Description for this area"><?=$info;?></textarea>
        <br /><br />
        <div><textarea id="dirs" class="ta" name="dirs"
            placeholder="Google directions link" rows="4"><?=$dirs;?></textarea>
        </div><br />
        <h3 class="up">Area Reference Sources: (NOTE: Book type cannot be 
            changed - if needed, delete and add a new one)</h3>
        <h4>When all references are used, click on 'Apply' to generate 
            more choices</h4>
        <?php 
            $hikeIndexNo = $indxNo;
            require "getRefs.php";
        ?>
    </form><br /><br />
</div>

<script src="../scripts/menus.js"></script>
<script type="text/javascript">
    var titles  = <?=$jsonBooks;?>;
    var authors = <?=$jsonAuths;?>;
</script>
<script src="editClusterPage.js"></script>
<script src="refs.js"></script>
</body>

</html>