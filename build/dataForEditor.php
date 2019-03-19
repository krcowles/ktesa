<?php
/**
 * The hike page editor allows the user to update information contained
 * in the database, whether for a new hike or an existing hike. Any changes
 * made by the user will not become effective until the edited hike is published.
 * When this module is invoked from the hikeEditor, the tab display setting
 * will be "1". If the user clicks on 'Apply' for any tab, that same tab will
 * display again with refreshed data.
 * PHP Version 7.1
 * 
 * @package Editing
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license None to date
 */
session_start();

// query string data:
$hikeNo = filter_input(INPUT_GET, 'hno'); // all tabs
$usr = filter_input(INPUT_GET, 'usr');
$tab = filter_input(INPUT_GET, 'tab');
// data for drop-down boxes
$selectData = dropdownData($pdo, 'cls');
$cnames = array_values($selectData);
$groups = array_keys($selectData);
/**
 * There are currently four tabs requiring data: each tab's needs are 
 * highlighted with comment blocks.
 * Tab1: [data contained in EHIKES table]
 */
$hikereq = "SELECT * FROM EHIKES WHERE indxNo = :hikeno;";
$hikeq = $pdo->prepare($hikereq);
if ($hikeq->execute(["hikeno" => $hikeNo]) === false) {
    throw new Exception(
        "Hike {$hikeNo} Not Found in EHIKES; File " . __FILE__ . 
        " line no. " . __LINE__
    );
}
$hike = $hikeq->fetch(PDO::FETCH_ASSOC);
$pgTitle = trim($hike['pgTitle']);  // this should never be null
$locale = fetch($hike['locale']);
$marker = fetch($hike['marker']);  // this also should never be null...
$collection = fetch($hike['collection']);
$cgroup = fetch($hike['cgroup']);
$cname = fetch($hike['cname']);
// Special case: when a new page requests to add a new group, advise the js
if ($marker === 'Cluster' && $cgroup === '') {
    $marker = 'Normal';
    $grpReq = "YES";
} else {
    $grpReq = "NO";
}
$logistics = fetch($hike['logistics']);
$miles = fetch($hike['miles']);
$feet = fetch($hike['feet']);
$diff = fetch($hike['diff']);
$fac = fetch($hike['fac']);
$wow = fetch($hike['wow']);
$seasons = fetch($hike['seasons']);
$expo = fetch($hike['expo']);
$curr_gpx = fetch($hike['gpx']);
$curr_trk = fetch($hike['trk']);
$lat = fetch($hike['lat']);
$lng = fetch($hike['lng']);
//$purl1 = fetch($hike['purl1']);  // not currently editable
//$purl2 = fetch($hike['purl2']);  // not currently editable
$dirs = fetch($hike['dirs']);
/**
 * Tab2: [photo displays (already uploaded)]
 */
require "photoSelect.php";
/**
 * Tab 3: [hike tips and hike descripton]
 */
$tips = fetch($hike['tips']);
$info = fetch($hike['info']);
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
    $reftype = fetch($ref['rtype']);
    array_push($rtypes, $reftype);
    $ritem1 = fetch($ref['rit1']);
    array_push($rit1s, $ritem1);
    $ritem2 = fetch($ref['rit2']);
    array_push($rit2s, $ritem2);
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
$pl = array();
$pu = array();
$pc = array();
for ($j=0; $j<$gpsDbCnt; $j++) {
    $gpsdat = $gpsq->fetch(PDO::FETCH_ASSOC);
    $pl[$j] = fetch($gpsdat['label']);
    $pu[$j] = fetch($gpsdat['url']);
    $pc[$j] = fetch($gpsdat['clickText']);
}
