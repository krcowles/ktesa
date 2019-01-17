<?php
/**
 * This script will collect the url info from the editor page and prepare it
 * for consumption by the js ajax request to extract album photo data. If either
 * of the url fields in the data base is empty, the db may update the field with
 * the incoming url's.
 * PHP Version 7.1
 * 
 * @package Editing
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
$incl = $_POST['ps'];
$curlids = [];
$albums = [];
$lnk1 = '';
$lnk2 = '';
$j = 0;
foreach ($incl as $newalb) {
    $alnk = 'lnk' . $newalb;
    $atype = 'alb' . $newalb;
    $curlids[$j] = filter_input(INPUT_POST, $alnk);
    $albums[$j] = filter_input(INPUT_POST, $atype);
    $j++;
}
// get a separate copy of $curlids to find any associations with purl1 or purl2
$arrObj = new ArrayObject($curlids);
$avail = $arrObj->getArrayCopy();
$supplied = count($curlids);
// get current urls in EHIKES and compare to incoming album links
$lnkReq = "SELECT purl1,purl2 FROM EHIKES WHERE indxNo = ?;";
$linkQ = $pdo->prepare($lnkReq);
$linkQ->execute([$hikeNo]);
$dburl = [];
// get empty strings if fields are null
$purls = $linkQ->fetch(PDO::FETCH_ASSOC);
for ($a=0; $a<2; $a++) {
    $dburl[$a] = fetch($purls[$a]);
}
/**
 * IF there is a future desire to delete an existing link, 
 * this is the place to code it;
 * see if there are already existing links, get a count, and eliminate
 * from the $avail list;
 */
$existing = 0;
for ($j=0; $j<count($dburl); $j++) {
    if ($dburl[$j] !== '') { // this purl already has a url in the db
        $existing++;
        if (in_array($dburl[$j], $avail)) {
            $offset = array_search($dburl[$j], $avail);
            array_splice($avail, $offset, 1);
        }
    }
}
// fill any empties with what is now available 
if ($existing < count($dburl)) {
    for ($k=0; $k<count($dburl); $k++) {
        if ($dburl[$k] == '') {
            if (count($avail) > 0) {
                $dburl[$k] = array_pop($avail);
            }
        }
    }
}
// update database values:
if ($dburl[0] === '') {
    $u1req = "UPDATE EHIKES SET purl1 = NULL WHERE indxNo = ?;";
    $u1 = $pdo->prepare($u1req);
    $u1->execute([$hikeNo]);
} else {
    $u1req = "UPDATE EHIKES SET purl1 = ? WHERE indxNo = ?;";
    $u1 = $pdo->prepare($u1req);
    $u1->execute([$dburl[0], $hikeNo]);
}
if ($dburl[1] === '') {
    $u2req = "UPDATE EHIKES SET purl2 = NULL WHERE indxNo = ?;";
    $u2 = $pdo->prepare($u2req);
    $u2->execute([$hikeNo]);
} else {
    $u2req = "UPDATE EHIKES SET purl2 = ? WHERE indxNo = ?;";
    $u2 = $pdo->prepare($u2req);
    $u2->execute([$dburl[1], $hikeNo]);
}
$alburls = json_encode($curlids);
$albtypes = json_encode($albums);
