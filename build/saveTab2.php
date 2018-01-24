<?php
session_start();
$_SESSION['activeTab'] = 2;
require_once "../mysql/dbFunctions.php";
$link = connectToDb(__FILE__, __LINE__);
$hikeNo = filter_input(INPUT_POST, 'pno');
$uid = filter_input(INPUT_POST, 'pid');
/* It is possible that no pictures are present, also that no
 * checkboxes are checked. Therefore, the script tests for these things
 * to prevent undefined vars
 */
// # captions corresponds to # pictures present
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
$photoReq = "SELECT picIdx,title,hpg,mpg,`desc` FROM ETSV WHERE indxNo = '{$hikeNo}'";
$photoq = mysqli_query($link, $photoReq);
if (!$photoq) {
    die("saveTab2.php: Failed to extract data from ETSV: " .
        mysqli_error($link));
}
$cnt = mysqli_num_rows($photoq);
if ($noOfPix !== $cnt) {
    echo '<p style="color:red;font-size:20px;margin-left:16px;">'
    . "WARNING: Retrieved photo count and no of captions don't match..</p>";
}

$p = 0;
while ($photo = mysqli_fetch_assoc($photoq)) {
    $thisid = $photo['picIdx'];
    $thispic = $photo['title'];
    $newcap = mysqli_real_escape_string($link, $ecapts[$p]);
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
        $del = mysqli_query($link, "DELETE FROM ETSV WHERE title = '{$thispic}';");
        if (!$del) {
            die("saveTab2.php: Failed to remove photo {$thispic}: " .
                mysqli_error($link));
        }
    } else {
        $updtreq = "UPDATE ETSV SET hpg = '{$disph}',mpg = '{$dispm}',"
            . "`desc` = '{$newcap}' WHERE picIdx = {$thisid};";
        $update = mysqli_query($link, $updtreq);
        if (!$update) {
            die("saveTab2.php: Failed to update ETSV table for hike {$hikeNo}: "
                . msyqli_error($link));
        }
    }
    $p++;
}
mysqli_free_result($photoq);
$redirect = "editDB.php?hno={$hikeNo}&usr={$uid}";
header("Location: {$redirect}");
