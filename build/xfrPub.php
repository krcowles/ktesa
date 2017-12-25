<?php
session_start();
$_SESSION['activeTab'] = 1;
require_once "../mysql/dbFunctions.php";
$link = connectToDb(__FILE__, __LINE__);
$getHike = filter_input(INPUT_GET, 'hno');
$uid = filter_input(INPUT_GET, 'usr');
$usr = mysqli_real_escape_string($link, $uid);
$status = 'pub' . $getHike;
/*
 * GET HIKES DATA
 */
$xfrReq = "INSERT INTO EHIKES (usrid,stat,pgTitle,locale,marker,collection," .
    "cgroup,cname,logistics,miles,feet,diff,fac,wow,seasons,expo,gpx,trk,lat," .
    "lng,aoimg1,aoimg2,purl1,purl2,dirs,tips,info) SELECT '{$usr}','{$status}'," .
    "pgTitle,locale,marker,collection,cgroup,cname,logistics,miles,feet,diff," .
    "fac,wow,seasons,expo,gpx,trk,lat,lng,aoimg1,aoimg2,purl1,purl2,dirs,tips," .
    "info FROM HIKES WHERE indxNo = {$getHike};";
$xfr = mysqli_query($link, $xfrReq);
if (!$xfr) {
    die("xfrPub.php: Failed to move hike data from HIKES to EHIKES for " .
        "Hike No {$gethike}: " . mysqli_error($link));
}
mysqli_free_result($xfr);
# Fetch the new hike no in EHIKES:
$indxReq = "SELECT indxNo FROM EHIKES ORDER BY indxNo DESC LIMIT 1;";
$indxq = mysqli_query($link, $indxReq);
if (!$indxq) {
    die("xfrPub.php: Did not retrieve new EHIKES indx no: " .
        mysqli_error($link));
}
$indxNo = mysqli_fetch_row($indxq);
$hikeNo = $indxNo[0];
mysqli_free_result($indxq);
/*
 * GET TSV DATA
 */
$xfrTsvReq = "INSERT INTO ETSV (indxNo,folder,title,hpg,mpg,`desc`,lat,lng," .
    "thumb,alblnk,date,mid,imgHt,imgWd,iclr,org) SELECT '{$hikeNo}',folder,title," .
    "hpg,mpg,`desc`,lat,lng,thumb,alblnk,date,mid,imgHt,imgWd,iclr,org FROM " .
    "TSV WHERE indxNo = {$getHike};";
$xfrTsv = mysqli_query($link, $xfrTsvReq);
if (!$xfrTsv) {
    die("xfrPub.php: Failed to move TSV data into ETSV for hike {$getHike}: " .
        mysqli_error($link));
}
mysqli_free_result($xfrTsv);
/*
 * GET GPSDAT DATA
 */
$gpsDatReq = "INSERT INTO EGPSDAT (indxNo,datType,label,url,clickText) " .
    "SELECT '{$hikeNo}',datType,label,url,clickText FROM GPSDAT WHERE " .
    "indxNo = {$getHike};";
$gpsDat = mysqli_query($link, $gpsDatReq);
if (!$gpsDat) {
    die("xfrPub: Failed to extract GPSDAT for hike {$getHike};");
}
mysqli_free_result($gpsDat);
/*
 * GET REFS DATA
 */
$refDatReq = "INSERT INTO EREFS (indxNo,rtype,rit1,rit2) SELECT " .
    "'{$hikeNo}',rtype,rit1,rit2 FROM REFS WHERE indxNo = {$getHike};";
$refDat = mysqli_query($link, $refDatReq);
if (!$refDat) {
    die("xfrPUb.php: Failed to extract REFS data for hke {$getHike}: " .
        mysqli_error($link));
}
mysqli_free_result($refDat);
$redirect = "editDB.php?hno={$hikeNo}&usr={$uid}";
header("Location: {$redirect}");
