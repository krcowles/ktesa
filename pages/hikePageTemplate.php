<?php
/**
 * This is the main routine utilized to display any given hike (or cluster) page.
 * Depending on the query string ('age'), either the in-edit hikes will be
 * accessed, or the already published hikes. All MySQL tables for in-edit hikes
 * begin with the letter "E".
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license None to date
 */
session_start();
$geoloc = "../../images/geoloc.png";
require "../php/global_boot.php";
require "hikePageData.php";
if ($mobileTesting) {
    $hdr = "../pages/responsivePage.php?hikeIndx={$hikeIndexNo}";
    if ($ehikes) {
        $hdr .= "&tbl={$tbl}";
    }
    if ($clusterPage) {
        $hdr .= "&clus={$clusterPage}";
    }
    header("Location: {$hdr}");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title><?= $hikeTitle;?></title>
    <meta charset="utf-8" />
    <meta name="description" content="Details about the <?= $hikeTitle;?> hike" />
    <meta name="author" content="Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/hikes.css" type="text/css" rel="stylesheet" />
    <?php require "../pages/iconLinks.html"; ?>
    <script type="text/javascript">var iframeWindow;</script>
    <script src="../scripts/canvas.js"></script>
    <script src="../scripts/jquery.js"></script>
    <script type="text/javascript">
        var isMobile, isTablet, isAndroid, isiPhone, isiPad, mobile;
        isMobile = navigator.userAgent.toLowerCase().match(/mobile/i) ? 
            true : false;
        isTablet = navigator.userAgent.toLowerCase().match(/tablet/i) ?
            true : false;
        isAndroid = navigator.userAgent.toLowerCase().match(/android/i) ?
            true : false;
        isiPhone = navigator.userAgent.toLowerCase().match(/iphone/i) ?
            true : false;
        isiPad = navigator.userAgent.toLowerCase().match(/ipad/i) ?
            true : false;
        mobile = isMobile && !isTablet;
    </script>
</head>
     
<body>
<script type="text/javascript">
    if (mobile) {
        // redirect to mobile page
        window.open(
            "responsivePage.php?hikeIndx=<?=$hikeIndexNo;?>&tbl=<?$tbl;?>", "_blank"
        );
    }
</script>

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

<script src="../scripts/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "ktesaPanel.php";?>

<p id="trail"><?= $hikeTitle;?></p>
<p id="active" style="display:none">Page</p>
<p id="gpx" style="display:none;"><?=$gpxfile;?></p>
<p id="cpg" style="display:none;"><?=$cluspg;?></p>
<p id="age" style="display:none;"><?=$state;?></p>

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
                <?= $main_dist;?></span><br />
            Max to Min Elevation: <span id="hmmx" class="sumClr">
                <?=$main_echg;?> ft</span><br />
            <span id="adnote">* Total Ascent / Descent:
                <span id="ascent" class="sumClr"><?=$main_asc;?></span> / 
                <span id="descent" class="sumClr"><?=$main_dsc;?></span> ft</span>
            <span id="advisory">Estimate based on track data<br /></span><br />
            Logistics: <span id="hlog" class="sumClr"><?= $hikeType;?></span><br />
            Exposure Type: <span id="hexp" 
                class="sumClr"><?= $hikeExposure;?></span><br />
            Seasons : <span id="hseas" class="sumClr">
                <?= $hikeSeasons;?></span><br />
            "Wow" Factor: <span id="hwow" class="sumClr"><?= $hikeWow;?></span>
        <?php if ($hikedLast) : ?>
            <br />Authors last hiked: <span class="sumClr"><?=$hikedLast;?></span>
        <?php endif; ?>
        </p>
        <?php if (!$clusterPage) : ?>
            <p id="addtl"><strong>More!</strong></p>
            <p id="mlnk">View <a href="<?= $fpLnk;?>"
                target="_blank">Full Page Map</a><br />
                <span class="track">Download <a id="dwn" href="#">GPX File</a></span>
            </p>
            <?= $photoAlbum;?>
            <p id="directions">On-line directions to the trailhead:<br />
            <span id="dlnk"><a href="<?= $hikeDirections;?>" target="_blank">
                Google Directions</a></span>
            </p>
            <p id="scrollmsg">Scroll down to see photos and additonal information.
                Click on photos for enlarged view. 
                <span style="color:red;">Red</span> segments on the elevation chart
                indicate slopes of 18 degrees or higher.
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
<div data-gpx="<?= $gpxfile;?>" id="chartline"><canvas id="grph"></canvas></div>
<!-- BOTH STYLES: -->
<div style="clear:both;padding-top:12px;">
<?php if (!is_null($hikeTips)) : ?>
<div id="trailTips" class="wysiwyg"><img id="tipPic" src="../images/tips.png"
    alt="special notes icon" /><p id="tipHdr">TRAIL TIPS!</p>
    <p id="tipNotes"><?= $hikeTips;?></p></div>
<?php endif; ?>

<div id="hikeInfo"><?= $hikeInfo;?></div></div>

<?=$bop;?>
<div id="bigpic">Click on any photo for an enlarged view</div>
<div id="imgArea"></div>
<br />
<p id="ptype" style="display:none">Hike</p>

<div class="popupCap"></div>

<!-- Gpx Files Download -->
<div class="modal fade" id="multigpx" tabindex="-1"
    aria-labelledby="Download Multiple GPX" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Download Multiple Gpx Fils</h5>
                <button type="button" class="btn-close"
                    data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5>This hike was created by multiple gpx files.
                    Click on any or all links to download:</h5>
                <ul id="idfiles" style="list-style:none;">
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    // some vars not set for Cluster Pages...
    var hike_file_list = <?=$hike_file_list;?>; // list of json track files
    var gpx_file_list  = <?=$gpx_files;?>;
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
<script src="../scripts/hikePageLayout.js"></script>
<script src="../scripts/popupCaptions.js"></script>
<script src="../scripts/rowManagement.js"></script>

<script src="../scripts/prepareTracks.js"></script>
<script src="../scripts/dynamicChart.js"></script>

</body>
</html>
