<?php
/**
 * This script authenticates the username/password combo entered by
 * the user when submitting a login requiest. The information is 
 * compared to entries in the USERS table.
 * PHP Version 7.4
 * 
 * @package Ktesa
 * @author  Tom Sandberg <tjsandberg@yahoo.com>
 * @author  Ken Cowles <krcowles29@gmail.com>
 * @license No license to date
 */
require "../php/global_boot.php";

define("UX_DAY", 60*60*24); // unix timestamp value for 1 day
$usrname = filter_input(INPUT_POST, 'usr_name');
$usrpass = filter_input(INPUT_POST, 'usr_pass');
$mstr1 = ($usrname === 'tom') ? true : false;
$mstr2 = ($usrname === 'kc')  ? true : false;

$usr_req = "SELECT `userid`,`passwd`,`passwd_expire` " .
    " FROM `USERS` WHERE `username` = :usr;";
$auth = $pdo->prepare($usr_req);
$auth->bindValue(":usr", $usrname);
$auth->execute();
$rowcnt = $auth->rowCount();
if ($rowcnt === 1) {  // located single instance of user
    $user_dat = $auth->fetch(PDO::FETCH_ASSOC);
    if (password_verify($usrpass, $user_dat['passwd'])) {  // user data correct
        if ($mstr1 || $mstr2) {
            $_SESSION['username'] = 'mstr';
            $_SESSION['userid'] = $mstr1 ? '1' : '2';
            $_SESSION['expire'] = "2050-12-31";
            $adminExpire = time() + 10 * UX_DAY * 365;  // 10 yrs
            if ($mstr1) {
                setcookie('nmh_mstr', 'mstr', $adminExpire, "/");
            } else {
                setcookie('nmh_mstr', 'mstr2', $adminExpire, "/");
            }
            echo "ADMIN";
            exit;
        } else {
            $expiration = $user_dat['passwd_expire'];
            $_SESSION['username'] = $usrname;
            $_SESSION['userid'] = $user_dat['userid'];
            $_SESSION['expire'] = $expiration;
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
            setcookie('nmh_id', $usrname, $expdate, "/");
        }
        echo "LOCATED";
    } else {  // user exists, but password doesn't match:
        echo "BADPASSWD" . $usrpass . ";" . $user_dat['passwd'];
    }
} else {  // not in USER table (or multiple entries for same user)
    echo "FAIL";
}
