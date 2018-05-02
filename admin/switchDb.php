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
session_start();
$setenv = '../mysql/setenv.php';
$currentSetenv = file($setenv);
foreach ($currentSetenv as &$line) {
    if (strpos($line, 'USERNAME_000') > 0) {
        if (strpos($line, 'test') > 0) { // using test db
            $test = true;
            $line = 'DEFINE("USERNAME_000", "id140870_krcowles", true);';
        } else {
            $test = false;
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
    if (strpos($line, 'DATABASE_LOC') > 0) {
        if ($test) {
            $line = 'DEFINE("DATABASE_LOC", "id140870_hikemaster", true);';
        } else {
            $line = 'DEFINE("DATABASE_LOC", "id140870_nmhikestest", true);';
        }
        $line .= "\n";
    }
}
if ($test) {  // if previously test, will now be 'N'orm
    $_SESSION['activeDb'] = 'N';
} else {  // if previously normal, will now be 'T'est
    $_SESSION['activeDb'] = 'T';
}
file_put_contents($setenv, $currentSetenv);
$redirect = 'admintools.php';
header("Location: {$redirect}");
