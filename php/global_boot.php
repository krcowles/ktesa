<?php
/**
 * This file is intended to be used as a global php bootstrap file and
 * to be 'required' by all session-creating php modules, as necessary.
 * It includes function definitions used by many modules as well as error
 * reporting and logging options, whether in admin, build, or run mode.
 * PHP Version 7.1
 * 
 * @package Global_Boot
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmai.com>
 * @license No license to date
 */
require "../../settings.php";
// Error and exception handling and logging:
require "../vendor/autoload.php";
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
$log = new Logger('ktesa');
$log->pushHandler(new StreamHandler('../ktesa.log', Logger::WARNING));
if ($appMode === 'development') {
    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->register();
}
// Function definitions:
require "../admin/adminFunctions.php";
require "../build/buildFunctions.php";
require "../mysql/dbFunctions.php";
// Establish database connection
// new PDO to be established here - will only need this one instance.
