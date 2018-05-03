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
$setenv = '../mysql/setenv.php';
$currentSetenv = file($setenv);
foreach ($currentSetenv as &$line) {
    if (strpos($line, 'DATABASE_LOC') > 0) {
        if (strpos($line, 'test')) {
            $test = true;
            $line = 'DEFINE("DATABASE_LOC", "id140870_hikemaster", true);';
        } else {
            $test = false;
            $line = 'DEFINE("DATABASE_LOC", "id140870_nmhikestest", true);';
        }
        $line .= "\n";
    }
    if (strpos($line, 'USERNAME_000') > 0) {
        if ($test) {
            $line = 'DEFINE("USERNAME_000", "id140870_krcowles", true);';
        } else {
            $line = 'DEFINE("USERNAME_000", "id140870_krcowlestest", true);';
        }
        $line .= "\n";
    }
    if (strpos($line, 'DATABASE_000') > 0) {
        if ($test) {
            $line = 'DEFINE("DATABASE_000", "id140870_hikemaster", true);';
        } else {
            $line = 'DEFINE("DATABASE_000", "id140870_nmhikestest", true);';
        }
        $line .= "\n";
    }
}
file_put_contents($setenv, $currentSetenv);
$redirect = 'admintools.php';
header("Location: {$redirect}");
