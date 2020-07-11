<?php
/**
 * The hike page editor allows the user to update information contained
 * in the database, whether for a new hike or an existing hike. Any changes
 * made by the user will not become permanently effective until the edited
 * hike is published. When this module is invoked from the hikeEditor, the
 * tab display setting will be "1". If the user clicks on 'Apply' for any tab,
 * that same tab will display again with refreshed data.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license None to date
 */
session_start();  // new page info & upload msgs

// query string data:
$hikeNo = filter_input(INPUT_GET, 'hikeNo');
$usr    = filter_input(INPUT_GET, 'usr');
$tab    = filter_input(INPUT_GET, 'tab');
$newclus = 'No';
if (isset($_SESSION['newcluster'])) {
    $newclus = $_SESSION['newcluster'];
    unset($_SESSION['newcluster']);
}

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
$pgTitle = trim($hike['pgTitle']);  // this item should never be null
$locale = $hike['locale'];
$cname = $hike['cname'];
$logistics = $hike['logistics'];
$miles = $hike['miles'];
if (empty($miles)) {
    $miles = '';
} else {
    $miles = sprintf("%.2f", $miles);
}
$feet = $hike['feet'];
$diff = $hike['diff'];
$fac = $hike['fac'];
$wow = $hike['wow'];
$seasons = $hike['seasons'];
$expo = $hike['expo'];
$curr_gpx = $hike['gpx'];  // can contain more than one filename, comma-separated
$curr_trk = $hike['trk'];
$lat = $hike['lat'] / LOC_SCALE;
$lng = $hike['lng'] / LOC_SCALE;
$dirs = $hike['dirs'];

/**
 * Tab2: [photo displays (already uploaded) and any waypoints]
 */
require "photoSelect.php";
require "wayPointEdits.php";

/**
 * Tab 3: [hike tips and hike descripton]
 */
$tips = $hike['tips'];
$info = $hike['info'];

/**
 * Tab 4: [References and GPS data]
 */
$refreq = "SELECT * FROM EREFS WHERE indxNo = :hikeno;";
$refq = $pdo->prepare($refreq);
$refq->execute(["hikeno" => $hikeNo]);
$noOfRefs = $refq->rowCount(); // needed for tab4display.php
$refs = $refq->fetchALL(PDO::FETCH_ASSOC);
$rtypes = [];
$rit1s = [];
$rit2s = [];
foreach ($refs as $ref) {
    array_push($rtypes, $ref['rtype']);
    array_push($rit1s, $ref['rit1']);
    array_push($rit2s, $ref['rit2']);
}
// Create the book drop-down options:
$bkReq = "SELECT * FROM BOOKS;";
$bkdat = $pdo->query($bkReq);
$bks = $bkdat->fetchALL(PDO::FETCH_ASSOC);
$bkopts = '';  // html for drop-down boxes
$defauth = ''; // default author when first populating selection boxes
$titles = '['; // arrays for javascript
$authors = '[';
foreach ($bks as $bkitem) {
    $titles .= '"' . $bkitem['title'] . '",';
    $authors .= '"' . $bkitem['author'] . '",';
    if ($defauth === '') {
        $defauth = $bkitem['author'];
    }
    $bkopts .= '<option value="' . $bkitem['indxNo'] . '">' . 
        $bkitem['title'] . '</option>' . PHP_EOL;
}
$titles = substr($titles, 0, strlen($titles)-1) . ']';
$authors = substr($authors, 0, strlen($authors)-1) . ']';
// GPS Data for tab 4:
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
    if ((strpos($url[$j], 'Map') !== false) || (strpos($url[$j], 'MAP') !== false)) {
        $fname[$j] = substr($url[$j], 8);
    } else {
        $fname[$j] = substr($url[$j], 7);
    }
}
