<?php
/**
 * The hike page editor allows the user to update information contained
 * in the database, whether for a new hike or a published hike copied
 * to the editor for offline changes. Any changes made by the user will
 * not become permanently effective until the edited hike is published.
 * When this module is invoked from the hikeEditor (or submitNewPg), the
 * tab display setting will be "1". If the user clicks on 'Apply' for any
 * tab, that same tab will display again with refreshed data.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license None to date
 */

// query string data:
$hikeNo = filter_input(INPUT_GET, 'hikeNo');
$tab    = filter_input(INPUT_GET, 'tab');

/**
 * There are currently four tabs requiring data: each tab's needs are 
 * highlighted with comment blocks.
 * 
 * Tab1: [data contained in EHIKES table]
 */
$clusters = getClusters($pdo);
$hikereq = "SELECT * FROM EHIKES WHERE indxNo = :hikeno;";
$hikeq = $pdo->prepare($hikereq);
if ($hikeq->execute(["hikeno" => $hikeNo]) === false) {
    throw new Exception("Hike {$hikeNo} Not Found in EHIKES");
}
$hike = $hikeq->fetch(PDO::FETCH_ASSOC);

$pgTitle   = $hike['pgTitle'];
$locale    = $hike['locale'];
$cname     = $hike['cname'];
$logistics = $hike['logistics'];
$miles     = $hike['miles'];
if (empty($miles)) {
    $miles = '';
} else {
    $miles = sprintf("%.2f", $miles);
}
$feet     = $hike['feet'];
$diff     = $hike['diff'];
$fac      = $hike['fac'];
$wow      = $hike['wow'];
$seasons  = $hike['seasons'];
$expo     = $hike['expo'];
$curr_gpx = $hike['gpx'];  // can contain more than one filename, comma-separated
$curr_trk = $hike['trk'];
$lat      = !empty($hike['lat']) ? $hike['lat']/LOC_SCALE : '';
$lng      = !empty($hike['lng']) ? $hike['lng']/LOC_SCALE : '';
$preview_name = $hike['preview'];
$dirs     = $hike['dirs'];

// collect data for any unpublished cluster groups
$pubReq = "SELECT `group` FROM `CLUSTERS` WHERE `pub`='N';";
$nonpubs = $pdo->query($pubReq)->fetchAll(PDO::FETCH_COLUMN);
$jsData = [];
if (count($nonpubs) > 0) {
    foreach ($nonpubs as $unpub) {
        $getNPdataReq = "SELECT `lat`,`lng` FROM `CLUSTERS` WHERE `group`=?;";
        $getNPdata = $pdo->prepare($getNPdataReq);
        $getNPdata->execute([$unpub]);
        $coords = $getNPdata->fetch(PDO::FETCH_ASSOC);
        $clat = is_null($coords['lat']) ? '""' : $coords['lat']/LOC_SCALE;
        $clng = is_null($coords['lng']) ? '""' : $coords['lng']/LOC_SCALE;
        $groupdat = '{group:"' . $unpub . '",loc:{lat:' . $clat .
            ',lng:' . $clng . '}}';
        array_push($jsData, $groupdat);
    }
}
$newgrps = '[' . implode(",", $jsData) . ']';

// separate gpx files if multiple
$additional_files = [];
$adders = '<ul id="addlist" style="margin-top:4px;">' . PHP_EOL;
$all_files = explode(",", $curr_gpx);
$curr_gpx = $all_files[0];
if (count($all_files) > 1) {
    for ($j=1; $j<count($all_files); $j++) {
        $extra = trim($all_files[$j]);
        array_push($additional_files, $extra); 
    }
}
for ($k=0; $k<count($additional_files); $k++) {
    $fileno = $k +1;
    $adders .= '<li><em>' . $additional_files[$k] . '</em>&nbsp;&nbsp;<span ' .
        'class="brown"> Do not include this file:&nbsp;&nbsp;' .
        '<input type="checkbox" name="deladd[]" value="' . $fileno .'" />' .
        '</span></li>' . PHP_EOL;
}
$adders .= '</ul>' . PHP_EOL;

// any alerts to display?
$user_alert = '';
if (isset($_SESSION['user_alert']) && !empty($_SESSION['user_alert'])) {
    $user_alert = $_SESSION['user_alert'];
    $_SESSION['user_alert'] == '';
}

/**
 * Tab2: [photo displays (already uploaded) and any waypoints]
 */
require "photoSelect.php"; // prior to wayPointEdits.php to define $wlat/$wlng
require "wayPointEdits.php";
$picdir  = getPicturesDirectory();
$prevdir = str_replace('zsize', 'previews', $picdir);
$prevImg = $prevdir . $preview_name;
$tstat = empty($preview_name) ? "Has Not" : "Has";
$btncolor = empty($preview_name) ? "btn-warning" : "btn-success";

/**
 * Tab 3: [hike tips and hike descripton]
 */
$tips    = $hike['tips'];
$info    = $hike['info'];

/**
 * Tab 4: [GPS data] Note: tab4display.php calls references from EREFS
 */
$gpsreq = "SELECT * FROM EGPSDAT WHERE indxNo = :hikeno " .
    "AND (datType = 'P' OR datType = 'A');";
$gpsq = $pdo->prepare($gpsreq);
$gpsq->execute(["hikeno" => $hikeNo]);
$gpsDbCnt = $gpsq->rowCount(); // needed for tab4display.php
$label = [];
$url = [];
$clickText = [];
$datId = [];
for ($j=0; $j<$gpsDbCnt; $j++) {
    $gpsdat = $gpsq->fetch(PDO::FETCH_ASSOC);
    $datId[$j] = $gpsdat['datId'];
    $url[$j] = $gpsdat['url'];
    $clickText[$j] = $gpsdat['clickText'];
    if ($gpsdat['label'] !== 'GPX:') {
        $fname[$j] = substr($url[$j], 8);
    } else {
        $fname[$j] = substr($url[$j], 7);
    }
}
