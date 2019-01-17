<?php
/**
 * This script will create an unpopulated IPTBLS table.
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
$req = $pdo->query("SHOW TABLES;");
$tbls = $req->fetchAll(PDO::FETCH_BOTH);
echo "<p>Results from SHOW TABLES:</p><ul>";
foreach ($tbls as $row) {
    if ($row[0] === "IPTBLS") {
        die("You must first DROP IPTBLS");
    }
    echo "<li>" . $row[0] . "</li>";
}
echo "</ul>";
try {
    $iptbl = $pdo->query($newips);
}
catch (PDOException $e) {
    pdo_err("CREATE TABLE IPTBLS", $e);
}
$iptbl_struct = $pdo->query("DESCRIBE IPTBLS");
$struct = $iptbl_struct->fetchAll(PDO::FETCH_BOTH);
echo '<p>HIKES Table created; Definitions are shown in the table below</p>';
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
