<?php
require "000mysql_connect.php";

$req = mysqli_query( $link, "SELECT * FROM HIKES WHERE indxNo = 137" );
if (!$req) {
    die ("Could not SELECT data from HIKES:  " . mysqli_err());
}
echo "<ul>";
while ($row = mysqli_fetch_row($req)) {
    for ($j=0; $j<count($row); $j++) {
        echo "<li>" . $row[$j] . "</li>";
    }
}
echo "</ul>";
mysqli_close($link);
?>

