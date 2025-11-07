<?php
/**
 * This script is only accessed when using a mobile device. The
 * invocation of this script by the user will determine membership.
 * Internet connection is required up to this point. Members will
 * be pointed to member_landing.php which installs the service
 * worker and offline cache. After that installation, 'offline first'
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
if ($_SESSION['cookie_state'] === "OK") {
    $redirect = "../pages/member_landing.html";
} else {
    $redirect = "../pages/nonmember_landing.html";
}
header("Location:{$redirect}", true);
