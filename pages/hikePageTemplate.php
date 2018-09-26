<?php
/**
 * This is the main routine utilized to display any given hike page.
 * 
 * Depending on the query string ('age'), either the in-edit hikes will be
 * accessed, or the already published hikes. All MySQL tables for
 * in-edit hikes begin with the letter "E".
 * 
 * @package Page_Display
 * @author  Tom Sandberge and Ken Cowles <krcowles29@gmail.com>
 * @license None to date
 * @link    ../docs/
 */
require "hikePageData.php";
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title><?php echo $hikeTitle;?></title>
    <meta charset="utf-8" />
    <meta name="description"
        content="Details about the {$hikeTitle} hike" />
    <meta name="author"
        content="Tom Sandberg and Ken Cowles" />
    <meta name="robots"
        content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../styles/logo.css"
        type="text/css" rel="stylesheet" />
    <link href="../styles/hikes.css"
        type="text/css" rel="stylesheet" />
    <script type="text/javascript">var ajaxDone = false;</script>
<?php if ($newstyle) : ?>
    <script type="text/javascript">var iframeWindow;</script>
    <script src="../scripts/canvas.js"></script>
<?php endif; ?>
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

<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>	
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail"><?php echo $hikeTitle;?></p>
<p id="gpx" style="display:none"><?php echo $gpxPath;?></p>
<!-- ---------------------------- OLD STYLE -------------------------- -->
<?php if (!$newstyle) : ?>
<div id="hikeSummary">
    <table id="topper">
        <thead>
            <tr>
                <th>Difficulty</th>
                <th>Round-trip</th>
                <th>Type</th>
                <th>Elev. Chg.</th>
                <th>Exposure</th>
                <th>Wow Factor</th>
                <th>Facilities</th>
                <th>Seasons</th>
<?php if ($hikePhotoLink2 == '') : ?>
                <th>Photos</th>
<?php endif; ?>
                <th>By Car</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= $hikeDifficulty;?></td>
                <td><?= $hikeLength;?></td>
                <td><?= $hikeType;?></td>
                <td><?= $hikeElevation;?></td>
                <td><?= $hikeExposure;?></td>
                <td><?= $hikeWow;?></td>
                <td><?= $hikeFacilities;?></td>
                <td><?= $hikeSeasons;?></td>
<?php if ($hikePhotoLink2 == '') : ?>
                <td><a href="<?= $hikePhotoLink1;?>" target="_blank">
                    <img style="margin-bottom:0px;border-style:none;"
                    src="../images/album_lnk.png" alt="photo album link icon" /></a></td>
<?php endif; ?>
                <td><a href="<?= $hikeDirections;?>" target="_blank">
                    <img style="margin-bottom:0px;padding-bottom:0px;"
                    src="../images/dirs.png" alt="google driving directions" /></a></td>
            </tr>
        </tbody>
    </table>
