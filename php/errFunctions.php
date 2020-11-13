<?php
/**
 * This module contains error handling functions defined for the project.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
/**
 * This function establishes production mode error handling, which
 * will present a user-friendly error page. Uncaught errors will be
 * logged to ktesa.log, and an email sent to site masters.
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
    $message = "An error occurred in {$errfile} on line {$errline}. " .
        "\nError no. {$errno}: {$errstr}\n";
    $message .= (new Exception)->getTraceAsString();
    error_log($message);
    // send email to site master
    errorPage();
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
    $message = "An uncaught exception occurred:\n" .
        "Code: " . $exception->getCode() . 
        " in file " . $exception->getFile() .
        " at line " . $exception->getLine() . "\n" .
        $exception->getMessage() . "\n" .
        "TRACE: " . $exception->getTraceAsString();
    error_log($message);
    // send email to site masters
    errorPage();
} 
/**
 * This is a custom handler to catch the ugly parse/compile errors et al
 * that don't otherwise get caught in error handlers or in whoops.
 * 
 * @return null
 */
function shutdownHandler() //will be called when php script ends.
{
    $lasterror = error_get_last();
    if (!empty($lasterror)) {
        switch ($lasterror['type'])
        {
        case E_ERROR:
        case E_CORE_ERROR:
        case E_COMPILE_ERROR:
        case E_USER_ERROR:
        case E_RECOVERABLE_ERROR:
        case E_CORE_WARNING:
        case E_COMPILE_WARNING:
        case E_PARSE:
            $error = "[SHUTDOWN] lvl:" . $lasterror['type'] .
                " | msg:" . $lasterror['message'] . " | file:" .
                $lasterror['file'] . " | ln:" . $lasterror['line'];
            shutdownError($error, "fatal");
        }
    }
}
/**
 * This function is called by the shutdown handler and receives 
 * a custom constructed error message from it. It is constructed
 * as a general-purpose call which could receive non-fatal errors.
 * 
 * @param string $errmsg the message about the fatal error
 * @param string $errlvl the level of the error
 * 
 * @return null
 */
function shutdownError($errmsg, $errlvl) 
{
    error_log($errmsg);
    errorPage();
}
/**
 * This is the user-friendly error page presented to the user
 * 
 * @return null
 */
function errorPage()
{
    // Cleanup all other output buffers before sending our output:
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    http_response_code(500);
    include "../php/user_error_page.php"; // send user error page
    exit(-1);
}
