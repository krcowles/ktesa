<?php
/**
 * This script will create an unpopulated EHIKES table.
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
    <title>Create EHIKES Table</title>
    <meta charset="utf-8" />
    <meta name="description" content="Create the <?php echo $table;?> Table" />
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
<p id="trail">Create EHIKES As Parent</p>
<div style="margin-left:16px;font-size:18px;">
    <p>This script will create the EHIKES table as a parent table for the other
        'E-tables' (ETSV, EREFS, EGPSDAT) which will reference EHIKES with
        foreign keys.</p>
<?php
$show = $pdo->query("SHOW TABLES;");
$tbls = $show->fetchAll(PDO::FETCH_BOTH);
echo "<p>Results from SHOW TABLES:</p><ul>";
foreach ($tbls as $row) {
    if ($row[0] === "EHIKES") {
        die("You must first DROP EHIKES");
    }
    echo "<li>" . $row[0] . "</li>";
}
echo "</ul>";
try {
    $pdo->query("CREATE TABLE EHIKES LIKE HIKES");
}
catch (PDOException $e) {
    pdo_err("CREATE TABLE EHIKES", $e);
}
try {
    $pdo->query("ALTER TABLE EHIKES ADD stat VARCHAR(10) AFTER usrid;");
}
catch (PDOException $e) {
    pdo_err("ALTER TABLE EHIKES", $e);
}
?>
    <p>Description of the EGPSDAT table:</p>
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
echo '<p>EHIKES Table created; Definitions are shown in the table below</p>';
$eh_struct = $pdo->query("DESCRIBE EHIKES;");
$struct = $eh_struct->fetchAll(PDO::FETCH_BOTH);
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
