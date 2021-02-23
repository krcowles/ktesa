<?php
/**
 * This is the main routine utilized to display any given hike page.
 * Depending on the query string ('age'), either the in-edit hikes will be
 * accessed, or the already published hikes. All MySQL tables for
 * in-edit hikes begin with the letter "E".
 * PHP Version 7.1
 * 
 * @package Page_Display
 * @author  Tom Sandberge and Ken Cowles <krcowles29@gmail.com>
 * @license None to date
 */
session_start();
require "../php/global_boot.php";
require "../php/gpxFunctions.php";
require "hikePageData.php";
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title><?= $hikeTitle;?></title>
    <meta charset="utf-8" />
    <meta name="description" content="Details about the <?= $hikeTitle;?> hike" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../styles/jquery-ui.css" type="text/css" rel="stylesheet" />
    <link href="../styles/ktesaPanel.css" type="text/css" rel="stylesheet" />
    <link href="../styles/hikes.css" type="text/css" rel="stylesheet" />
    <script type="text/javascript">var iframeWindow;</script>
    <script src="../scripts/canvas.js"></script>
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
</head>
     
<body> 
<?php if (strpos($hikeTitle, '[Proposed]') !== false) : ?>
<script> 
function off() {
    document.getElementById("overlay").style.display = "none";
}
</script>
<div id="overlay" onclick="off()">
    <div id="text">Warning! This is a draft hike page.<br />
    The authors have not done this hike yet.<br />
    Click anywhere to remove this warning.</div>
</div>
<?php endif; ?>

<?php require "ktesaPanel.php";?>
<p id="trail"><?= $hikeTitle;?></p>
<p id="page_id" style="display:none">Page</p>
<p id="gpx" style="display:none"><?=$gpxPath;?></p>
<p id="cpg" style="display:none"><?=$cluspg;?></p>

<!-- Side Panel: -->
<div id="unhide">></div>
<div id="sidePanel">
    <div id="hide"><</div>
        <p id="stats">Hike Statistics</p>
        <p id="summary">
            Nearby City / Locale: <span class="sumClr">
                <?= $hikeLocale;?></span><br />
            Hike Difficulty: <span id="hdiff" class="sumClr">
                <?= $hikeDifficulty;?></span><br />
            Total Length of Hike: <span id="hlgth" class="sumClr">
                <?= $hikeLength;?></span><br />
            Max to Min Elevation: <span id="hmmx" class="sumClr">
                <?= sprintf("%.0f", ($pmax - $pmin) * 3.28084);?> ft</span><br />
        <?php if ($displayAscDsc) : ?>
            Total Ascent: <span class="sumClr">
                <?= sprintf("%.0f", $pup * 3.28084);?> ft</span><br />
            Total Descent: <span class="sumClr">
                <?= sprintf("%.0f", $pdwn * 3.28084);?> ft</span><br />
        <?php endif; ?>
            Logistics: <span id="hlog" class="sumClr"><?= $hikeType;?></span><br />
            Exposure Type: <span id="hexp" 
                class="sumClr"><?= $hikeExposure;?></span><br />
            Seasons : <span id="hseas" class="sumClr">
                <?= $hikeSeasons;?></span><br />
            "Wow" Factor: <span id="hwow" class="sumClr"><?= $hikeWow;?></span>
        </p>
        <?php if (!$clusterPage) : ?>
            <p id="addtl"><strong>More!</strong></p>
            <p id="mlnk">View <a href="<?= $fpLnk;?>"
                target="_blank">Full Page Map</a><br />
                <span class="track">View <a id="view" href="<?= $gpxPath;?>"
                    target="_blank">GPX File</a></span><br />
                <span class="track">Download <a id="dwn" href="<?= $gpxPath;?>"
                        download>GPX File</a></span>
            </p>
            <?= $photoAlbum;?>
            <p id="directions">The following link provides on-line directions to
                the trailhead:</p>
            <p id="dlnk"><a href="<?= $hikeDirections;?>" target="_blank">
                Google Directions</a>
            </p>
            <p id="scrollmsg">Scroll down to see images, hike description,
                reference sources and additonal information as applicable
            </p>
            <!-- When there are multiple tracks, display the note following -->
            <div id="trknote">NOTE: The <span id="top">topmost checked</span>
                item in the 'Tracks' box (in the upper right-hand corner of the
                map) will display its elevation chart and hike data. Turn tracks
                on and off for display.
            </div>
            <p id="problems">If you are having problems with this page, please: 
                <a href="mailto:krcowles29@gmail.com">send us a note!</a>
            </p>
        <?php else : ?>
            <?php include "relatedInfo.php"; ?>
            <p id="mlnk">View <a href="<?= $fpLnk;?>" target="_blank">
                Full Page Map</a></p><br />
            <p id="directions">The following link provides on-line directions to
                the area:</p>
            <p id="dlnk"><a href="<?= $hikeDirections;?>" target="_blank">
                Google Directions</a>
            </p>
            <p id="cdesc"><a href="#hikeInfo">
                Area Description & Links</a> Below</p>
            <div><div id="crefs">For photos and more:</div>
                <fieldset>
                <legend>See pages:</legend>
                <?=$relHikes;?>
                </fieldset>
            </div>
            <div id="trknote">NOTE: The <span id="top">topmost</span> checked
                item in the 'Tracks' box (in the upper right-hand corner of the
                map) will display its elevation chart and hike data.
                Uncheck boxes to see other items.
            </div>
        <?php endif; ?>
</div>
<!-- Map & Chart on right adjacent to side panel: -->
<iframe id="mapline" src="<?=  $tmpMap;?>"></iframe>
<div data-gpx="<?= $gpxPath;?>" id="chartline"><canvas id="grph"></canvas></div>
<!-- BOTH STYLES: -->
<div style="clear:both;"><br />
<?php if (!is_null($hikeTips)) : ?>
<div id="trailTips"><img id="tipPic" src="../images/tips.png"
    alt="special notes icon" /><p id="tipHdr">TRAIL TIPS!</p>
    <p id="tipNotes"><?= $hikeTips;?></p></div>
<?php endif; ?>
<div id="hikeInfo"><?= $hikeInfo;?></div></div><br />

<?=$bop;?>

<div id="imgArea"></div>
<p id="ptype" style="display:none">Hike</p>
<div id="dbug"></div>

<div class="popupCap"></div>

<script type="text/javascript">
    <?php if (isset($hikeFiles)) : ?>
    var hikeFiles = <?=$hikeFiles;?>;
    <?php endif; ?>
    <?php if (isset($sidePanelData)) : ?>
    var panelData = <?=$sidePanelData;?>;
    <?php endif; ?>
    <?php if (isset($capCnt)) : ?>
    var photocnt  = <?=$capCnt;?>;
    var d  = "<?=implode("|", $descs);?>";
    var al = "<?=implode("|", $alblnks);?>";
    var p  = "<?=implode("|", $piclnks);?>";
    var c  = "<?=implode("|", $captions);?>";
    var as = "<?=implode("|", $aspects);?>";
    var w  = "<?=implode("|", $widths);?>";
    <?php endif; ?>
</script>
<script src="../scripts/menus.js"></script>
<script src="../scripts/picRowFormation.js"></script>
<script src="../scripts/captions.js"></script>
<script src="../scripts/rowManagement.js"></script>
<script src="../scripts/prepareTracks.js"></script>
<script src="../scripts/dynamicChart.js"></script>

</body>
</html>
