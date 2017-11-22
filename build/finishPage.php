<?php
require_once "../mysql/setenv.php";
$hikeNo = filter_input(INPUT_GET,'hno');
$namereq = "SELECT pgTitle from EHIKES WHERE indxNo = {$hikeNo};";
$name = mysqli_query($link,$namereq);
if (!$name) {
    die("finishPage.php: Failed to retrieve hike name from EHIKES for hike {$hikeNo}: " .
        mysqli_error($link));
}
$nameDat = mysqli_fetch_row($name);
$hikeName = $nameDat[0];

?>
<!DOCTYPE html>
<html lang="en-us">
    <head>
        <title>Complete Hike</title>
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
    <p id="trail">Complete the Hike Creation Process</p>
    <p id="ptype" style="display:none">Finish</p>
    
    <p id="ptype" style="display:none">Finish</p>
    <form target="_blank" action="displayHikePg.php" method="POST">
    <div style="margin-left:16px;">
    <?php
    $ptable = 'ETSV';
    $pgType = 'Finish';
    require "photoSelect.php";
    ?>
    </div>
    </form>
    
    <script src="validateHike.js"></script>
    </body>
</html>