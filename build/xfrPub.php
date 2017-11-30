<?php
require_once '../mysql/setenv.php';
$getHike = filter_input(INPUT_GET,'hno');
$uid = filter_input(INPUT_GET,'usr');
$status = 'pub' . $getHike;
/*
 * GET HIKES DATA
 */
$hikeDatReq = "SELECT * FROM HIKES WHERE indxNo = {$getHike};";
$hikeDat = mysqli_query($link,$hikeDatReq);
if (!$hikeDat) {
    die("xfrPub.php: Failed to extract HIKES data for hike {$getHike}: " .
        mysqli_error($link));
}
$hdat = mysqli_fetch_assoc($hikeDat);
$pt = mysqli_real_escape_string($link,$hdat['pgTitle']);
$ui = mysqli_real_escape_string($link,$hdat['usrid']);
$lo = mysqli_real_escape_string($link,$hdat['locale']);
$mr = mysqli_real_escape_string($link,$hdat['marker']);
$co = mysqli_real_escape_string($link,$hdat['collection']);
$cg = mysqli_real_escape_string($link,$hdat['cgroup']);
$cn = mysqli_real_escape_string($link,$hdat['cname']);
$lg = mysqli_real_escape_string($link,$hdat['logistics']);
$mi = mysqli_real_escape_string($link,$hdat['miles']);
$ft = mysqli_real_escape_string($link,$hdat['feet']);
$di = mysqli_real_escape_string($link,$hdat['diff']);
$fa = mysqli_real_escape_string($link,$hdat['fac']);
$wo = mysqli_real_escape_string($link,$hdat['wow']);
$se = mysqli_real_escape_string($link,$hdat['seasons']);
$xp = mysqli_real_escape_string($link,$hdat['expo']);
$gx = mysqli_real_escape_string($link,$hdat['gpx']);
$tr = mysqli_real_escape_string($link,$hdat['trk']);
$lt = mysqli_real_escape_string($link,$hdat['lat']);
$ln = mysqli_real_escape_string($link,$hdat['lng']);
$a1 = mysqli_real_escape_string($link,$hdat['aoimg1']);
$a2 = mysqli_real_escape_string($link,$hdat['aoimg2']);
$p1 = mysqli_real_escape_string($link,$hdat['purl1']);
$p2 = mysqli_real_escape_string($link,$hdat['purl2']);
$dr = mysqli_real_escape_string($link,$hdat['dirs']);
$tp = mysqli_real_escape_string($link,$hdat['tips']);
$io = mysqli_real_escape_string($link,$hdat['info']);
mysqli_free_result($hikeDat);
# Insert all HIKES data into EHIKES:
$ehikeDatReq = "INSERT INTO EHIKES (pgTitle,usrid,stat,locale,marker,collection," .
    "cgroup,cname,logistics,miles,feet,diff,fac,wow,seasons,expo,gpx,trk,lat,lng," .
    "aoimg1,aoimg2,purl1,purl2,dirs,tips,info) VALUES('{$pt}','{$ui}','{$status}'," .
    "'{$lo}','{$mr}','{$co}','{$cg}','{$cn}','{$lg}','{$mi}','{$ft}','{$di}'," .
    "'{$fa}','{$wo}','{$se}','{$xp}','{$gx}','{$tr}','{$lt}','{$ln}','{$a1}'," .
    "'{$a2}','{$p1}','{$p2}','{$dr}','{$tp}','{$io}');";
