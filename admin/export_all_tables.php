<?php
/** 
 * This script will export all tables automatically and download
 * them to the client machine's browser. Refer to comments in the
 * adminFunctions.php module: the export utilizes both $pdo for
 * accessing the current db, and mysqli for formulating the .sql
 * file's string contents. 
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

$download = filter_input(INPUT_GET, 'dwnld');
$which    = isset($_GET['db']) ? filter_input(INPUT_GET, 'db') : false;

if ($which === 'main') {
    // create array of tables to export: NOTE: due to foreign keys, 
    // EHIKES must be first
    $tables = array('EHIKES');
    $data = $pdo->query("SHOW TABLES;");
    $tbls_list = $data->fetchALL(PDO::FETCH_BOTH);
    foreach ($tbls_list as $row) {
        if (($row[0] !== 'EHIKES') && ($row[0] !== 'FAVORITES')) {
            array_push($tables, $row[0]);
        }
    }
    array_push($tables, 'FAVORITES'); // due to foreign keys, FAVORITES must be last

    $backup_name = "mainbackup.sql";
    // mysqli prep:
    $link =  mysqli_connect($HOSTNAME, $USERNAME, $PASSWORD, $DATABASE);
    if (!$link) {
        throw new Exception(
            "Could not connect to the database using mysqli: File " .
            __FILE__ . "at line " . __LINE__
        );
    }
    if (!mysqli_set_charset($link, "utf8")) {
        throw new Exeption(
            "Function mysqli_set_charset failed when called from file " .
            __FILE__ . " line " . mysqli_error($link)
        );
    }
    // export function is contained in adminFunctions.php
    exportDatabase($pdo, $link, $DATABASE, $tables, $download, $backup_name = false);
} elseif ($which === 'gpx') {
    $gpxtables = array('GPX', 'META', 'EGPX', 'EMETA');
    $backup_name = "gpxbackup.sql";
    // mysqli prep:
    $link =  mysqli_connect($HOSTNAME, $USERNAME, $PASSWORD, $GPXDATA);
    if (!$link) {
        throw new Exception(
            "Could not connect to the database using mysqli: File " .
            __FILE__ . "at line " . __LINE__
        );
    }
    if (!mysqli_set_charset($link, "utf8")) {
        throw new Exeption(
            "Function mysqli_set_charset failed when called from file " .
            __FILE__ . " line " . mysqli_error($link)
        );
    }
    exportDatabase($gdb, $link, $GPXDATA, $gpxtables, $download, false);
}
