<?php
/**
 * This is the main routine utilized to display any given hike page.
 * Released hikes only.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license None to date
 */
session_start();
require "../php/global_boot.php";
require "hikePageData.php";
$hikeno = filter_input(INPUT_GET, 'hikeIndx');

$userfav = false;
if (isset($_SESSION['userid'])) {
    $getFavsReq = "SELECT `hikeNo` FROM `FAVORITES` WHERE `userid`=?";
    $getFavs = $pdo->prepare($getFavsReq);
    $getFavs->execute([$_SESSION['userid']]);
    $favs = $getFavs->fetchAll(PDO::FETCH_COLUMN);
    if (count($favs) > 0) {
        if (in_array($hikeno, $favs)) {
            $userfav = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title><?= $hikeTitle;?></title>
    <meta charset="utf-8" />
    <meta name="description" content="Details about the <?= $hikeTitle;?> hike" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous" />
    <link href="../styles/ktesaNavbar.css" rel="stylesheet" />
    <link href="../styles/responsivePage.css" rel="stylesheet" />
    <!--<link href="../styles/hikes.css" type="text/css" rel="stylesheet" />-->
    <script src="../scripts/jquery.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>
    <script type="text/javascript">var iframeWindow;</script>
    <script src="../scripts/canvas.js"></script>
</head>
     
<body>
    
<!-- Overlay for Proposed Hikes -->
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
<!-- End Overlay -->

<?php require "ktesaNavbar.php";?>
<p id="trail"><?= $hikeTitle;?></p>
<p id="gpx" style="display:none"><?=$gpxPath;?></p>
<p id="cpg" style="display:none"><?=$cluspg;?></p>
<p id="hikeno" style="display:none;"><?=$hikeno;?></p>

<!-- Hike Stats -->
<div id="hike_stats"></div>
<div id="sidePanel">
    <?php if (!$clusterPage) : ?> 
        <p id="scrollmsg">Scroll down to see images, hike description,
            reference sources and additonal information as applicable
        </p>
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

<iframe id="mapline" src="<?=  $tmpMap;?>"></iframe>
<div data-gpx="<?= $gpxPath;?>" id="chartline"><canvas id="grph"></canvas></div>

<?php if (!is_null($hikeTips)) : ?>
<div id="trailTips">
    <img id="tipPic" src="../images/tips.png"
    alt="special notes icon" /><span id="tipHdr">TRAIL TIPS!</span><br />
    <span><?= $hikeTips;?></span>
</div>
<?php endif; ?>
<div id="hikeInfo"><?= $hikeInfo;?></div></div><br />

<?=$bop;?>

<div id="imgArea"></div>
<p id="ptype" style="display:none">Hike</p>

<!-- page buttons -->
<?php if (isset($_SESSION['userid'])) : ?>
    <?php if ($userfav) : ?>
        <button id="favs" class="btn-sm btn-danger">Unmark Favorite</button>
    <?php else : ?>
        <button id="favs" class="btn-sm btn-primary">Mark as Favorite</button>
    <?php endif; ?>
<?php endif; ?>
<button id="hinfo" type="button" class="btn btn-primary btn-sm"
    data-bs-toggle="modal" data-bs-target="#hikeData">
  Hike Stats
</button>

<div class="modal fade" id="hikeData" tabindex="-1" aria-labelledby="statlist"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statlist"><?=$hikeTitle;?> 
                    Statistics</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Hike Directions: <a href="<?= $hikeDirections;?>"
                    target="_blank">Google Directions</a><br />
                Logistics: <span id="hlog" class="sumClr"><?= $hikeType;?></span>
                <br />
                Total Length of Hike: <span id="hlgth" class="sumClr">
                    <?= $hikeLength;?></span><br />
                Max to Min Elevation: <span id="hmmx" class="sumClr">
                    <?= sprintf("%.0f", ($pmax - $pmin) * 3.28084);?> ft</span><br />
                Hike Difficulty: <span id="hdiff" class="sumClr">
                    <?= $hikeDifficulty;?></span><br />
                Exposure Type: <span id="hexp" class="sumClr">
                    <?= $hikeExposure;?></span><br />
                Seasons : <span id="hseas" class="sumClr">
                    <?= $hikeSeasons;?></span><br />
                Region: <span class="sumClr"> <?= $hikeLocale;?></span><br />
                Features: <span id="hwow" class="sumClr"><?= $hikeWow;?></span><br />
                <?php if (!($photoAlbum === '<br />')) : ?>
                    More photos: <?=$link;?><br />
                <?php endif; ?>
                <p>View <a href="<?= $fpLnk;?>" target="_blank">Full Page Map</a>
                <br />
                Hike track:
                <span>View <a id="view" href="<?= $gpxPath;?>"
                    target="_blank">GPX File</a></span><br />
                <span>Download <a id="dwn" href="<?= $gpxPath;?>"
                        download>GPX File</a></span>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="popupCap"></div>

<script type="text/javascript">
    <?php if (isset($hikeFiles)) : ?>
    var hikeFiles = <?=$hikeFiles;?>;
    <?php endif; ?>
    <?php if (isset($sidePanelData)) : ?>
    var panelData = <?=$sidePanelData;?>;
    <?php else: ?>
        <?php if (isset($capCnt)) : ?>
        var photocnt  = <?=$capCnt;?>;
        var d  = "<?=implode("|", $descs);?>";
        var al = "<?=implode("|", $alblnks);?>";
        var p  = "<?=implode("|", $piclnks);?>";
        var c  = "<?=implode("|", $captions);?>";
        var as = "<?=implode("|", $aspects);?>";
        var w  = "<?=implode("|", $widths);?>";
        <?php endif; ?>
    <?php endif; ?>
</script>
<script src="../scripts/responsivePage.js"></script>
<script src="../scripts/picRowFormation.js"></script>
<script src="../scripts/captions.js"></script>
<script src="../scripts/rowManagement.js"></script>
<script src="../scripts/prepareTracks.js"></script>
<script src="../scripts/dynamicChart.js"></script>

</body>
</html>
