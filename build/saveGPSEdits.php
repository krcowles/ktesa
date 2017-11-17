<?php
/* Since GPS Maps & Data may have been marked for deletion in the edit phase,
 * the approach taken is to simply delete all GPS data, then add back any 
 * other than those so marked, including any changes made thereto. This then
 * includes newly added GPS data, so all get INSERTED, and no algorithm is required
 * to determine which only get updated vs which get added vs which get deleted.
 */ 
if ($tbl_type === 'new') {
    # deletion is not required for 'old' since there aren't any yet in EGPSDAT
    $delrefsreq = "DELETE FROM EGPSDAT WHERE indxNo = '{$hikeNo}';";
    $delrefs = mysqli_query($link,$delrefsreq);
    if (!$delrefs) {
        die("saveGPSEdits.php: Failed to delete old GPS data for {$hikeNo}: " .
            mysqli_error($link));
    }
    mysqli_free_result($delrefs);
    $useIndxNo = $hikeNo;
} else {
    $useIndxNo = $newNo;
}
# Now add the newly edited ones back in, sans any deletions
$plbl = $_POST['plabl'];
$purl = $_POST['plnk'];
$pcot = $_POST['pctxt'];
# NOTE: The following post only collects checked boxes
$deletes = $_POST['delprop']; # any entries will contain the ref no on editDB.php
if (count($deletes) > 0) {
    $chk_del = true;
} else {
    $chk_del = false;
}
$dindx = 0;
$newcnt = count($plbl);
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
    if ($addit && $plbl[$j] !== '') {
        $a = mysqli_real_escape_string($link,$plbl[$j]);
        $b = mysqli_real_escape_string($link,$purl[$j]);
        $c = mysqli_real_escape_string($link,$pcot[$j]);
        $addpreq = "INSERT INTO EGPSDAT (indxNo,datType,label,url,clickText) " .
            "VALUES ('{$useIndxNo}','P','{$a}','{$b}','{$c}');";
        $addp = mysqli_query($link,$addpreq);
        if (!$addp) {
            die("saveGPSEdits.php: Failed to insert EGPSDAT data: " . 
                mysqli_error($link));
        }
    }
}
mysqli_free_result($addp);
$albl = $_POST['alabl'];
$aurl = $_POST['alnk'];
$acot = $_POST['actxt'];
# NOTE: The following post only collects checked boxes
$deletes = $_POST['delact']; # any entries will contain the ref no on editDB.php
if (count($deletes) > 0) {
    $chk_del = true;
} else {
    $chk_del = false;
}
$dindx = 0;
$newcnt = count($albl);
/*
 * NOTE: the only items that have 'delete' boxes are those for which GPS data
 * already existed in the database, and they are listed before any that might
 * get added. Therefore, proceeding through the loop, the first ones can be
 * compared to any corresponding $deletes ref pointer.
 */
for ($k=0; $k<$newcnt; $k++) {
    $addit = true;
    if ($chk_del) {
        if ($k === intval($deletes[$dindx])) {
            $dindx++; # skip this and look for the next;
            if ($dindx === count($deletes)) {
                $chk_del = false;
            }
            $addit = false;
        }  
    } 
    if ($addit && $albl[$k] !== '') {
        $a = mysqli_real_escape_string($link,$albl[$k]);
        $b = mysqli_real_escape_string($link,$aurl[$k]);
        $c = mysqli_real_escape_string($link,$acot[$k]);
        $addareq = "INSERT INTO EGPSDAT (indxNo,datType,label,url,clickText) " .
            "VALUES ('{$useIndxNo}','A','{$a}','{$b}','{$c}');";
        $adda = mysqli_query($link,$addareq);
        if (!$adda) {
            die("saveGPSEdits.php: Failed to insert EGPSDAT data: " . 
                mysqli_error($link));
        }
    }
}
mysqli_free_result($adda);