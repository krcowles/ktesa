<?php
/**
 * This script will create an unpopulated HIKES table.
 * PHP Version 7.1
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
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
$hike_tbl = <<<htbl
CREATE TABLE HIKES (
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
    info varchar(4096) );
htbl;
$tbls = $pdo->query("SHOW TABLES;");
$all_tbls = $tbls->fetchAll(PDO::FETCH_BOTH);
echo "<p>Results from SHOW TABLES:</p><ul>";
foreach ($all_tbls as $row) {
    if ($row[0] === "HIKES") {
        die("You must first drop HIKES");
    }
    echo "<li>" . $row[0] . "</li>";
}
echo "</ul>";
try {
    $pdo->query($hike_tbl);
}
catch (PDOException $e) {
    pdoErr("CREATE TABLE HIKES", $e);
}
echo '<p>HIKES Table created; Definitions are shown in the table below</p>';
$htbl = $pdo->query("DESCRIBE HIKES;");
$htbl_struct = $htbl->fetchAll(PDO::FETCH_BOTH);
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
foreach ($htbl_struct as $row) {
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
