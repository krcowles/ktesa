<?php
/**
 * Save the user's choice to accept cookies.
 * PHP Version 7.4
 *
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowle29@gmail.com>
 * @license No license to date
 */
session_start();

if (isset($_SESSION['username'])) {
    $useCookieReq = "UPDATE `USERS` SET `opt` = 'Y' WHERE `username` = :uname;";
    $useCookie = $pdo->prepare($useCookieReq);
    $useCookie->execute(["uname" => $_SESSION['username']]);
    echo "SAVED";
} else {
    // what to do when a non-user (or not-logged-in user) accepts?
    echo '<script type="text/javascript">alert("No cookies are saved until ' .
        '"you become a member\nIf you are a member, you must login to save ' .
        'your choice")</script>';
}
