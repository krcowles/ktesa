<?php
require_once "../mysql/dbFunctions.php";
$link = connectToDb($file, $line);
$act = filter_input(INPUT_GET, 'act');
if ($act === 'rel') {
    $msg = "Publish";
} elseif ($act === 'del') {
    $msg = "Remove";
}
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title><?php echo $msg;?> Hike from EHIKES</title>
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
<p id="trail">EHIKES Available to <?php echo $msg;?></p>
<p id="action" style="display:none"><?php echo $act;?></p>
<?php
$usr = 'mstr';
$age = 'new';
$show = 'all';
require '../php/TblConstructor.php';
?>
<script src="../scripts/jquery-1.12.1.js"></script>
<script type="text/javascript">
    var enos = <?php echo $enos;?>;
</script>
<script src ="reldel.js"></script>
</body>
</html>
