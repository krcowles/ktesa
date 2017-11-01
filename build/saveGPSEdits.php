<?php
if ($tbl_type === 'old') {
    # all GPSDAT data needs to be copied to EGPSDAT, whether 'A' or 'P' type
    $getpReq = "SELECT * FROM GPSDAT WHERE indxNo = {$hikeNo};";
    $getpq = mysqli_query($link,$getpReq);
    if (!$getpq) {
        die("saveGPSEdits.php: Failed to pull GPSDAT data for move to EGPSDAT: " .
            mysqli_error($link));
    }
    if (mysqli_num_rows($getpq) !== 0) {
        while ($gdat = mysqli_fetch_assoc($getpq)) {
            $dtype = $gdat['datType'];
            $dlbl = mysqli_real_escape_string($link,$gdat['label']);
            $durl = mysqli_real_escape_string($link,$gdat['url']);
            $dcot = mysqli_real_escape_string($link,$gdat['clickText']);
            $gpsReq = "INSERT INTO EGPSDAT (indxNo,datType,label,url,clickText) " .
                "VALUES ('{$newNo}','{$dtype}','{$dlbl}','{$durl}','{$dcot}');";
            $gpsq = mysqli_query($link,$gpsReq);
            if (!$gpsq) {
                die("saveGPSEdits.php: Failed to add EGPSDAT data for hike {$hikeNo}: " .
                    mysqli_error($link));
            }
        }
        mysqli_free_result($gpsq);
    }
    mysqli_free_result($gpsq);
    $useNo = $newNo;
} else {
    $useNo = $hikeNo;
}
# Delete any Proposed or Actual Data so marked in editDB.php
$delps = $_POST['delprop'];
$delas = $_POST['delact'];
if (count($delps) !== 0 || count($delas) !== 0) {
    $gpsidReq = "SELECT datId,datType FROM EGPSDAT WHERE indxNo = {$useNo};"; # 'P's and 'A's
    $egpsq = mysqli_query($link,$gpsidReq);
    if (!$egpsq) {
        die("saveGPSEdits.php: Failed to extract datIds from EGPSDAT for hike {$useNo}: " .
            mysqli_error($link));
    }
    $pids = [];
    $aids = [];
    while ($gpsrow = mysqli_fetch_assoc($egpsq)) {
        $thisid = $gpsrow['datId'];
        if ($gpsrow['datType'] === 'P') {
            array_push($pids,$thisid);
        } else {
            array_push($aids,$thisid);
        }
    }
    mysqli_free_result($egpsq);
    # check for proposed data:
    if (count($pids) !== 0) {
        $pindx = 0;
        $pcnt = 0;
        while ($pindx < count($delps)) {
            if ($delps[$pindx] == $pcnt) {
                # delete this gps ref
                $delpReq = "DELETE FROM EGPSDAT WHERE datId = {$pids[$pindx]};";
                $delpq = mysqli_query($link,$delpReq);
                if (!$delpq) {
                    die("saveGPSEdits.php: Failed to delete id {$pids[$pindx]}: " .
                        mysqli_error($link));
                }
                $pindx++;
            } 
            $pcnt++;
        }
        mysqli_free_result($delpq);
    }  
    # check for actual data
    if (count($aids) !== 0) {
        $aindx = 0;
        $acnt = 0;
        while ($aindx < count($delas)) {
            if ($delas[$aindx] == $acnt) {
                # delete this gps ref
                $delaReq = "DELETE FROM EGPSDAT WHERE datId = {$aids[$aindx]};";
                $delaq = mysqli_query($link,$delaReq);
                if (!$delpq) {
                    die("saveGPSEdits.php: Failed to delete id {$aids[$aindx]}: " .
                        mysqli_error($link));
                }
                $aindx++;
            } 
            $acnt++;
        }
        mysqli_free_result($delaq);
    }    
}

