<?php
require_once "../mysql/dbFunctions.php";
$link = connectToDb(__FILE__, __LINE__);
?>
<!DOCTYPE html>
<html lang="en-us">

<head>
    <title>Create HIKES Table</title>
    <meta charset="utf-8" />
    <meta name="description" content="Create the HIKES Table" />
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
<p id="trail">Create the HIKES Table</p>
<div style="margin-left:16px;font-size:18px;">
    <p>This script will create the HIKES table in the 'mysql' database...</p>
<?php
    # NOTE: AUTO_INCREMENT seems to have conditional requirements surrounding it, esp PRIMARY KEY
    $tbl = mysqli_query($link, "CREATE TABLE HIKES (
        indxNo smallint NOT NULL AUTO_INCREMENT PRIMARY KEY,
        pgTitle varchar(30) NOT NULL,
        usrid varchar(32) NOT NULL,
        locale varchar(20),
        marker varchar(11),
        collection varchar(15),
        cgroup varchar(3),
        cname varchar(25),
        logistics varchar(12),
        miles decimal(4,2),
        feet smallint(5),
        diff varchar(14),
        fac varchar(30),
        wow varchar(50),
        seasons varchar(12),
        expo varchar(15),
        gpx varchar(1024),
        trk varchar(1024),
        lat double(13,10),
        lng double(13,10),
        aoimg1 varchar(512),
        aoimg2 varchar(512),
        purl1 varchar(1024),
        purl2 varchar(1024),
        dirs varchar(1024),
        tips varchar(4096),
        info varchar(4096));");
if (!$tbl) {
    die("<p>CREATE TABLE failed;  Check error code: " . mysqli_error($link) . "</p>");
} else {
    echo '<p>HIKES Table created; Definitions are shown in the table below</p>';
}
    $req = mysqli_query($link, "SHOW TABLES;");
if (!$req) {
    die("<p>SHOW TABLES request failed: " . mysqli_error($link) . "</p>");
}
    echo "<p>Results from SHOW TABLES:</p><ul>";
while ($row = mysqli_fetch_row($req)) {
    echo "<li>" . $row[0] . "</li>";
}
    echo "</ul>";
?>
    <p>Description of the HIKES table:</p>
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
    $tbl = mysqli_query($link, "DESCRIBE HIKES;");
if (!$tbl) {
    die("<p>DESCRIBE 'test' FAILED: " . mysqli_error($link) . "/p>");
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
