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
<?php
$usr = 'mstr';
$age = 'new';
$show = 'rel';
$reldel = true;
require '../php/TblConstructor.php';
?>
<script src="../scripts/jquery-1.12.1.js"></script>
<script src ="release.js"></script>
</body>
</html>