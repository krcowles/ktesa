<?php
/**
 * This script will process the admin tools request to either delete a
 * page from EHIKES, or to publish it to the main site. The outcome is
 * determined by the query string parameter 'act'
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";

$act = filter_input(INPUT_GET, 'act');
if ($act === 'rel') {
    $msg = "Publish";
} elseif ($act === 'del') {
    $msg = "Delete";
}
$usr = 'mstr';
$age = 'new';
$show = 'all';
$pageType = "Publish";
$getEhikesReq = "SELECT `indxNo` FROM `EHIKES`;";
$ehikes = $pdo->query($getEhikesReq)->fetchAll(PDO::FETCH_ASSOC);
$enos = '[';
foreach ($ehikes as $hike) {
    $enos .= $hike['indxNo'] . ",";
}
$enos =  rtrim($enos, ",") . ']';
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title><?=$msg;?> Hike from EHIKES</title>
    <meta charset="utf-8" />
    <meta name="description" content="Select hike to release from table" />
    <meta name="author" content="Tom Sandberg and Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/tables.css" type="text/css" rel="stylesheet" />
    <script src="../scripts/jquery.js"></script>
</head>

<body>
<script src="../scripts/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "../pages/ktesaPanel.php"; ?>
<p id="trail">EHIKES Available to <?= $msg;?></p>
<p id="active" style="display:none">Admin</p>
<p id="action" style="display:none"><?= $act;?></p>
<?php
require '../php/makeTables.php';
?>
<script type="text/javascript">
    var enos = <?=$enos;?>;
</script>
<script src="../scripts/columnSort.js"></script>
<script src ="reldel.js"></script>

</body>
</html>
