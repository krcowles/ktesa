<?php
/**
 * This script will create an unpopulated USERS table.
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
    <title>Create USERS Table</title>
    <meta charset="utf-8" />
    <meta name="description" content="Create the USERS Table" />
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
<p id="trail">Create the USERS Table</p>
<div style="margin-left:16px;font-size:18px;">
    <p>This script will create the USERS table for site administration.</p>
<?php
$usrtbl = <<<usrtbl
CREATE TABLE USERS (
    userid smallint NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username varchar(32) NOT NULL,
    passwd varchar(255) NOT NULL, 
    passwd_expire date,
    last_name varchar(30) NOT NULL,
    first_name varchar(20) NOT NULL,
    email varchar(50) NOT NULL,
    facebook_url varchar(100),
    twitter_handle varchar(20),
    bio varchar(500) );
usrtbl;
$tbls = $pdo->query("SHOW TABLES;");
$all = $tbls->fetchAll(PDO::FETCH_BOTH);
echo "<p>Results from SHOW TABLES:</p><ul>";
foreach ($all as $row) {
    if ($row[0] === "USERS") {
        die("You must first DROP USERS");
    }
    echo "<li>" . $row[0] . "</li>";
}
echo "</ul>";
try {
    $usrtbl = $pdo->query($usrtbl);
}
catch (PDOException $e) {
    pdoErr("CREATE TABLE USERS", $e);
}
$utbl = $pdo->query("DESCRIBE USERS;");
$u_struct = $utbl->fetchAll(PDO::FETCH_BOTH);
echo '<p>USERS Table created; Definitions are shown in the table below</p>';
?>
    <p>Description of the USERS table:</p>
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
foreach ($u_struct as $row) {
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
