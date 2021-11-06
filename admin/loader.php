<?php
/**
 * This script is the essence of the action for loading all tables. It
 * can be called individually, or as a part of the reload action. NOTE:
 * if the sql file was created via a phpMyAdmin 'export', additional SQL
 * commands (ones not present when performing 'EXPORT ALL TABLES') may
 * be present and require modifications (e.g. a "COMMIT" statement at the
 * end of file, and/or C-style comment lines).
 * PHP Version 7.8
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */

// Read in entire file
if ($main) {
    $dbFile = "../data/nmhikesc_main.sql";
} else {
    $dbFile = "../data/nmhikesc_gpx.sql";
}

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
    // There are 3 kinds of queries: CREATE TABLE, INSERT INTO, AND ALTER:
    // NOTE: Any present 'COMMIT' or C-style comments must be removed
    if (strpos($lines[$i], "CREATE TABLE") !== false) {
        $msg = '"' . $lines[$i] . '"';
        $create = "";
        do {
            $create .= $lines[$i];
        } while (strpos($lines[$i++], ";") === false);
        if ($main) {
            $pdo->exec($create);
        } else {
            $gdb->exec($create);
        }
        $qcnt++;
        $i--;
    } elseif (strpos($lines[$i], "INSERT INTO") !== false) {
        $msg = '"' . $lines[$i] . '"';
        $insert = "";
        do {
            $insert .= $lines[$i];
        } while (strrpos($lines[$i], ";") !== strlen($lines[$i++])-2);
        if ($main) {
            $pdo->query($insert);
        } else {
            $gdb->query($insert);
        }  
        $qcnt++;
        $i--;
    } elseif (strpos($lines[$i], "ALTER") !== false) {
        $msg = '"' . $lines[$i] . '"';
        $alter = "";
        do {
            $alter .= $lines[$i];
        } while (strpos($lines[$i++], ";") === false);
        if ($main) {
            $pdo->query($alter);
        } else {
            $gdb->query($alter);
        }
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
$gpx_only = <<<HDR
<script type="text/javascript">
    var doneid = document.getElementById("done");
    doneid.style.display = "block";
HDR;
$main_msg = <<<BOD
    xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            alert("Checksums regenerated");
        }
    };
    xhr.open("get", "manageChecksums.php?act=ajax&reload=y");
    xhr.send();
BOD;
if ($main) {
    $mout = $gpx_only . PHP_EOL . $main_msg . PHP_EOL;
} else {
    $mout = $gpx_only . PHP_EOL;
}
$mout .= '</script>' . PHP_EOL;
echo $mout;
