<?php
/**
 * This file saves data present on tab4 (Related Hike Info), including
 * uploads of gps file data (gpx or html maps).
 * PHP Version 7.0
 * 
 * @package Editing
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 * @link    ../docs/
 */
session_start();
require_once "../mysql/dbFunctions.php";
require 'buildFunctions.php';
$link = connectToDb(__FILE__, __LINE__);
$hikeNo = filter_input(INPUT_POST, 'rno');
$usr = filter_input(INPUT_POST, 'rid');
$uid = mysqli_real_escape_string($link, $usr);
/**
 * There are two sections of 'references': 1) existing in db; 2) new (if any)
 *   1. Those which already exist in the database may have been edited by the
 *      user, or marked for deletion. These are noted as 'drtype', 'drit1', 
 *      and 'drit2' (where 'd' prefix means 'deletable'). These are processed 
 *      differently than:
 *   2. Those which may have been added by the user. These have 'hidden' elements
 *      which need to be differentiated. This is a result of the fact that
 *      the displays for books/photo-essays are different than those which are not 
 *      (e.g. drop-downs vs inputs), so this script needs to process only those 
 *      which are visible, as those represent ones that may have been added.
 *      For every rit1 and rit2 there is a corresponding hidden rit1, rit2.
 */
// 1. Refernces already existing in the database:
$delrefsreq = "DELETE FROM EREFS WHERE indxNo = '{$hikeNo}';";
$delrefs = mysqli_query($link, $delrefsreq) or die(
    "saveTab4.php: Failed to delete old EREFS for {$hikeNo}: " .
    mysqli_error($link)
);
// Now add the newly edited ones back in, sans any deletions
// NOTE: The following posts collect all items, even if empty (but not if hidden)
if (isset($_POST['drtype'])) {
    $drtypes = $_POST['drtype'];
}
if (isset($_POST['drit1'])) {
    $drit1s = $_POST['drit1'];
}
if (isset($_POST['drit2'])) {
    $drit2s = $_POST['drit2'];
}
// determine if any refs were marked for deletion ('delref's)
if (isset($_POST['delref'])) {
    $deletes = $_POST['delref']; // any entries will contain the ref# on editDB.php
    $chk_del = true;
} else {
    $deletes = [];
    $chk_del = false;
}
$dindx = 0;
if (isset($_POST['drit1'])) {
    $newcnt = count($drit1s);
} else {
    $newcnt = 0;
}
for ($j=0; $j<$newcnt; $j++) {
    $addit = true;
    if ($chk_del) {
        if ($j === intval($deletes[$dindx])) {
            $dindx++; // skip this and look for the next;
            if ($dindx === count($deletes)) {
                $chk_del = false;
            }
            $addit = false;
        }
    }
    if ($addit && $drit1s[$j] !== '') {
        $a = mysqli_real_escape_string($link, $drtypes[$j]);
        $b = mysqli_real_escape_string($link, $drit1s[$j]);
        $c = mysqli_real_escape_string($link, $drit2s[$j]);
        $addrefreq = "INSERT INTO EREFS (indxNo,rtype,rit1,rit2) VALUES " .
            "('{$hikeNo}','{$a}','{$b}','{$c}');";
        $addref = mysqli_query($link, $addrefreq) or die(
            __FILE__ . " " . __LINE__ . ": Failed to insert EREFS data: " . 
            mysqli_error($link)
        );
    }
}
// 2. New references added, if any: (displayed items are yes/no)
$newtypes = $_POST['rtype'];
$usebooks = $_POST['usebks'];
$useothrs = $_POST['notbks'];
$addcnt = 0;
$addtypes = [];
$addrit1s = [];
$addrit2s = [];
for ($s=0; $s<count($usebooks); $s++) {
    $bkid = 'brit1' . $s;
    $auid = 'brit2' . $s;
    $o1id = 'orit1' . $s;
    $o2id = 'orit2' . $s;
    if ($usebooks[$s] === 'yes' && $useothrs[$s] === 'no') {
        $auth = filter_input(INPUT_POST, $auid);
        if ($_POST[$auid] !== '') {
            // this is a new book or photo essay reference:
            $a = $newtypes[$s]; // no wierd characters here
            array_push($addtypes, $a);
            $b = mysqli_real_escape_string($link, $_POST[$bkid]);
            array_push($addrit1s, $b);
            $c = mysqli_real_escape_string($link, $_POST[$auid]);
            array_push($addrit2s, $c);
            $addcnt++;
        }
    } elseif ($usebooks[$s] === 'no' && $useothrs[$s] === 'yes') {
        if ($_POST[$o1id] !== '') {
            $a = $newtypes[$s]; // no wierd characters here
            array_push($addtypes, $a);
            $b = mysqli_real_escape_string($link, $_POST[$o1id]);
            array_push($addrit1s, $b);
            $c = mysqli_real_escape_string($link, $_POST[$o2id]);
            array_push($addrit2s, $c);
            $addcnt++;
        }
    }
}
// add any new items to EREFS:
if ($addcnt > 0) {
    for ($m=0; $m<$addcnt; $m++) {
        if (trim($addtypes[$m]) === 'Book:') {
            $addnewreq = "INSERT INTO EREFS (indxNo,rtype,rit1) VALUES " .
                "('{$hikeNo}','{$addtypes[$m]}','{$addrit1s[$m]}');";
            $addnew = mysqli_query($link, $addnewreq) or die(
                __FILE__ . " " . __LINE__ . ": Failed to insert EREFS data: " . 
                mysqli_error($link)
            );
        } else {
            $addnewreq = "INSERT INTO EREFS (indxNo,rtype,rit1,rit2) VALUES " .
                "('{$hikeNo}','{$addtypes[$m]}','{$addrit1s[$m]}'," .
                "'{$addrit2s[$m]}');";
            $addnew = mysqli_query($link, $addnewreq) or die(
                __FILE__ . " " . __LINE__ . ": Failed to insert EREFS data: " . 
                mysqli_error($link)
            );
        }
    }
}
/* Since GPS Maps & Data may have been marked for deletion in the edit phase,
 * the approach taken is to simply delete all GPS data, then add back any 
 * other than those so marked, including any changes made thereto. This then
 * includes newly added GPS data, so all get INSERTED, and no algorithm is required
 * to determine which only get updated vs which get added vs which get deleted.
 */
