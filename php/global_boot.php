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
require "../admin/set_sql_mode.php";
// Function definitions:
require "../admin/adminFunctions.php";
require "../build/buildFunctions.php";
require "errFunctions.php";

// PHP site recommends following value for future expansion of E_ALL
error_reporting(-1);  // 2147483647 is also suggested on PHP site, both work
if ($appMode === 'production') {
    ini_set('log_errors', 1); // (this may be the default anyway)
    ini_set('error_log', '../ktesa.log');
    // UNCAUGHT error/exception handling:
    set_error_handler('ktesaErrors'); // errors not using Throwable interface
    set_exception_handler('ktesaExceptions'); // uncaught exceptions (no try/ctach)
    // A method for fatal errors that handlers don't catch
    register_shutdown_function("shutdownHandler");
} else { // development
    /**
     * In this mode, no error_log is specified, so syslog could be used;
     * However, with whoops, there is no syslog, thus the following three
     * statements are not needed.
     * Use them if/when whoops is not available.
     */
    //ini_set('display_errors', "1"); // default is off i.e. 'production'
    //ini_set('display_startup_errors', 1);  // should never be 'on' in production
    //ini_set('log_errors', 1);

    // In effect, the default UNCAUGHT error/exception handler
    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->register();
}

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
    pdoErr("connect to database", $e);
}
/**
 * The following tests were used as a means to verify some of the
 * error and exception handling. In some cases, the tests utilize 
 * scenarios unlikely to occur, but nonetheless validate the function.
 * The site initially should be set to 'development' for these tests.
 * To test the collection of info in the ktesa.log, and to generate 
 * the user-friendly error page, set the site to 'production'.
 * When testing, remember that only the first un-commented error will 
 * trip, so comment out any code preceding the desired test.
 * 
 * NOTE: Whoops does not invoke for CAUGHT errors/exceptions
 * 
 * try/catch: in production mode, errors and warnings are not caught, 
 * and the catch block will not execute, but the errors will still be
 * logged. Only 'throwable' objects can be caught.
 * See documentation on Google Drive for more detail.
 */

/*
$var = 1;
try {
    fopen("blah"); // WARNING (caught in dev mode only)
    //$var->method(); // ERROR OBJECT THROWN (but uses 'Throwable')
    //$x = 1/0; // WARNING (caught in dev mode only)
    //trigger_error("triggered"); // Error, NO ERROR OBJECT (caught in dev mode only)
    //throw new Error("new type error"); // ERROR OBJECT THROWN (uses Throwable)
    //throw new Exception("Yay!");
    //$x = $y*fred; // Error (caught in dev mode)
}
catch (Throwable $t) {
    // Because it now implements the Throwable interface:
    $message = "Here's the data: ";
    $message .= "In " . $t->getFile() . " at line " . $t->getLine() .
        " error code " . $t->getCode() . " with message:<br />" .
        $t->getMessage() . "; TRACE INFO:<br />" . $t->getTraceAsString();
    die($message);
}
*/
// UNCAUGHT - invokes whoops in dev mode
//fopen("fred");
//$var->method();
//$x = 1/0;
//trigger_error("triggered for whoops");
//throw new Error("whoops-catchable error");
