
<!DOCTYPE html>
<html lang="en-us">

<head>
    <title>DROP USERS</title>
    <meta charset="utf-8" />
    <meta name="description" content="Drop the HIKES Table" />
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
<p id="trail">DROP USERS Table</p>
<div style="margin-left:16px;font-size:18px;">

<?php
# Error messages:
$drop_fail = "<p>Could not delete tbl 'USERS': " . mysqli_error($link) . "</p>";
$query_fail = "<p>Query did not succeed: SHOW TABLES</p>";

# Connect:
include 'local_mysql_connect.php';

# Execute the DROP TABLE command:
echo "<p>Removing any previous instantiation of table 'USERS':</p>";
$remtbl = mysqli_query($link,"DROP TABLE USERS;");
if (!remtbl) {
    die ($drop_fail);
} else {
    echo "<p>USERS Table Removed; Remaining tables in mysql database:</p>";
}

$req = mysqli_query($link,"SHOW TABLES");
if (!$req) {
    die ($query_fail);
}
echo "<ul>\n";
while ($row = mysqli_fetch_row($req)) {
    echo "<li>" . $row[0] . "</li>\n";
}
echo "</ul>\nDONE";
mysqli_close($link);
?>
    
</div>
</body>
</html>