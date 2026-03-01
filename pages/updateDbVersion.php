<?php
/**
 * Set the user's sw-ver to current version in the db.
 * This will then by-pass the updater on subsequent visits.
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";
$userid = filter_input(INPUT_POST, 'user');
$latest = filter_input(INPUT_POST, 'latest');

$verReq = "UPDATE `USERS` SET `sw_ver`=? WHERE `userid`=?;";
$user_update = $pdo->prepare($verReq);
try {
    $user_update->execute([$latest, $userid]);
} catch (PDOException $pdo_err) {
    echo $pdo_err->getMessage();
    exit;
}
echo "ok";
