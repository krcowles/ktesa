<?php
require_once "../mysql/dbFunctions.php";
$link = connectToDb(__FILE__, __LINE__);
$hikeNo = filter_input(INPUT_GET, 'hno');
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Remove Hike from EHIKES</title>
    <meta charset="utf-8" />
    <meta name="description" content="Select hike to release from table" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <link href="../build/tables.css" type="text/css" rel="stylesheet" />
    <link href="../styles/logo.css" type="text/css" rel="stylesheet" />
</head>

<body>
<div id="logo">
    <img id="hikers" src="../images/hikers.png" alt="hikers icon" />
    <p id="logo_left">Hike New Mexico</p>
    <img id="tmap" src="../images/trail.png" alt="trail map icon" />
    <p id="logo_right">w/Tom &amp; Ken</p>
</div>
<p id="trail">Remove EHIKE <?php echo $hikeNo;?></p>
<div style="margin-left:16px;font-size:20px;">
<?php
    echo '<p style="font-size:24px;color:brown;">UNDER CONSTRUCTION</p>';
?>
    </div>
<script src="../scripts/jquery-1.12.1.js"></script>
<script src ="delete.js"></script>
</body>
</html>
