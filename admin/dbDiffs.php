<?php
/**
 * This module checks a database for changes that have occurred since the
 * last reload. ]
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
// Note: all `Checksums` creation dates are identical, so just check first entry:
$getDateReq = "SELECT `creation` FROM `Checksums` WHERE `indx`='1';";
$getDate = $pdo->query($getDateReq)->fetch(PDO::FETCH_ASSOC);
$lastchk = $getDate['creation'];
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
$alerts['newuser'] = 'no';
$alerts['ehikes']  = 'no';
foreach ($chkTables as $ctbl) {
    if (!in_array($ctbl, $allTables)) {
        array_push($obs, $ctbl);
    }
}
foreach ($allTables as $tbl) {
    // NOTE: For a reload, the only table not updated is VISITORS
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
                    // check to see if this is a non-admin user
                    $whoReq = "SELECT `usrid` FROM `EHIKES`;";
                    $userids = $pdo->query($whoReq)->fetchAll(PDO::FETCH_COLUMN);
                    if (count($userids) > 0
                        && !in_array('1', $userids) && !in_array('2', $userids)
                    ) {
                        $alerts['ehikes'] = 'yes';
                    } else {
                        $alerts['ehikes'] = 'no';
                    }    
                }
                if ($tbl === 'USERS') {
                    $alerts['newuser'] = 'yes';                }
            }   
        }      
    }
}
