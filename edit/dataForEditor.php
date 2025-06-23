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
$user   = $_SESSION['userid'];
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
$status = $hike['stat'] == '0' ? 'new' : 'old';

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
if (empty($hike['gpx'])) {
    $gpx_arr = ['main'=>[], 'add1'=>[], 'add2'=>[], 'add3'=>[]];
} else {
    // decoded data is of type stdClass - deep convert to standard php arrays
    $stdClass = json_decode($hike['gpx'], true);
    $gpx_arr = [];
    foreach ($stdClass as $item => $value) {
        $gpx_arr[$item] = $value;
    }
}
// assign gpx data if present
$curr_gpx  = empty($gpx_arr['main']) ? '' : array_keys($gpx_arr['main'])[0];
$additional_files[1] = $addtional_files[2] = $addtional_files[3] = '';
$add1 = $gpx_arr['add1'];
$add2 = $gpx_arr['add2'];
$add3 = $gpx_arr['add3'];
if (!empty($add1)) {
    $additional_files[1] = array_keys($add1)[0];
}
if (!empty($add2)) {
    $additional_files[2] = array_keys($add2)[0];
}
if (!empty($add3)) {
    $additional_files[3] = array_keys($add3)[0];
}
// remaining tab1 fields:
$lat      = !empty($hike['lat']) ? $hike['lat']/LOC_SCALE : '';
$lng      = !empty($hike['lng']) ? $hike['lng']/LOC_SCALE : '';
$preview_name = $hike['preview'];
$dirs     = $hike['dirs'];

// collect data for any unpublished cluster groups
$pubReq = "SELECT `group`,`lat`,`lng` FROM `CLUSTERS` WHERE `pub`='N';";
$nonpubs = $pdo->query($pubReq)->fetchAll(PDO::FETCH_ASSOC);
$jsData = [];
foreach ($nonpubs as $unpub) {
    $clat = is_null($unpub['lat']) ? '""' : $unpub['lat']/LOC_SCALE;
    $clng = is_null($unpub['lng']) ? '""' : $unpub['lng']/LOC_SCALE;
    $groupdat = '{group:"' . $unpub['group'] . '",loc:{lat:' . $clat .
        ',lng:' . $clng . '}}';
    array_push($jsData, $groupdat);
}
$newgrps = '[' . implode(",", $jsData) . ']';

$adders = '<ul id="addlist" style="margin-top:4px;">' . PHP_EOL;
for ($j=1; $j<4; $j++) {
    if (!empty($additional_files[$j])) {
        $adders .= '<li id="adder' . $j . '"><em>' .
            $additional_files[$j] . '</em>&nbsp;&nbsp;<span ' .
            'class="brown"> Delete this file:&nbsp;&nbsp;' .
            '<input type="checkbox" name="deladd[]" value="' .
            $additional_files[$j] .
            '" />' . '</span></li>' . PHP_EOL;
    }
}
$adders .= '</ul>' . PHP_EOL;

// Any alerts to display? These appear in a javascript alert only, not on the page
if (isset($_SESSION['clus_loc'])) {
    $clus_loc_alert = $_SESSION['clus_loc'];
    unset($_SESSION['clus_loc']);
}
$user_alert = '';
if (isset($_SESSION['alerts']) && !empty(checkForEmptyArray($_SESSION['alerts']))) {
    $user_alert = '';
    foreach ($_SESSION['alerts'] as $alert) {
        if ($alert !== '') {
            $user_alert .= $alert . "\n";
        }
    }
    $_SESSION['alerts'] = ["", "", "", ""];
}

/**
 * Tab2: [photo displays (already uploaded) and any waypoints]
 */
require "photoSelect.php"; // prior to wayPointEdits.php to define $wlat/$wlng
require "wptHtmlDefs.php";
require "wayPointEdits.php";
$picdir  = getPicturesDirectory();
$prevdir = str_replace('zsize', 'previews', $picdir);
$prevImg = $prevdir . $preview_name;
$tstat = empty($preview_name) ? "Has Not" : "Has";
$btncolor = empty($preview_name) ? "btn-warning" : "btn-success";
$org_prev = '';
if ($status == 'old') {
    $orgpic
        = $pdo->query(
            "SELECT `preview` FROM `HIKES` WHERE `indxNo`={$hike['stat']};"
        )->fetch(PDO::FETCH_ASSOC);
    $org_prev = $orgpic['preview'];
}

/**
 * Tab 3: [hike tips and hike descripton]
 */
$tips    = $hike['tips'];
$info    = $hike['info'];

/**
 * Tab 4: [GPS data] Note: tab4display.php calls references from EREFS
 */
$gpsreq = "SELECT * FROM EGPSDAT WHERE indxNo = :hikeno;";
$gpsq = $pdo->prepare($gpsreq);
$gpsq->execute(["hikeno" => $hikeNo]);
$gpsDbCnt = $gpsq->rowCount(); // needed for tab4display.php
$gps_label = [];
$url       = [];
$clickText = [];
$datId     = [];
$del_str   = [];
$user_file = [];
for ($j=0; $j<$gpsDbCnt; $j++) {
    $gpsdat = $gpsq->fetch(PDO::FETCH_ASSOC);
    $datId[$j]     = $gpsdat['datId'];
    $gps_label[$j] = $gpsdat['label'];
    $url[$j]       = $gpsdat['url'];
    $clickText[$j] = $gpsdat['clickText'];
    if (strpos($gps_label[$j], 'GPX') !== false) {
        $gpsGpx = getGPSurlData($url[$j]);
        $fname[$j] = $gpsGpx[0];
        $json_str = implode(",", $gpsGpx[1]);
        $del_str[$j] = $json_str;
        $user_file[$j] = $fname[$j];
    } else { // kml or map (html/pdf)
        $fname[$j] = $url[$j];
        $del_str[$j] = $fname[$j];
        $ext = pathinfo($fname[$j], PATHINFO_EXTENSION);
        $start_pos = strpos($fname[$j], 'maps') !== false ? 8 : 7;
        $barefile = substr($fname[$j], $start_pos);
        $user_length = strpos($barefile, "-");
        // NOTE: assumes user filename has no embedded hyphen; case not addressed
        $user_file[$j] = substr($barefile, 0, $user_length) . "." . $ext;
    }
}
