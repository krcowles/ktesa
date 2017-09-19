<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>MySql Connect</title>
    <meta charset="utf-8" />
    <meta name="description" content="Use MySql database" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
</head>
<body>
<p>Pulling selected data from HIKES</p>
<?php
require "000mysql_connect.php";

$req = mysqli_query( $link, "SELECT * FROM HIKES" );
if (!$req) {
    die ("Could not SELECT data from HIKES:  " . mysqli_err());
}
echo "<ul>";
while ($row = mysqli_fetch_row($req)) {
        echo "<li>" . $row[0] . "</li>";
    }
echo "</ul>";
mysqli_close($link);
?>
</body>
</html>