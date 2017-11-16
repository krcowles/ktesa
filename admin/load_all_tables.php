<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Load All Tables</title>
    <meta charset="utf-8" />
    <meta name="description" content="Present tools for admin of site" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <link href="admintools.css" type="text/css" rel="stylesheet" />
<body>
<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>	
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">Loading Database</p>
<div style="margin-left:16px;">
<p>Please wait until the 'DONE' message appears below</p>
<?php
require_once '../mysql/setenv.php';
// Temporary variable, used to store current query
$templine = '';
// Read in entire file
$lines = file("../data/database.sql");
// Loop through each line
$gottbl = false;
foreach ($lines as $line) {
    // Skip it if it's a comment
    if (substr($line, 0, 2) == '--' || $line == '') {
        continue;
    }
    // Add this line to the current segment
    $templine .= $line;
    // If it has a semicolon at the end, it's the end of the query
    if (substr(trim($line), -1, 1) == ';') {
        // look for create and table name
        $createTbl = strpos($templine,"TABLE");
        if ($createTbl !== false) {
            $tblLgth = strpos($templine,'(') - 3 - ($createTbl + 13);
            $tblName = substr($templine,$createTbl+14,$tblLgth);
            $gottbl = true;
        }
        // Perform the query
        $req = mysqli_query($link, $templine);
        if (!$req) {
            die ("<p>load_all_tables.php: Failed: " .
                mysqli_error($link) . "</p>");
        }
        if ($gottbl) {
            $gottbl = false;
            echo "<br>Submitted query " . substr($templine,0,64) . date('l jS \of F Y h:i:s A');
            flush();
        }
        $templine = '';    // Reset temp variable to empty
    }
}
mysqli_free_result($req);
mysqli_close($link);
?>
<p>DONE: Tables imported successfully</p>
</div>
