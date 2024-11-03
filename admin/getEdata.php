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
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

$tables = ['EHIKES', 'ETSV', 'EREFS', 'EGPSDAT'];
$link =  mysqli_connect($HOSTNAME, $USERNAME, $PASSWORD, $DATABASE);
if (!$link) {
    throw new Exception(
        "Could not connect to the database using mysqli: File " .
        __FILE__ . "at line " . __LINE__
    );
}
if (!mysqli_set_charset($link, "utf8")) {
    throw new Exception(
        "Function mysqli_set_charset failed when called from file " .
        __FILE__ . " line " . mysqli_error($link)
    );
}
// export function is contained in adminFunctions.php
exportDatabase(
    $pdo, $link, $DATABASE, $tables, 'N', $backup_name = 'ETables.sql'
);