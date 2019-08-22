<?php
/**
 * This script authenticates the username/password combo entered by
 * the user when submitting a login requiest. The information is 
 * compared to entries in the USERS table. If the user's browser cookies
 * are turned off, then the script allows login via php session variables.
 * PHP Version 7.1
 * 
 * @package Admin
 * @author  Tom Sandberg and Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

define("UX_DAY", 60*60*24); // unix timestamp value for 1 day
$usrname = filter_input(INPUT_POST, 'usr_name');
$usrpass = filter_input(INPUT_POST, 'usr_pass');
$browser_cookies = filter_input(INPUT_POST, 'browser_cookies');
$cookies = ($browser_cookies === 'ON') ? true : false;
$admin = ($usrname === 'tom' || $usrname === 'kc') ? true : false;
$usr_req = "SELECT username,passwd,passwd_expire FROM USERS WHERE username = :usr;";
$auth = $pdo->prepare($usr_req);
$auth->bindValue(":usr", $usrname);
$auth->execute();
$rowcnt = $auth->rowCount();
if ($rowcnt === 1) {  // located single instance of user
    $user_dat = $auth->fetch(PDO::FETCH_ASSOC);
    if (password_verify($usrpass, $user_dat['passwd'])) {  // user data correct
        if ($admin) {
            $adminExpire = time() + 10 * UX_DAY * 365;  // 10 yrs
            if ($cookies) {
                setcookie('nmh_mstr', 'mstr', $adminExpire, "/");
            } else {
                $_SESSION['loggedin'] = 'mstr';
            }
            echo "ADMIN";
            exit;
        } else {
            $expiration = $user_dat['passwd_expire'];
            $american = str_replace("-", "/", $expiration);
            $expdate = strtotime($american);
            if ($expdate <= time()) {
                echo "EXPIRED";
                exit;
            } else {
                $days = floor(($expdate - time())/UX_DAY);
                if ($days <= 5) {
                    // set current cookie pending renewal
                    setcookie('nmh_id', $usrname, $expdate, "/");
                    echo "RENEW";
                    exit;
                }
            }
            if ($cookies) {
                setcookie('nmh_id', $usrname, $expdate, "/");
            } else {
                $_SESSION['loggedin'] = $usrname;
            }
        }
        echo "LOCATED";
    } else {  // user exists, but password doesn't match:
        echo "BADPASSWD" . $usrpass . ";" . $user_dat['passwd'];
    }
} else {  // not in USER table (or multiple entries for same user)
    echo "FAIL";
}
