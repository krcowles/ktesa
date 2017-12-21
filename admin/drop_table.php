<?php
require_once '../mysql/setenv.php';
$table = filter_input(INPUT_GET, 'tbl');
?>
<!DOCTYPE html>
<html lang="en-us">

<head>
    <title>DROP <?php echo $table;?></title>
    <meta charset="utf-8" />
    <meta name="description" content="Drop the specified Table" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <style type="text/css">
        body {background-color: #eaeaea;}
    </style>
</head>

<body>
<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">DROP <?php echo $table;?> Table</p>
<div style="margin-left:16px;font-size:18px;">

<?php
# Error messages:
$drop_fail = "<p>Could not delete tbl '{$table}': " . mysqli_error($link) . "</p>";
$query_fail = "<p>Query did not succeed: SHOW TABLES</p>";
# Execute the DROP TABLE command:
echo "<p>Removing any previous instantiation of table '{$table}':</p>";
$remtbl = mysqli_query($link, "DROP TABLE {$table};");
if (!remtbl) {
    die($drop_fail);
} else {
    echo "<p>{$table} Table Removed; Remaining tables in mysql database:</p>";
}
mysqli_free_result($remtbl);
$req = mysqli_query($link, "SHOW TABLES");
if (!$req) {
    die($query_fail);
}
echo "<ul>\n";
while ($row = mysqli_fetch_row($req)) {
    echo "<li>" . $row[0] . "</li>\n";
}
echo "</ul>\nDONE";
mysqli_free_result($req);
mysqli_close($link);
?>
    
</div>
</body>
</html>