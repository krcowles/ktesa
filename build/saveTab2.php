<?php
/**
 * This routine saves any changes made (or current data) on tab2
 * ('Photo Selection') and updates the ETSV table.
 * PHP Version 7.1
 * 
 * @package Editing
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

$hikeNo = filter_input(INPUT_POST, 'hikeNo');
$usr = filter_input(INPUT_POST, 'usr');
// waypoint data, if present:
$wids = isset($_POST['wids']) ? $_POST['wids'] : null;  // picIdx for waypoint
$wdes = isset($_POST['wsym']) ? $_POST['wdes'] : null;
$wsym = isset($_POST['wsym']) ? $_POST['wsym'] : null;
$wlat = isset($_POST['wlat']) ? $_POST['wlat'] : null;
$wlng = isset($_POST['wlng']) ? $_POST['wlng'] : null;
/* It is possible that no pictures are present, also that no
 * checkboxes are checked. Therefore, the script tests for these things
 * to prevent undefined vars
 */
// # of captions corresponds to # pictures present
if (isset($_POST['ecap'])) {
    $ecapts = $_POST['ecap'];
    $noOfPix = count($ecapts);
} else {
    $ecapts = [];
    $noOfPix = 0;
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
$photoReq = "SELECT * FROM ETSV WHERE indxNo = ?;";
$photoq = $pdo->prepare($photoReq);
$photoq->execute([$hikeNo]);
$p = 0;
while ($photo = $photoq->fetch(PDO::FETCH_ASSOC)) {
    if (!empty($photo['imgHt'])) {
        $thisid = $photo['picIdx'];
        $thispic = $photo['title'];
        $newcap = $ecapts[$p];
        // look for a matching checkbox then set for display (or map)
        $disph = 'N';
        for ($i=0; $i<count($displayPg); $i++) {
            if ($thispic == $displayPg[$i]) {
                $disph = 'Y';
                break;
            }
        }
        $dispm = 'N';
        for ($j=0; $j<count($displayMap); $j++) {
            if ($thispic == $displayMap[$j]) {
                $dispm = 'Y';
                break;
            }
        }
        $deletePic = false;
        for ($k=0; $k<$noOfRems; $k++) {
            if ($rems[$k] === $thispic) {
                $deletePic = true;
                break;
            }
        }
        if ($deletePic) {
            $delreq = "DELETE FROM ETSV WHERE title = ?;";
            $del = $pdo->prepare($delreq);
            $del->execute([$thispic]);
        } else {
            $updtreq = "UPDATE ETSV SET hpg = ?, mpg = ?, `desc` = ? WHERE picIdx = ?;";
            $update = $pdo->prepare($updtreq);
            $update->execute([$disph, $dispm, $newcap, $thisid]);
        }
        $p++;
    }
}
if (isset($wids)) {
    for ($k=0; $k<count($wids); $k++) {
        $wayptquery = "UPDATE ETSV SET title = ?, lat = ?, lng = ?, iclr = ?
            WHERE picIdx = ?;";
        $waypt = $pdo->prepare($wayptquery);
        $waypt->execute([$wdes[$k], $wlat[$k], $wlng[$k], $wsym[$k], $wids[$k]]);
    }
}
$redirect = "editDB.php?hikeNo={$hikeNo}&usr={$usr}&tab=2";
header("Location: {$redirect}");
