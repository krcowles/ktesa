<?php
require_once '../mysql/setenv.php';
?>
<!DOCTYPE html>
<html lang="en-us">

<head>
    <title>Create IPTBLS Table</title>
    <meta charset="utf-8" />
    <meta name="description" content="Create the IPTBLS Table" />
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
<p id="trail">Create the IPTBLS Table</p>
<div style="margin-left:16px;font-size:18px;">
    <p>This script will create the IPTBLS table for site administration.</p>
<?php
echo "<p>mySql Connection Opened</p>";
# NOTE: AUTO_INCREMENT seems to have conditional requirements surrounding it, esp PRIMARY KEY
$newips = <<<ipg
CREATE TABLE IPTBLS (
ipIndx smallint NOT NULL AUTO_INCREMENT PRIMARY KEY,
indxNo smallint,
compl varchar(1),
tdname varchar(30),
tdpg varchar(5),
tdmiles decimal(4,2),
tdft smallint(5),
tdexp varchar(15),
tdalb varchar(1024) );
ipg;
$tbl = mysqli_query($link, $newips);
if (!$tbl) {
    die("<p>CREATE IPTBLS TABLE failed: " . mysqli_error($link) . "</p>");
} else {
    echo '<p>IPTBLS Table created; Definitions are shown in the table below</p>';
}
mysqli_free_result($tbl);
$req = mysqli_query($link, "SHOW TABLES;");
if (!$req) {
    die("<p>SHOW TABLES request failed: " . mysqli_error($link) . "</p>");
}
echo "<p>Results from SHOW TABLES:</p><ul>";
while ($row = mysqli_fetch_row($req)) {
    echo "<li>" . $row[0] . "</li>";
}
echo "</ul>";
mysqli_free_result($req);
?>
    <p>Description of the IPTBLS table:</p>
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
    $dtbl = mysqli_query($link, "DESCRIBE IPTBLS;");
if (!$dtbl) {
    die("<p>DESCRIBE IPTBLS FAILED: " . mysqli_error($link) . "/p>");
}
    $first = true;
while ($row = mysqli_fetch_row($dtbl)) {
    echo "<tr>";
    for ($i=0; $i<count($row); $i++) {
        echo "<td>" . $row[$i] . "</td>";
    }
    echo "</tr>" . PHP_EOL;
}
    mysqli_free_result($dtbl);
    mysqli_close($link);
?>
       </tbody>
    </table>
    <p>DONE</p>
</div>
</body>
</html>

