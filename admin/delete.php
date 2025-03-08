<?php
/**
 * This script allows an admin to remove a hike from the EHIKES table,
 * including it's associated entries in the EGPSDAT, EREFS and ETSV tables.
 * It does not delete any associated e-json files.
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require '../php/global_boot.php';
$hikeNo = filter_input(INPUT_GET, 'hno');

$mainJson = getTrackFileNames($pdo, $hikeNo, 'edit');
$tracks = $mainJson[0];
$getGPSreq = "SELECT `url` FROM `EGPSDAT` WHERE `label` LIKE 'GPX%' AND " .
    "`indxNo`=?;";
$getGPS = $pdo->prepare($getGPSreq);
$getGPS->execute([$hikeNo]);
$gpsFields = $getGPS->fetchAll(PDO::FETCH_ASSOC);
foreach ($gpsFields as $gdata) {
    $gpsFiles = getGPSurlData($gdata['url']);
    $tracks = array_merge($tracks, $gpsFiles[1]);
}
foreach ($tracks as $json) {
    $efile = '../json/' . $json;
    if (!unlink($efile)) {
        throw new Exception("Could not delete {$json}");
    }
}
// Delete ETSV photos:
$getEtsv = $pdo->query("SELECT `mid`,`thumb` FROM `ETSV` WHERE `indxNo`={$hikeNo};");
$picdat = $getEtsv->fetchAll(PDO::FETCH_ASSOC);
$getPreview = $pdo->query("SELECT `preview` FROM `EHIKES` WHERE `indxNo`={$hikeNo}");
$preview = $getPreview->fetch(PDO::FETCH_ASSOC);
$picloc   = getPicturesDirectory();
$thumbloc = str_replace('zsize', 'thumbs', $picloc);
$prevloc  = str_replace('zsize', 'previews', $picloc);
foreach ($picdat as $pic) {
    $photo = $picloc . $pic['mid'] . "_" . $pic['thumb'] . "_z.jpg";
    unlink($photo);
}
unlink($thumbloc . $preview['preview']);
unlink($prevloc . $preview['preview']);
// Now delete EHIKES data with dependencies...
$deleteHikeReq = "DELETE FROM `EHIKES` WHERE `indxNo`=?;";
$deleteHike = $pdo->prepare($deleteHikeReq);
$deleteHike->execute([$hikeNo]);
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Remove Hike from EHIKES</title>
    <meta charset="utf-8" />
    <meta name="description" content="Select hike to release from table" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/tables.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
</head>

<body>
<script src="../scripts/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">Remove EHIKE <?= $hikeNo;?></p>
<p id="active" style="display:none">Admin</p>

<div style="margin-left:16px;font-size:20px;">
<?php
    echo '<p style="font-size:24px;color:brown;">Hike-in-Edit ' . $hikeNo .
        ' was removed along with any corresponding json files, photos, previews ' .
        'and thumbs</p>';
?>
</div>

</body>
</html>
