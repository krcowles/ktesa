<!DOCTYPE html>
<html lang="en-us">
    
<head>
    <title>Photo Test Driver</title>
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
    <link href="validateHike.css" type="text/css" rel="stylesheet" />
</head>

<body>
<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>	
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">Photo Test Driver</p>
<div id="photoSelector">
    <p id="ptype" style="display:none">Edit</p>
<?php
$hikeNo = 5;
$ptable = 'ETSV';
include "photoSelect.php";
?>
</div>
</body>
</html>
