<?php
/**
 * This script will populate ETSV for the EHIKE posted
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();

require "../php/global_boot.php";

$hike    = filter_input(INPUT_POST, 'hike');
$ehikeno = filter_input(INPUT_POST, 'ehike');

// find the hikeno for $hike:
$hikenoReq = "SELECT `indxNo` FROM `HIKES` WHERE `pgTitle`=?;";
$hikeinfo = $pdo->prepare($hikenoReq);
$hikeinfo->execute([$hike]);
$hikeno = $hikeinfo->fetchAll(PDO::FETCH_COLUMN);
$tsvdataReq = "SELECT * FROM `TSV` WHERE `indxNo`=?;";
$tsvdata = $pdo->prepare($tsvdataReq);
$tsvdata->execute([$hikeno[0]]);
$tsv = $tsvdata->fetchAll(PDO::FETCH_ASSOC);
// omit waypoints
$import = [];
foreach ($tsv as $entry) {
    if (!empty($entry['thumb'])) {
        array_push($import, $entry);
    }
}
// enter pic data into ETSV for this EHIKE
foreach ($import as $newpic) {
    $newEitemReq = "INSERT INTO `ETSV` (`indxNo`,`folder`,`title`,`hpg`,`mpg`," .
        "`desc`,`lat`,`lng`,`thumb`,`alblnk`,`date`,`mid`,`imgHt`,`imgWd`,`iclr`," .
        "`org`) VALUES ($ehikeno,?,?,'N','N',?,?,?,?,?,?,?,?,?,?,?);";
    $newEpic = $pdo->prepare($newEitemReq);
    try {
        $newEpic->execute(
            [
                $newpic['folder'],
                $newpic['title'],
                $newpic['desc'],
                $newpic['lat'],
                $newpic['lng'],
                $newpic['thumb'],
                $newpic['alblnk'],
                $newpic['date'],
                $newpic['mid'],
                $newpic['imgHt'],
                $newpic['imgWd'],
                $newpic['iclr'],
                $newpic['org']
            ]
        );
    }
    catch (PDOException $pdoe) {
        echo "bad";
        exit;
    }
}
echo "ok";
