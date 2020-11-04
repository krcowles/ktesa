<?php
/**
 * When a user wishes to edit a hike already published, this script
 * will extract the data from the HIKES db and copy it into EHIKES
 * where it can be edited. When edits are complete the administrator
 * can then publish the EHIKE which updates the HIKES db with the 
 * edited data. The user is directed to the editor via editDB.php.
 * PHP Version 7.1
 * 
 * @package Editing
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
$getHike = filter_input(INPUT_GET, 'hikeNo');
$userid = $_SESSION['userid'];
/*
 * GET HIKES DATA
 */
$xfrReq = "INSERT INTO EHIKES (usrid,stat,pgTitle,locale,marker,`collection`," .
    "cgroup,cname,logistics,miles,feet,diff,fac,wow,seasons,expo,gpx,trk,lat," .
    "lng,purl1,purl2,dirs,tips,info,eThresh,dThresh,maWin)" .
    "SELECT ?,?," .
    "pgTitle,locale,marker,collection,cgroup,cname,logistics,miles,feet,diff," .
    "fac,wow,seasons,expo,gpx,trk,lat,lng,purl1,purl2,dirs,tips," .
    "info,eThresh,dThresh,maWin FROM HIKES WHERE indxNo = ?;";
$query = $pdo->prepare($xfrReq);
$query->execute([$userid, $getHike, $getHike]);
// Fetch the new hike no in EHIKES:
$indxReq = "SELECT indxNo FROM EHIKES ORDER BY indxNo DESC LIMIT 1;";
$indxq = $pdo->query($indxReq);
$indxNo = $indxq->fetch(PDO::FETCH_NUM);
$hikeNo = $indxNo[0];
/*
 * GET TSV DATA
 */
$xfrTsvReq = "INSERT INTO ETSV (indxNo,folder,title,hpg,mpg,`desc`,lat,lng," .
    "thumb,alblnk,date,mid,imgHt,imgWd,iclr,org) SELECT ?,folder,title," .
    "hpg,mpg,`desc`,lat,lng,thumb,alblnk,date,mid,imgHt,imgWd,iclr,org FROM " .
    "TSV WHERE indxNo = ?;";
$tsvq = $pdo->prepare($xfrTsvReq);
$tsvq->execute([$hikeNo, $getHike]);
/*
 * GET GPSDAT DATA
 */
$gpsDatReq = "INSERT INTO EGPSDAT (indxNo,datType,label,url,clickText) " .
    "SELECT ?,datType,label,url,clickText FROM GPSDAT WHERE " .
    "indxNo = ?;";
$gpsq = $pdo->prepare($gpsDatReq);
$gpsq->execute([$hikeNo, $getHike]);
/*
 * GET REFS DATA
 */
$refDatReq = "INSERT INTO EREFS (indxNo,rtype,rit1,rit2) SELECT " .
    "?,rtype,rit1,rit2 FROM REFS WHERE indxNo = ?;";
$refq = $pdo->prepare($refDatReq);
$refq->execute([$hikeNo, $getHike]);
// Back to the editor
$redirect = "editDB.php?tab=1&hikeNo={$hikeNo}";
header("Location: {$redirect}");
