<?php
/**
 * This routine updates ETSV values for photo captions, and hpg and mpg
 * values for the corresponding photos. It requires the waypoint save 
 * script as well (see waypointSave.php for documentation). Note that the
 * JQuery-ui 'sortable' feature was added for drag 'n drop reshuffling of
 * the photos/checkboxes/images - all of which remain associated with each
 * other. However, waypoints do not have these features, and so the no.
 * of photos does not necessarily equal the number of waypoints, and the
 * photo feature indices must be carefully handled.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
$hikeNo = filter_input(INPUT_POST, 'hikeNo');
verifyAccess('post');

/**
 * Separate the stored database photos from the stored waypoints,
 * if either is present. The stored photos will then be indexed
 * the same as the posted captions.
 */
$itemReq = "SELECT * FROM `ETSV` WHERE `indxNo` = ?;";
$items = $pdo->prepare($itemReq);
$items->execute([$hikeNo]);
if (($tsvarray = $items->fetchAll(PDO::FETCH_ASSOC)) === false) {
    throw new Exception("Photo fetch failed in saveTab2");
}

$photosOnly = [];
$wayptsOnly = [];
foreach ($tsvarray as $data) {
    if (!empty($data['mid'])) {
        array_push($photosOnly, $data);
    } else {
        array_push($wayptsOnly, $data);
    }
}
$picCount = count($photosOnly);
$wptCount = count($wayptsOnly);

/* It is possible that no pictures are present, also that no
 * checkboxes are checked. Any photos have associated captions,
 * so # of capts = # of photos
 */
if (isset($_POST['ecap'])) {
    $ecapts = $_POST['ecap'];
    // associate each $ecapt with a 
} else { // no photos on page yet
    $ecapts = [];
}
// 'pix' are the checkboxes indicating a photo is spec'd for the hike page
if (isset($_POST['pix'])) {
    $displayPg = $_POST['pix'];
} else {
    $displayPg = [];
}
// 'mapit' are the checkboxes indicating a photo is spec'd for the map
if (isset($_POST['mapit'])) {
    $displayMap = $_POST['mapit'];
} else {
    $displayMap = [];
}
// 'rem' are the checkboxes marking photos to be deleted
if (isset($_POST['rem'])) {
    $rems = $_POST['rem'];
    $noOfRems = count($rems);
} else {
    $rems = [];
    $noOfRems = 0;
}
/**
 * Sort the photos according to 'org' field, which holds drag 'n drop
 * placement when shuffled by user. "cmp" function in editFunctions.php
 */
usort($photosOnly, "cmp");

/**
 * Make any changes to photos specified by user
 */
for ($n=0; $n<$picCount; $n++) {
    $thisid = (string) $photosOnly[$n]['picIdx'];
    $newcap = $ecapts[$n];
    // look for a matching checkbox then set for display (or map)
    $disph = 'N';
    for ($i=0; $i<count($displayPg); $i++) {
        if ($thisid == $displayPg[$i]) {
            $disph = 'Y';
            break;
        }
    }
    $dispm = 'N';
    for ($j=0; $j<count($displayMap); $j++) {
        if ($thisid == $displayMap[$j]) {
            $dispm = 'Y';
            break;
        }
    }
    $deletePic = false;
    for ($k=0; $k<$noOfRems; $k++) {
        if ($rems[$k] === $thisid) {
            $deletePic = true;
            break;
        }
    }
    if ($deletePic) {
        $delreq = "DELETE FROM `ETSV` WHERE `picIdx` = ?;";
        $del = $pdo->prepare($delreq);
        $del->execute([$thisid]);
    } else {
        $updtreq = "UPDATE `ETSV` SET `hpg`=?,`mpg`=?,`desc`=?,`org`=? "
            . "WHERE picIdx = ?;";
        $update = $pdo->prepare($updtreq);
        $update->execute([$disph, $dispm, $newcap, $n, $thisid]);
    }
}
// enter/save waypoints
require "waypointSave.php";

// get last_hiked date:
require "lastHiked.php";

$redirect = "editDB.php?tab=2&hikeNo={$hikeNo}";
header("Location: {$redirect}");
