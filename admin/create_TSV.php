<?php
/**
 * This script will create an unpopulated TSV table.
 * PHP Version 7.1
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
?>
<!DOCTYPE html>
<html lang="en-us">

<head>
    <title>Create TSV Table</title>
    <meta charset="utf-8" />
    <meta name="description" content="Create the TSV Table" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <style type="text/css">
        body { background-color: #eaeaea; }
        table {
           border-collapse: collapse;
           border-style: solid;
           border-width: 3px;
           margin-left: 80px;
           border-color: DarkBlue;
           background-color: #EDF2F7;
        }
        thead tr {
           border-style: solid;
           border-width: 2px;
        }
        td {
            text-align: center;
        }
        td:nth-child(1) {
            text-align: left;
            padding-left: 12px;
        }
    </style>
</head>

<body>
<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">Create the TSV Table</p>
<div style="margin-left:16px;font-size:18px;">
    <p>This script will create the TSV table for site administration.</p>
<?php
$newtsv = <<<tsv
CREATE TABLE TSV (
picIdx smallint NOT NULL AUTO_INCREMENT PRIMARY KEY,
indxNo smallint,
folder varchar(30),
usrid varchar(32),
title varchar(128),
hpg varchar(1),
mpg varchar(1),
`desc` varchar(512),
lat double(13,10),
lng double(13,10),
thumb varchar(1024),
alblnk varchar(1024),
date DATETIME,
mid varchar(1024),
imgHt smallint,
imgWd smallint,
iclr varchar(32),
org varchar(1024) );
tsv;
$tbls = $pdo->query("SHOW TABLES;");
$tlist = $tbls->fetchAll(PDO::FETCH_BOTH);
echo "<p>Results from SHOW TABLES:</p><ul>";
foreach ($tlist as $row) {
    if ($row[0] === "TSV") {
        die("You must first DROP TSV");
    }
    echo "<li>" . $row[0] . "</li>";
}
echo "</ul>";
?>
    <p>Description of the TSV table:</p>
    <table>
        <colgroup>
            <col style="width:100px">
            <col style="width:120px">
            <col style="width: 80px">
            <col style="width:60px">
            <col style="width:60px">
            <col style="width:160px">
        </colgroup>
        <thead>
            <tr>
                <th>FIELD</th>
                <th>TYPE</th>
                <th>NULL</th>
                <th>KEY</th>
                <th>DEFAULT</th>
                <th>EXTRA</th>
            </tr>
        </thead>
        <tbody>
<?php
try {
    $pdo->query($newtsv);
}
catch (PDOException $e) {
    pdoErr("CREATE TABLE TSV", $e);
}
$tsv_struct = $pdo->query("DESCRIBE TSV");
$struct = $tsv_struct->fetchAll(PDO::FETCH_BOTH);
echo '<p>TSV Table created; Definitions are shown in the table below</p>';
foreach ($struct as $row) {
    echo "<tr>";
    for ($i=0; $i<count($row); $i++) {
        echo "<td>" . $row[$i] . "</td>";
    }
    echo "</tr>" . PHP_EOL;
}
?>
       </tbody>
    </table>
    <p>DONE</p>
</div>
</body>
</html>
