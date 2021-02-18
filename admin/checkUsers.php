<?php
/**
 * Compare the old and current USERS table to see if new users have
 * been added. If so, alert the admin during download.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
verifyAccess('ajax');

$currentUsersReq = "SELECT COuNT(*) FROM `USERS`;";
$pastUsersReq    = "SELECT COUNT(*) FROM `LKUSERS`;";
$currentUsers = $pdo->query($currentUsersReq)->fetch(PDO::FETCH_NUM);
$pastUsers    = $pdo->query($pastUsersReq)->fetch(PDO::FETCH_NUM);
$delta = $currentUsers[0] - $pastUsers[0];
echo $delta;
