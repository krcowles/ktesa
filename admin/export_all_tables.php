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
// Note that this establishes the database credentials

$download = filter_input(INPUT_GET, 'dwnld');
// For exporting only the VISITORS table:
if ($download === 'V') {
    $tables = ['VISITORS'];
    $backup_name = "visitors.sql";
} else {
    //  NOTE: due to foreign keys, EHIKES must be first
    $tables = array('EHIKES');
    $data = $pdo->query("SHOW TABLES;");
    $tbls_list = $data->fetchALL(PDO::FETCH_BOTH);
    foreach ($tbls_list as $row) {
        if (($row[0] !== 'EHIKES') && ($row[0] !== 'FAVORITES')) {
            array_push($tables, $row[0]);
        }
    }
    array_push($tables, 'FAVORITES'); // due to foreign keys, FAVORITES must be last
    $backup_name = false;
}

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
exportDatabase($pdo, $link, $DATABASE, $tables, $download, $backup_name);
