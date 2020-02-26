<?php
/** 
 * This script converts lat/lon data from float to int 
 * PHP Version 7.1
 * 
 * @category None
 * @package  Admin
 * @author   Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license  No license to date
 * @link     None
 */

/*
ALTER TABLE EHIKES
DROP COLUMN latInt,
DROP COLUMN lngInt,
ADD COLUMN latInt int(10) DEFAULT NULL AFTER lat,
ADD COLUMN lngInt int(10) DEFAULT NULL AFTER lng;
*/

require "../php/global_boot.php";
// Note that this establishes the database credentials

$tbl = $pdo->prepare(
    "
    ALTER TABLE EHIKES
    ADD COLUMN latInt int(10) DEFAULT NULL AFTER lat,
    ADD COLUMN lngInt int(10) DEFAULT NULL AFTER lng;
    ALTER TABLE HIKES
    ADD COLUMN latInt int(10) DEFAULT NULL AFTER lat,
    ADD COLUMN lngInt int(10) DEFAULT NULL AFTER lng;
    ALTER TABLE TSV
    ADD COLUMN latInt int(10) DEFAULT NULL AFTER lat,
    ADD COLUMN lngInt int(10) DEFAULT NULL AFTER lng;
    ALTER TABLE ETSV
    ADD COLUMN latInt int(10) DEFAULT NULL AFTER lat,
    ADD COLUMN lngInt int(10) DEFAULT NULL AFTER lng;
"
);
if ($tbl->execute() === false) {
    throw new Exception("Query failed");
}
die;

$tbl = $pdo->prepare("SELECT * FROM HIKES");
if ($tbl->execute() === false) {
    throw new Exception("Query failed");
}
$tblData = $tbl->fetchALL(PDO::FETCH_ASSOC);

foreach ($tblData as $tblDataItem) {
    $indxNo = $tblDataItem['indxNo'];
    $latInt = is_null($tblDataItem['lat']) ?
        null :
        (int) ((float)($tblDataItem['lat']) * LOC_SCALE);
    $lngInt = is_null($tblDataItem['lng']) ?
        null :
        (int) ((float)($tblDataItem['lng']) * LOC_SCALE);

    $updtreq = "UPDATE HIKES SET latInt = ?, lngInt = ? "
    . "WHERE indxNo = ?;";
    $update = $pdo->prepare($updtreq);
    $update->execute([$latInt, $lngInt, $indxNo]);
}

$tbl = $pdo->prepare("SELECT * FROM EHIKES");
if ($tbl->execute() === false) {
    throw new Exception("Query failed");
}
$tblData = $tbl->fetchALL(PDO::FETCH_ASSOC);

foreach ($tblData as $tblDataItem) {
    $indxNo = $tblDataItem['indxNo'];
    $latInt = is_null($tblDataItem['lat']) ?
        null :
        (int) ((float)($tblDataItem['lat']) * LOC_SCALE);
    $lngInt = is_null($tblDataItem['lng']) ?
        null :
        (int) ((float)($tblDataItem['lng']) * LOC_SCALE);

    $updtreq = "UPDATE EHIKES SET latInt = ?, lngInt = ? "
    . "WHERE indxNo = ?;";
    $update = $pdo->prepare($updtreq);
    $update->execute([$latInt, $lngInt, $indxNo]);
}

$tbl = $pdo->prepare("SELECT * FROM TSV");
if ($tbl->execute() === false) {
    throw new Exception("Query failed");
}
$tblData = $tbl->fetchALL(PDO::FETCH_ASSOC);

foreach ($tblData as $tblDataItem) {
    $picIdx = $tblDataItem['picIdx'];
    $latInt = is_null($tblDataItem['lat']) ?
        null :
        (int) ((float)($tblDataItem['lat']) * LOC_SCALE);
    $lngInt = is_null($tblDataItem['lng']) ?
        null :
        (int) ((float)($tblDataItem['lng']) * LOC_SCALE);

    $updtreq = "UPDATE TSV SET latInt = ?, lngInt = ? "
    . "WHERE picIdx = ?;";
    $update = $pdo->prepare($updtreq);
    $update->execute([$latInt, $lngInt, $picIdx]);
}

$tbl = $pdo->prepare("SELECT * FROM ETSV");
if ($tbl->execute() === false) {
    throw new Exception("Query failed");
}
$tblData = $tbl->fetchALL(PDO::FETCH_ASSOC);

foreach ($tblData as $tblDataItem) {
    $picIdx = $tblDataItem['picIdx'];
    $latInt = is_null($tblDataItem['lat']) ?
        null :
        (int) ((float)($tblDataItem['lat']) * LOC_SCALE);
    $lngInt = is_null($tblDataItem['lng']) ?
        null :
        (int) ((float)($tblDataItem['lng']) * LOC_SCALE);

    $updtreq = "UPDATE ETSV SET latInt = ?, lngInt = ? "
    . "WHERE picIdx = ?;";
    $update = $pdo->prepare($updtreq);
    $update->execute([$latInt, $lngInt, $picIdx]);
}