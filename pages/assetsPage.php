<?php
/**
 * Create a page listing all the club assets for the chosen region;
 * NOTE: Duplicate file names are not permitted...
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
$area = filter_input(INPUT_GET, 'area');
// intial load for testing:
/*
for ($j=0; $j<4; $j++) {
    $file = $j . 'gpx';
    $case = 'this is a test for ' . $j;
    $prep = "INSERT INTO `CLUB_ASSETS` (`item`,`region`,`description`) " .
        "VALUES ('{$file}','{$area}','{$case}');";
    $pdo->query($prep);
}
*/

$req = "SELECT `item`,`description` FROM `CLUB_ASSETS` WHERE `region`=?;";
$prep = $pdo->prepare($req);
$prep->execute([$area]);
$items = $prep->fetchAll(PDO::FETCH_KEY_PAIR);
$links = [];
foreach ($items as $file => $desc) {
    $link = '<a href="../club_assets/' . $file . '" download>' .
        $file . ': ' . $desc . '</a><br />';
    array_push($links, $link);
}
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <title>Club Assets -> Region <?=$area;?></title>
    <meta charset="utf-8" />
    <meta name="description" content="'About' Page" />
    <meta name="author" content="Ken Cowles" />
    <meta name="robots" content="nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="../styles/bootstrap.min.css" rel="stylesheet" />
    <link href="../styles/assetsPage.css" rel="stylesheet" />
    <?php require "../pages/iconLinks.html"; ?>
    <script src="../scripts/jquery.js"></script>
</head>
<body>

<script src="../scripts/popper.min.js"></script>
<script src="../scripts/bootstrap.min.js"></script>
<?php require "ktesaPanel.php"; ?>
<p id="active" style="display:none;">Assets</p>
<p id="region" style="display:none;"><?=$area;?></p>
<div id="contents">
    <h5>Click on an item to download it</h5>
    <?php for ($i=0; $i<count($links); $i++) : ?>
        <?=$links[$i];?>
    <?php endfor; ?>
</div>

</body>
</html>