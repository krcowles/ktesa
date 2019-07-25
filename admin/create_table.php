<?php
/**
 * This script will create an unpopulated table based on the 
 * table specified in the query string.
 * PHP Version 7.1
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

$errmsg = '';
$etbl = false;
$table = filter_input(INPUT_GET, 'tbl');
if (substr($table, 0, 1) === 'E') {
    $etbl = true;
    $reftbl = substr($table, 1, (strlen($table)-1));
    $query = "CREATE TABLE {$table} LIKE {$reftbl};";
    if ($table === "EHIKES") {
        $alt = "ALTER TABLE EHIKES ADD stat VARCHAR(10) AFTER usrid;";
    } else {
        $alt = "ALTER TABLE {$table} ADD CONSTRAINT {$table}_Constraint " .
            "FOREIGN KEY FK_{$table}(indxNo) REFERENCES EHIKES(indxNo) " .
            "ON DELETE CASCADE ON UPDATE CASCADE;";
    }
} else {
    switch ($table)
    {
    case "BOOKS":
        $query = "CREATE TABLE BOOKS (
            indxNo smallint NOT NULL AUTO_INCREMENT PRIMARY KEY,
            title varchar(200) NOT NULL,
            author varchar(200) NOT NULL);";
        break;
    case "EHIKES":
        $query = "CREATE TABLE EHIKES LIKE HIKES;";
        $ehike = true;
        break;
    case "EGPSDAT":

    case "GPSDAT":
        $query = "CREATE TABLE GPSDAT (
            datId smallint NOT NULL AUTO_INCREMENT PRIMARY KEY,
            indxNo smallint,
            datType varchar(1),
            label varchar(128),
            url varchar(1024),
            clickText varchar(256) );";
        break;
    case "HIKES":
        $query = "CREATE TABLE HIKES (
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
            info varchar(4096) );";
        break;
    case "IPTBLS":
        $query = "CREATE TABLE IPTBLS (
            ipIndx smallint NOT NULL AUTO_INCREMENT PRIMARY KEY,
            indxNo smallint,
            compl varchar(1),
            tdname varchar(30),
            tdpg varchar(5),
            tdmiles decimal(4,2),
            tdft smallint(5),
            tdexp varchar(15),
            tdalb varchar(1024) );";
        break;
    case "REFS":
        $query = "CREATE TABLE REFS (
            refId smallint NOT NULL AUTO_INCREMENT PRIMARY KEY,
            indxNo smallint,
            rtype varchar(30),
            rit1 varchar(1024),
            rit2 varchar(512) );";
        break;
    case "TSV":
        $query = "CREATE TABLE TSV (
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
            org varchar(1024) );";
        break;
    case "USERS":
        $query = "CREATE TABLE USERS (
            userid smallint NOT NULL AUTO_INCREMENT PRIMARY KEY,
            username varchar(32) NOT NULL,
            passwd varchar(255) NOT NULL, 
            passwd_expire date,
            last_name varchar(30) NOT NULL,
            first_name varchar(20) NOT NULL,
            email varchar(50) NOT NULL,
            facebook_url varchar(100),
            twitter_handle varchar(20),
            bio varchar(500) );";
        break;
    default:
        $errmsg = "Table type not supported";
    }
}
if ($errmsg !== '') {
    throw new Exception($errmsg);
}
$list = showTables($pdo, $table);
if ($list[1] !== '') {
    throw new Exception($list[1]);
}
$show = $list[0];
$tbl = $pdo->query($query);
if ($etbl) {
    $pdo->query($alt);
}
$tbldat = describeTable($pdo, $table);
?>
<!DOCTYPE html>
<html lang="en-us">

<head>
    <title>Create <?= $table;?> Table</title>
    <meta charset="utf-8" />
    <meta name="description" content="Create the specified table" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <link href="showTbls.css" type="text/css" rel="stylesheet" />
</head>

<body>
<?php require "../pages/pageTop.php"; ?>
<p id="trail">Create the <?= $table;?> Table</p>
<div style="margin-left:16px;font-size:18px;">
    <p>This script will create the <?= $table;?> table in the database...</p>
    <p>Results from SHOW TABLES (prior to adding <?= $table;?>):</p>
    <ul>
    <?php for($i=0; $i<count($show); $i++) : ?>
        <li><?= $show[$i];?></li>
    <?php endfor; ?>
    </ul>
    <p><?= $table;?> Table created; Definitions are shown in the table below</p>
    <p>Description of the <?= $table;?> table:</p>
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
        <?php foreach ($tbldat as $row) : ?>
            <tr>
            <?php for ($i=0; $i<count($row); $i++) : ?>
                <td><?= $row[$i];?></td>
            <?php endfor; ?>
        <?php endforeach; ?>
            </tr>
        </tbody>
    </table>
    <p>DONE</p>
</div>

</body>
</html>
