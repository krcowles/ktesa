<!DOCTYPE html>
<html lang="en-us">

<head>
    <title>DROP: HIKES Table</title>
    <meta charset="utf-8" />
    <meta name="description" content="Drop HIKES Table" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
</head>

<body>
<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">Create A New Page</p>
<div style="margin-left:16px;font-size:18px;">
    <p>This script will delete the HIKES table from the id140870_hikemaster 
        Database</p>
<?php
require '000mysql_connect.php';   # returns $link as connection
echo "<p>mySql Connection Opened</p>";

echo "<p>Removing previous instantiation of table 'HIKES':</p>";
$remtbl = mysqli_query($link,"DROP TABLE HIKES;");
if (!remtbl) {
    die("<p>Did not delete tbl 'HIKES'; Check error status: " . $mysqli_error($link) . "</p>");
} else {
    if (!$req) {
        die("<p>DB Request Failed: SHOW TABLES" . mysqli_error($link) . "</p>");
    }
    while ($row = mysqli_fetch_row($req)) {
        echo "<li>{$row[0]}</li>";
    }
    echo "</ul>";
    echo "<p>DONE.</p>";;
}
mysqli_close($link);
?>
    
</div>
</body>
</html>