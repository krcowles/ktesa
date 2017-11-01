<?php
if ($tbl_type === 'old') {
    # all REFS data needs to be copied to EREFS
    # The new hike no ($newno) was established in savePicEdits.php
    $getReq = "SELECT * FROM REFS WHERE indxNo = {$hikeNo};";
    $getq = mysqli_query($link,$getReq);
    if (!$getq) {
        die("saveRefEdits.php: Failed to pull REFS data for move to EREFS: " .
            mysqli_error($link));
    }
    while ($r = mysqli_fetch_assoc($getq)) {
        $rtype = $r['rtype'];
        $rit1 = mysqli_real_escape_string($link,$r['rit1']);
        $rit2 = mysqli_real_escape_string($link,$r['rit2']);
        $refReq = "INSERT INTO EREFS (indxNo,rtype,rit1,rit2) VALUES (" .
            "'{$newNo}','{$rtype}','{$rit1}','{$rit2}');";
        $refq = mysqli_query($link,$refReq);
        if (!$refq) {
            die("saveRefEdits.php: Failed to add EREFS data for hike {$hikeNo}: " .
                mysqli_error($link));
        }
    }
    mysqli_free_result($getq);
    $useNo = $newNo;
} else {
    $useNo = $hikeNo;
}
# Delete any refs so marked in editDB.php
$deletes = $_POST['delref'];
echo "No of deletes: " . count($deletes);
if (count($deletes) !== 0) {
    $erefReq = "SELECT refId FROM EREFS WHERE indxNo = {$useNo};";
    $erefq = mysqli_query($link,$erefReq);
    if (!$erefq) {
        die("saveRefEdits.php: Failed to extract refids from EREFS for hike {$useNo}: " .
            mysqli_error($link));
    }
    $rcnt = 0;
    $rindx = 0;
    while ($refrow = mysqli_fetch_row($erefq)) {
        $thisid = $refrow[0];
        if ($deletes[$rindx] == $rcnt) {
            # delete this ref
            $delReq = "DELETE FROM EREFS WHERE refId = {$thisid};";
            $delq = mysqli_query($link,$delReq);
            if (!$delq) {
                die("saveRefEdits.php: Failed to delete id {$thisid}: " .
                    mysqli_error($link));
            }
            $rindx++;
        }
        $rcnt++;
    }
    mysqli_free_result($delq);
    mysqli_free_result($erefq);
}
