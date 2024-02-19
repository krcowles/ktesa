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
$redirect = "editDB.php?tab=4&hikeNo={$hikeNo}";
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
// Delete all References already existing in the database when they exist
$delrefsreq = "DELETE FROM EREFS WHERE indxNo = ?;";
$delrefs = $pdo->prepare($delrefsreq);
$delrefs->execute([$hikeNo]);
// 1. Now add the newly edited ones back in (if any), sans any deletions
$drit1s = isset($_POST['drit1']) ? $_POST['drit1'] : false;
if ($drit1s) {
    $drtypes = $_POST['drtype'];  // reference type from select drop-down
    $drit2s = isset($_POST['drit2']) ? $_POST['drit2'] : [];
    // determine if any refs were marked for deletion ('delref's)
    if (isset($_POST['delref'])) {
        $deletes = $_POST['delref']; // array will contain the ref# on editDB.php
        $chk_del = true;
    } else {
        $deletes = [];
        $chk_del = false;
    }
    $dindx = 0;
    for ($j=0; $j<count($drit1s); $j++) {
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
        if ($addit && !empty($drit1s[$j])) {
            if ($drtypes[$j] !== 'Book:' && $drtypes[$j] !== 'Photo Essay:'
                && $drtypes[$j] !== 'Text:'
            ) {
                $rit1 = filter_var($drit1s[$j], FILTER_VALIDATE_URL);
                if (empty($rit1) || $rit1 === false) {
                    $rit1 = " --- INVALID URL DETECTED ---";
                }
            } else {
                $rit1 = $drit1s[$j];  // constrained text, no filter required
            }
            $addrefreq = "INSERT INTO EREFS (indxNo,rtype,rit1,rit2) VALUES " .
                "(?,?,?,?);";
            $orefs = $pdo->prepare($addrefreq);
            $orefs->execute([$hikeNo, $drtypes[$j], $rit1, $drit2s[$j]]);
        }
    }
}
// 2. New references added, if any: (displayed items are yes/no)
$newtypes = $_POST['rtype'];
$usebooks = $_POST['usebks']; // value="yes" means book;
$useothrs = $_POST['notbks']; // value="no"  means URL
$addcnt = 0;
$addtypes = [];
$addrit1s = [];
$addrit2s = [];
for ($s=0; $s<count($usebooks); $s++) {
    $bkid = 'brit1' . $s;  // input contains book name
    $auid = 'brit2' . $s;  // input contains book author
    $o1id = 'orit1' . $s;  // input contains URL
    $o2id = 'orit2' . $s;  // input contains clickText
    if ($usebooks[$s] === 'yes' && $useothrs[$s] === 'no') {
        $auth = filter_input(INPUT_POST, $auid);
        if (!empty($auth)) {
            // this is a new book or photo essay reference:
            array_push($addtypes, $newtypes[$s]);
            $bkname = filter_input(INPUT_POST, $bkid);
            array_push($addrit1s, $bkname);
            $bkauth = filter_input(INPUT_POST, $auid);
            array_push($addrit2s, $bkauth);
            $addcnt++;
        }
    } elseif ($usebooks[$s] === 'no' && $useothrs[$s] === 'yes') {
        if ($_POST[$o1id] !== '') {  // This is the url
            array_push($addtypes, $newtypes[$s]);
            if (strpos($newtypes[$s], 'Text') !== false) {
                $url = $_POST[$o1id];
            } else {
                $url = filter_var($_POST[$o1id], FILTER_VALIDATE_URL);
                if ($url === false) {
                    $_SESSION['riturl'] = "The URL you entered is not valid";
                    $url = " --- INVALID URL DETECTED ---";
                }
            }
            array_push($addrit1s, $url);
            $ctxt = filter_var($_POST[$o2id]);
            array_push($addrit2s, $ctxt);
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
 * GPS Data File upload section. May be a gpx or kml file, or an html map file
 */
$alert_set = false;
unset($_SESSION['uplmsg']);
$_SESSION['gpsmsg'] = '';
if (!empty($_FILES['newgps']['name'])) {
    $tab4upload = prepareUpload('newgps');
    $gpsfile = uploadFile($tab4upload);
    if ($gpsfile !== 'none') {
        if ($tab4upload['ext'] === "gpx") {
            $prevs = 'ls ' . '../json/egp' . $hikeNo . '_* | wc -l';
            $preloads = intval(system($prevs));
            $next  = $preloads + 1;
            $gps_array = makeTrackFiles(
                $pdo, 'gps', $tab4upload['ufn'], "newgps.gpx", $hikeNo, $next
            );
            $gpsDbDat = json_encode($gps_array);
            $ngpsreq
                = "INSERT INTO `EGPSDAT` (`indxNo`,`label`,`url`," .
                    "`clickText`) VALUES (?,'GPX:',?,'GPX Track File');";
            if (!unlink($gpsfile)) {
                throw new Exception("Could not delete temporary file {$gpsfile}; ");
            }
        } else { // kml
            $gpsDbDat = $gpsfile;
            $ngpsreq
                = "INSERT INTO `EGPSDAT` (`indxNo`,`label`,`url`," .
                    "`clickText`) VALUES (?,'KML:',?,'KML File');";
        }
        $newgps = $pdo->prepare($ngpsreq);
        $newgps->execute([$hikeNo, $gpsDbDat]);
    } else {
        $alert_set = true;
    }
}
// Uploading of html map files:
if (!empty($_FILES['newmap']['name'])) {
    $mapfile = uploadFile(prepareUpload('newmap'));
    // Any issues?
    if ($mapfile === 'none') {
        $alert_set = true;
    } else {
        $ngpsreq = "INSERT INTO EGPSDAT (indxNo,label,`url`," .
            "clickText) VALUES (?,'MAP:',?,'Map File');";
        $newgps = $pdo->prepare($ngpsreq);
        $newgps->execute([$hikeNo, $mapfile]);
    }
}
if (!$alert_set) {
    $_SESSION['alerts'] = ["", "", "", ""];
}
/**
 * NOTE: the only items that have 'delete' boxes are those for which GPS data
 * already existed in the database.
 */
$clickText = isset($_POST['clickText']) ? $_POST['clickText'] : [];
$datId     = isset($_POST['datId']) ? $_POST['datId'] : [];
$label     = isset($_POST['label']) ? $_POST['label'] : [];
$del_value = isset($_POST['del_value']) ? $_POST['del_value'] : [];
$deletes   = isset($_POST['delgps']) ? $_POST['delgps'] : [];
for ($j=0; $j<count($clickText); $j++) {
    if (in_array($datId[$j], $deletes)) {
        if ($label[$j] === "GPX:") {
            $json_files = explode(",", $del_value[$j]);
            foreach ($json_files as $file) {
                if (!unlink("../json/" . $file)) {
                    throw new Excdeption("Could not remove {$file}");
                }
            }
        } else {
            // delete html, pdf, or kml file
            if (!unlink($del_value[$j])) {
                throw new Exception("Could not remove file: {$del_value[$j]}; ");
            }
        }
        $delReq = "DELETE FROM `EGPSDAT` WHERE `datId`=?;";
        $delEntry = $pdo->prepare($delReq);
        $delEntry->execute([$datId[$j]]);
    } else {
        $updteReq = "UPDATE `EGPSDAT` SET `clickText`=? WHERE " .
        "`datId`=?;";
        $updte = $pdo->prepare($updteReq);
        $updte->execute([$clickText[$j], $datId[$j]]);
    }
}

// return to editor with new data:
header("Location: {$redirect}");
