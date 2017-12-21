<?php
require_once "../mysql/setenv.php";
$hikeNo = filter_input(INPUT_GET, 'hno');
$namereq = "SELECT pgTitle from EHIKES WHERE indxNo = {$hikeNo};";
$name = mysqli_query($link, $namereq);
if (!$name) {
    die("finishPage.php: Failed to retrieve hike name from EHIKES for hike {$hikeNo}: " .
        mysqli_error($link));
}
$nameDat = mysqli_fetch_row($name);
$hikeName = $nameDat[0];
# Determine whether or not there are any images to display:
$imgReq = "SELECT hpg FROM ETSV WHERE indxNo = {$hikeNo};";
$imgs = mysqli_query($link, $imgReq);
if (!$imgs) {
    die("finishPage.php: Failed to get img count from ETSV for hike {$hikeNo}: " .
        mysqli_error($link));
}
$picCnt = mysqli_num_rows($imgs);
mysqli_free_result($imgs);
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
    <p style="margin-left:16px;">To complete the Hike for <?php echo $hikeName;?>,
        submit photo selections for the page and map, if photos are present<p>
    <form target="_blank" action="displayHikePg.php" method="POST">
    <div style="margin-left:16px;">
    <?php
    if ($picCnt > 0) {
        $pics = 'YES';
        $ptable = 'ETSV';
        $pgType = 'Finish';
        require "photoSelect.php";
    } else {
        $pics = 'NO';
        echo '<br /><input style="font-size:18px" type="submit" ' .
            'value="Create Page w/No Photos" />' . PHP_EOL;
    }
    ?>
    </div>
    <input type="Hidden" name="hikeno" value="<?php echo $hikeNo;?>" />
    <input type="Hidden" name="usepics" value="<?php echo $pics;?>" />
    </form>
    
    <script src="validateHike.js"></script>
    </body>
</html>