<?php
session_start();
$_SESSION['activeTab'] = 4;
require_once "../mysql/dbFunctions.php";
$link = connectToDb(__FILE__, __LINE__);
$hikeNo = filter_input(INPUT_POST, 'rno');
$usr = filter_input(INPUT_POST, 'rid');
$uid = mysqli_real_escape_string($link, $usr);
/* Since references may have been marked for deletion in the edit phase,
 * the approach taken is to simply delete all refs, then add back any 
 * other than those so marked, including any changes made thereto. This then
 * includes newly added refs, so all get INSERTED, and no algorithm is required
 * to determine which only get updated vs which get added vs which get deleted.
 */
$delrefsreq = "DELETE FROM EREFS WHERE indxNo = '{$hikeNo}';";
$delrefs = mysqli_query($link, $delrefsreq);
if (!$delrefs) {
    die("saveTab4.php: Failed to delete old EREFS for {$hikeNo}: " .
        mysqli_error($link));
}
mysqli_free_result($delrefs);
# Now add the newly edited ones back in, sans any deletions
# NOTE: The following posts collect all items, even if empty...
$ertypes = $_POST['rtype'];  # because there is always a default rtype
$erit1s = $_POST['rit1'];
$erit2s = $_POST['rit2'];
# NOTE: The following post only collects checked boxes
$deletes = $_POST['delref']; # any entries will contain the ref no on editDB.php
if (count($deletes) > 0) {
    $chk_del = true;
} else {
    $chk_del = false;
}
$dindx = 0;
$newcnt = count($erit1s);
/*
 * NOTE: the only items that have 'delete' boxes are those for which references
 * already existed in the database, and they are listed before any that might
 * get added. Therefore, proceeding through the loop, the first ones can be
 * compared to any corresponding $deletes ref pointer.
 */
for ($j=0; $j<$newcnt; $j++) {
    $addit = true;
    if ($chk_del) {
        if ($j === intval($deletes[$dindx])) {
            $dindx++; # skip this and look for the next;
            if ($dindx === count($deletes)) {
                $chk_del = false;
            }
            $addit = false;
        }
    }
    if ($addit && $erit1s[$j] !== '') {
        $a = mysqli_real_escape_string($link, $ertypes[$j]);
        $b = mysqli_real_escape_string($link, $erit1s[$j]);
        $c = mysqli_real_escape_string($link, $erit2s[$j]);
        $addrefreq = "INSERT INTO EREFS (indxNo,rtype,rit1,rit2) VALUES " .
            "('{$hikeNo}','{$a}','{$b}','{$c}');";
        $addref = mysqli_query($link, $addrefreq);
        if (!$addref) {
            die("saveTab4.php: Failed to insert EREFS data: " .
                mysqli_error($link));
        }
    }
}
mysqli_free_result($addref);
/* Since GPS Maps & Data may have been marked for deletion in the edit phase,
 * the approach taken is to simply delete all GPS data, then add back any 
 * other than those so marked, including any changes made thereto. This then
 * includes newly added GPS data, so all get INSERTED, and no algorithm is required
 * to determine which only get updated vs which get added vs which get deleted.
 */
$delgpsreq = "DELETE FROM EGPSDAT WHERE indxNo = '{$hikeNo}';";
$delgps = mysqli_query($link, $delgpsreq);
if (!$delgps) {
    die("saveTab4.php: Failed to delete old GPS data for {$hikeNo}: " .
        mysqli_error($link));
}
mysqli_free_result($delgps);
# Now add the newly edited ones back in, sans any deletions
$lbl = $_POST['labl'];
$url = $_POST['lnk'];
$cot = $_POST['ctxt'];
# NOTE: The following post only collects checked boxes
$deletes = $_POST['delgps']; # any entries will contain the ref no on editDB.php
if (count($deletes) > 0) {
    $chk_del = true;
} else {
    $chk_del = false;
}
$dindx = 0;
$newcnt = count($lbl);
/*
 * NOTE: the only items that have 'delete' boxes are those for which GPS data
 * already existed in the database, and they are listed before any that might
 * get added. Therefore, proceeding through the loop, the first ones can be
 * compared to any corresponding $deletes ref pointer.
 */
for ($j=0; $j<$newcnt; $j++) {
    $addit = true;
    if ($chk_del) {
        if ($j === intval($deletes[$dindx])) {
            $dindx++; # skip this and look for the next;
            if ($dindx === count($deletes)) {
                $chk_del = false;
            }
            $addit = false;
        }
    }
    if ($addit && $lbl[$j] !== '') {
        $a = mysqli_real_escape_string($link, $lbl[$j]);
        $b = mysqli_real_escape_string($link, $url[$j]);
        $c = mysqli_real_escape_string($link, $cot[$j]);
        // For now, all entries will be marked 'P'
        $addgpsreq = "INSERT INTO EGPSDAT (indxNo,datType,label,url,clickText) " .
            "VALUES ('{$hikeNo}','P','{$a}','{$b}','{$c}');";
        $addgps = mysqli_query($link, $addgpsreq);
        if (!$addgps) {
            die("saveTab4.php: Failed to insert EGPSDAT data: " .
                mysqli_error($link));
        }
    }
}
mysqli_free_result($addgps);
// return to editor with new data:
$redirect = "editDB.php?hno={$hikeNo}&usr={$uid}";
header("Location: {$redirect}");
