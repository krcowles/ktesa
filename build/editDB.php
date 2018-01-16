<?php
session_start();
require_once "../mysql/dbFunctions.php";
$link = connectToDb(__FILE__, __LINE__);
$hikeNo = filter_input(INPUT_GET, 'hno');
$uid = filter_input(INPUT_GET, 'usr');
if (isset($_SESSION['activeTab'])) {
    $dispTab = $_SESSION['activeTab'];
} else {
    $dispTab = 1;
}
# Error output styling string:
$pstyle = '<p style="color:red;font-size:18px;">';
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
<div id="main" style="padding:16px;">
<h3>Edits made to this hike will be retained in the New/In-Edit database, 
    and will not show up when displaying published hikes until these edits 
    have been formally released</h3>
<?php
/*
 *  Below: pull out the available cluster groups and establish association
 * with cluster group name for displaying in drop-down <select>
 */
$groups = [];
$cnames = [];
$clusterreq = "SELECT cgroup, cname FROM HIKES";
$clusterq = mysqli_query($link, $clusterreq);
if (!$clusterq) {
    die($pstyle . "editDB.php: Failed to extract cluster info from HIKES: " .
        mysqli_error($link) . "</p>");
}
while ($cluster = mysqli_fetch_assoc($clusterq)) {
    $cgrp = $cluster['cgroup'];
    if (strlen($cgrp) !== 0) {
        # no duplicates please (NOTE: "array_unique" leaves holes)
        $match = false;
        for ($i=0; $i<count($groups); $i++) {
            if ($cgrp == $groups[$i]) {
                $match = true;
                break;
            }
        }
        if (!$match) {
            array_push($groups, $cgrp);
            array_push($cnames, $cluster['cname']);
        }
    }
}
mysqli_free_result($clusterq);
$groupCount = count($cnames);
/*
 * EXTRACT ALL DATA, saved in various tabs
 */
$hikereq = "SELECT * FROM EHIKES WHERE indxNo = {$hikeNo};";
$hikeq = mysqli_query($link, $hikereq);
if (!$hikeq) {
    die($pstyle . "editDB.php: Failed to extract hike data from EHIKES: " .
        mysqli_error($link) . "</p>");
}
$hike = mysqli_fetch_assoc($hikeq);
# Although some fields will not be edited, they are needed for xfr into EHIKES
function fetch($var)
{
    $clean = is_null($var) ? '' : $var;
    return trim($clean);
}
$hikeTitle = trim($hike['pgTitle']);  # this should never be null!if (is_null($hike['locale'])) {
$hikeLocale = fetch($hike['locale']);
$hikeMarker = fetch($hike['marker']);  # this also should never be null...
$hikeColl = fetch($hike['collection']);
# collection will not be edited
$hikeClusGrp = fetch($hike['cgroup']);
$hikeGrpTip = fetch($hike['cname']);
$hikeStyle = fetch($hike['logistics']);
$hikeMiles = fetch($hike['miles']);
$hikeFeet = fetch($hike['feet']);
$hikeDiff = fetch($hike['diff']);
$hikeFac = fetch($hike['fac']);
$hikeWow = fetch($hike['wow']);
$hikeSeasons = fetch($hike['seasons']);
$hikeExpos = fetch($hike['expo']);
$hikeGpx = fetch($hike['gpx']);
$hikeTrack = fetch($hike['trk']);
# gpx & trk will not be edited
$hikeLat = fetch($hike['lat']);
$hikeLng = fetch($hike['lng']);
$hikeAddImg1 = fetch($hike['aoimg1']);
$hikeAddImg2 = fetch($hike['aoimg2']);
# aoimg1 & aoimg2 will not be edited
$hikeUrl1 = fetch($hike['purl1']);
$hikeUrl2 = fetch($hike['purl2']);
$hikeDirs = fetch($hike['dirs']);
$hikeTips = fetch($hike['tips']);
$hikeDetails = fetch($hike['info']);
mysqli_free_result($hikeq);
?>
<p id="hikeNo" style='display:none'><?php echo $hikeNo;?></p>
<p id="entry" style="display:none"><?php echo $dispTab;?></p>
<em style="color:DarkBlue;font-size:18px;">Any changes below will be made for 
    the hike: "<?php echo $hikeTitle;?>". To save your edits, select the 
    'Apply' button at the bottom. When you are done applying edits, or if no
    edits are being made, you may simply exit this page.
</em><br /><br />
<p style="font-size:18px;color:Brown;">Preview page with current edits
    (i.e. edits already applied):&nbsp;
    <button id="preview" style="font-size:18px;color:DarkBlue;">Preview</button></p>
<!-- tabs -->
<button id="t1" class="tablist active">Basic Data</button>
<button id="t2" class="tablist">Photo Selection</button>
<button id="t3" class="tablist">Descriptive Text</button>
<button id="t4" class="tablist">Related Hike Info</button>
<div id="line"></div>
<div id="tab1" class="active tab-panel">
<form action="saveTab1.php" method="POST" enctype="multipart/form-data">
    <?php
    require 'tab1display.php';
    ?>
</form>
</div>
<div id="tab2" class="tab-panel">
<form action="newPhotos.php" method="POST">
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
<form action="saveTab4.php" method="POST" enctype="multipart/form-data">
    <?php
    require 'tab4display.php';
    ?>
</form>
</div>

</div>
<div class="popupCap"></div>
<!-- jQuery script source is included in photoSelect.php -->
<script src="editDB.js"></script>
</body>
</html>
