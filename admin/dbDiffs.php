<?php
/**
 * This module checks a database for resident changes that have occurred
 * since the last time checksums were generated.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
// Get the current checksums
$getSumsReq = "SELECT `name`,`chksum` FROM `Checksums`;";
$getSums = $pdo->query($getSumsReq)->fetchAll(PDO::FETCH_KEY_PAIR);
$chkTables = array_keys($getSums);   // table names
$chkValues = array_values($getSums); // corresponding checksums
// Get a list of all tables in the db
$allTablesReq = "SHOW TABLES;";
$allTables = $pdo->query($allTablesReq)->fetchAll(PDO::FETCH_COLUMN);

/**
 * Look for various scenarios, identified by the arrays below. These
 * will be available to the caller
 */
$obs     = [];  // the table name in `Checksums` is no longer active
$missing = [];  // the table name in the db has no `Checksums` entry
$nomatch = [];  // this table has a changed value for checksum
$alerts['newuser'] = 'no'; // a new user has been added to USERS
$alerts['ehikes']  = 'no'; // there are EHIKES present in resident db
$alerts['usrehk']  = 'no'; // a non-admin user has an EHIKE present
foreach ($chkTables as $ctbl) {
    if (!in_array($ctbl, $allTables)) {
        array_push($obs, $ctbl);
    }
}
foreach ($allTables as $tbl) {
    // NOTE: For a reload, the only table not reloaded is VISITORS
    if ($tbl !== 'Checksums' && $tbl !== 'VISITORS') {
        if (!in_array($tbl, $chkTables)) {
            array_push($missing, $tbl);
        } else {
            $tbl_loc = array_search($tbl, $chkTables);
            $sumReq = "CHECKSUM TABLE {$tbl};";
            $tblsum = $pdo->query($sumReq)->fetch(PDO::FETCH_NUM);
            if ($tblsum[1] !== $chkValues[$tbl_loc]) {
                array_push($nomatch, $tbl);
                if ($tbl === 'EHIKES') {
                    $alerts['ehikes'] = 'yes';
                    // identify any non-admin users
                    $whoReq = "SELECT `usrid` FROM `EHIKES`;";
                    $userids = $pdo->query($whoReq)->fetchAll(PDO::FETCH_COLUMN);
                    $allusers = count($userids);
                    $tally = array_count_values($userids);
                    $admin1 = isset($tally['1']) ? $tally['1'] : 0;
                    $admin2 = isset($tally['2']) ? $tally['2'] : 0;
                    $admins = $admin1 + $admin2;
                    if ($allusers - $admins > 0) {
                        $alerts['usrehk'] = 'yes';
                    }   
                }
                if ($tbl === 'USERS') {
                    $alerts['newuser'] = 'yes';                }
            }   
        }      
    }
}
