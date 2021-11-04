<?php
/**
 * This file saves data present on tab4 (Related Hike Info), including
 * uploads of gps file data (gpx/kml or html maps).
 * PHP Version 7.4
 * 
 * @package Editing
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";

$hikeNo = filter_input(INPUT_POST, 'hikeNo');
$redirect = "editDB.php?tab=4&hikeNo={$hikeNo}";
$_SESSION['user_alert'] = ''; // start clean
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
// Delete all References already existing in the database:
$delrefsreq = "DELETE FROM EREFS WHERE indxNo = ?;";
$delrefs = $pdo->prepare($delrefsreq);
$delrefs->execute([$hikeNo]);
// 1. Now add the newly edited ones back in (if any), sans any deletions
if (isset($_POST['drtype'])) {
    $drtypes = $_POST['drtype'];  // reference type from select drop-down
}
if (isset($_POST['drit1'])) {  // item1 : either book name or url
    $drit1s = $_POST['drit1'];
}
if (isset($_POST['drit2'])) {  // item2 : either author or click-text
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
        $addrefreq = "INSERT INTO EREFS (indxNo,rtype,rit1,rit2) VALUES (?,?,?,?);";
        $orefs = $pdo->prepare($addrefreq);
        $orefs->execute([$hikeNo, $drtypes[$j], $rit1, $drit2s[$j]]);
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
        if ($_POST[$auid] !== '') {
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
 * GPS Data File upload section. May be a gpx or kml file, or an html map file
 *  - GPX files are stored in the META & GPX tables
 *  - KML files are stored in the gpx directory
 *  - HTML map files are stored in the maps directory
 */
// Retrieve any existing EGPSDAT data
$currGpsReq = "SELECT * FROM `EGPSDAT` WHERE `indxNo`=?;";
$currGps = $pdo->prepare($currGpsReq);
$currGps->execute([$hikeNo]);
$gps_data = $currGps->fetchAll(PDO::FETCH_ASSOC);
$no_previous = count($gps_data) === 0 ? true : false;

// uploads here will put data in EGPSDAT
$_SESSION['gpsmsg'] = '';
$gpxtxt = filter_input(INPUT_POST, 'glnktxt'); // either gpx or kml
$gpsfile = uploadGpxKmlFile($pdo, $gdb, 'newgps', $hikeNo, true, true, false);
if ($_SESSION['user_alert'] !== 'No file specified') {
    if (empty($_SESSION['user_alert'])) {
        if ($gpsfile[3] === 'gpx') { // gpx file
            $ngpsreq
                = "INSERT INTO `EGPSDAT` (`indxNo`,`fileno`,`label`,`clickText`) " .
                    "VALUES (?,?,'GPX:',?);";
            $newgps = $pdo->prepare($ngpsreq);
            $newgps->execute([$hikeNo, $gpsfile[0], $gpxtxt]);
        } else if ($gpsfile[3] === 'kml') { // kml file
            $kmlLabel = '../gpx/' . $gpsfile[2]; 
            $nkmlReq
                = "INSERT INTO `EGPSDAT` (`indxNo`,`label`,`clickText`) " .
                    "VALUES (?,?,?);";
            $newkml = $pdo->prepare($nkmlReq);
            $newkml->execute([$hikeNo, $kmlLabel, $gpxtxt]);
        } else {
            $_SESSION['user_alert'] = "Wrong file type for upload: use gpx or kml";
            header("Location: {$redirect}");
            exit;
        }
        $_SESSION['gpsmsg'] .= "{$gpsfile[2]} was successfully uploaded; ";
    } // ELSE do nothing - proceed with html uploads if any; preserve alert
} else {
    // The typical case will not have an upload here
    $_SESSION['user_alert'] = '';
}
$saved_alert = !empty($_SESSION['user_alert']) ? $_SESSION['user_alert'] : false;
$_SESSION['user_alert'] = ''; // reset for next upload

// HTML MAP File Upload
$htmtxt = filter_input(INPUT_POST, 'hlnktxt');
$htmname = $_FILES['newmap']['name'];
$htmbase = pathinfo($htmname, PATHINFO_EXTENSION);
// preliminary extension check so no gpx file is uploaded here
if (strtolower($htmbase) === 'html') {
    $htmlfile = uploadGpxKmlFile($pdo, $gdb, 'newmap', $hikeNo, true);
    if ($_SESSION['user_alert'] !== 'No file specified') {
        if (empty($_SESSION['user_alert'])) {
            if ($htmlfile[3] === 'html') {
                $mtxt = "../maps/" . $htmlfile[2];
                $ngpsreq = "INSERT INTO EGPSDAT (`indxNo`,`label`,`clickText`) " .
                    "VALUES (?,?,?);";
                $newgps = $pdo->prepare($ngpsreq);
                $newgps->execute([$hikeNo, $mtxt, $htmtxt]);
                $_SESSION['gpsmsg'] .= " {$htmlfile[2]} was successfully uploaded" ;
            } else {
                $_SESSION['user_alert'] = "Only html files can be uploaded here";
            }
        } // ELSE DO NOTHING: Proceed with any further processing
    } else {
        // the typical case will not have an upload here,
        $_SESSION['user_alert'] = '';
    }
} else {
    $_SESSION['user_alert'] = "Incorrect file type: should be .html";
}
if ($saved_alert && !empty($_SESSION['user_alert'])) {
    $_SESSION['user_alert'] = $saved_alert . PHP_EOL . $_SESSION['user_alert'];
} else {
    $_SESSION['user_alert'] = $saved_alert;
}

/**
 * NOTE: the only items that might appear in this last section on Tab4 are items
 * for which GPS data already exists in the database. Every posting here will have
 * a 'clickText' [Link text] <textarea>, a hidden input containing the textarea's
 * 'datId', a file name, and a checkbox allowing the user to delete the item.
 * The checkbox will also have a value of 'datId' in order to ID the entry to be
 * deleted, if any.
 */
// Pick up all 'clickText' fields (one-to-one corr. w/$gps_data values in the array)
$clickText = isset($_POST['clickText']) ? $_POST['clickText'] : [];
// any checked boxes will contain 'datId' of the entries to be deleted
if (isset($_POST['delgps'])) {
    $deletes = $_POST['delgps'];
    $chk_del = true;
} else {
    $deletes = [];
    $chk_del = false;
}
// there may not be pre-existing data
if (!$no_previous) {
    $itemno = 0;
    foreach ($gps_data as $entry) {
        // is this entry to be deleted?
        if (in_array($entry['datId'], $deletes)) {
            // is this a GPX file?
            if (!empty($entry['fileno'])) {
                deleteGpxData('new', $gdb, $entry['fileno']);
            } else { // kml or html file with defined path
                $nongpx = explode("|", $entry['label']);
                if (unlink($nongpx[1]) === false) {
                    throw new Exception("Could not delete {$nongpx[1]}");
                }
            }
            $delgpsreq = "DELETE FROM EGPSDAT WHERE datId = ?;";
            $delgps = $pdo->prepare($delgpsreq);
            $delgps->execute([$entry['datId']]);
        } else {
            $updtgps = "UPDATE EGPSDAT SET clickText = ? WHERE datID = ?;";
            $update = $pdo->prepare($updtgps);
            $update->execute([$clickText[$itemno], $entry['datId']]);
        }
        $itemno++;
    }
}
// return to editor with new data:
header("Location: {$redirect}");
