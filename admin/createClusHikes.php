<?php
/**
 * Create the CLUSHIKES table, whose entries are hike numbers (`indxNo`) and
 * which point to a cluster in CLUSTERS. A hike number can have 1 to many entries.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
$chtbl  = <<<CLUS
CREATE TABLE `CLUSHIKES`(
    `tblid` smallint(6) NOT NULL AUTO_INCREMENT,
    `indxNo` smallint(6) NOT NULL,
    `cluster` smallint(6) NOT NULL,
    PRIMARY KEY (`tblid`)
);
CLUS;
$makeTable = $pdo->query($chtbl);

echo "DONE";
