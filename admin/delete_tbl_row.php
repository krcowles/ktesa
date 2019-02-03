<?php
/**
 * This script will delete a single row in the selected table.
 * PHP Version 7.1
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
$tbl_type = filter_input(INPUT_GET, 'tbl');
$rowno = filter_input(INPUT_GET, 'indx');
if ($tbl_type === 'u') {
    $table = "USERS";
    $idfield = "userid";
} elseif ($tbl_type === 'eh') {
    $table = "EHIKES";
    $idfield = "indxNo";
} elseif ($tbl_type === 'h') {
    $table = "HIKES";
    $idfield = "indxNo";
} elseif ($tbl_type === 'et') {
    $table = "ETSV";
    $idfield = "picIdx";
} elseif ($tbl_type === 't') {
    $table = "TSV";
    $idfield = "picIdx";
} elseif ($tbl_type === 'er') {
    $table = "EREFS";
    $idfield = "refId";
} elseif ($tbl_type === 'r') {
    $table = "REFS";
    $idfield = "refId";
} elseif ($tbl_type === 'eg') {
    $table = "EGPSDAT";
    $idfield = "datId";
} elseif ($tbl_type === 'g') {
    $table = "GPSDAT";
    $idfield = "datId";
} else {
    die("Not supported for DELETE ROW at this time");
}
$lastid = "SELECT {$idfield} FROM {$table} ORDER BY {$idfield} DESC LIMIT 1";
$last = $pdo->query($lastid);
$iddat = $last->fetch(PDO::FETCH_NUM);
$tblcnt = $iddat[0];
if ($tblcnt === null) {
    die("There are no rows to delete in this table");
}
if ($rowno > $tblcnt) {
    $badrow = true;
    $toobig = '<p>The specified row is larger than last row of the table; Please ' .
        'return to admin tools and specify a valid row number';
} else {
    $badrow = false;
    $remreq = "DELETE FROM {$table} WHERE {$idfield} = " . $rowno . ";";
    $pdo->query($remreq);
    $good = "<p>Row " . $rowno . " successfully removed; </p>";
}
?>
 <!DOCTYPE html>
<html lang="en-us">

<head>
    <title>Delete a Row</title>
    <meta charset="utf-8" />
    <meta name="description" content="Drop the HIKES Table" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <style type="text/css">
        body {background-color: #eaeaea;}
    </style>
</head>

<body>
<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">Delete Row From HIKES Table</p>
<div style="margin-left:16px;font-size:18px;"> 
<?php if ($badrow) : ?>
    <p><?= $toobig;?></p>
<?php else : ?>
    <p><?= $good;?></p>
<?php endif; ?>
</div>
</body>
</html>
