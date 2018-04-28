<?php
/**
 * This script is the essence of the action for loading all tables. It
 * can be called individually, or as a part of the reload action.
 * PHP Version 7.1
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 * @link    ../docs/
 */
require_once "../mysql/dbFunctions.php";
$link = connectToDb(__FILE__, __LINE__);
// Temporary variable, used to store current query
$templine = '';
// Read in entire file
$dbFile = "../data/id140870_hikemaster.sql";
$lines = file($dbFile);
if (!$lines) {
    die(
        __FILE__ . " Line: " . __LINE__ . 
        " Failed to read database from file: {$dbFile}."
    );
}
// Loop through each line
$gottbl = false;
$totalQs = 0;
// doing this twice, once just to get info for the progress bar:
foreach ($lines as $line) {
    // Skip it if it's a comment
    if (substr($line, 0, 2) == '--' || $line == '') {
        continue;
    }
    if (substr(trim($line), -1, 1) == ';') {
        $totalQs++;
    }
}
echo "<script type='text/javascript'>var totq = {$totalQs};</script>";
$qcnt = 0;
foreach ($lines as $line) {
    // Skip it if it's a comment
    if (substr($line, 0, 2) == '--' || $line == '') {
        continue;
    }
    // Add this line to the current segment
    $templine .= $line;
    // If it has a semicolon at the end, it's the end of the query
    $qstr = trim($templine);
    if (substr(trim($line), -1, 1) == ';') {
        // look for create and table name
        $createTbl = strpos($qstr, "CREATE TABLE");
        if ($createTbl !== false) {
            $tblLgth = strpos($qstr, '(') - 3 - ($createTbl + 13);
            $tblName = substr($qstr, $createTbl+14, $tblLgth);
            $gottbl = true;
        }
        // Perform the query
        $req = mysqli_query($link, $qstr);
        if (!$req) {
            die(
                "<p>load_all_tables.php: Failed: " . mysqli_error($link) . "</p>"
            );
        }
        if (!is_bool($req)) {
            mysqli_free_result($req);
        }
        $qcnt++;
        echo "<script type='text/javascript'>var qcnt = {$qcnt};</script>";
        if ($gottbl) {
            $gottbl = false;
            echo "<br />Completed " . $tblName . " at: " . 
                date('l jS \of F Y h:i:s A');
            flush();
        } else {
            echo "<br />Completed " . substr($qstr, 0, 32) . 
                date('l jS \of F Y h:i:s A');
            flush();
        }
        $templine = '';    // Reset temp variable to empty
    }
}
mysqli_close($link);
