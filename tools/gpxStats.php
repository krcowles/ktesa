<?php
/**
 * This script is also used as a one-time tool to update the META table
 * with the 'length' and 'min2max' for each track, after the META and GPX
 * tables have been loaded via 'loadAllGpx.php'. 
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowle29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

$getMaxFileno = "SELECT `fileno` FROM `GPX` ORDER BY `fileno` DESC LIMIT 1;";
$maxfileno = $gdb->query($getMaxFileno)->fetch(PDO::FETCH_NUM);
$filecount = $maxfileno[0];

for ($j=1; $j<=$filecount; $j++) {
    $getTracks = "SELECT `trackno` FROM `GPX` WHERE `fileno`={$j} " .
        "ORDER BY `trackno` DESC LIMIT 1;";
    $noOfTracks = $gdb->query($getTracks)->fetch(PDO::FETCH_NUM);
    $trkcount = $noOfTracks[0];
    for ($k=1; $k<=$trkcount; $k++) {
        $getData = "SELECT `lat`,`lon`,`ele` FROM `GPX` WHERE `fileno`=? " .
            "AND `trackno`=?;";
        $gpsdata = $gdb->prepare($getData);
        $gpsdata->execute([$j, $k]);
        $gps = $gpsdata->fetchAll(PDO::FETCH_ASSOC);
        // in case of a missing fileno
        if (count($gps) !== false) {
            $length = (float) 0;
            $maxele = (float) 0;
            $minele = (float) 100000;
            $asc = 0;
            $dsc = 0;
            for ($i=0; $i<count($gps)-1; $i++) {
                $calcs = distance(
                    floatval($gps[$i]['lat']), floatval($gps[$i]['lon']), 
                    floatval($gps[$i+1]['lat']), floatval($gps[$i+1]['lon'])
                );
                $length += $calcs[0];
                $maxele = floatval($gps[$i]['ele']) > $maxele ? 
                    floatval($gps[$i]['ele']) : $maxele;
                $minele = floatval($gps[$i]['ele']) < $minele ? 
                    floatval($gps[$i]['ele']) : $minele;
                $delta = round($gps[$i+1]['ele'], 2) - round($gps[$i]['ele'], 2);
                if ($delta < 0) {
                    $dsc -= $delta;
                } else {
                    $asc += $delta;
                }
            }
            // convert from meters to feet
            $min2max   = ($maxele - $minele) * 3.2808;
            $asc       = round($asc*3.2808);
            $dsc       = round($dsc*3.2808);
            $length    = ($length * 3.2808)/5280;
            $dbmin2max = round($min2max);
            $dblength  = round($length, 2);

            $add2dbReq = "UPDATE `META` SET `length`=?,`min2max`=?,`asc`=?," .
                "`dsc`=? WHERE `fileno`=? AND `trkno`=?;";
            $add2db = $gdb->prepare($add2dbReq);
            $add2db->execute([$dblength, $dbmin2max, $asc, $dsc, $j, $k]);
        }
    }
}
echo "Statistics updated in META Table";
