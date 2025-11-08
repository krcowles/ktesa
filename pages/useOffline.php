<?php
/**
 * When a map has already been saved, it may now be used offline. 
 * Multiple maps may be saved, and the user can select one for display.
 * NOTE: This script will apply to both mobile and non-mobile devices.
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowle29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
$userid = validSession('useOffline.php');
$type = $_GET['type'] ?: 'mobile';
$map2use = isset($_GET['map']) ? $_GET['map'] : '';

$mapReq = "SELECT `saved_maps` FROM `MEMBER_PREFS` WHERE `userid`=?;";
$getMaps = $pdo->prepare($mapReq);
$getMaps->execute([$userid]);
$maps = $getMaps->fetch(PDO::FETCH_ASSOC);
$user_maps = explode(",", $maps['saved_maps']);
$opts = "";
if (empty($user_maps[0])) {
    $opts = false;
} else {
    foreach ($user_maps as $map) {
        $opts .= "<option value={$map}>{$map}</option>" . PHP_EOL;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Use Offline Map</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
          content="Use a saved offline map">
    <meta name="author" content="Ken Cowles">
    <meta name="robots" content="nofollow">
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/leaflet.css" rel="stylesheet">
    <link href="../styles/useOffline.css" rel="stylesheet">
    <?php require "../pages/iconLinks.html"; ?>
    <style type="text/css">
        #map {
            overflow: hidden;
            position: relative;
            left: 3rem;
            height: 550px;
            width: 700px;
            border: 1px solid #AAA;
            margin-left: 6rem;
        }
    </style>
    <script src="../scripts/jquery.js"></script>
    <script type="text/javascript">
        var type = '<?=$type?>';
    </script>
</head>
<body>
<script src="../scripts/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<p id='selectmap'  style="display:none"><?=$map2use?></p>
<?php
if ($type === 'mobile') {
    include "mobileNavbar.php";

} else {
    include "ktesaPanel.php";
}
?>
<p id="active" style="display:none">Offline</p>

<div id="map"></div>

<div id="mapmodal" style="display:none">
    <span id="gotopts">
    <?php if ($opts) : ?>
        <?=$opts;?>
    <?php else : ?>
        <?="none";?>
    <?php endif; ?>
    </span>
</div>

<script src="../scripts/ktesaOfflineDB.js"></script>
<script src="../scripts/leaflet.js"></script>
<script src="../scripts/useOffline.js"></script>
</body>
</html>
