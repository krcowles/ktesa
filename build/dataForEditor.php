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
$hikeNo = filter_input(INPUT_GET, 'hno');
$uid = filter_input(INPUT_GET, 'usr');
$dispTab = filter_input(INPUT_GET, 'tab');
// data for drop-down boxes
$selectData = dropdownData($pdo, 'cls');
$cnames = array_values($selectData);
$groups = array_keys($selectData);
// assign existing hike data
$hikereq = "SELECT * FROM EHIKES WHERE indxNo = :hikeno;";
$hikeq = $pdo->prepare($hikereq);
$retrieved = $hikeq->execute(["hikeno" => $hikeNo]);
if ($retrieved === false) {
    throw new Exception(
        "Hike {$hikeNo} Not Found in EHIKES; File " . __FILE__ . 
        " line no. " . __LINE__
    );
}
// there will be only one row...
$hike = $hikeq->fetch(PDO::FETCH_ASSOC);
$hikeTitle = trim($hike['pgTitle']);  // this should never be null
$hikeLocale = fetch($hike['locale']);
$hikeMarker = fetch($hike['marker']);  // this also should never be null...
$hikeColl = fetch($hike['collection']);
$hikeClusGrp = fetch($hike['cgroup']);
$hikeGrpTip = fetch($hike['cname']);
// Special case: when a new page requests to add a new group, advise the js
if ($hikeMarker === 'Cluster' && $hikeClusGrp === '') {
    $hikeMarker = 'Normal';
    $grpReq = "YES";
} else {
    $grpReq = "NO";
}
$hikeStyle = fetch($hike['logistics']);
$hikeMiles = fetch($hike['miles']);
$hikeFeet = fetch($hike['feet']);
$hikeDiff = fetch($hike['diff']);
$hikeFac = fetch($hike['fac']);
$hikeWow = fetch($hike['wow']);
$hikeSeasons = fetch($hike['seasons']);
$hikeExpos = fetch($hike['expo']);
$hikeGpx = fetch($hike['gpx']);
$curr_gpx = $hikeGpx;
$curr_trk = fetch($hike['trk']);
$hikeLat = fetch($hike['lat']);
$hikeLng = fetch($hike['lng']);
$hikeUrl1 = fetch($hike['purl1']);
$hikeUrl2 = fetch($hike['purl2']);
$hikeDirs = fetch($hike['dirs']);
$hikeTips = $hike['tips'];
$hikeDetails = fetch($hike['info']);
// References for tab4:
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
