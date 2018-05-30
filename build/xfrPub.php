<?php
/**
 * When a user wishes to edit a hike already published, this script
 * will extract the data from the HIKES db and copy it into EHIKES
 * where it can be edited. When edits are complete the administrator
 * can then publish the EHIKE which updates the HIKES db with the 
 * edited data. The user is directed to the editor via editDB.php.
 * PHP Version 7.0
 * 
 * @package Editing
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require_once "../mysql/dbFunctions.php";
$link = connectToDb(__FILE__, __LINE__);
$getHike = filter_input(INPUT_GET, 'hno');
$uid = filter_input(INPUT_GET, 'usr');
$usr = mysqli_real_escape_string($link, $uid);
/*
 * GET HIKES DATA
 */
$xfrReq = "INSERT INTO EHIKES (usrid,stat,pgTitle,locale,marker,`collection`," .
    "cgroup,cname,logistics,miles,feet,diff,fac,wow,seasons,expo,gpx,trk,lat," .
    "lng,aoimg1,aoimg2,purl1,purl2,dirs,tips,info) SELECT '{$usr}','{$getHike}'," .
    "pgTitle,locale,marker,collection,cgroup,cname,logistics,miles,feet,diff," .
    "fac,wow,seasons,expo,gpx,trk,lat,lng,aoimg1,aoimg2,purl1,purl2,dirs,tips," .
    "info FROM HIKES WHERE indxNo = {$getHike};";
$xfr = mysqli_query($link, $xfrReq) or die(
    __FILE__ . " Line " . __LINE__ . ": Failed to move hike data from HIKES "
    . "to EHIKES for Hike No {$gethike}: " . mysqli_error($link)
);
// Fetch the new hike no in EHIKES:
$indxReq = "SELECT indxNo FROM EHIKES ORDER BY indxNo DESC LIMIT 1;";
$indxq = mysqli_query($link, $indxReq) or die(
    __FILE__ . " Line " . __LINE__ . ": Did not retrieve new EHIKES "
    . "indx no: " . mysqli_error($link)
);
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
$xfrTsv = mysqli_query($link, $xfrTsvReq) or die(
    __FILE__ . " Line " . __LINE__ . ": Failed to move TSV data into ETSV "
    . "for hike {$getHike}: " . mysqli_error($link)
);
/*
 * GET GPSDAT DATA
 */
$gpsDatReq = "INSERT INTO EGPSDAT (indxNo,datType,label,url,clickText) " .
    "SELECT '{$hikeNo}',datType,label,url,clickText FROM GPSDAT WHERE " .
    "indxNo = {$getHike};";
$gpsDat = mysqli_query($link, $gpsDatReq) or die(
    __FILE__ . " Line " . __LINE__ . ": Failed to extract GPSDAT for "
    . "hike {$getHike};" . mysqli_error($link)
);
/*
 * GET REFS DATA
 */
$refDatReq = "INSERT INTO EREFS (indxNo,rtype,rit1,rit2) SELECT " .
    "'{$hikeNo}',rtype,rit1,rit2 FROM REFS WHERE indxNo = {$getHike};";
$refDat = mysqli_query($link, $refDatReq) or die(
    __FILE__ . " Line " . __LINE__ . ": Failed to extract REFS data for "
    . "hike {$getHike}: " . mysqli_error($link)
);
$redirect = "editDB.php?hno={$hikeNo}&usr={$uid}&tab=1";
header("Location: {$redirect}");