$ehikeDat = mysqli_query($link,$ehikeDatReq);
if (!$ehikeDat) {
    die("xfrPub.php: Failed to insert data into EHIKES: " . mysqli_error($link));
}
mysqli_free_result($ehikeDat);
$indxReq = "SELECT indxNo FROM EHIKES ORDER BY indxNo DESC LIMIT 1;";
$indxq = mysqli_query($link,$indxReq);
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
$tsvDatReq = "SELECT * FROM TSV WHERE indxNo = {$getHike};";
$tsvDat = mysqli_query($link,$tsvDatReq);
if (!$tsvDat) {
    die("xfrPub.php: Failed to extract TSV data for hike {$getHike}: " .
        mysqli_error($link));
}
while ( ($pic = mysqli_fetch_assoc($tsvDat)) ) {
    $ix = $hikeNo;
    $fl = mysqli_real_escape_string($link,$pic['folder']);
    $ti = mysqli_real_escape_string($link,$pic['title']);
    $hp = $pic['hpg'];
    $mp = $pic['mpg'];
    $ds = mysqli_real_escape_string($link,$pic['desc']);
    $lt = mysqli_real_escape_string($link,$pic['lat']);
    $ln = mysqli_real_escape_string($link,$pic['lng']);
    $th = mysqli_real_escape_string($link,$pic['thumb']);
    $al = mysqli_real_escape_string($link,$pic['alblnk']);
    $dt = mysqli_real_escape_string($link,$pic['date']);
    $md = mysqli_real_escape_string($link,$pic['mid']);
    $ih = mysqli_real_escape_string($link,$pic['imgHt']);
    $iw = mysqli_real_escape_string($link,$pic['imgWd']);
    $ic = mysqli_real_escape_string($link,$pic['iclr']);
    $og = mysqli_real_escape_string($link,$pic['org']);
    $etsvReq = "INSERT INTO ETSV (`indxNo`,folder,title,hpg,mpg,`desc`,lat,lng," .
        "thumb,alblnk,date,mid,imgHt,imgWd,iclr,org) VALUES ('{$ix}','{$fl}'," .
        "'{$ti}','{$hp}','{$mp}','{$ds}','{$lt}','{$ln}','{$th}','{$al}'," .
        "'{$dt}','{$md}','{$ih}','{$iw}','{$ic}','{$og}');";
    $etsv = mysqli_query($link,$etsvReq);
    if (!$etsv) {
        die("xfrPub.php: Failed to insert data into ETSV for hike {$getHike}: " .
            mysqli_error($link));
    }
}
mysqli_free_result($etsv);
mysqli_free_result($tsvDat);
/*
 * GET GPSDAT DATA
 */
$gpsDatReq = "SELECT * FROM GPSDAT WHERE indxNo = {$getHike};";
$gpsDat = mysqli_query($link,$gpsDatReq);
if (!$gpsDat) {
    die("xfrPub: Failed to extract GPSDAT for hike {$getHike};");
}
while( ($gps = mysqli_fetch_assoc($gpsDat)) ) {
    $ix = $hikeNo;
    $ty = $gps['datType'];
    $lb = mysqli_real_escape_string($link,$gps['label']);
    $ur = mysqli_real_escape_string($link,$gps['url']);
    $ct = mysqli_real_escape_string($link,$gps['clickText']);
    $egpsReq = "INSERT INtO EGPSDAT (indxNo,datType,label,url,clickText) VALUES (" .
        "'{$ix}','{$ty}','{$lb}','{$ur}','{$ct}');";
    $egps = mysqli_query($link,$egpsReq);
    if (!$egps) {
        die("xfrPub.php: Failed to insert into EGPSDAT for hike {$getHike}: " .
            mysqli_error($link));
    }
}
mysqli_free_result($egps);
mysqli_free_result($gpsDat);
/*
 * GET REFS DATA
 */
$refDatReq = "SELECT * FROM REFS WHERE indxNo = {$getHike};";
$refDat = mysqli_query($link,$refDatReq);
if (!$refDat) {
    die("xfrPUb.php: Failed to extract REFS data for hke {$getHike}: " .
        mysqli_error($link));
}
while ( ($refs = mysqli_fetch_assoc($refDat)) ) {
    $ix = $hikeNo;
    $rt = mysqli_real_escape_string($link,$refs['rtype']);
    $r1 = mysqli_real_escape_string($link,$refs['rit1']);
    $r2 = mysqli_real_escape_string($link,$refs['rit2']);
    $erefsReq = "INSERT INTO EREFS (indxNo,rtype,rit1,rit2) VALUES " .
        "('{$ix}','{$rt}','{$r1}','{$r2}');";
    $erefs = mysqli_query($link,$erefsReq);
    if (!$erefs) {
        die("xfrPUb.php: Failed to insert EREFS data for hike {$getHike}: " .
            mysqli_error($link));
    }
}
mysqli_free_result($erefs);
mysqli_free_result($refDat);
$redirect = "editDB.php?hno={$hikeNo}&usr={$uid}";
header("Location: {$redirect}");
?>