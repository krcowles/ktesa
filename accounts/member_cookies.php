<?php
/**
 * Save the user's new choice to accept/reject cookies.
 * NOTE: The menu option to change cookie choice only appears for
 * logged-in members.
 * PHP Version 7.4
 *
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowle29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";

$choice  = filter_input(INPUT_POST, 'choice');

$useCookieReq = "UPDATE `USERS` SET `cookies` = :choice WHERE " .
    "`username` = :uname;";
$useCookie = $pdo->prepare($useCookieReq);
$useCookie->execute(["uname" => $_SESSION['username'], "choice" => $choice]);
$_SESSION['cookies'] = $choice;
