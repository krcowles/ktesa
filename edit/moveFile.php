<?php
/**
 * Move the club asset from the holding area to the released file area;
 * this is only a temporary helper tool.
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.php>
 * @license No license to date
 */
require "../php/global_boot.php";

$file2move = filter_input(INPUT_POST, 'filename');
$region    = filter_input(INPUT_POST, 'region');
$from = "../club_hold/{$file2move}";
$to   = "../club_assets/{$file2move}";
if (!rename($from, $to)) {
    echo "Could not move file";
    exit;
}
// enter info in database
$fileAddReq = "INSERT INTO `CLUB_ASSETS` (`item`, `region`) VALUES (?, ?);";
$fileAdd = $pdo->prepare($fileAddReq);
$fileAdd->execute([$file2move, $region]);
echo "OK";
