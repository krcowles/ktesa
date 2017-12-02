<?php
session_start();
$_SESSION['activeTab'] = 2;
require_once '../mysql/setenv.php';
$hikeNo = filter_input(INPUT_POST,'pno');
$uid = filter_input(INPUT_POST,'pid');
$ecapts = $_POST['ecap'];
$noOfPix = count($ecapts);
# capture the states of each for displaying on page or and/or on map
$displayPg = $_POST['pix'];
$displayMap = $_POST['mapit'];
$photoReq = "SELECT picIdx,title,hpg,mpg,`desc` FROM ETSV WHERE indxNo = '{$hikeNo}'";
$photoq = mysqli_query($link,$photoReq);
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
    $newcap = mysqli_real_escape_string($link,$ecapts[$p]);
    # determine if $thispic has a corresponding checkbox value:
    # NOTE: If not checked, array will not contain $thispic
    $disph = 'N';
    for ($i=0; $i<$noOfPix; $i++) {
        if ($thispic == $displayPg[$i]) {
            $disph = 'Y';
            break;
        }
    }
    $dispm = 'N';
    for ($j=0; $j<$noOfPix; $j++) {
        if ($thispic == $displayMap[$j]) {
            $dispm = 'Y';
            break;
        }
    } 
    $updtreq = "UPDATE ETSV SET hpg = '{$disph}',mpg = '{$dispm}',"
        ."`desc` = '{$newcap}' WHERE picIdx = {$thisid};";
    $update = mysqli_query($link,$updtreq);
    if (!$update) {
        die("savePicEdits.php: Failed to update ETSV table for hike {$hikeNo}: "
            . msyqli_error($link));
    }
    $p++;
}
mysqli_free_result($update);
mysqli_free_result($photoq);
$redirect = "editDB.php?hno={$hikeNo}&usr={$uid}";
header("Location: {$redirect}");
?>