<?php
/* Since references may have been marked for deletion in the edit phase,
 * the approach taken is to simply delete all refs, then add back any 
 * other than those so marked, including any changes made thereto. This then
 * includes newly added refs, so all get INSERTED, and no algorithm is required
 * to determine which only get updated vs which get added vs which get deleted.
 */ 
if ($tbl_type === 'new') {
    # deletion is not required for 'old' since there aren't any yet in EREFS
    $delrefsreq = "DELETE FROM EREFS WHERE indxNo = '{$hikeNo}';";
    $delrefs = mysqli_query($link,$delrefsreq);
    if (!$delrefs) {
        die("saveRefEdits.php: Failed to delete old refs for {$hikeNo}: " .
            mysqli_error($link));
    }
    mysqli_free_result($delrefs);
    $useIndxNo = $hikeNo;
} else {
    $useIndxNo = $newNo;
}
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
        $a = mysqli_real_escape_string($link,$ertypes[$j]);
        $b = mysqli_real_escape_string($link,$erit1s[$j]);
        $c = mysqli_real_escape_string($link,$erit2s[$j]);
        $addrefreq = "INSERT INTO EREFS (indxNo,rtype,rit1,rit2) VALUES " .
            "('{$useIndxNo}','{$a}','{$b}','{$c}');";
        $addref = mysqli_query($link,$addrefreq);
        if (!$addref) {
            die("saveRefEdits.php: Failed to insert EREFS data: " . 
                mysqli_error($link));
        }
    }
}
mysqli_free_result($addref);
