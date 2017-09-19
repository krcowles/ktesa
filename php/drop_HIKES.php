<?php
require '000mysql_connect.php';   # returns $link as connection
echo "<p>Opened</p>";

echo "<p>Removing previous instantiation of table 'HIKES':</p>";
$remtbl = mysqli_query($link,"DROP TABLE HIKES;");
if (!remtbl) {
    die("<p>Did not delete tbl 'HIKES'; Check to see if already deleted" . $mysqli_error($link) . "</p>");
} else {
    echo "<p>Table HIKES removed.</p>";
}
?>
