<?php
/**
 * This file creates the $picdat array from which photos will be
 * presented on the page for display. The array is then time-sorted
 * 
 * @package Edit
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 * @link    ../docs/
 */
$picdat = [];
for ($m=0; $m<$albOcnt; $m++) {
    $totPhotDat = array(
        "folder" => $folder[$m],
        "pic" => $titles[$m],
        "desc" => $descriptions[$m],
        "alb" => $alinks[$m],
        "org" => $o[$m],
        "thumb" => $t[$m],
        "nsize" => $n[$m],
        "pHt" => $imgHt[$m],
        "pWd" => $imgWd[$m],
        "taken" => $timeStamp[$m],
        "lat" => $lats[$m],
        "lng" => $lngs[$m],
        "gpsdate" => $gpds[$m],
        "gpstime" => $gpts[$m]
    );
    array_push($picdat, $totPhotDat);
}
usort(
    $picdat, function ($a, $b) {
        $stampA = $a['taken'];
        $stampB = $b['taken'];
        $timeApos = strpos($stampA, " ");
        $timeBpos = strpos($stampB, " ");
        $dateA = substr($stampA, 0, $timeApos);
        $dateB = substr($stampB, 0, $timeBpos);
        // Breakdown dates first: all should be yyyy:mm:dd
        $yrA = substr($dateA, 0, 4);
        $yrB = substr($dateB, 0, 4);
        $moA = substr($dateA, 5, 2);
        $moB = substr($dateB, 5, 2);
        $dayA = substr($dateA, 8, 2);
        $dayB = substr($dateB, 8, 2);
        if ($yrA == $yrB) {
            if ($moA == $moB) {
                if ($dayA == $dayB) {
                    // same date, compare times: hh:mm:ss
                    $timeA = substr($stampA, $timeApos+1, 8);
                    $timeB = substr($stampB, $timeBpos+1, 8);
                    $hrsA = substr($timeA, 0, 2);
                    $hrsB = substr($timeB, 0, 2);
                    $minsA = substr($timeA, 3, 2);
                    $minsB = substr($timeB, 3, 2);
                    $secsA = substr($timeA, 6, 2);
                    $secsB = substr($timeB, 6, 2);
                    if ($hrsA == $hrsB) {
                        if ($minsA == $minsB) {
                            if ($secsA == $secsB) {
                                return 0;
                            }
                            return ($secsA < $secsB) ? -1 : 1;
                        }
                        return ($minsA < $minsB) ? -1 : 1;
                    }
                    return ($hrsA < $hrsB) ? -1 : 1;
                }
                return ($dayA < $dayB) ? -1 : 1;
            }
            return ($moA < $moB) ? -1 : 1;
        }
        return ($yrA < $yrB) ? -1 : 1;
    }
);