$delgpsreq = "DELETE FROM EGPSDAT WHERE indxNo = '{$hikeNo}';";
$delgps = mysqli_query($link, $delgpsreq) or die(
    "saveTab4.php: Failed to delete old GPS data for {$hikeNo}: " .
    mysqli_error($link)
);
// Now add the newly edited ones back in, sans any deletions
$lbl = $_POST['labl'];
$url = $_POST['lnk'];
$cot = $_POST['ctxt'];
// NOTE: The following post only collects checked boxes
if (isset($_POST['delgps'])) {
    $deletes = $_POST['delgps']; // any entries will contain the ref# on editDB.php
    $chk_del = true;
} else {
    $deletes = [];
    $chk_del = false;
}
$dindx = 0;
$newcnt = count($lbl);
/**
 * GPS Data File upload section.
 */
$_SESSION['gpsmsg'] = '';
$gpsupl = basename($_FILES['newgps']['name']);
if ($gpsupl !== '') {
    $gpsok = true;
    $gpstype = fileTypeAndLoc($gpsupl);
    switch ($gpstype[2]) {
    case 'gpx':
        $newlbl = 'GPX:';
        $newcot = 'Track File';
        break;
    case 'kml':
        $newlbl = 'KML:';
        $newcot = "Google Earth File";
        break;
    default:
        $gpsok = false;
    }
    if ($gpsok) {
        $upload = validateUpload("newgps", $gpstype[0]);
        $_SESSION['gpsmsg'] .= $upload[1];
        $newurl = $gpstype[0] . $upload[0];
        $newlnk = mysqli_real_escape_string($link, $newurl);
        $newgpsreq = "INSERT INTO EGPSDAT (indxNo,datType,label,`url`,clickText) " .
            "VALUES ('{$hikeNo}','P','{$newlbl}','{$newlnk}','{$newcot}');";
        $newgps = mysqli_query($link, $newgpsreq) or die(
            __FILE__ . " Line " . __LINE__ . ": Failed to insert new gps "
            . "file loc for hike {$hikeNo}: " . mysqli_error($link)
        );
        $_SESSION['gpsmsg'] .= "<br /><em>A default 'Label' and " .
            "'Click-on-Text' have been provided for {$gpsupl}.</em>";
    } else {
        $_SESSION['gpsmsg'] .= '<p style="color:red;">FILE NOT UPLOADED: ' .
            "File Type NOT .gpx or .kml for {$gpsupl}.</p>";
    }
}
$mapupl = basename($_FILES['newmap']['name']);
if ($mapupl !== '') {
    $mapok = true;
    $maptype = fileTypeAndLoc($mapupl);
    switch ($maptype[2]) {
    case 'html':
        $newlbl = "MAP:";
        $newcot = 'Area Map';
        break;
    default:
        $mapok = false;
    }
    if ($mapok) {
        $upload = validateUpload("newmap", $maptype[0]);
        $_SESSION['gpsmsg'] .= $upload[1];
        $newurl = $maptype[0] . $upload[0];
        $newlnk = mysqli_real_escape_string($link, $newurl);
        $newmapreq = "INSERT INTO EGPSDAT (indxNo,datType,label,`url`,clickText) " .
            "VALUES ('{$hikeNo}','P','{$newlbl}','{$newlnk}','{$newcot}');";
        $newmap = mysqli_query($link, $newmapreq) or die(
            __FILE__ . " Line " . __LINE__ . ": Failed to insert new gps "
            . "file loc for hike {$hikeNo}: " . mysqli_error($link)
        );
        $_SESSION['gpsmsg'] .= "<br /><em>A default 'Label' and " .
            "'Click-on-Text' have been provided for {$mapupl}.</em>";
    } else {
        $_SESSION['gpsmsg'] .= '<p style="color:red;">FILE NOT UPLOADED: ' .
            "File Type NOT .html for {$mapupl}.</p>";
    }
}
/**
 * NOTE: the only items that have 'delete' boxes are those for which GPS data
 * already existed in the database, and they are listed before any that might
 * get added. Therefore, proceeding through the loop, the first ones can be
 * compared to any corresponding $deletes ref pointer.
 */
for ($j=0; $j<$newcnt; $j++) {
    $addit = true;
    if ($chk_del) {
        if ($j === intval($deletes[$dindx])) {
            $dindx++; // skip this and look for the next;
            if ($dindx === count($deletes)) {
                $chk_del = false;
            }
            $addit = false;
        }
    }
    if ($addit && $url[$j] !== '') {
        $a = mysqli_real_escape_string($link, $lbl[$j]);
        $b = mysqli_real_escape_string($link, $url[$j]);
        $c = mysqli_real_escape_string($link, $cot[$j]);
        // For now, all entries will be marked 'P'
        $addgpsreq = "INSERT INTO EGPSDAT (indxNo,datType,label,url,clickText) " .
            "VALUES ('{$hikeNo}','P','{$a}','{$b}','{$c}');";
        $addgps = mysqli_query($link, $addgpsreq) or die(
            "saveTab4.php: Failed to insert EGPSDAT data: " .
            mysqli_error($link)
        );
    }
}
// return to editor with new data:
$redirect = "editDB.php?hno={$hikeNo}&usr={$uid}&tab=4";
header("Location: {$redirect}");
