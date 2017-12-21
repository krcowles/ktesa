<?php
require_once "../mysql/setenv.php";
$hikeRow = intval(filter_input(INPUT_POST, 'hikeno'));
$usePix = filter_input(INPUT_POST, 'usepics');
# indicator for hikePageTemplate.php
$building = true;
$usetsv = false;
# output msg styling (when error encountered)
$pstyle = '<p style="margin-left:16px;color:red;font-size:20px;">';
# -------------------------- PIC ROW CONSTRUCTION --------------------------
# Adjust database to register picture choices: hike pg & map
if ($usePix == 'YES') {
    $picarray = $_POST['pix'];  # pix checked for inclusion on hike page
    $noOfPix = count($picarray);
    $maparray = $_POST['mapit']; # pix checked for inclusion on map
    $noOfMapPix = count($maparray);
    $getPixReq = "SELECT picIdx,title FROM ETSV WHERE indxNo = '{$hikeRow}';";
    $getPix = mysqli_query($link, $getPixReq);
    if (!$getPix) {
        die("displayHikePg.php: Failed to retrieve picture data for hike " .
                $hikeRow . " in ETSV: " . mysqli_error($link));
    }
    while ($picdata = mysqli_fetch_assoc($getPix)) {
        $disph = 'N';
        $dispm = 'N';
        $thispic = $picdata['title'];
        # for each title, ascertain hpg, mpg status
        for ($i=0; $i<$noOfPix; $i++) {
            if ($thispic == $picarray[$i]) {
                $disph = 'Y';
                break;
            }
        }
        for ($j=0; $j<$noOfMapPix; $j++) {
            if ($thispic == $maparray[$j]) {
                $dispm = 'Y';
                break;
            }
        }
        if ($disph === 'Y' || $dispm === 'Y') {
            $pstatq = "UPDATE ETSV SET hpg = '{$disph}', mpg = '{$dispm}' " .
                "WHERE picIdx = {$picdata['picIdx']};";
            $pstat = mysqli_query($link, $pstatq);
            if (!$pstat) {
                die("displayHikePg.php: Failed to update TSV pg setting: " .
                        mysqli_error($link));
            }
        }
    }
    mysqli_free_result($pstat);
}
# Set the status field to "sub" (meaning "submitted" as a complete hike
$subreq = "UPDATE EHIKES SET stat = 'sub' WHERE indxNo = {$hikeRow};";
$sub =  mysqli_query($link, $subreq);
if (!$sub) {
    die("displayHikePg.php: Failed to change status field in EHIKES: " .
        mysqli_error($link));
}
mysqli_free_result($sub);
/*  PENDING NEW PROCESS REVIEW....
if (!mail('krcowles29@gmail.com','Hike Submitted',$hikeRow)) {
    echo "Failed to send submit message";
}
 */
/*
    ------------------------------ PIC ROW CONSTRUCTION -------------------------
*/
include "../pages/hikePageTemplate.php";
