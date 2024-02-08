<?php
/**
 * Create a waypoints table (remove them from the TSV tables)
 */
require "../php/global_boot.php";

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
