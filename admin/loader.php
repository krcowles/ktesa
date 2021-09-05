<?php
/**
 * This script is the essence of the action for loading all tables. It
 * can be called individually, or as a part of the reload action.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */

// Read in entire file
$dbFile = "../data/nmhikesc_main.sql";
$lines = file($dbFile);
if (!$lines) {
    throw new Exception(
        __FILE__ . " Line: " . __LINE__ . 
        " Failed to read database from file: {$dbFile}."
    );
}
$totalQs = 0; // total Queries
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
    // There are 2 kinds of query: CREATE TABLE and INSERT INTO:
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
    xhr.open("get", "manageChecksums.php?act=ajax&reload=y");
    xhr.send();
    </script>' . PHP_EOL;
