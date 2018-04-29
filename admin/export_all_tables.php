<?php
/** 
 * This script will export all tables automatically and download
 * them to the client machine's browser. 
 * PHP Version 7.1
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require_once 'adminFunctions.php';
require_once '../mysql/dbFunctions.php';
$selection = filter_input(INPUT_GET, 'dwnld');
$download = $selection === 'Y' ? true : false;
// create array of tables to export: 
//    NOTE: due to foreign keys, EHIKES must be first
$link = connectToDb(__FILE__, __LINE__);
$tables = array('EHIKES');
$tbl_list = mysqli_query($link, "SHOW TABLES;") or die(
    __FILE__ . " Line " . __LINE__ . "Failed to get list of tables: "
    . mysqli_error($link)
);
while ($row = mysqli_fetch_row($tbl_list)) {
    if ($row[0] !== 'EHIKES') {
        array_push($tables, $row[0]);
    }
}
$dev = $_SERVER['SERVER_NAME'] == 'localhost' ? true : false;
if ($dev) {
    $mysqlUserName = USERNAME_LOC;
    $mysqlPassword = PASSWORD_LOC;
    $mysqlHostName = HOSTNAME_LOC;
    $DbName = DATABASE_LOC;
} else {
    $mysqlUserName = USERNAME_000;
    $mysqlPassword = PASSWORD_000;
    $mysqlHostName = HOSTNAME_000;
    $DbName = DATABASE_000;
}
$backup_name = "mybackup.sql";
exportDatabase(
    $mysqlHostName, $mysqlUserName, $mysqlPassword, $DbName, $tables, 
    $download, $backup_name = false
);
