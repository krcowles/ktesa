<?php
/**
 * This script creates the displayed 'Index' page showing a map to the
 * Visitor Center, and any hikes available from the center (or associated
 * with it).
 * PHP Version 7.1
 * 
 * @package Index_Display
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

$hikeIndexNo = filter_input(INPUT_GET, 'hikeIndx');
$query = "SELECT pgTitle,lat,lng,aoimg1,dirs,info "
    . "FROM HIKES WHERE indxNo = :hikeIndexNo;";
$indxPgData = $pdo->prepare($query);
$indxPgData->bindValue(':hikeIndexNo', $hikeIndexNo, PDO::PARAM_INT);
$indxPgData->execute();
$row = $indxPgData->fetch();
$indxTitle = $row['pgTitle'];
$lnkText = str_replace('Index', '', $indxTitle);
$parkMap = unserialize($row['aoimg1']);
$mapsrc = '../images/' . $parkMap[0];
// currently loading map without ht/width attributes, i.e. $parkMap[1] & [2]
$parkDirs = $row['dirs'];
$parkInfo = $row['info'];
// form html for references:
$rtable = 'REFS';
$gtable = 'GPSDAT';

$pageType = 'Index';
require 'relatedInfo.php';  // get the $refHtml output
// INDEX TABLE OF HIKES, if any:
$iptblsreq = "SELECT compl,tdname,tdpg,tdmiles,tdft,tdexp,tdalb " .
    "FROM IPTBLS WHERE indxNo = :hikeIndexNo;";
$iptbl = $pdo->prepare($iptblsreq);
$iptbl->bindValue(':hikeIndexNo', $hikeIndexNo, PDO::PARAM_INT);
$iptbl->execute();
// Collect data for building the table in html
$hiked = array();
$tdpg = array();
$exposure = array();
$tdname = array();
$tdmiles = array();
$tdfeet = array();
$tdalb = array();
$j = 0;
foreach ($iptbl as $indxTbl) {
    $tdpg[$j] = $indxTbl['tdpg'];
    $hiked[$j] = ($indxTbl['compl'] == 'Y') ? true : false;
    // Exposure settings:
    $expos = $indxTbl['tdexp'];
    if ($expos == 'Sunny') {
        $exposure[$j] = '../images/fullSun.jpg';
    } elseif ($expos == 'Partial') {
        $exposure[$j] = '../images/partShade.jpg';
    } elseif ($expos == 'Shady') {
        $exposure[$j] = '../images/goodShade.jpg';
    } elseif ($expos == 'X') {
        $exposure[$j] = '';
    }
    $tdname[$j] = $indxTbl['tdname'];
    $tdmiles[$j] = $indxTbl['tdmiles'];
    $tdfeet[$j] = $indxTbl['tdft'];
    $tdalb[$j] = $indxTbl['tdalb'];
    $j++;
}
$item_cnt = $j;
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $indxTitle;?></title>
    <meta charset="utf-8" />
    <meta name="language"
                    content="EN" />
    <meta name="description"
            content="Details about the {$hikeTitle} hike" />
    <meta name="author"
            content="Tom Sandberg and Ken Cowles" />
    <meta name="robots"
            content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <link href="../styles/subindx.css" type="text/css" rel="stylesheet" />
</head>

<body>
<?php require "pageTop.php"; ?>
<p id="trail"><?php echo $indxTitle;?></p>

<img class="mainPic" src="<?php echo $mapsrc;?>" alt="Park Service Map" />
<p id="dirs"><a href="<?php echo $parkDirs;?>" target="_blank">
    Directions to the <?php echo $lnkText;?> Visitor Center</a></p>
<?php
    echo '<p id="indxContent">' . $parkInfo . '</p>' . "\n";
    echo '<fieldset><legend id="fldrefs">References &amp; Links</legend>';
    echo $refHtml . '</fieldset>' . "\n";
?>
<div id="hdrContainer">
<p id="tblHdr">Hiking & Walking Opportunities at <?= $lnkText;?>:</p>
</div>
<div>
<?php if ($item_cnt > 0) : ?>
<table id="siteIndx">
    <thead>
        <tr>
            <th class="hdrRow" scope="col">Trail</th>
            <th class="hdrRow" scope="col">Trail Length</th>
            <th class="hdrRow" scope="col">Elevation</th>
            <th class="hdrRow" scope="col">Exposure</th>
            <th class="hdrRow" scope="col">Photos</th>
        </tr>
    </thead>
    <tbody>
    <?php for ($k=0; $k<$item_cnt; $k++) : ?>
        <?php if ($hiked[$k]) : ?>
            <tr>
                <td><a href="hikePageTemplate.php?hikeIndx=<?= $tdpg[$k];?>"
                    target="_blank"><?= $tdname[$k];?></a></td>
                <td><?= $tdmiles[$k];?> miles</td>
                <td><?= $tdfeet[$k];?> feet</td>
                <td><img class="expShift" src="<?= $exposure[$k];?>"
                    alt="exposure icon" /></td>
                <td><a href="<?= $tdalb[$k];?>" target="_blank">
                    <img class="flckrShift" src="../images/album_lnk.png" 
                    alt="Photos symbol" /></a></td>
            </tr>
    <?php else : ?>
            <tr>
                <td><?= $tdname[$k];?></td>
                <td><?= $tdmiles[$k];?> miles</td>
                <td>? feet</td>
                <td style="text-align:center;">N/A</td>
                <td style="text-align:center;">N/A</td>
            </tr>
    <?php endif; ?>
    <?php endfor; ?>
    </tbody>
</table>
<?php else : ?>
    <p style="text-align:center;">No hikes yet associated with this park</p>
    <p style="margin-left:16px;">Total no. of hikes read from 
        tblRow: <?= $i;?></p>
<?php endif; ?>
</div>
<script src="../scripts/jquery-1.12.1.js"></script>
</body>

</html>
