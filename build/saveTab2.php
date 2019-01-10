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

$hikeNo = filter_input(INPUT_POST, 'pno');
$uid = filter_input(INPUT_POST, 'pid');
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
$photoReq = "SELECT picIdx,title,hpg,mpg,`desc` FROM ETSV WHERE indxNo = ?;";
$photoq = $pdo->prepare($photoReq);
$photoq->execute([$hikeNo]);
$cnt = $photoq->rowCount();
if ($noOfPix !== $cnt) {
    echo '<p style="color:red;font-size:20px;margin-left:16px;">'
    . "WARNING: Retrieved photo count and no of captions don't match..</p>";
}
$p = 0;
while ($photo = $photoq->fetch(PDO::FETCH_ASSOC)) {
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
$redirect = "editDB.php?hno={$hikeNo}&usr={$uid}&tab=2";
header("Location: {$redirect}");
