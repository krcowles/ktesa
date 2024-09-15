<?php
/**
 * As a part of checking database differences prior to executing a reload,
 * this script will examine the EHIKES and USERS tables in the .sql file
 * to be utilized for the reload, and compare them to the contents of the
 * resident database.  Changes in these tables constitute the primary indicators
 * that the reload could potentially overwrite valid user data. Both tables are
 * historically small and thus time impact to perform the test is minimal.
 * The Checksums table is also scanned for new or missing entries in the sql.
 * NOTE: Some tables or checksums may be missing due to a failed/partial
 * reload: verify first that the tables exist!
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";

$not_in_new = [];
$not_in_old = [];
$mismatched = [];
$new_users  = [];
$del_users  = [];
$new_hikes  = [];
$del_hikes  = [];
/**
 * Retrieve key data from the resident database to use in comparing
 * differences with the new database residing in the .sql file.
 * 1. USERS (array of usernames)
 * 2. Checksums (array of tables, and array of checksum values corresponding)
 * 3. EHIKES (associative array: id => title)
 */
$chksums = $pdo->query("SHOW TABLES LIKE 'Checksums%';")->fetchAll(PDO::FETCH_NUM);
$chksums_exists = empty($chksums) ? false : true;
$users = $pdo->query("SHOW TABLES LIKE 'USERS%';")->fetchAll(PDO::FETCH_NUM);
$users_exists = empty($users) ? false : true;
$ehikes = $pdo->query("SHOW TABLES LIKE 'EHIKES%';")->fetchAll(PDO::FETCH_NUM);
$ehikes_exists = empty($ehikes) ? false : true;
if ($chksums_exists && $users_exists && $ehikes_exists) {
    $residentChksumReq = "SELECT `name`,`chksum` FROM `Checksums`;";
    $residentChksums
        = $pdo->query($residentChksumReq)->fetchAll(PDO::FETCH_KEY_PAIR);
    $resTbls      = array_keys($residentChksums);
    $resSums      = array_values($residentChksums);

    $resUsersReq  = "SELECT `username` FROM `USERS`;";
    $resUsers     = $pdo->query($resUsersReq)->fetchAll(PDO::FETCH_COLUMN);
    // Page titles are guaranteed unique (usrid's are not!)
    $resEhikesReq = "SELECT `pgTitle` FROM `EHIKES`;";
    $resEhikes    = $pdo->query($resEhikesReq)->fetchAll(PDO::FETCH_COLUMN);

    // LOAD sql fle
    $dbFile = "../data/nmhikesc_main.sql";
    $sqlFile = file($dbFile);
    if (!$sqlFile) {
        throw new Exception(
            __FILE__ . " Line: " . __LINE__ . 
            " Failed to read database from file: {$dbFile}."
        );
    }
    $users_data = [];
    $ehike_data = [];
    $checksums  = [];
    for ($k=0; $k<count($sqlFile); $k++) {
        if (strpos($sqlFile[$k], "INSERT INTO Checksums") !== false) {
            while (strpos($sqlFile[$k], ";") === false) {
                $k++;
                array_push($checksums, $sqlFile[$k]);
            }
        }
        if (strpos($sqlFile[$k], "INSERT INTO USERS") !== false) {
            while (strpos($sqlFile[$k], ";") === false) {
                $k++;
                array_push($users_data, $sqlFile[$k]);
            }
        }
        if (strpos($sqlFile[$k], "INSERT INTO EHIKES") !== false) {
            while (strpos($sqlFile[$k], ";") === false) {
                $k++;
                array_push($ehike_data, $sqlFile[$k]);
            }
        }
    }
    /**
     * Process the arrays to format as the resident db key data arrays;
     * Eliminate key quotes with string
     */
    // Checksums:
    $sqlTbls = [];
    $sqlSums = [];
    $chksum_data = preg_replace("/\',\'/", "|", $checksums, 3);
    foreach ($chksum_data as $entry) {
        $table_name_start = strpos($entry, "|") + 1;
        $table_name_end   = strpos($entry, "|", 7);
        $tbl_length = $table_name_end - $table_name_start;
        $table_name = substr($entry, $table_name_start, $tbl_length);
        $remainder  = substr($entry, $table_name_end + 1);
        $chksum_val_end = strpos($remainder, "|");
        $checksum_val   = substr($remainder, 0, $chksum_val_end);
        array_push($sqlTbls, $table_name);
        array_push($sqlSums, intval($checksum_val));
    }
    // Ehikes:
    $sqlEhikes = [];
    $ehike_data = preg_replace("/\',\'/", "|", $ehike_data, 3);
    foreach ($ehike_data as $ehike) {
        $ehike_start = strpos($ehike, "|") + 1;
        $ehike_end   = strpos($ehike, "|", $ehike_start);
        $ehike_lgth  = $ehike_end - $ehike_start;
        $ehike_pg    = substr($ehike, $ehike_start, $ehike_lgth);
        array_push($sqlEhikes, $ehike_pg);
        /*
        $remainder   = substr($ehike, $ehike_end + 1);
        $hike_id_end = strpos($remainder, "|");
        $id          = substr($remainder, 0, $hike_id_end);
        $sqlEhikes[$ehike_pg] = $id;
        */
    }
    // Users
    $sqlUsers = [];
    $user_data = preg_replace("/\',\'/", "|", $users_data);
    foreach ($user_data as $usr) {
        $user_name_start = strpos($usr, "|") + 1;
        $user_name_end   = strpos($usr, "|", $user_name_start);
        $user_length = $user_name_end - $user_name_start;
        $user_name = substr($usr, $user_name_start, $user_length);
        array_push($sqlUsers, $user_name);
    }
    // Tables should be the same
    for ($j=0; $j<count($resTbls); $j++) {
        if (!in_array($resTbls[$j], $sqlTbls)) {
            array_push(
                $not_in_new, 
                "<h5>{$resTbls[$j]} is not found in the new (sql) db</h5>"
            );
        }
    }
    for ($k=0; $k<count($sqlTbls); $k++) {
        if (!in_array($sqlTbls[$k], $resTbls)) {
            array_push(
                $not_in_old,
                "<h5>{$sqlTbls[$k]} is not found in the resident db</h5>",
            );
        }
    }
    /**
     * Examine checksums to identify any changed values. When the changed
     * value (or difference) occurs in the USERS or EHIKES table, further
     * identify the responsible items.
     */
    for ($l=0; $l<count($resTbls); $l++) {
        // For every table that still resides in the new (sql) db:
        if (!in_array($resTbls[$l], $not_in_new)) {
            $sqlSumsLoc = array_search($resTbls[$l], $sqlTbls);
            if ($resSums[$l] !== $sqlSums[$sqlSumsLoc]) {
                $nomatch = "<h5>The checksums for {$resTbls[$l]} do not match</h5>";
                array_push($mismatched, $nomatch);
                // if USERS or non-admin EHIKES, what are the differences?
                if ($resTbls[$l] === 'USERS') {
                    $nusers = array_diff($sqlUsers, $resUsers);
                    $new_users = array_merge($new_users, $nusers);
                    $dusers = array_diff($resUsers, $sqlUsers);
                    $del_users = array_merge($del_users, $dusers);
                } elseif ($resTbls[$l] === 'EHIKES') {
                    $nhikes = array_diff($sqlEhikes, $resEhikes);
                    $new_hikes = array_merge($new_hikes, $nhikes);
                    $dhikes = array_diff($resEhikes, $sqlEhikes);
                    $del_hikes = array_merge($del_hikes, $dhikes);
                }
            }
        }
    }
    /**
     * The above assumes that any changes in the sql database had been 'registered'
     * by means of the checksum, but that may not have happened, so: for USERS and
     * EHIKES [ONLY], check again! Compare the actual data
     */
    foreach ($resUsers as $usr) {
        if (!in_array($usr, $sqlUsers)) {
            if (!in_array($usr, $del_users)) {
                array_push($del_users, $usr);
            }
        }
    }
    foreach ($resEhikes as $ehk) {
        if (!in_array($ehk, $sqlEhikes)) {
            if (!in_array($ehk, $del_hikes)) {
                array_push($del_hikes, $ehk);
            } 
        }
    }
    foreach ($sqlUsers as $susr) {
        if (!in_array($susr, $resUsers)) {
            if (!in_array($susr, $new_users)) {
                array_push($new_users, $susr);
            }
        }
    }
    foreach ($sqlEhikes as $sehk) {
        if (!in_array($sehk, $resEhikes)) {
            if (!in_array($sehk, $new_hikes)) {
                array_push($new_hikes, $sehk);
            }
        }
    }
}
// return info
$returnArr = [];
$returnArr['mismatch']   = count($mismatched) > 0 ? $mismatched : ['none'];
$returnArr['not_in_new'] = count($not_in_new) > 0 ? $not_in_new : ['none'];
$returnArr['not_in_old'] = count($not_in_old) > 0 ? $not_in_old : ['none'];
$returnArr['new_users']  = count($new_users)  > 0 ? $new_users  : ['none'];
$returnArr['del_users']  = count($del_users)  > 0 ? $del_users  : ['none'];
$returnArr['new_hikes']  = count($new_hikes)  > 0 ? $new_hikes  : ['none'];
$returnArr['del_hikes']  = count($del_hikes)  > 0 ? $del_hikes  : ['none'];
$returnjs = json_encode($returnArr);
echo $returnjs;
