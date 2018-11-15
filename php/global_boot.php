<?php
/**
 * This file is intended to be used as a global php bootstrap file and
 * to be 'required' by all session-creating php modules, as necessary.
 * It includes function definitions used by many modules as well as error
 * reporting and logging options, whether in development or production mode.
 * It also estabishes the PDO object for the session.
 * PHP Version 7.1
 * 
 * @package Global_Boot
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmai.com>
 * @license No license to date
 */
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
// test control:
$mono = false;
$whps = false;
$errh = false;
$exch = false;

require "../../settings.php";
// Error settings:
error_reporting(2147483647); // recommended by PHP site instead of E_ALL for future vals
ini_set('log_errors', 1);
ini_set('error_log', '../ktesa.log');

//ini_set('display_errors', 'on'); // default is off 
//ini_set('display_startup_errors', 1);  // should never be 'on' in production
// Error and exception packages:
require "../vendor/autoload.php";
if ($mono) {
    $log = new Logger('ktesa');
    $log->pushHandler(new StreamHandler('../ktesa.log', Logger::WARNING));
}
/** 
 * The error_handler negates instantiation of whoops (on errors) and is only
 * set in production mode. The exception handler does not negate instatiation
 * whoops (on exceptions), though whoops precludes it.
 * Error logs are kept in ktesa.log
 */
if ($whps) {
    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->register();
} 
if ($errh) {
    set_error_handler('ktesa_errors');
}
if ($exch) {
    set_exception_handler('ktesa_exceptions');
}
function ktesa_errors($errno, $errstr, $errfile, $errline) {
    $message = "An error occurred in {$errfile} on line {$errline}. " .
        "<br />Error no. {$errno}: {$errstr}<br /><br />";
    echo $message;
    //error_log($message."\n", 3, '../ktesa.log');
    die("Execution halted");
}
function ktesa_exceptions($exception) {
    // Note: handler will not be invoked when whoops is active
    // execution halts automatically after the uncaught exception: no 'die' needed.
    $message = "An uncaught exception occurred:<br />" . $exception->getMessage();
    echo $message;
} 

// error tests:
$handle = fopen("asdf"); // create an error with this command
//trigger_error("This is a test; this is ONLY a test!");
//$log->warning("try this...");
//throw new Exception("Test throw");




// Function definitions:
require "../admin/adminFunctions.php";
require "../build/buildFunctions.php";
require "../mysql/dbFunctions.php";

// Establish database connection
$options = array(
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throws a PDOException, sets error code
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
);
$dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s',HOSTNAME, DATABASE, CHARSET);
try {
    $pdo = new PDO( $dsn, USERNAME, PASSWORD, $options);
}
catch (\PDOException $e) {
    echo "A problem was encountered connecting to the database: " 
        . $e->getMessage() . " with error code: " . (int)$e->getCode();
    echo "<br />The caller was " . $src_file . " at line " . $src_line;
    echo "<br />The error occurred in " . $e->getFile() . " at line " . $e->getLine() . " <br />";
    throw new Exception("Failure to connect to db");
}
