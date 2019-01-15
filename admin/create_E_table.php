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
$table = filter_input(INPUT_GET, 'tbl');
$reftbl = substr($table, 1, (strlen($table)-1));
?>
<!DOCTYPE html>
<html lang="en-us">

<head>
    <title>Create <?php echo $table;?> Table</title>
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
<p id="trail">Create the <?php echo $table;?> Table</p>
<div style="margin-left:16px;font-size:18px;">
    <p>This script will create the <?php echo $table;?> table for site administration.</p>
<?php
$show = $pdo->query("SHOW TABLES;");
$tbls = $show->fetchAll(PDO::FETCH_BOTH);
echo "<p>Results from SHOW TABLES:</p><ul>";
foreach ($tbls as $row) {
    if ($row[0] === $table) {
        die("You must first DROP {$table}");
    }
    echo "<li>" . $row[0] . "</li>";
}
echo "</ul>";
try {
    $pdo->query("CREATE TABLE {$table} LIKE {$reftbl};");
}
catch (PDOException $e) {
    pdo_err("CREATE TABLE {$table}", $e);
}
$childreq = "ALTER TABLE {$table} ADD CONSTRAINT {$table}_Constraint " .
"FOREIGN KEY FK_{$table}(indxNo) REFERENCES EHIKES(indxNo) " .
"ON DELETE CASCADE ON UPDATE CASCADE;";
try {
    $pdo->query($childreq);
}
catch (PDOException $e) {
    pdo_err("ALTER TABLE {$table}", $e);
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
$tbl = $pdo->query("DESCRIBE {$table};");
$tstruct = $tbl->fetchAll(PDO::FETCH_BOTH);
foreach ($tstruct as $row) {
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
