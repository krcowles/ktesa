<?php
/**
 * This file is intended to be used as a global php bootstrap file and
 * is to be 'required' by all session-creating php modules.
 * It includes function definitions used by many modules as well as 
 * error reporting and logging options, whether in development or 
 * production mode. [The error reporting/logging will not be fully
 * incorporated until the PDO migration is complete.]
 * This file also establishes the PDO object for the session ($pdo).
 * PHP Version 7.1
 * 
 * @package Global_Boot
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
//require "../vendor/autoload.php";
require "../admin/mode_settings.php"; // Capture this code version's settings
$settings = $_SERVER["DOCUMENT_ROOT"] . "/../settings.php";
require $settings;
// Function definitions:
require "../admin/adminFunctions.php";
require "../build/buildFunctions.php";
require "../mysql/dbFunctions.php";
require "../admin/set_sql_mode.php";

// Initial handler for PDO Exceptions: (defined in ../mysql/dbFunctions.php)
set_exception_handler('defaultExceptions');

// Establish session database connection
$options = array(
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => $mode_str,
);
// 'SET SESSION sql_mode = "ANSI,TRADITIONAL" '
$dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=%s', $HOSTNAME, $DATABASE, $CHARSET
);
try {
    $pdo = new PDO($dsn, $USERNAME, $PASSWORD, $options);
}
catch (\PDOException $e) {
    pdo_err("connect to database", $e);
}
