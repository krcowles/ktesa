<?php
/**
 * The CLUSTERS table will hold information on Visitor Centers and
 * on Clusters; These will then be referenced by the CLUSHIKES table
 * entries.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
$clustbl  = <<<CLUS
CREATE TABLE `CLUSTERS`(
    `clusid` smallint(6) NOT NULL AUTO_INCREMENT,
    `group` varchar(100) NOT NULL,
    `lat` int(10) NULL,
    `lng` int(10) NULL,
    `page` smallint(6) NULL,
    PRIMARY KEY (`clusid`)
);
CLUS;
$makeTable = $pdo->query($clustbl);

$chtbl  = <<<CH
CREATE TABLE `CLUSHIKES`(
    `tblid` smallint(6) NOT NULL AUTO_INCREMENT,
    `indxNo` smallint(6) NOT NULL,
    `cluster` smallint(6) NOT NULL,
    PRIMARY KEY (`tblid`)
);
CH;
$makeTable = $pdo->query($chtbl);
header("Location: admintools.php");
