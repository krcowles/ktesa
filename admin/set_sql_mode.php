<?php
$qstr = 'SET sql_mode = "';
$callpath = getcwd();
$subdir = strrpos($callpath,"/") + 1;
$baseaddr = substr($callpath,0,$subdir) . 'admin/';
$modes = file($baseaddr . 'sql_modes.ini',FILE_IGNORE_NEW_LINES);
foreach ($modes as $setting) {
    if (substr($setting,0,1) == 'Y') {
        $qstr .= substr($setting,2,strlen($setting)-2) . ",";
    }
}
$qstr = substr($qstr,0,strlen($qstr)-1) . '";';
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
/*
echo "<p>Results from SHOW VARIABLES:<br>";
while ($row = mysqli_fetch_row($req)) {
    echo "$row[0]: $row[1] <br>";
}
echo ' done <br>';
*/
