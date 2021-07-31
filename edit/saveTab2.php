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
verifyAccess('post');

/* It is possible that no pictures are present, also that no
 * checkboxes are checked. Therefore, the script tests for these conditons
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
$photoReq = "SELECT * FROM `ETSV` WHERE `indxNo` = ?;";
$photoq = $pdo->prepare($photoReq);
$photoq->execute([$hikeNo]);
$picarray = $photoq->fetchAll(PDO::FETCH_ASSOC);
usort($picarray, "cmp"); // sort by stored sequence number 
for ($n=0; $n<count($picarray); $n++) {
    if (!empty($picarray[$n]['imgHt'])) {  // waypoints have empty imgHt
        $thisid = (string) $picarray[$n]['picIdx'];
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
}
// enter/save waypoints
require "waypointSave.php";

$redirect = "editDB.php?tab=2&hikeNo={$hikeNo}";
header("Location: {$redirect}");
