<?php
/**
 * This script may be called either as a result of page load (getLogin.php)
 * or via the validateUser function (ajax). The latter requires query string
 * parameters to define user.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
session_start();
require "../php/global_boot.php";
define("UX_DAY", 60*60*24); // unix timestamp value for 1 day

$username = isset($_POST['usr_name']) ?
    filter_input(INPUT_POST, 'usr_name') : $_SESSION['username'];
$userpass = isset($_POST['usr_pass']) ? filter_input(INPUT_POST, 'usr_pass') : false;

// retrieve required user data
$usr_req = "SELECT `userid`,`passwd`,`passwd_expire`,`facebook_url`" .
    " FROM `USERS` WHERE `username` = :usr;";
$auth = $pdo->prepare($usr_req);
$auth->execute(["usr" => $username]);
$rowcnt = $auth->rowCount();
if ($rowcnt === 1) {  // located single instance of user
    $user_dat = $auth->fetch(PDO::FETCH_ASSOC);
    if (password_verify($userpass, $user_dat['passwd'])) {  // user data correct
        if ($username == 'kc' || $username == 'tom') {
            $_SESSION['username'] = $username;
            $_SESSION['userid'] = $user_dat['userid'];
            $_SESSION['expire'] = "2050-12-31";
            $_SESSION['cookie_state'] = "OK";
            $_SESSION['cookies'] = 'accept';
            $adminExpire = time() + 10 * UX_DAY * 365;  // 10 yrs
            $admin_cookie = $user_dat['userid'] == 1 ? 'mstr' : 'mstr2';
            setcookie(
                'nmh_mstr', $admin_cookie, $adminExpire, "/", "", true, true
            );
            echo "ADMIN";
            exit;
        } else {
            $expiration = $user_dat['passwd_expire'];
            $_SESSION['username'] = $username;
            $_SESSION['userid'] = $user_dat['userid'];
            $_SESSION['expire'] = $expiration;
            $_SESSION['cookie_state'] = "OK";
            $_SESSION['cookies'] = $user_dat['facebook_url'];
            $american = str_replace("-", "/", $expiration);
            $expdate = strtotime($american);
            if ($expdate <= time()) {
                echo "EXPIRED";
                exit;
            } else {
                $days = floor(($expdate - time())/UX_DAY);
                if ($days <= 5) {
                    if ($user_dat['facebook_url'] === 'accept') {
                        // set current cookie pending renewal
                        setcookie(
                            "nmh_id", $username, $expdate, "/", "", true, true
                        );
                    }
                    echo "RENEW";
                    exit;
                }
            }
            if ($user_dat['facebook_url'] === 'accept') {
                setcookie(
                    "nmh_id", $username, $expdate, "/", "", true, true
                );
            }
        }
        echo "LOCATED";
    } else {  // user exists, but password doesn't match:
        echo "BADPASSWD" . $userpass . ";" . $user_dat['passwd'];
    }
} else {  // not in USER table (or multiple entries for same user)
    echo "FAIL";
}
