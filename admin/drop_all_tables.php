<?php
require_once "../mysql/dbFunctions.php";
$link = connectToDb(__FILE__, __LINE__);
$qty = filter_input(INPUT_GET, 'no');
# --------- the following is a terrible way to do this --- change later
if ($qty === 'all') {
    $action = 'ALL Tables';
    $strt = 0;
} else {
    $action = 'All E-Tables';
    $strt = 6;
}
# --------- change the above
$table = array('USERS','HIKES','TSV','REFS','GPSDAT','IPTBLS',
    'ETSV','EREFS','EGPSDAT','EHIKES','tmpPix'); # NOTE: E-tables are order-sensitive
$tblcnt = count($table); # total number of hike tables

?>
<!DOCTYPE html>
<html lang="en-us">

<head>
    <title>DROP <?php echo $action;?></title>
    <meta charset="utf-8" />
    <meta name="description" content="Drop the specified Tables" />
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
<p id="trail">DROP <?php echo $action;?></p>
<div style="margin-left:16px;font-size:18px;">

<?php
# Error messages:
$query_fail = "<p>Query did not succeed: SHOW TABLES</p>";
# Execute the DROP TABLE command for chosen tables:
for ($i=$strt; $i<$tblcnt; $i++) {
    echo "<p>Removing any previous instantiation of table '{$table[$i]}':</p>";
    $remtbl = mysqli_query($link, "DROP TABLE {$table[$i]};");
    if (!$remtbl) {
        die("<p>drop_all_tables.php: Failed to drop {$table[$i]}: " .
            mysqli_error($link) . "</p>");
    } else {
        echo "<p>{$table[$i]} Table Removed</p>";
    }
}
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
