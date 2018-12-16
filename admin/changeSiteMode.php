<?php
/**
 * This script will change the mode of the site between production
 * and development, and redirect back to the admintools page.
 * PHP Version 7.1
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
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
header("Location: admintools.php");
