<?php
/**
 * This script will download VISITORS data for the specified year
 * as a file called 'visitor_data.sql'.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

$arch_year = filter_input(INPUT_GET, 'yr');

$dwnld_name = "visitor_data.sql";
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
$tables = ['VISITORS'];
// export function is contained in adminFunctions.php
exportDatabase($pdo, $link, $DATABASE, $tables, 'N', $dwnld_name);