</div>
<?php else : ?>
<!--  ---------------------------- NEW STYLE -------------------------- -->
<!-- Side Panel: -->
<div id="unhide">></div>
<div id="sidePanel">
    <div id="hide"><</div>
    <p id="stats"><strong>Hike Statistics</strong></p>
    <p id="summary">
        Nearby City / Locale: <span class="sumClr"><?= $hikeLocale;?></span><br />
        Hike Difficulty: <span class="sumClr"><?= $hikeDifficulty;?></span><br />
        Total Length of Hike: <span class="sumClr"><?= $hikeLength;?></span><br />
        Max to Min Elevation: <span class="sumClr"><?= sprintf("%.0f", ($pmax - $pmin) * 3.28084);?> ft</span><br />
        <?php if ((isset($showAscDsc) && $showAscDsc == true) || is_numeric($hikeEThresh)) : ?>
        Total Ascent: <span class="sumClr"><?= sprintf("%.0f", $pup * 3.28084);?> ft</span><br />
        Total Descent: <span class="sumClr"><?= sprintf("%.0f", $pdwn * 3.28084);?> ft</span><br />
        <?php endif; ?>
        Logistics: <span class="sumClr"><?= $hikeType;?></span><br />
        Exposure Type: <span class="sumClr"><?= $hikeExposure;?></span><br />
        Seasons : <span class="sumClr"><?= $hikeSeasons;?></span><br />
        "Wow" Factor: <span class="sumClr"><?= $hikeWow;?></span>
    </p>
    <p id="addtl"><strong>More!</strong></p>
    <p id="mlnk">View <a href="<?= $fpLnk;?>" target="_blank">Full Page Map</a><br />
        <span class="track">View <a id="view" href="<?= $gpxPath;?>"
            target="_blank">GPX File</a></span><br />
        <span class="track">Download <a id="dwn" href="<?= $gpxPath;?>"
                download>GPX File</a></span>
    </p>
    <p id="albums">For improved photo viewing,<br />check out
        the following album(s):
    </p>
    <p id="alnks"><a href="<?= $hikePhotoLink1;?>" target="_blank">Photo Album Link</a>
    <?php if (strlen($hikePhotoLink2) !== 0) : ?>
        <br /><a href="<?= $hikePhotoLink2;?>" target="_blank">Additional Album Link</a>
    <?php endif; ?>
    </p>
    <p id="directions">The following link provides on-line directions to the trailhead:</p>
    <p id="dlnk"><a href="<?= $hikeDirections;?>" target="_blank">
        Google Directions</a>
    </p>
    <p id="scrollmsg">Scroll down to see images, hike description,
        reference sources and additonal information as applicable
    </p>
    <p id="closer">If you are having problems with this page, please: 
        <a href="mailto:krcowles29@gmail.com">send us a note!</a>
    </p>
</div>
<!-- Map & Chart on right adjacent to side panel: -->
<iframe id="mapline" src="<?=  $tmpMap;?>"></iframe>
<div data-gpx="<?= $gpxPath;?>" id="chartline"><canvas id="grph"></canvas></div>
<?php endif; ?>
<!-- BOTH STYLES: -->
<div style="clear:both;"><br />
<?php if ($hikeTips !== '') : ?>
<div id="trailTips"><img id="tipPic" src="../images/tips.png"
    alt="special notes icon" /><p id="tipHdr">TRAIL TIPS!</p>
    <p id="tipNotes"><?= $hikeTips;?></p></div>
<?php endif; ?>
<div id="hikeInfo"><?= $hikeInfo;?></div></div><br />
<?php
require 'relatedInfo.php';
if ($bop !== '') {
    echo $bop;
}
?>
<div id="imgArea"></div>
<p id="ptype" style="display:none">Hike</p>
<div id="dbug"></div>

<div class="popupCap"></div>

<script type="text/javascript">
    var photocnt = <?= $capCnt;?>;
    var d = "<?= implode("|", $descs);?>";
    var al = "<?= implode("|", $alblnks);?>";
    var p = "<?= implode("|", $piclnks);?>";
    var c = "<?= implode("|", $captions);?>";
    var as = "<?= implode("|", $aspects);?>";
    var w = "<?= implode("|", $widths);?>";
</script>
<script src="../scripts/jquery-1.12.1.js"></script>
<script src="../scripts/picRowFormation.js"></script>
<script src="../scripts/captions.js"></script>
<script src="../scripts/rowManagement.js"></script>
<?php if ($newstyle) : ?>
<script src="../scripts/dynamicChart.js"></script>
<?php endif; ?>
<script type="text/javascript">
    $(document).ready(function() {
        $.ajax({
            url: '../php/tmpMapDelete.php',
            data: {'file' : "<?php echo $tmpMap;?>" },
            success: function (response) {
               var msg = "Map deleted: " + "<?php echo $tmpMap?>";
            },
            error: function () {
               var msg = "Map NOT deleted: " + "<?php echo $tmpMap?>";
            }
        });
    });
</script>

</body>
</html>
