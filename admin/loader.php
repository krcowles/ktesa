<?php
/**
 * This script is the essence of the action for loading all tables. It
 * can be called individually, or as a part of the reload action. NOTE:
 * if the sql file was created via a phpMyAdmin 'export', additional SQL
 * commands (ones not present when performing 'EXPORT ALL TABLES') may
 * be present and require modifications (e.g. a "COMMIT" statement at the
 * end of file, and/or C-style comment lines). The VISITORS table will not
 * have been dropped and will not be reloaded.
 * PHP Version 7.8
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */

 require_once "../php/global_boot.php";
// Read in entire file; whether a reload of main, or test db, input file is:
$dbFile = "../data/nmhikesc_main.sql";
$db_contents = file($dbFile);
$lines = [];
if (!$db_contents) {
    throw new Exception(
        __FILE__ . " Line: " . __LINE__ . 
        " Failed to read database from file: {$dbFile}."
    );
}
$totalQs = 0; // total Queries

// remove the lines containing VISITORS table data
for ($k=0; $k<count($db_contents); $k++) {
    if (strpos($db_contents[$k], "CREATE TABLE `VISITORS`") !== false) {
        // continue to skip lines until next "CREATE TABLE" is encountered
        $k++;
        while (strpos($db_contents[$k], "CREATE TABLE") === false) {
            $k++;
        }
        $k -= 3; // back up to include CREATE TABLE and some blank lines...
    } else {
        array_push($lines, $db_contents[$k]);
    }
}
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
$msg_out = false;
$line_cnt = count($lines);

for ($i=0; $i<$line_cnt; $i++) {
    // Skip it if it's empty or a comment
    if (substr($lines[$i], 0, 2) == '--' || trim($lines[$i]) == '') {
        continue;
    }
    // There are 3 kinds of queries: CREATE TABLE, INSERT INTO, AND ALTER:
    // NOTE: Any present 'COMMIT' or C-style comments must be removed
    if (strpos($lines[$i], "CREATE TABLE") !== false) {
        $msg = '"' . $lines[$i] . '"';
        $create = "";
        do {
            $create .= $lines[$i];
        } while (strpos($lines[$i++], ";") === false);
        $pdo->exec($create);
        $qcnt++;
        $i--;
    } elseif (strpos($lines[$i], "INSERT INTO") !== false) {
        $msg = '"' . $lines[$i] . '"';
        $insert = "";
        do {
            $insert .= $lines[$i];
        } while (strrpos($lines[$i], ";") !== strlen($lines[$i++])-2);
        $pdo->query($insert);
        $qcnt++;
        $i--;
    } elseif (strpos($lines[$i], "ALTER") !== false) {
        $msg = '"' . $lines[$i] . '"';
        $alter = "";
        do {
            $alter .= $lines[$i];
        } while (strpos($lines[$i++], ";") === false);
        $pdo->query($alter);
        $qcnt++;
        $i--;
    } else {
        throw new Exception(
            "Unrecognized table entry at db line " . $i . "<br />" . $lines[$i]
        );
    }
    if (!$msg_out) {
        echo "<script type='text/javascript'>var qcnt = {$qcnt};</script>";
        echo "<br />Completed " . $msg . " at: " . date('l jS \of F Y h:i:s A');
        flush();
        $msg_out = false;
    }
}
echo PHP_EOL . '<script type="text/javascript">
    var doneid = document.getElementById("done");
    doneid.style.display = "block";
    xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            alert("Checksums regenerated");
        }
    };
    xhr.open("get", "manageChecksums.php?action=gen");
    xhr.send();
    </script>' . PHP_EOL;
