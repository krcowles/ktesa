<?php
/**
 * This module contains error handling functions defined for the project.
 * PHP Version 7.1
 * 
 * @package Error_Handling
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
/**
 * This function will print out the PDO Exception encountered, 
 * including where it occurred.
 * 
 * @param string       $cmd  The command attempted by the PDO
 * @param PDOException $pdoe The exception object invoked
 * 
 * @return null
 */
function pdoErr($cmd, $pdoe)
{
    $msg = "A problem was encountered with the {$cmd} command: " .
        "<br />The error message: " . $pdoe->getMessage() . 
        "; resulted in  code " . (int)$pdoe->getCode() .
        "<br />The error occurred in " . $pdoe->getFile() . 
        " at line " . $pdoe->getLine(); 
    throw new Exception($msg);
}
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
 * This is the user-friendly error page presented to the user
 * 
 * @return null
 */
function errorPage()
{
    $user_error_page = "../php/user_error_page.php";
    header("Location: {$user_error_page}");
}
