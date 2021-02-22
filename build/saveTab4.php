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
        if ($drtypes[$j] !== 'Book:' && $drtypes[$j] !== 'Photo Essay:') {
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
            $url = filter_var($_POST[$o1id], FILTER_VALIDATE_URL);
            if ($url === false) {
                $_SESSION['riturl'] = "The URL you entered is not valid";
                $url = " --- INVALID URL DETECTED ---";
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
$_SESSION['gpsmsg'] = '';
$gpsfile = uploadGpxKmlFile('newgps', true);
if ($_SESSION['user_alert'] !== 'No file specified') {
    if (empty($_SESSION['user_alert'])) {
        $ngpsreq
            = "INSERT INTO `EGPSDAT` (`indxNo`,`datType`,`label`,`url`,`clickText`) " .
                "VALUES (?,'P','GPX:',?,'GPX Track File');";
        $newgps = $pdo->prepare($ngpsreq);
        $newgps->execute([$hikeNo, $gpsfile]);
        $gfile = pathinfo($gpsfile, PATHINFO_FILENAME);
        $_SESSION['gpsmsg'] .= "{$gfile} was successfully uploaded; ";
    } else {
        header("Location: {$redirect}");
        exit;
    }
} else {
    $_SESSION['user_alert'] = '';
}
$htmlfile = validateUpload('newmap');
if (!empty($htmlfile['file'])) {
    if ($htmlfile['type'] === 'html') {
        if (empty($_SESSION['user_alert'])) {
            $newurl = '../maps/' . $htmlfile['file'];
            $ngpsreq = "INSERT INTO EGPSDAT (indxNo,datType,label,`url`," .
                "clickText) VALUES (?,'P','MAP:',?,'Map File');";
            $newgps = $pdo->prepare($ngpsreq);
            $newgps->execute([$hikeNo, $newurl]);
            $_SESSION['gpsmsg'] .= " {$htmlfile['file']} was successfully uploaded" ;
        } else {
            header("Location: {$redirect}");
            exit;
        } 
    } else {
        $_SESSION['user_alert'] = "Only html files can be uploaded here";
        header("Location: {$redirect}");
        exit;
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
    // any entries will contain datId of the corresponding text
    $deletes = $_POST['delgps'];
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
header("Location: {$redirect}");
