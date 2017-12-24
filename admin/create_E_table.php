<?php
require_once "../mysql/dbFunctions.php";
$link = connectToDb($file, $line);
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
echo "<p>mySql Connection Opened</p>";
$tblreq = "CREATE TABLE {$table} LIKE {$reftbl};";
$tbl = mysqli_query($link, $tblreq);
if (!$tbl) {
    die("<p>create_E_table.php: Failed to create {$table}: " .
        mysqli_error($link));
}
$childreq = "ALTER TABLE {$table} ADD CONSTRAINT {$table}_Constraint " .
"FOREIGN KEY FK_{$table}(indxNo) REFERENCES EHIKES(indxNo) " .
"ON DELETE CASCADE ON UPDATE CASCADE;";
$child = mysqli_query($link, $childreq);
if (!$child) {
    die("<p>CREATE {$table} failed: " . mysqli_error($link) . "</p>");
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
$req = mysqli_query($link, "SHOW TABLES;");
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
    $tbl = mysqli_query($link, "DESCRIBE {$table};");
if (!$tbl) {
    die("<p>DESCRIBE {$table} FAILED: " . mysqli_error($link) . "/p>");
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

