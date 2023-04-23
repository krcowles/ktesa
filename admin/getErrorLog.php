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
$error_log = file("../../ktprivate/ktesa/ktesa.log");
foreach ($error_log as $line) {
    echo $line . "<br />";
}
