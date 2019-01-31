<?php
/**
 * This file is intended to be used as a global php bootstrap file and
 * is to be 'required' by all session-creating php modules.
 * It includes function definitions used by many modules as well as 
 * error reporting and logging options, whether in development or 
 * production mode. This file also establishes the PDO object for
 * the session ($pdo).
 * PHP Version 7.1
 * 
 * @package Global_Boot
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../vendor/autoload.php"; // CHECK OTHER FILES FOR REPEAT!!
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
$dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=%s', $HOSTNAME, $DATABASE, $CHARSET
);
try {
    $pdo = new PDO($dsn, $USERNAME, $PASSWORD, $options);
}
catch (\PDOException $e) {
    pdo_err("connect to database", $e);
}
error_reporting(2147483647); // PHP site recommends for future expansion of E_ALL
if ($appMode === 'production') {
    ini_set('log_errors', 1); // this may be the default anyway
    ini_set('error_log', '../ktesa.log');
    set_error_handler('ktesaErrors'); // errors not using Throwable interface
    set_exception_handler('ktesaExceptions'); // uncaught exceptions
} else { // development
    // in this mode, no error_log is specified, so we use syslog
    ini_set('display_errors', 'on'); // default is off i.e. 'production'
    ini_set('display_startup_errors', 1);  // should never be 'on' in production
    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->register();
}
// belongs in separate file when done per PSR1

/**
 * This function establishes production mode error handling, which
 * will utilize a user-friendly error page.
 * 
 * @param string $errno   The error number reported back by the error
 * @param string $errstr  The actual error message reported
 * @param string $errfile The file name in which the error occurred
 * @param string $errline The line in the above file in which error occurred
 * 
 * @return null
 */
function ktesaErrors($errno, $errstr, $errfile, $errline)
{
    $message = "A ktesa error occurred in {$errfile} on line {$errline}. " .
        "<br />Error no. {$errno}: {$errstr}<br /><br />";
    error_log($message."\n", 3, '../ktesa.log'); // eventually, switch to email?
    error_page();
    die("ERROR: Execution halted"); // die is required in this handler
}
/**
 * This is the production mode exception handler, also presenting 
 * exception data to the logger and a user-friendly page to the user.
 * Note that execution halts automatically after the uncaught exception.
 * 
 * @param object $exception The exception object
 * 
 * @return null
 */
function ktesaExceptions($exception)
{
    $message = "A KTESA uncaught exception occurred:<br />" .
        $exception->getMessage();
    error_log($message);
    // ktesa.log should show trace
    errorPage();
} 
/**
 * This is the user-friendly error page presented to the user
 * 
 * @return null
 */
function errorPage()
{
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
    //$msg = "Is there a throwable i/f? " . $er->getFile() .
        " at line " . $er->getLine();
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


