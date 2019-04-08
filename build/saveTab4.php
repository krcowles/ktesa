<?php
/**
 * This file saves data present on tab4 (Related Hike Info), including
 * uploads of gps file data (gpx or html maps).
 * PHP Version 7.1
 * 
 * @package Editing
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";

$hikeNo = filter_input(INPUT_POST, 'hikeNo');
$usr = filter_input(INPUT_POST, 'usr');
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
$delrefsreq = "DELETE FROM EREFS WHERE indxNo = ?;";
$delrefs = $pdo->prepare($delrefsreq);
$delrefs->execute([$hikeNo]);
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
        $addrefreq = "INSERT INTO EREFS (indxNo,rtype,rit1,rit2) VALUES (?,?,?,?);";
        $orefs = $pdo->prepare($addrefreq);
        $orefs->execute([$hikeNo, $drtypes[$j], $drit1s[$j], $drit2s[$j]]);
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
            $b = $_POST[$bkid];
            array_push($addrit1s, $b);
            $c = $_POST[$auid];
            array_push($addrit2s, $c);
            $addcnt++;
        }
    } elseif ($usebooks[$s] === 'no' && $useothrs[$s] === 'yes') {
        if ($_POST[$o1id] !== '') {
            $a = $newtypes[$s]; // no wierd characters here
            array_push($addtypes, $a);
            $b = filter_var($_POST[$o1id], FILTER_VALIDATE_URL);
            if ($b === false) {
                $_SESSION['riturl'] = "The URL you entered is not valid";
                $b = " --- INVALID URL ---";
            }
            array_push($addrit1s, $b);
            $c = $_POST[$o2id];

            array_push($addrit2s, $c);
            $addcnt++;
        }
    }
}
// add any new items to EREFS:
if ($addcnt > 0) {
    for ($m=0; $m<$addcnt; $m++) {
        if (trim($addtypes[$m]) === 'Book:') {
            $addnewreq = "INSERT INTO EREFS (indxNo,rtype,rit1) VALUES (?,?,?);";
            $addnew = $pdo->prepare($addnewreq);
            $addnew->execute([$hikeNo, $addtypes[$m], $addrit1s[$m]]);
        } else {
            $addnewreq 
                = "INSERT INTO EREFS (indxNo,rtype,rit1,rit2) VALUES (?,?,?,?)";
            $addnew = $pdo->prepare($addnewreq);
            $addnew->execute([$hikeNo, $addtypes[$m], $addrit1s[$m], $addrit2s[$m]]);
        }
    }
}
/*
 * Beyond uploading new GPS Data files, the only user editing permitted is
 * on the click-text for a file. If the click-text is marked for deletion,
 * the GPS Data reference, in its entirety, will be deleted.
 *
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
        $newcot = 'GPX Track File';
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
        $ngpsreq = "INSERT INTO EGPSDAT (indxNo,datType,label,`url`,clickText) " .
            "VALUES (?,'P',?,?,?);";
        $newgps = $pdo->prepare($ngpsreq);
        $newgps->execute([$hikeNo, $newlbl, $newurl, $newcot]);
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
        $newcot = 'Map';
        break;
    default:
        $mapok = false;
    }
    if ($mapok) {
        $upload = validateUpload("newmap", $maptype[0]);
        $_SESSION['gpsmsg'] .= $upload[1];
        $newurl = $maptype[0] . $upload[0];
        $newmapreq = "INSERT INTO EGPSDAT (indxNo,datType,label,`url`,clickText) " .
            "VALUES (?,'P',?,?,?);";
        $newmap = $pdo->prepare($newmapreq);
        $newmap->execute([$hikeNo, $newlbl, $newurl, $newcot]);
    } else {
        $_SESSION['gpsmsg'] .= '<p style="color:red;">FILE NOT UPLOADED: ' .
            "File Type NOT .html for {$mapupl}.</p>";
    }
}
/**
 * NOTE: the only items that have 'delete' boxes are those for which GPS data
 * already existed in the database. Those checkboxes will have values of
 * 0..$newcnt.
 */
// Pick up any changes to click-text
$clickText = isset($_POST['clickText']) ? $_POST['clickText'] : [];
$datId = isset($_POST['datId']) ? $_POST['datId'] : [];
if (isset($_POST['delgps'])) {
    $deletes = $_POST['delgps']; // any entries will contain datId of the corresponding text
    $chk_del = true;
} else {
    $deletes = [];
    $chk_del = false;
}
$datacnt = empty($clickText) ? 0 : count($clickText);
$cb_indx = 0;
for ($j=0; $j<$datacnt; $j++) {
    $update = true;
    if ($chk_del) {  // delete checkboxes exist
        if ($datId[$j] == $deletes[$cb_indx]) {
            $delgpsreq = "DELETE FROM EGPSDAT WHERE datId = ?;";
            $delgps = $pdo->prepare($delgpsreq);
            $delgps->execute([$datId[$j]]);
            $cb_indx++; // advance to next delete checkbox, if there is one
            $update = false;
            if ($cb_indx >= count($deletes)) {
                $chk_del = false;
            }
        }
    }
    if ($update) {
        $addgpsreq = "UPDATE EGPSDAT SET clickText = ? WHERE datID = ?;";
        $addgps = $pdo->prepare($addgpsreq);
        $addgps->execute([$clickText[$j], $datId[$j]]);
    }
}
// return to editor with new data:
$redirect = "editDB.php?hikeNo={$hikeNo}&usr={$usr}&tab=4";
header("Location: {$redirect}");
