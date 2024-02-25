<?php
/**
 * Create and populate WAYPTS table with data from gpx files and TSV table
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to data
 */

/**
 * --------------------------- NOTE ---------------------------
 * This module contains three scripts: all currently commented out.
 * 
 * The first is to be run when in the 'noGpx' branch, and creates
 * the WAYPTS and EWPTS tables and loads the WAYPTS table with 
 * current waypoints residing in the (old) TSV table. After this 
 * has been done, the waypoints will still reside in the old table,
 * and can be manually deleted when appropriate.
 * 
 * The second script is designed to run in the old master branch
 * (prior to the creation of 'noGpx') for the purpose of extracting,
 * then saving, the old gpx waypoint data as a JSON file (wpt_data.json).
 * After this had been successfully created in the old master branch,
 * the data is copied over to the 'noGpx' branch and placed in the 
 * 'tools' directory for extraction
 * 
 * The third script takes the wpt_data.json file, reads it and 
 * converts the data so that it can be used to write to the WAYPTS
 * table, with the 'type' field equal to 'gpx'
 */
require "../php/global_boot.php";
/**
 * The first script (to be run in the 'noGpx' branch)
 */
/*
$wptTableReq = <<< TBL
CREATE TABLE `WAYPTS` (
    `wptId` smallint(6) NOT NULL AUTO_INCREMENT,
    `indxNo` smallint(6) DEFAULT NULL,
    `type` varchar(3) DEFAULT NULL,
    `name` varchar(60) DEFAULT NULL,
    `lat` int(10) DEFAULT NULL,
    `lng` int(10) DEFAULT NULL,
    `sym` varchar(32) DEFAULT NULL,
    PRIMARY KEY (`wptId`)
  ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
TBL;

$ewptTableReq = <<< ETBL
CREATE TABLE `EWAYPTS` (
    `wptId` smallint(6) NOT NULL AUTO_INCREMENT,
    `indxNo` smallint(6) DEFAULT NULL,
    `type` varchar(3) DEFAULT NULL,
    `name` varchar(60) DEFAULT NULL,
    `lat` int(10) DEFAULT NULL,
    `lng` int(10) DEFAULT NULL,
    `sym` varchar(32) DEFAULT NULL,
    PRIMARY KEY (`wptId`),
    KEY `EWAYPTS_Constraint` (`indxNo`),
    CONSTRAINT `EWAYPTS_Constraint` FOREIGN KEY (`indxNo`) REFERENCES `EHIKES`(`indxNo`) ON DELETE CASCADE ON UPDATE CASCADE
  ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
ETBL;

$pdo->query("DROP TABLE IF EXISTS  `WAYPTS`;");
$pdo->query("DROP TABLE IF EXISTS  `EWAYPTS`;");
$pdo->query($wptTableReq);
$pdo->query($ewptTableReq);
echo "TABLES CREATED...<br />";

// copy TSV waypoints to new db
$TSVReq = "SELECT `indxNo`,`title`,`lat`,`lng`,`iclr` FROM `TSV` WHERE " .
    "`mid` IS NULL;";
$tsv = $pdo->query($TSVReq)->fetchAll(PDO::FETCH_ASSOC);
foreach ($tsv as $wpt) {
    $WayptsReq
        = "INSERT INTO `WAYPTS` (`indxNo`,`type`,`name`,`lat`,`lng`,`sym`) " .
            "VALUES (?,'db',?,?,?,?);";
    $db_wpt = $pdo->prepare($WayptsReq);
    $db_wpt->execute(
        [$wpt['indxNo'], $wpt['title'], $wpt['lat'], $wpt['lng'], $wpt['iclr']]
    );
}
echo "DB Populated...<br />";
*/

/**
 * The second script (to be executed in the old master, previous to 'noGpx')
 */
/*
$getGpxFilesReq = "SELECT `indxNo`,`gpx` FROM `HIKES`;";
$org_files = $pdo->query($getGpxFilesReq)->fetchAll(PDO::FETCH_ASSOC);
$wpt_array = [];
foreach ($org_files as $gpx) {
    if (!empty($gpx['gpx'])) {
        $allgpx = explode(",", $gpx['gpx']);
        foreach ($allgpx as $file) {
            $gpxdata = simplexml_load_file("../gpx/" . $file);
            if ($gpxdata === false) {
                echo "Not loaded: " . $file . "<br/>";
            } else {
                $file_wpts = $gpxdata->wpt->count();
                if ($file_wpts > 0) {
                    for ($k=0; $k<$file_wpts; $k++) {
                        $tbl_data = [$gpx['indxNo']];
                        $gpx_lat = floatval($gpxdata->wpt[$k]['lat']->__toSTring());
                        $lat = intval($gpx_lat * LOC_SCALE);
                        $gpx_lng = floatval($gpxdata->wpt[$k]['lon']->__toString());
                        $lng = intval($gpx_lng * LOC_SCALE);
                        array_push($tbl_data, $lat);
                        array_push($tbl_data, $lng);
                        array_push(
                            $tbl_data, $gpxdata->wpt[$k]->name->__toString()
                        );
                        array_push(
                            $tbl_data, $gpxdata->wpt[$k]->sym->__toString()
                        );  
                        array_push($wpt_array, $tbl_data);  
                    }
                }
            }
        }
    }
}
$gpx_wpts = json_encode($wpt_array, JSON_FORCE_OBJECT, 5);
file_put_contents("wpt_data.json", $gpx_wpts);
echo "Updated<br/>";
*/
/**
 * Third script (to be run in the noGpx branch)
 */
/*
$wpt_object = file_get_contents("wpt_data.json");
$wpt_arr = json_decode($wpt_object, true);
foreach ($wpt_arr as $wpt) {
    $addWptReq
        = "INSERT INTO `WAYPTS` (`indxNo`,`type`,`name`,`lat`,`lng`,`sym`)" .
            " VALUES (?,'gpx',?,?,?,?);";
    $addWpt = $pdo->prepare($addWptReq);
    $addWpt->execute([$wpt[0], $wpt[3], $wpt[1], $wpt[2], $wpt[4]]);
}
echo "WAYPTS table populated with gpx waypoints<br/>";
*/
