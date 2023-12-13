<?php
/**
 * This is the editing routine for cluster pages. The basic page data
 * is extracted from EHIKES, and the references from EREFS via the 
 * getRefs.php script below.
 * PHP Version 7.4
 * 
 * @package Ktesa
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
    <meta name="description" content="Edit the created/selected cluster page" />
    <meta name="author" content="Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/editClusterPage.css" type="text/css" rel="stylesheet" />
    <link href="refs.css" type="text/css" rel="stylesheet" />
    <?php require "../pages/iconLinks.html"; ?>
    <script src="../scripts/jquery.js"></script>
</head>
<body>
<script src="https://unpkg.com/@popperjs/core@2.4/dist/umd/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">Cluster Page Editor</p>
<p id="active" style="display:none;">Edit</p>

<div id="main">
    <p id="locale" style="display:none;"><?=$area;?></p>
    <form id="form" action="saveClusterPage.php" method="post">
        <input type="hidden" name="indxNo" value="<?=$indxNo;?>" />
        <input type="hidden" name="clustergroup" value="<?=$page;?>" />
        <h5 id="hdr">Enter the data required to create a new page for
            the "<?=$page;?>"</h5>
        <div id="buttons">
            <button id="preview" type="button" class="btn btn-secondary">
                Preview</button>
            <button id="submit" type="submit" class="btn btn-secondary">
                Apply</button>
        </div>
        <span id="select_locale">Enter a representative locale for
            this group&nbsp;&nbsp;
            <?php require "localeBox.html"; ?></span><br /><br />
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
        <h4 class="up">Area Reference Sources: (NOTE: Book type cannot be 
            changed - if needed, delete and add a new one)</h4>
        <h5>When all references are used, click on 'Apply' to generate 
            more choices</h5>
        <?php 
            $hikeIndexNo = $indxNo;
            require "getRefs.php";
        ?>
    </form><br /><br />
</div>

<script type="text/javascript">
    var titles  = <?=$jsonBooks;?>;
    var authors = <?=$jsonAuths;?>;
</script>
<script src="editClusterPage.js"></script>
<script src="refs.js"></script>
</body>

</html>