<!DOCTYPE html>
<html lang="en-us">

<head>
    <title>Delete a Row</title>
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
<p id="trail">Delete Row From HIKES Table</p>
<div style="margin-left:16px;font-size:18px;">

<?php
# Error message:
$drop_fail = "<p>Could not delete the specified row: " . mysqli_error($link) . "</p>";

# Get input:
$rowno = filter_input(INPUT_GET,'indx');

# Connect:
$dev = $_SERVER['SERVER_NAME'] == 'localhost' ? true : false;
if ($dev) {
    $rel_addr = '../mysql/';
    require_once "../mysql/local_mysql_connect.php";
} else {
    $rel_addr = '../php/';
    require_once "../php/000mysql_connect.php";
}

# Execute the DROP TABLE command:
$remrow = mysqli_query($link,"DELETE FROM HIKES WHERE indxNo = " . $rowno . ";");
if (!remrow) {
    die ($drop_fail);
} else {
    echo "<p>Row " . $rowno . " successfully removed; </p>";
}

mysqli_close($link);
?>
    
</div>
</body>
</html>
