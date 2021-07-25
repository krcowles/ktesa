<?php
/**
 * This script will perform one of two actions:
 * 1. Update the Checksums Table with new checksum values. This would ostensibly
 *    occur after looking for and verifying any db changes.
 * 2. Examine the Checksums Table and compare its checksums with current database
 *    checksums. Present differences, missing tables, etc.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";

$action = filter_input(INPUT_GET, 'act');
$latest = "sumDate.txt";

if ($action === "updte") {
    $dropold = "DROP TABLE IF EXISTS `Checksums`";
    $pdo->query($dropold);
    $createChkSumsReq = "CREATE TABLE `Checksums` (
        `indx` smallint(6) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) DEFAULT NULL,
        `chksum` bigint DEFAULT NULL,
        PRIMARY KEY (`indx`)
    );";
    $createSums = $pdo->query($createChkSumsReq);
    
    $allTablesReq = "SHOW TABLES;";
    $allTables = $pdo->query($allTablesReq)->fetchAll(PDO::FETCH_COLUMN);
    foreach ($allTables as $tbl) {
        if ($tbl !== 'Checksums') {
            $sumReq = "CHECKSUM TABLE {$tbl};";
            $tblsum = $pdo->query($sumReq)->fetch(PDO::FETCH_NUM);
            $addSumReq = "INSERT INTO `Checksums` (`name`, `chksum`) VALUES " .
                "(?, ?);";
            $addSum = $pdo->prepare($addSumReq);
            $addSum->execute([$tbl, $tblsum[1]]);
        }
    }
    // Note table creation time:
    date_default_timezone_set('America/Denver');
    $ctime = date('M d Y H:i:s');
    file_put_contents($latest, $ctime);
} elseif ($action === 'exam') {
    $lastchk = file_get_contents($latest);
    // Get the current checksums
    $getSumsReq = "SELECT `name`,`chksum` FROM `Checksums`;";
    $getSums = $pdo->query($getSumsReq)->fetchAll(PDO::FETCH_KEY_PAIR);
    $chkTables = array_keys($getSums);
    $chkValues = array_values($getSums);
    // Get a list of all tables in the db
    $allTablesReq = "SHOW TABLES;";
    $allTables = $pdo->query($allTablesReq)->fetchAll(PDO::FETCH_COLUMN);
    // arrays holding mismatches
    $obs     = [];  // the table name in `Checksums` is no longer active
    $missing = [];  // the table name in the db has no `Checksums` entry
    $nomatch = [];  // this table has a changed value for checksum
    foreach ($chkTables as $ctbl) {
        if (!in_array($ctbl, $allTables)) {
            array_push($obs, $ctbl);
        }
    }
    foreach ($allTables as $tbl) {
        if ($tbl !== 'Checksums') {
            if (!in_array($tbl, $chkTables)) {
                array_push($missing, $tbl);
            } else {
                $cksumReq = "CHECKSUM TABLE {$tbl};";
                $tblsum = $pdo->query($cksumReq)->fetch(PDO::FETCH_NUM);
                if ($getSums[$tbl] !== $tblsum[1]) {
                    array_push($nomatch, $tbl);
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Checksum Results</title>
    <meta charset="utf-8" />
    <meta name="description" content="Look for database changes" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/ktesaPanel.css" type="text/css" rel="stylesheet" />
    <link rel="stylesheet" href="../styles/jquery-ui.css">
    <script src="../scripts/jquery.js"></script>
    <script src="../scripts/jquery-ui.js"></script>
    <style type="text/css">ul {font-weight: bold;}</style>
</head>
<body style="background-color:#eaeaea;">
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">Database Change Management</p>
<p id="page_id" style="display:none">Admin</p>

<div style="margin-left:24px;">
<?php if ($action === 'updte') : ?>
    <h3>All current tables in the database have had new checksums created</h3>
<?php else : ?>
    <h3>All tables in the database have had their corresponding checksums
        validated.<br />Results compared to last generated checksums 
        on <span style="color:brown;"><?=$lastchk;?></span>:</h3>
    <?php if (count($obs) > 0) : ?>
        <h3 style="color:brown;">The following tables appear in the Checksums 
        Table but not in the database:</h3>
        <ul>
            <?php foreach ($obs as $old) : ?>
                <li><?=$old;?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <?php if (count($missing) > 0) : ?>
        <h3 style="color:brown;">The following database tables do not appear 
        in the Checksums Table:</h3>
        <ul>
            <?php foreach ($missing as $out) : ?>
                <li><?=$out;?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <?php if (count($nomatch) > 0) : ?>
        <h3 style="color:brown;">The following tables have changed:</h3>
        <ul>
            <?php for ($j=0; $j<count($nomatch); $j++) : ?>
                <li><?=$nomatch[$j];?></li>
            <?php endfor; ?>
        </ul>
    <?php else : ?>
        <h3 style="color:darkblue">No [other] changes have been detected since 
        the 'last checked' date above</h3>
    <?php endif; ?>
<?php endif; ?>
</div>

<script src="../scripts/menus.js" type="text/javascript"></script>

</body>
</html>
