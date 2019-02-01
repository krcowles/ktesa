<?php
/**
 * This file will be included as part of the global_boot.php file.
 * PHP Version 7.1
 * 
 * @package Database_Acess
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
/**
 * This is the default exception handler, when thrown exceptions are not
 * otherwise caught.
 * 
 * @param object $exception 'Throwable' type Exception object
 * 
 * @return null
 */
function defaultExceptions($exception)
{
    echo "The routine has encountered an error: <br />"
        . $exception->getMessage() . PHP_EOL;
}
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
