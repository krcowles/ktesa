<?php
/**
 * This script is only accessed when using a mobile device. The
 * invocation of this script by the user will determine membership.
 * Internet connection is required up to this point. Members will
 * be pointed to member_landing.php which installs the service
 * worker and offline caches. After that installation, 'offline first'
 * is in play, meaning cached assets will respond to the selected
 * fetches instead of fetching from the server. Non-members will
 * proceed without offline access. 
 * PHP Version 8.3.9
 * 
 * @package Ktesa
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
require_once "../accounts/getLogin.php";
// Without this, debug is painful!
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
//header("Expires: 0");

if ($_SESSION['cookie_state'] === "OK") {
    // The user is a member:
    $current_version = "1.0"; // set by admin in admintools
    $user = $_SESSION['userid'];
    $versionReq = "SELECT `sw_ver` FROM `USERS` WHERE `userid`='{$user}' LIMIT 1;";
    $version = $pdo->query($versionReq)->fetchColumn(0);
    if ($version !== $current_version) {
        /**
         * Note: all users will get the 'update' at first,
         * as the database has initialized all sw_ver's to '0.0'
         */
        $updater = "./update.php?ver={$current_version}&usr={$_SESSION['userid']}";
        header("Location:{$updater}", true);
    } else {
        $member = "../pages/member_landing.html"; 
        header("Location:{$member}", true);
    }
} else {
    // User is not a member
    $nonmember = "../pages/nonmember_landing.php";
    header("Location:{$nonmember}", true);
}
