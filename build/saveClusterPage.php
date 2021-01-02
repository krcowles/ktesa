<?php
/**
 * This script allows data entry for a new cluster page, or updating
 * data for an existing one.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license None to date
 */
session_start();
require "../php/global_boot.php";

$cname  = filter_input(INPUT_POST, 'clustergroup');
$indxNo = filter_input(INPUT_POST, 'indxNo');
$locale = filter_input(INPUT_POST, 'locale');
$info   = filter_input(INPUT_POST, 'info');
$lat    = filter_input(INPUT_POST, 'lat');
$lng    = filter_input(INPUT_POST, 'lng');
$dirs   = filter_input(INPUT_POST, 'dirs');
// Google maps direction links no longer pass FILTER_VALIDATE_URL...
if (empty($dirs)) {
    $dirs = '';
}
   
$lat = empty($lat) ? null : $lat * LOC_SCALE;
$lng = empty($lng) ? null : $lng * LOC_SCALE;

$saveReq = "UPDATE `EHIKES` SET `locale`=?,`lat`=?,`lng`=?,`dirs`=?,`info`=? " .
    "WHERE `indxNo`=?;";
$saveClus = $pdo->prepare($saveReq);
$saveClus->execute([$locale, $lat, $lng, $dirs, $info, $indxNo]);

// Unpublished Clusters only:
$clusStateReq = "SELECT `pub` FROM `CLUSTERS` WHERE `group`=?;";
$clusState = $pdo->prepare($clusStateReq);
$clusState->execute([$cname]);
$pub = $clusState->fetch(PDO::FETCH_ASSOC);
if ($pub['pub'] === 'N') {
    $coordsReq = "UPDATE `CLUSTERS` SET `lat`=?,`lng`=? WHERE `group`=?;";
    $coords = $pdo->prepare($coordsReq);
    $coords->execute([$lat, $lng, $cname]);
}

// Save any updated references (Pre-populated refs)
// First, delete all References already existing in the database:
$delrefsreq = "DELETE FROM EREFS WHERE indxNo = ?;";
$delrefs = $pdo->prepare($delrefsreq);
$delrefs->execute([$indxNo]);
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
            // Sometimes text gets shifted when simply deleting INVALID msg:
            $durl = trim($drit1s[$j]);
            $rit1 = filter_var($durl, FILTER_VALIDATE_URL);
            if (empty($rit1) || $rit1 === false) {
                $_SESSION['riturl'] = "The URL you entered is not valid";
                $rit1 = " --- INVALID URL DETECTED ---";
            }
        } else {
            $rit1 = $drit1s[$j];  // constrained text, no filter required
        }
        $addrefreq = "INSERT INTO EREFS (indxNo,rtype,rit1,rit2) VALUES (?,?,?,?);";
        $orefs = $pdo->prepare($addrefreq);
        $orefs->execute([$indxNo, $drtypes[$j], $rit1, $drit2s[$j]]);
    }
}

// Save newly added references
$newtypes = $_POST['rtype'];  // select #href boxes
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
            $addnew->execute([$indxNo, $addtypes[$m], $addrit1s[$m]]);
        } else {
            $addnewreq 
                = "INSERT INTO EREFS (indxNo,rtype,rit1,rit2) VALUES (?,?,?,?)";
            $addnew = $pdo->prepare($addnewreq);
            $addnew->execute([$indxNo, $addtypes[$m], $addrit1s[$m], $addrit2s[$m]]);
        }
    }
}
// Once the data has been saved, return with indexNo instead of cluster name
$redir = "editClusterPage.php?hikeNo={$indxNo}";
header("Location: {$redir}");
