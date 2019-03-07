<?php
/**
 * This module reads the mysql settings contained in sql_modes.ini
 * and writes the selected settings back out. 
 * PHP Version 7.1
 * 
 * @package Admin
 * @author  Tom Sandberge and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require '../php/global_boot.php';
$old = file('sql_modes.ini', FILE_IGNORE_NEW_LINES);
$curr_ons = $_POST['ons'];
if (is_null($curr_ons)) {
    $noOn = 0;
} else {
    $noOn = count($curr_ons);
}
$modePtr = fopen("sql_modes.ini", "w");
if ($modePtr === false) {
    die("<p>Could not open modes file for writing</p>");
}
foreach ($old as $opt) {
    $opt = substr($opt, 2, strlen($opt)-2);
    $match = false;
    for ($j=0; $j<$noOn; $j++) {
        if ($opt == $curr_ons[$j]) {
            $match = true;
            break;
        }
    }
    if ($match) {
        $new = 'Y:' . $opt . "\n";
    } else {
        $new = 'N:' . $opt . "\n";
    }
    fwrite($modePtr, $new);
}
fclose($modePtr);
$_SESSION['sqlmode'] = 'active';
header("Location: admintools.php");
