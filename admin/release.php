<?php
require_once '../mysql/setenv.php';
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Move Hike from EHIKES</title>
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
<p id="trail">List All EHIKES</p>
<p id="action" style="display:none">Release</p>
<?php
$usr = 'mstr';
$age = 'new';
$show = 'rel';
$rel = true;
$del = false;
require '../php/TblConstructor.php';
?>
<script src="../scripts/jquery-1.12.1.js"></script>
<script src ="release.js"></script>
</body>
</html>