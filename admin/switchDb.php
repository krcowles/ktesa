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
$redirect = 'admintools.php';
header("Location: {$redirect}");
