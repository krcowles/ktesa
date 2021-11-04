<?php
/**
 * This script will modify the GPSDAT and EGPSDAT table structures
 * to comply with the new GPX database approach
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowle29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
require "toolFunctions.php";

$mod1Req = <<<GMOD
ALTER TABLE `GPSDAT`
DROP COLUMN `datType`,
DROP COLUMN `url`;
GMOD;
$mod2Req = <<<EMOD
ALTER TABLE `EGPSDAT`
DROP COLUMN `datType`,
DROP COLUMN `url`;
EMOD;

$mod3Req = <<<GADD
ALTER TABLE `GPSDAT`
ADD COLUMN `fileno` INT NULL AFTER `indxNo`;
GADD;

$mod4Req = <<<EADD
ALTER TABLE `EGPSDAT`
ADD COLUMN `fileno` INT NULL AFTER `indxNo`;
EADD;

$pdo->query($mod1Req);
$pdo->query($mod2Req);
$pdo->query($mod3Req);
$pdo->query($mod4Req);

$gpsDat = file("../data/nmhikesc_main.sql");
$indxNos = [];
$dbgpx   = [];
$datIds  = [];
$nonGPX  = [];
$nonIds  = [];
$start   = 0;
$stop    = 0;
for ($i=100; $i<5000; $i++) {
    if (strpos($gpsDat[$i], "INSERT INTO GPSDAT VALUES") !== false) {
        $start = $i+1;
    }
    if (strpos($gpsDat[$i], "CREATE TABLE `HIKES`") !== false) {
        $stop = $i-2;
        break;
    }
}
for ($x=$start; $x<$stop; $x++) {
    $sub = substr($gpsDat[$x], 1);
    $vals = substr_replace($sub, "", -3);
    $varray = explode(",", $vals);
    if (!empty($varray[0])) {
        $el = substr($varray[3], 1); // trim leading single quote
        $el = trim(substr_replace($el, "", -1)); // trim trailing single quote
        if ($el === "GPX:" || $el === 'GPX') {
            $hike = substr($varray[1], 1);
            $hikeno = substr_replace($hike, "", -1);
            array_push($indxNos, $hikeno);
            $gfilepath = $varray[4];
            $gfile = substr($gfilepath, 8);
            $gfilename = substr_replace($gfile, "", -1);
            array_push($dbgpx, $gfilename);
            $idTxt = $varray[0];
            $idTxt = substr($idTxt, 1);
            $idStr = substr_replace($idTxt, "", -1);
            array_push($datIds, $idStr);
        } else {
            $item = $varray[3];
            $item = substr($item, 1);
            $other = trim(substr_replace($item, "", -1));
            // in the 'old' db, label is separate from location
            $loc = $varray[4];
            $loc = substr($loc, 1); // trim leading char
            $locStr = substr_replace($loc, "", -1); // trim ending char
            array_push($nonGPX, $locStr);
            $nonid = $varray[0];
            $nonid = substr($nonid, 1);
            $nonStr = substr_replace($nonid, "", -1);
            array_push($nonIds, $nonStr);
        }
    }
}

for ($i=0; $i<count($dbgpx); $i++) {
    $hikeIndxNo = $indxNos[$i];
    uploadGPSDATA($hikeIndxNo, $dbgpx[$i], $gdb);
}
for ($j=0; $j<count($indxNos); $j++) {
    $getFileno = "SELECT `fileno` FROM `META` WHERE `fname`=?;";
    $fno = $gdb->prepare($getFileno);
    $fno->execute([$dbgpx[$j]]);
    $fileno = $fno->fetchAll(PDO::FETCH_COLUMN);
    $updateGPSDAT = "UPDATE `GPSDAT` SET `fileno`=? WHERE `indxNo`=? AND `datId`=?;";
    $updte = $pdo->prepare($updateGPSDAT);
    $updte->execute([$fileno[0], $indxNos[$j], $datIds[$j]]);
}
for ($k=0; $k<count($nonGPX); $k++) {
    $nons = "UPDATE `GPSDAT` SET `label`=? WHERE `datId`=?;";
    $updteNon = $pdo->prepare($nons);
    $updteNon->execute([$nonGPX[$k], $nonIds[$k]]);
}
echo "GPSDAT Updated";
