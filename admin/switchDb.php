<?php
/**
 * The current version of this script will switch back and forth
 * between connecting to the test db and the 'normal' site db.
 * The contents of mysql/setenv.php will be altered accordingly.
 * PHP Version 7.0
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require '../php/global_boot.php';
$mode_settings = 'mode_settings.php';
$values = file($mode_settings);
foreach ($values as &$line) {
    if (strpos($line, "dbState =") !== false) {
        if (strpos($line, "test") !== false) {
            $line =str_replace('test', 'main', $line);
        } else {
            $line = str_replace('main', 'test', $line);
        }
    }
}
file_put_contents($mode_settings, $values);
// need to build in a small amount of time to allow file write completion
while (time() - filemtime($mode_settings) > 1) {
    usleep(5000);
}
$redirect = 'admintools.php';
header("Location: {$redirect}");
