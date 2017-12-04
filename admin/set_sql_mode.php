<?php

//$qstr = "SET sql_mode = 'ONLY_FULL_GROUP_BY,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'";
$qstr = "SET sql_mode = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'";
$req = mysqli_query($link, $qstr);
if (!$req) {
    die ("<p>sql_mode.php 1: Failed: " .
        mysqli_error($link) . "</p>");
}

$qstr = "SHOW VARIABLES LIKE 'sql_mode';";
$req = mysqli_query($link, $qstr);
if (!$req) {
    die ("<p>sql_mode.php 2: Failed: " .
        mysqli_error($link) . "</p>");
}
//echo "<p>Results from SHOW VARIABLES:<br>";
//while ($row = mysqli_fetch_row($req)) {
//    echo "$row[0]: $row[1] <br>";
//}

//echo ' done <br>';
