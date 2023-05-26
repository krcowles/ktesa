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
$caller = getIpAddress();
$log = $caller === '127.0.0.1' || $caller === "::1" ? 'local' : 'remote';
if ($log === 'remote') {
    $log = $sitePrivateDir . "/public_html/ktesa.log";
} else {
    $log = "../../ktprivate/ktesa/ktesa.log";
}
$error_log = file($log);
foreach ($error_log as $line) {
    echo $line . "<br />";
}
