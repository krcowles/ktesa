<?php
/**
 * This file is intended to be used as a global php bootstrap file and
 * to be 'required' by all session-creating php modules, as necessary.
 * It includes function definitions used by many modules as well as error
 * reporting and logging options, whether in development or production mode.
 * It also establishes the PDO object for the session.
 * PHP Version 7.1
 * 
 * @package Global_Boot
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmai.com>
 * @license No license to date
 */
require "../vendor/autoload.php";
require "../../settings.php";

error_reporting(2147483647); // PHP site recommends for future expansion of E_ALL
if ($appMode === 'production') {
    ini_set('log_errors', 1); // this may be the default anyway
    ini_set('error_log', '../ktesa.log');
    set_error_handler('ktesa_errors'); // errors not using Throwable interface
    set_exception_handler('ktesa_exceptions'); // uncaught exceptions
} else { // development
    // in this mode, no error_log is specified, so we use syslog
    ini_set('display_errors', 'on'); // default is off i.e. 'production'
    ini_set('display_startup_errors', 1);  // should never be 'on' in production
    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->register();
}
// production mode error handlers:
function ktesa_errors($errno, $errstr, $errfile, $errline) {
    $message = "A ktesa error occurred in {$errfile} on line {$errline}. " .
        "<br />Error no. {$errno}: {$errstr}<br /><br />";
    error_log($message."\n", 3, '../ktesa.log'); // eventually, switch to email?
    error_page();
    die("ERROR: Execution halted"); // die is required in this handler
}
function ktesa_exceptions($exception) {
    // execution halts automatically after the uncaught exception: no 'die' needed.
    $message = "A KTESA uncaught exception occurred:<br />" . $exception->getMessage();
    error_log($message);
    // ktesa.log should show trace
    error_page();
} 
function error_page() {
    $user_error_page = "user_error_page.php";
    header("Location: {$user_error_page}");
}

// TESTS:
/*
$var = 1;
try {
    //fopen("blah"); // WARNING
    //$var->method(); // ERROR OBJECT THROWN (uses Throwable)
    //$x = 1/0; // WARNING
    //trigger_error("triggered"); // NO ERROR OBJECT - use error_handler
    //throw new Error("new type error"); ERROR OBJECT THROWN (uses Throwable)
    //throw new Exception("Yay!");
    //$x = $y*fred; 
}
catch (Throwable $t) {
    // Because it now implements the Throwable interface:
    $message = "Here's the data: ";
    $message .= "In " . $t->getFile() . " at line " . $t->getLine() .
        " error code " . $t->getCode() . " with message:<br />" .
        $t->getMessage() . "; TRACE INFO:<br />" . $t->getTraceAsString();
    die($message);
}
catch (Error $er) {
    die("Old error class?");
    //$msg = "Is there a throwable i/f? " . $er->getFile() . " at line " . $er->getLine();
    //die($msg);
}
catch ( DivsionByZeroError $d) {
    die("Even web says use error_handler - why this class/catch?");
    $m = "Division error: " . $d->getFile() . " at line " . $d->getLine();
    die($m);
}
finally {
    echo "FINALLY... block";
    // for exceptions, code execution continues after finally...
}
*/

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
