<?php
require 'setenv.php';
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
echo "<p>mySql Connection Opened</p>";
# NOTE: AUTO_INCREMENT seems to have conditional requirements surrounding it, esp PRIMARY KEY
$newtsv = <<<tsv
CREATE TABLE TSV (
picIdx smallint NOT NULL AUTO_INCREMENT PRIMARY KEY,
indxNo smallint,
folder varchar(30),
usrid varchar(32),
title varchar(64),
hpg varchar(1),
mpg varchar(1),
`desc` varchar(128),  # does not like the keyword desc
lat double(13,10),
lng double(13,10),
thumb varchar(256),
alblnk varchar(256),
date DATETIME,
mid varchar(256),
imgHt smallint,
imgWd smallint,
iclr varchar(32),
org varchar(256) );
tsv;
$tbl = mysqli_query($link,$newtsv);
if (!$tbl) {
    die("<p>CREATE TABLE failed;  Check error code: " . mysqli_error($link) . "</p>");
} else {
    echo '<p>TSV Table created; Definitions are shown in the table below</p>';
}
$req = mysqli_query($link,"SHOW TABLES;");
if (!$req) {
    die("<p>SHOW TABLES request failed: " . mysqli_error($link) . "</p>");
}
echo "<p>Results from SHOW TABLES:</p><ul>";
while ($row = mysqli_fetch_row($req)) {
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
    $tbl = mysqli_query($link,"DESCRIBE TSV;");
    if (!$tbl) {
        die("<p>DESCRIBE TSV FAILED: " . mysqli_error($link) . "/p>");
    } 
    $first = true;  
    while ($row = mysqli_fetch_row($tbl)) {
        echo "<tr>";
        for ($i=0; $i<count($row); $i++) {
            echo "<td>" . $row[$i] . "</td>";
        }
        echo "</tr>" . PHP_EOL;
    }
    mysqli_close($link);
?>
       </tbody>
    </table>
    <p>DONE</p>
</div>
</body>
</html>
