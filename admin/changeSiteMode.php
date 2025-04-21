<?php
/**
 * This script will toggle the mode setting of the site between production
 * and development. When reloaded, the page will reflect the new value
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require '../php/global_boot.php';
$mode_settings = "mode_settings.php";
$values = file($mode_settings);
foreach ($values as &$line) {
    if (strpos($line, "appMode =") !== false) {
        if (strpos($line, "production") !== false) {
            $line = str_replace("production", "development", $line);
        } else {
            $line = str_replace("development", "production", $line);
        }
    }
}
file_put_contents($mode_settings, $values);
sleep(1);
header("Location: admintools.php");
