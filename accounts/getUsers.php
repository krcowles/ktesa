<?php
/**
 * Get the list of current users and verify new submission is unique
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

$name = filter_input(INPUT_POST, 'username');
// Get current list of usernames:
$unameReq = "SELECT `username` FROM USERS;";
$users = $pdo->query($unameReq)->fetchAll(PDO::FETCH_COLUMN);
$match = "NO";
foreach ($users as $key => $user) {
    if ($user == $name) {
        $match = "YES";
        break;
    }
}
echo $match;
