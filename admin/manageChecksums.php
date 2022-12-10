<?php
/**
 * This script will perform one of two actions:
 * 1. Update the Checksums Table with new checksum values. This would ostensibly
 *    occur after looking for and verifying any db changes.
 * 2. Examine the Checksums Table and compare its checksums with current database
 *    checksums. Present differences, missing tables, etc.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";

$action  = filter_input(INPUT_GET, 'action');

if ($action === 'gen') {
    $dropold = "DROP TABLE IF EXISTS `Checksums`";
    $pdo->query($dropold);
    $createChkSumsReq = "CREATE TABLE `Checksums` (
        `indx` smallint(6) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) DEFAULT NULL,
        `chksum` bigint DEFAULT NULL,
        `creation` datetime DEFAULT NULL,
        PRIMARY KEY (`indx`)
    );";
    $createSums = $pdo->query($createChkSumsReq);
    
    // Note table creation time:
    date_default_timezone_set('America/Denver');
    $ctime = date('Y-m-d H:i:s');
    // Fill in table
    $allTablesReq = "SHOW TABLES;";
    $allTables = $pdo->query($allTablesReq)->fetchAll(PDO::FETCH_COLUMN);
    foreach ($allTables as $tbl) {
        // NOTE: For a reload, the only table not updated is VISITORS
        if ($tbl !== 'Checksums' && $tbl !== 'VISITORS') {
            $sumReq = "CHECKSUM TABLE {$tbl};";
            $tblsum = $pdo->query($sumReq)->fetch(PDO::FETCH_NUM);
            $addSumReq = "INSERT INTO `Checksums` (`name`,`chksum`,`creation`) " .
                "VALUES (?, ?, ?);";
            $addSum = $pdo->prepare($addSumReq);
            $addSum->execute([$tbl, $tblsum[1], $ctime]);
        }
    }
}
if ($action === 'cmp') { // Perform a comparison of current with old
    include "dbDiffs.php";
    $returnArr = [];
    $returnArr['obs']     = count($obs) > 0 ? $obs : ['none'];
    $returnArr['missing'] = count($missing) > 0 ? $missing : ['none'];
    $returnArr['nomatch'] = count($nomatch) > 0 ? $nomatch : ['none'];
    $returnArr['alerts']  = $alerts;
    $returnjs = json_encode($returnArr);
    echo $returnjs;
}
