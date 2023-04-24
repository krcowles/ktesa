<?php
/**
 * Echo error log text for admin review
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license None to date
 */
require "../php/global_boot.php";

$log = $sitePrivateDir . "/public_html/ktesa.log";
$error_log = file($log);
foreach ($error_log as $line) {
    echo $line . "<br />";
}
